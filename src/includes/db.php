<?php
// src/includes/db.php
$host = 'db'; 
$dbname = 'yuumishop';
$username = 'user';
$password = 'password';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // --- THÊM DÒNG NÀY ĐỂ FIX GIỜ VIỆT NAM ---
    $conn->exec("SET time_zone = '+07:00';"); 
    
} catch(PDOException $e) {
    die("Lỗi kết nối DB: " . $e->getMessage());
}

// Set múi giờ cho PHP luôn cho chắc
date_default_timezone_set('Asia/Ho_Chi_Minh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>