<?php
// admin/login.php
require_once '../includes/db.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    if($_SESSION['user_role'] == 'admin') {
        header("Location: admin/index.php");
    }else{
        header("Location: index.php");   
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin'");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Sai email hoặc không phải tài khoản Admin!";
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Yuumi Shop</title>
    <link rel="stylesheet" href="../assets/css/style.css"> 
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body style="background: #333; display: flex; align-items: center; justify-content: center; height: 100vh;">

<div class="auth-box" style="margin: 0;">
    <h2 style="color: #333;">Admin Panel</h2>
    <?php if($error): ?><div class="alert"><?= $error ?></div><?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <input type="email" name="email" placeholder="Email Quản trị viên" required>
        </div>
        <div class="form-group">
            <input type="password" name="password" placeholder="Mật khẩu" required>
        </div>
        <button type="submit" class="btn" style="width: 100%; background: #333;">Truy cập quản trị</button>
    </form>
    <p style="text-align: center; margin-top: 15px;">
        <a href="../index.php">← Về trang chủ</a>
    </p>
</div>

</body>
</html>