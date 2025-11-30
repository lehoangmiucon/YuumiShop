<?php
// src/includes/db.php
$host = 'db'; // Tên service trong docker-compose
$dbname = 'yuumishop';
$username = 'user';
$password = 'password';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // Nếu lỗi kết nối, thử đợi một chút (trick cho docker lúc mới khởi động)
    die("Đang khởi động Database, vui lòng F5 lại sau 10 giây! <br> Lỗi chi tiết: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>