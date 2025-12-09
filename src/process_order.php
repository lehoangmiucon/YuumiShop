<?php
require_once 'includes/db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['temp_order'])) {
    header("Location: index.php");
    exit;
}

$info = $_SESSION['temp_order'];
$user_id = $_SESSION['user_id'];
$total_cart_value = 0; // Tính lại tổng giá trị giỏ hàng gốc để validate
foreach ($_SESSION['cart'] as $item) {
    $total_cart_value += $item['price'] * $item['qty'];
}

$payment_method = $info['payment_method'];
$status = ($payment_method == 'VNPAY') ? 'paid' : 'pending';

// --- LOGIC XỬ LÝ ĐIỂM (SERVER SIDE VALIDATION) ---
$points_used = isset($info['points_used']) ? intval($info['points_used']) : 0;

// Validate 1: Khách có đủ điểm không?
$stmtUser = $conn->prepare("SELECT points FROM users WHERE id = ?");
$stmtUser->execute([$user_id]);
$userPoints = $stmtUser->fetchColumn();

if ($points_used > $userPoints) {
    die("Lỗi: Số điểm sử dụng vượt quá số điểm hiện có!");
}

// Validate 2: Kiểm tra giới hạn 20% (Safe Mode)
$max_allowed_points = floor(($total_cart_value * 0.2) / 100); 
if ($points_used > $max_allowed_points) {
    // Nếu hack HTML để gửi số điểm cao hơn quy định -> Reset về max cho phép
    $points_used = $max_allowed_points; 
}

$discount_amount = $points_used * 100; // 1 điểm = 100đ
$final_total = $total_cart_value - $discount_amount;

// Đảm bảo không âm
if ($final_total < 0) $final_total = 0;

try {
    $conn->beginTransaction();

    // 1. Lưu Orders
    $stmt = $conn->prepare("INSERT INTO orders (user_id, fullname, phone, address, payment_method, total_amount, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $info['fullname'], $info['phone'], $info['address'], $payment_method, $final_total, $status]);
    $order_id = $conn->lastInsertId();

    // 2. Lưu Order Items
    $sql_item = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
    $stmt_item = $conn->prepare($sql_item);
    foreach ($_SESSION['cart'] as $pid => $item) {
        $stmt_item->execute([$order_id, $pid, $item['qty'], $item['price']]);
    }

    // 3. TRỪ ĐIỂM ĐÃ DÙNG (Không cộng điểm ở đây nữa!)
    if ($points_used > 0) {
        $stmt_minus = $conn->prepare("UPDATE users SET points = points - ? WHERE id = ?");
        $stmt_minus->execute([$points_used, $user_id]);
    }

    // *** LƯU Ý: Đã xóa đoạn cộng điểm ở đây theo yêu cầu Safe Mode ***
    // Điểm sẽ được cộng ở admin/orders.php khi đơn hàng chuyển sang 'shipped'

    // 4. Xóa giỏ hàng
    $conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
    
    $conn->commit();
    unset($_SESSION['cart']);
    unset($_SESSION['temp_order']);

    $_SESSION['flash_msg'] = ['msg' => 'Thanh toán thành công!', 'type' => 'success'];
    header("Location: order_success.php?id=$order_id");

} catch (Exception $e) {
    $conn->rollBack();
    die("Lỗi xử lý đơn hàng: " . $e->getMessage());
}
?>