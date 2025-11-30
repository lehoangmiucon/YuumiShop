<?php
require_once '../includes/db.php';

// Check quyền
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Không có quyền truy cập!");
}

// Xử lý Xóa User
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    // Không cho phép xóa admin
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->execute([$id]);
    $u = $check->fetch();
    
    if ($u && $u['role'] != 'admin') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        echo "<script>alert('Đã xóa thành công!'); window.location.href='users.php';</script>";
    } else {
        echo "<script>alert('Không thể xóa Admin!');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khách hàng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h3>Yuumi Admin</h3>
        <a href="index.php">Dashboard</a>
        <a href="users.php" style="background: #34495e; border-left: 3px solid #f1c40f;">Quản lý Khách hàng</a>
        <a href="products.php">Quản lý Sản phẩm</a>
        <a href="../index.php">Về trang chủ Web</a>
    </div>

    <div class="content">
        <h2>Danh sách Khách hàng</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Email</th>
                    <th>Ngày tham gia</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Chỉ lấy user thường
                $stmt = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= $row['created_at'] ?></td>
                    <td>
                        <a href="users.php?delete_id=<?= $row['id'] ?>" 
                           onclick="return confirm('Bạn chắc chắn muốn xóa khách hàng này?')"
                           class="btn-danger">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>