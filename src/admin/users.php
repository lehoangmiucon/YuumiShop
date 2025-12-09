<?php
require_once '../includes/db.php';
// Check quyền
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Không có quyền truy cập!");
}

// Xử lý Xóa User
if (isset($_POST['delete_user_id'])) {
    $id = $_POST['delete_user_id'];
    
    // Không cho phép xóa admin
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->execute([$id]);
    $u = $check->fetch();
    
    if ($u && $u['role'] != 'admin') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['flash_msg'] = ['msg' => 'Đã xóa khách hàng thành công!', 'type' => 'success'];
        }
    } else {
        $_SESSION['flash_msg'] = ['msg' => 'Không thể xóa tài khoản Admin!', 'type' => 'error'];
    }
    header("Location: users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Khách hàng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h3>Yuumi Admin</h3>
            <a href="index.php">Dashboard</a>
            <a href="products.php">Quản lý Sản phẩm</a>
            <a href="orders.php">Quản lý Đơn hàng</a>
            <a href="users.php" style="background: #34495e; border-left: 3px solid #f1c40f;">Quản lý Khách hàng</a>
            <a href="reviews.php">Quản lý Đánh giá</a>
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
                        <th>SĐT</th>
                        <th>Ngày tham gia</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Lấy user thường
                    $stmt = $conn->query("SELECT * FROM users WHERE role = 'user' ORDER BY id DESC");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                    ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone'] ?? 'Chưa cập nhật') ?></td>
                        <td><?= $row['created_at'] ?></td>
                        <td>
                            <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn-edit"><i class="fas fa-edit"></i> Sửa</a>
                            
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="delete_user_id" value="<?= $row['id'] ?>">
                                <button type="submit" 
                                        class="btn-danger" 
                                        style="border:none; cursor:pointer;"
                                        data-confirm="Bạn chắc chắn muốn xóa khách hàng này? Mọi dữ liệu liên quan sẽ bị xóa.">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>