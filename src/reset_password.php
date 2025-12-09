<?php
// reset_password.php
require_once 'includes/db.php';
$msg = "";
$error = "";

// Lấy tham số từ URL
$token = $_GET['token'] ?? '';
$email = $_GET['email'] ?? '';

// Check token hợp lệ không
$stmt = $conn->prepare("SELECT id FROM users WHERE email=? AND reset_token=? AND reset_token_expire > NOW()");
$stmt->execute([$email, $token]);
$user = $stmt->fetch();

if (!$user) {
    die("<div style='text-align:center; padding:50px;'>Token không hợp lệ hoặc đã hết hạn! <a href='forgot_password.php'>Thử lại</a></div>");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if ($new_pass !== $confirm_pass) {
        $error = "Mật khẩu xác nhận không khớp!";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        // Cập nhật pass và xóa token
        $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_token_expire=NULL WHERE id=?")
             ->execute([$hash, $user['id']]);
        
        echo "<script>alert('Đổi mật khẩu thành công!'); window.location.href='login.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đặt lại mật khẩu</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-form-wrapper" style="background: white; padding: 40px; border-radius: 10px; width: 400px;">
        <h2 style="text-align: center; color: #2ecc71;">Mật khẩu mới</h2>
        <?php if($error) echo "<div class='alert' style='color:red'>$error</div>"; ?>
        
        <form method="POST">
            <div class="input-group">
                <label>Mật khẩu mới</label>
                <div class="input-wrapper">
                    <input type="password" name="new_password" required placeholder="Nhập pass mới">
                </div>
            </div>
            <div class="input-group">
                <label>Xác nhận mật khẩu</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" required placeholder="Nhập lại pass mới">
                </div>
            </div>
            <button type="submit" class="auth-btn">Lưu mật khẩu</button>
        </form>
    </div>
</body>
</html>