<?php 
require_once 'includes/db.php';

// Check nếu đã login thì đá về trang tương ứng
if (isset($_SESSION['user_id'])) {
    if (trim($_SESSION['user_role']) === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: index.php");
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Lưu session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = trim($user['role']); // Quan trọng: Cắt khoảng trắng

        // --- MERGE GIỎ HÀNG (Khách -> DB) ---
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            foreach ($_SESSION['cart'] as $pid => $item) {
                // Check xem trong DB có chưa
                $check = $conn->prepare("SELECT id FROM cart WHERE user_id=? AND product_id=?");
                $check->execute([$user['id'], $pid]);
                $exists = $check->fetch(PDO::FETCH_ASSOC);

                if ($exists) {
                    // Có rồi -> Cộng dồn số lượng
                    $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE id=?")
                         ->execute([$item['qty'], $exists['id']]);
                } else {
                    // Chưa có -> Thêm mới
                    $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")
                         ->execute([$user['id'], $pid, $item['qty']]);
                }
            }
        }
        
        // Chuyển hướng phân quyền chặt chẽ
        if ($_SESSION['user_role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    } else {
        $error = "Email hoặc mật khẩu không đúng!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Yuumi Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">
    <div class="split-screen">
        <div class="auth-left">
            <div class="auth-caption">
                <h1>Chào mừng trở lại!</h1>
                <p>Tiếp tục hành trình chăm sóc thú cưng cùng Yuumi Shop.</p>
            </div>
        </div>

        <div class="auth-right">
            <div class="auth-form-wrapper">
                <div class="auth-header">
                    <h2>Đăng Nhập</h2>
                    <p>Điền thông tin để truy cập vào tài khoản của bạn.</p>
                </div>

                <?php if($error): ?>
                    <div style="background: #ffebee; color: #c62828; padding: 10px; border-radius: 5px; margin-bottom: 20px; font-size: 14px;">
                        <i class="fas fa-exclamation-circle"></i> <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
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
                            <input type="password" name="password" id="password" placeholder="Nhập mật khẩu" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                        </div>
                        <div style="text-align: right; margin-top: 5px;">
                            <a href="forgot_password.php" style="font-size: 13px; color: #2ecc71; text-decoration: none;">Quên mật khẩu?</a>
                        </div>
                    </div>

                    <button type="submit" class="auth-btn">Đăng Nhập</button>
                </form>

                <div class="social-login">
                    <div class="social-divider"><span>Hoặc đăng nhập với</span></div>
                    <div class="social-icons">
                        <a href="#" class="social-btn"><i class="fab fa-google" style="color: #db4437;"></i> Google</a>
                        <a href="#" class="social-btn"><i class="fab fa-facebook" style="color: #3b5998;"></i> Facebook</a>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px; font-size: 14px;">
                    Chưa có tài khoản? <a href="register.php" style="color: #2ecc71; font-weight: bold; text-decoration: none;">Đăng ký ngay</a>
                </div>
                
                <div style="text-align: center; margin-top: 15px;">
                    <a href="index.php" style="color: #999; font-size: 13px; text-decoration: none;">← Về trang chủ</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            var x = document.getElementById("password");
            var icon = document.querySelector(".toggle-password");
            if (x.type === "password") {
                x.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                x.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
    </script>
</body>
</html>