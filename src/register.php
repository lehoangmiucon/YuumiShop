<?php 
require_once 'includes/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address']; // Thêm địa chỉ luôn cho tiện

    // Check email
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    
    if($stmt->rowCount() > 0){
        $error = "Email này đã được sử dụng!";
    } else {
        $sql = "INSERT INTO users (name, email, password, address, role) VALUES (?, ?, ?, ?, 'user')";
        $stmt = $conn->prepare($sql);
        if($stmt->execute([$name, $email, $password, $address])) {
            // Đăng ký xong chuyển qua login
            $_SESSION['flash_msg'] = ['msg' => 'Chào mừng đến với Yuumi Shop!', 'type' => 'success'];
            header("Location: login.php");
            exit;
        } else {
            $error = "Lỗi hệ thống!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký - Yuumi Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="split-screen">
        <div class="auth-left" style="background-image: url('assets/images/auth-bg-2.jpg');">
            <div class="auth-caption">
                <h1>Tham gia cộng đồng!</h1>
                <p>Tạo tài khoản để nhận ưu đãi và chăm sóc thú cưng tốt hơn.</p>
            </div>
        </div>

        <div class="auth-right">
            <div class="auth-form-wrapper">
                <div class="auth-header">
                    <h2>Tạo Tài Khoản</h2>
                    <p>Miễn phí và chỉ mất 1 phút.</p>
                </div>

                <?php if($error): ?>
                    <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="input-group">
                        <label>Họ và tên</label>
                        <div class="input-wrapper">
                            <i class="fas fa-user"></i>
                            <input type="text" name="name" placeholder="Nguyễn Văn A" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="example@email.com" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Mật khẩu</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Tạo mật khẩu" required>
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Địa chỉ (Tùy chọn)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" name="address" placeholder="Nhập địa chỉ giao hàng">
                        </div>
                    </div>

                    <button type="submit" class="auth-btn">Đăng Ký</button>
                </form>

                <div style="text-align: center; margin-top: 30px; font-size: 14px;">
                    Đã có tài khoản? <a href="login.php" style="color: #2ecc71; font-weight: bold; text-decoration: none;">Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>