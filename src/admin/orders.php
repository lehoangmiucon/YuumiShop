<?php
require_once '../includes/db.php';
// Check quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Cấm truy cập!");
}

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];

    // Lấy thông tin đơn hàng cũ để check
    $old_order = $conn->query("SELECT status, user_id FROM orders WHERE id=$order_id")->fetch();

    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        
        // --- LOGIC TÍCH ĐIỂM SAFE MODE ---
        // Chỉ cộng điểm khi chuyển sang 'shipped' VÀ trạng thái cũ chưa phải là 'shipped' hoặc 'completed'
        // (Để tránh cộng điểm nhiều lần nếu admin bấm update liên tục)
        if ($new_status == 'shipped' && !in_array($old_order['status'], ['shipped', 'completed'])) {
            
            // 1. Lấy chi tiết các món trong đơn hàng để phân loại
            $sql_items = "SELECT oi.quantity, oi.price, p.category 
                          FROM order_items oi 
                          JOIN products p ON oi.product_id = p.id 
                          WHERE oi.order_id = ?";
            $items = $conn->prepare($sql_items);
            $items->execute([$order_id]);
            
            $total_points_earned = 0;

            while ($item = $items->fetch(PDO::FETCH_ASSOC)) {
                $item_total = $item['price'] * $item['quantity'];
                
                // 2. Tính điểm theo từng món
                if ($item['category'] == 'pet') {
                    // Thú cưng: 1 triệu = 1 điểm (Lợi nhuận thấp)
                    $points = floor($item_total / 1000000);
                } else {
                    // Phụ kiện, thức ăn, v.v...: 100k = 1 điểm (Lợi nhuận cao)
                    $points = floor($item_total / 100000);
                }
                $total_points_earned += $points;
            }

            // 3. Cộng điểm cho user
            if ($total_points_earned > 0) {
                $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?")
                     ->execute([$total_points_earned, $old_order['user_id']]);
            }
        }

        $_SESSION['flash_msg'] = ['msg' => 'Cập nhật trạng thái thành công!', 'type' => 'success'];
    } else {
        $_SESSION['flash_msg'] = ['msg' => 'Lỗi cập nhật!', 'type' => 'error'];
    }
    
    header("Location: orders.php");
    exit;
}

// Lấy danh sách đơn hàng
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$orders = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý đơn hàng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="index.php">Dashboard</a>
        <a href="reviews.php">Quản lý Đánh giá</a>
        <a href="products.php">Quản lý Sản phẩm</a>
        <a href="orders.php" style="background: #34495e; border-left: 3px solid #f1c40f;">Quản lý Đơn hàng</a>
        <a href="users.php">Quản lý Khách hàng</a>
        <a href="../index.php">Về trang chủ</a>
    </div>

    <div class="content">
        <h2>Danh sách đơn hàng</h2>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Ngày đặt</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $orders->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td>#<?= $row['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($row['fullname']) ?><br>
                        <small><?= htmlspecialchars($row['phone']) ?></small>
                    </td>
                    <td><?= number_format($row['total_amount']) ?> đ</td>
                    <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $row['payment_method'] == 'VNPAY' ? 'success' : 'warning' ?>">
                            <?= $row['payment_method'] ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                            <select name="status" onchange="this.form.submit()" 
                                style="padding: 5px; border-radius: 4px; border: 1px solid #ddd; 
                                       background-color: <?= $row['status']=='paid'?'#d4edda':($row['status']=='pending'?'#fff3cd':'#e2e3e5') ?>">
                                <option value="pending" <?= $row['status']=='pending'?'selected':'' ?>>Chờ xử lý</option>
                                <option value="shipped" <?= $row['status']=='shipped'?'selected':'' ?>>Đang giao</option>
                                <option value="paid" <?= $row['status']=='paid'?'selected':'' ?>>Đã thanh toán (Hoàn tất)</option>
                                <option value="cancelled" <?= $row['status']=='cancelled'?'selected':'' ?>>Đã hủy</option>
                            </select>
                            <input type="hidden" name="update_status" value="1">
                        </form>
                    </td>
                    <td>
                        <a href="order_detail.php?id=<?= $row['id'] ?>" class="btn-edit" style="background:#3498db;">Chi tiết</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .badge-success { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
    .badge-warning { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; font-size: 12px; }
</style>
<?php include '../includes/footer.php'; ?> </body>
</html>