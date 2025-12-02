<?php
require_once '../includes/db.php';

// Check quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Cấm truy cập!");
}

// 1. LẤY THÔNG TIN SẢN PHẨM CẦN SỬA
if (!isset($_GET['id'])) {
    header("Location: products.php");
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Sản phẩm không tồn tại!");
}

// 2. XỬ LÝ CẬP NHẬT (KHI BẤM LƯU)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
    $name = $_POST['name'];
    $species = $_POST['species'];
    $category = $_POST['category'];
    $gender = $_POST['gender'];
    $price = $_POST['price'];
    $rating = $_POST['rating'];
    $description = $_POST['description'];
    
    // Giữ ảnh cũ mặc định
    $image_path = $product['image']; 

    // Nếu có chọn ảnh mới thì xử lý upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Xác định folder lưu ảnh (Logic giống trang thêm mới)
            $sub_folder = "others/";
            if ($category == 'pet') {
                if ($species == 'dog') $sub_folder = "dogs/";
                elseif ($species == 'cat') $sub_folder = "cats/";
                else $sub_folder = "pets/";
            } elseif ($category == 'food') $sub_folder = "food/";
            elseif ($category == 'accessory') $sub_folder = "accessory/";
            elseif ($category == 'toy') $sub_folder = "toy/";
            elseif ($category == 'health') $sub_folder = "health/";

            $target_dir = "../assets/images/" . $sub_folder;
            if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

            $new_filename = $sub_folder . strtolower($category) . "_" . time() . "." . $ext;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], "../assets/images/" . $new_filename)) {
                $image_path = $new_filename; // Cập nhật đường dẫn ảnh mới
            }
        }
    }

    // Cập nhật Database
    $sql = "UPDATE products SET name=?, species=?, category=?, gender=?, price=?, rating=?, description=?, image=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$name, $species, $category, $gender, $price, $rating, $description, $image_path, $id])) {
        echo "<script>alert('Cập nhật thành công!'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Sửa sản phẩm</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h3>Admin Panel</h3>
        <a href="index.php">Dashboard</a>
        <a href="reviews.php">Quản lý Đánh giá</a>
        <a href="products.php" style="background: #34495e; border-left: 3px solid #f1c40f;">Quản lý Sản phẩm</a>
        <a href="../index.php">Về trang chủ</a>
    </div>

    <div class="content">
        <h2>Sửa sản phẩm: #<?= $product['id'] ?></h2>
        
        <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Danh mục:</label>
                        <select name="category" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="pet" <?= $product['category']=='pet'?'selected':'' ?>>Thú Cưng</option>
                            <option value="food" <?= $product['category']=='food'?'selected':'' ?>>Thức ăn</option>
                            <option value="accessory" <?= $product['category']=='accessory'?'selected':'' ?>>Phụ kiện</option>
                            <option value="toy" <?= $product['category']=='toy'?'selected':'' ?>>Đồ chơi</option>
                            <option value="health" <?= $product['category']=='health'?'selected':'' ?>>Y tế</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Loài:</label>
                        <select name="species" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="dog" <?= $product['species']=='dog'?'selected':'' ?>>Chó</option>
                            <option value="cat" <?= $product['species']=='cat'?'selected':'' ?>>Mèo</option>
                            <option value="all" <?= $product['species']=='all'?'selected':'' ?>>Tất cả</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Giới tính:</label>
                        <select name="gender" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="unisex" <?= $product['gender']=='unisex'?'selected':'' ?>>Unisex</option>
                            <option value="male" <?= $product['gender']=='male'?'selected':'' ?>>Đực</option>
                            <option value="female" <?= $product['gender']=='female'?'selected':'' ?>>Cái</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Đánh giá:</label>
                        <select name="rating" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="5" <?= $product['rating']==5?'selected':'' ?>>5 Sao</option>
                            <option value="4.5" <?= $product['rating']==4.5?'selected':'' ?>>4.5 Sao</option>
                            <option value="4" <?= $product['rating']==4?'selected':'' ?>>4 Sao</option>
                            <option value="3" <?= $product['rating']==3?'selected':'' ?>>3 Sao</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Giá (VNĐ):</label>
                    <input type="number" name="price" value="<?= $product['price'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết:</label>
                    <textarea name="description" rows="5" style="width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; margin-top: 5px;"><?= htmlspecialchars($product['description']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Hình ảnh hiện tại:</label><br>
                    <img src="../assets/images/<?= $product['image'] ?>" width="100" style="margin-bottom: 10px; border: 1px solid #ddd; padding: 5px; border-radius: 5px;">
                    <br>
                    <label>Chọn ảnh mới (Nếu muốn thay đổi):</label>
                    <input type="file" name="image" accept="image/*">
                </div>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="update_product" class="btn" style="flex: 1;">Lưu Cập Nhật</button>
                    <a href="products.php" class="btn-danger" style="text-align: center; padding: 12px; border-radius: 4px; background: #7f8c8d; width: 100px;">Hủy</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>