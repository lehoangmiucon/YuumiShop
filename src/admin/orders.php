<?php
require_once '../includes/db.php';

// Check quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Cấm truy cập!");
}

// Xử lý cập nhật trạng thái đơn hàng
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    
    // Lấy trạng thái cũ để tránh cộng điểm nhiều lần
    $old_order = $conn->query("SELECT status, user_id, total_amount FROM orders WHERE id=$order_id")->fetch();
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);

    // Nếu chuyển sang PAID và trước đó chưa PAID -> Cộng điểm
    if ($status == 'paid' && $old_order['status'] != 'paid') {
        $points = floor($old_order['total_amount'] / 100000); // 10k = 1 điểm
        $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?")
             ->execute([$points, $old_order['user_id']]);
    }

    echo "<script>alert('Cập nhật trạng thái thành công!'); window.location.href='orders.php';</script>";
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
</body>
</html>