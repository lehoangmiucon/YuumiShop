<?php
// admin/delete_product.php
require_once '../includes/db.php';

// Check quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Bạn không có quyền thực hiện hành động này!");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Xóa sản phẩm khỏi DB
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    if ($stmt->execute([$id])) {
        // Quay lại trang quản lý và báo thành công
        echo "<script>alert('Đã xóa sản phẩm thành công!'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi xóa sản phẩm!'); window.history.back();</script>";
    }
} else {
    header("Location: products.php");
}
?>