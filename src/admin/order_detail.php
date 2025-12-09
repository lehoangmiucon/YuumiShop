<?php
require_once '../includes/db.php';
// Check quyền Admin... (Ní copy đoạn check quyền cũ qua nhé)
if (!isset($_GET['id'])) die("Thiếu ID đơn hàng");
$order_id = $_GET['id'];

// Lấy thông tin chung
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

// Lấy list sản phẩm
$stmt_items = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt_items->execute([$order_id]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Chi tiết đơn hàng #<?= $order_id ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="index.php">Dashboard</a>
        <a href="reviews.php">Quản lý Đánh giá</a>
        <a href="orders.php">Quản lý Đơn hàng</a> </div>
    <div class="content">
        <h2>Chi tiết đơn hàng #<?= $order_id ?></h2>
        <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
            <p><strong>Khách hàng:</strong> <?= $order['fullname'] ?></p>
            <p><strong>SĐT:</strong> <?= $order['phone'] ?></p>
            <p><strong>Địa chỉ:</strong> <?= $order['address'] ?></p>
            <p><strong>Thanh toán:</strong> <?= $order['payment_method'] ?></p>
            <p><strong>Trạng thái:</strong> <span style="color: green; font-weight: bold;"><?= strtoupper($order['status']) ?></span></p>
        </div>

        <h3>Sản phẩm đã mua</h3>
        <table>
            <thead><tr><th>Sản phẩm</th><th>Ảnh</th><th>Giá</th><th>SL</th><th>Tổng</th></tr></thead>
            <tbody>
                <?php while($item = $stmt_items->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?= $item['name'] ?></td>
                    <td><img src="../assets/images/<?= $item['image'] ?>" width="50"></td>
                    <td><?= number_format($item['price']) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td><?= number_format($item['price'] * $item['quantity']) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <h3 style="text-align: right; margin-top: 20px;">Tổng doanh thu: <?= number_format($order['total_amount']) ?> VND</h3>
        <a href="orders.php" class="btn">Quay lại danh sách</a>
    </div>
</div>
</body>
</html>