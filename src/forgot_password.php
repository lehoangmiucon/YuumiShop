<?php
// forgot_password.php
require_once 'includes/db.php';
$msg = "";
$link = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $stmt = $conn->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // 1. Tạo token ngẫu nhiên
        $token = bin2hex(random_bytes(32));
        // 2. Token hết hạn sau 15 phút
        $expire = date("Y-m-d H:i:s", strtotime('+15 minutes'));

        // 3. Lưu vào DB
        $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expire = ? WHERE email = ?")
             ->execute([$token, $expire, $email]);

        // 4. Tạo link (Giả lập gửi email)
        // Lưu ý: Thay localhost:8080 thành domain thực tế của bạn nếu cần
        $resetLink = "http://localhost:8080/reset_password.php?token=" . $token . "&email=" . $email;
        
        $msg = "Đã gửi yêu cầu! (Mô phỏng: Click vào link dưới đây)";
        $link = $resetLink;
    } else {
        $msg = "Email không tồn tại trong hệ thống!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quên mật khẩu - Yuumi Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="auth-form-wrapper" style="background: white; padding: 40px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 100%; max-width: 400px;">
        <h2 style="text-align: center; color: #2ecc71; margin-bottom: 20px;">Quên mật khẩu?</h2>
        
        <?php if($msg): ?>
            <div class="alert" style="background: #e8f5e9; color: #2e7d32; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                <?= $msg ?>
            </div>
            <?php if($link): ?>
                <div style="margin-bottom: 20px; word-break: break-all; background: #f1f1f1; padding: 10px; border-radius: 5px;">
                    <small>Link reset (Check mail):</small><br>
                    <a href="<?= $link ?>" style="color: blue; font-weight: bold;">Click vào đây để đặt lại mật khẩu</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <label>Nhập email đã đăng ký</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="example@gmail.com" required>
                </div>
            </div>
            <button type="submit" class="auth-btn">Gửi yêu cầu</button>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <a href="login.php" style="color: #666;">Quay lại đăng nhập</a>
        </div>
    </div>
</body>
</html>