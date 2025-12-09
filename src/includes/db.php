<?php
// src/includes/db.php

// Lấy thông tin từ biến môi trường Docker (nếu có), nếu không thì dùng mặc định
$host = getenv('MYSQL_HOST') ?: 'db';
$dbname = getenv('MYSQL_DATABASE') ?: 'yuumishop';
$username = getenv('MYSQL_USER') ?: 'user';
$password = getenv('MYSQL_PASSWORD') ?: 'password';

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set múi giờ MySQL để đồng bộ
    $conn->exec("SET time_zone = '+07:00';");
    
} catch(PDOException $e) {
    // Trong môi trường production, không nên echo lỗi chi tiết ra màn hình
    die("Lỗi kết nối DB. Vui lòng thử lại sau."); 
}

// Set múi giờ cho PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>