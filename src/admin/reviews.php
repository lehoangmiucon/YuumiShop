<?php
require_once '../includes/db.php';

// Check quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Cấm truy cập!");
}

// Xử lý xóa bình luận
if (isset($_GET['delete_id'])) {
    $review_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    if ($stmt->execute([$review_id])) {
        echo "<script>alert('Đã xóa bình luận!'); window.location.href='reviews.php';</script>";
    }
}

// Lấy danh sách bình luận (Join với bảng Users và Products để lấy tên)
$sql = "SELECT r.*, u.name as user_name, p.name as product_name, p.image 
        FROM reviews r 
        JOIN users u ON r.user_id = u.id 
        JOIN products p ON r.product_id = p.id 
        ORDER BY r.created_at DESC";
$reviews = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Đánh giá</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="index.php">Dashboard</a>
        <a href="products.php">Quản lý Sản phẩm</a>
        <a href="orders.php">Quản lý Đơn hàng</a>
        <a href="users.php">Quản lý Khách hàng</a>
        <a href="reviews.php" style="background: #34495e; border-left: 3px solid #f1c40f;">Quản lý Đánh giá</a> <a href="../index.php">Về trang chủ</a>
    </div>

    <div class="content">
        <h2>Danh sách Đánh giá & Bình luận</h2>
        
        <table style="width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <thead>
                <tr style="background: #f8f9fa; text-align: left;">
                    <th style="padding: 15px;">ID</th>
                    <th style="padding: 15px;">Sản phẩm</th>
                    <th style="padding: 15px;">Khách hàng</th>
                    <th style="padding: 15px;">Đánh giá</th>
                    <th style="padding: 15px;">Nội dung</th>
                    <th style="padding: 15px;">Ngày</th>
                    <th style="padding: 15px;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $reviews->fetch(PDO::FETCH_ASSOC)): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 15px;">#<?= $row['id'] ?></td>
                    <td style="padding: 15px;">
                        <img src="../assets/images/<?= $row['image'] ?>" width="40" height="40" style="object-fit: cover; border-radius: 4px; vertical-align: middle; margin-right: 10px;">
                        <span style="font-size: 13px; font-weight: 600;"><?= htmlspecialchars($row['product_name']) ?></span>
                    </td>
                    <td style="padding: 15px;"><?= htmlspecialchars($row['user_name']) ?></td>
                    <td style="padding: 15px;">
                        <span style="color: #f1c40f; font-weight: bold;"><?= $row['rating'] ?> ★</span>
                    </td>
                    <td style="padding: 15px; max-width: 300px;">
                        <div style="font-size: 13px; color: #333;">
                            <strong>Ưu:</strong> <?= htmlspecialchars($row['pros']) ?><br>
                            <strong>Nhược:</strong> <?= htmlspecialchars($row['cons']) ?><br>
                            <em>"<?= htmlspecialchars($row['comment']) ?>"</em>
                        </div>
                    </td>
                    <td style="padding: 15px; font-size: 12px; color: #888;">
                        <?= date('d/m/Y H:i', strtotime($row['created_at'])) ?>
                    </td>
                    <td style="padding: 15px;">
                        <a href="reviews.php?delete_id=<?= $row['id'] ?>" 
                           onclick="return confirm('Bạn có chắc muốn xóa bình luận này?')" 
                           style="color: red; text-decoration: none; font-size: 13px;">
                           <i class="fas fa-trash"></i> Xóa
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>