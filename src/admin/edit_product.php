<?php
require_once '../includes/db.php';

// Check quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Cấm truy cập!");
}

if (!isset($_GET['id'])) {
    header("Location: users.php");
    exit;
}

$id = $_GET['id'];

// Xử lý cập nhật
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_user'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $points = $_POST['points']; // Admin có quyền sửa điểm thưởng

    $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=?, points=? WHERE id=?");
    if ($stmt->execute([$name, $phone, $address, $points, $id])) {
        $_SESSION['flash_msg'] = ['msg' => 'Cập nhật thông tin khách hàng thành công!', 'type' => 'success'];
        header("Location: users.php");
        exit;
    } else {
        $error = "Lỗi khi cập nhật!";
    }
}

// Lấy thông tin user
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) die("Khách hàng không tồn tại!");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa khách hàng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <div class="sidebar">
            <h3>Admin Panel</h3>
            <a href="index.php">Dashboard</a>
            <a href="products.php">Quản lý Sản phẩm</a>
            <a href="orders.php">Quản lý Đơn hàng</a>
            <a href="users.php" style="background: #34495e; border-left: 3px solid #f1c40f;">Quản lý Khách hàng</a>
            <a href="reviews.php">Quản lý Đánh giá</a>
            <a href="../index.php">Về trang chủ</a>
        </div>
        <div class="content">
            <h2>Sửa thông tin: <?= htmlspecialchars($user['name']) ?></h2>
            
            <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 600px;">
                <form method="POST">
                    <div class="form-group">
                        <label>Tên khách hàng:</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email (Không thể sửa):</label>
                        <input type="email" value="<?= htmlspecialchars($user['email']) ?>" disabled style="background: #eee;">
                    </div>

                    <div class="form-group">
                        <label>Số điện thoại:</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label>Địa chỉ:</label>
                        <textarea name="address" rows="3" style="width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; resize: none"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Điểm thưởng:</label>
                        <input type="number" name="points" value="<?= $user['points'] ?>">
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" name="update_user" class="btn" style="flex: 1;">Lưu Cập Nhật</button>
                        <a href="users.php" class="btn-danger" style="text-align: center; padding: 12px; border-radius: 4px; background: #7f8c8d; width: 100px;">Hủy</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
