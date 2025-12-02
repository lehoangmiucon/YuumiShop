<?php
require_once 'includes/db.php';

if (!isset($_SESSION['cart']) || empty($_SESSION['cart']) || !isset($_SESSION['temp_order'])) {
    header("Location: index.php");
    exit;
}

$info = $_SESSION['temp_order'];
$user_id = $_SESSION['user_id'];
$total = $info['total_amount'];
$payment_method = $info['payment_method'];
$status = ($payment_method == 'VNPAY') ? 'paid' : 'pending';

// Logic dùng điểm (nếu có)
$points_used = isset($info['points_used']) ? intval($info['points_used']) : 0;
$discount_amount = $points_used * 1000; // 1 điểm = 1000đ
$final_total = $total - $discount_amount;

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

    // 3. XỬ LÝ ĐIỂM THƯỞNG
    
    // Trừ điểm đã dùng (nếu có)
    if ($points_used > 0) {
        $stmt_minus = $conn->prepare("UPDATE users SET points = points - ? WHERE id = ?");
        $stmt_minus->execute([$points_used, $user_id]);
    }

    // Cộng điểm mới (Chỉ cộng khi thanh toán VNPAY hoặc Admin xác nhận Paid)
    // Tỷ lệ: 100,000 VND = 10 điểm
    if ($status == 'paid') {
        $points_earned = floor($final_total / 100000);
        $stmt_add = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
        $stmt_add->execute([$points_earned, $user_id]);
    }

    // 4. Xóa giỏ hàng
    $conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);

    $conn->commit();

    unset($_SESSION['cart']);
    unset($_SESSION['temp_order']);
    
    header("Location: order_success.php?id=$order_id");

} catch (Exception $e) {
    $conn->rollBack();
    die("Lỗi xử lý đơn hàng: " . $e->getMessage());
}
?>