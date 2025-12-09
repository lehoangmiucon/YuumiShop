<?php
require_once '../includes/db.php';

// Check quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    die("Cấm truy cập! <a href='../index.php'>Về trang chủ</a>");
}

// Xử lý thêm sản phẩm
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $species = $_POST['species'];
    $category = $_POST['category']; 
    $gender = $_POST['gender']; 
    $price = $_POST['price'];
    $rating = $_POST['rating']; 
    $description = $_POST['description']; // <--- 1. LẤY DỮ LIỆU MÔ TẢ
    
    // --- XỬ LÝ UPLOAD ẢNH ---
    $image_path = "default.jpg"; 

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $filename = $_FILES['image']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            // Xác định folder
        $sub_folder = "others/";
            if ($category == 'pet') {
                if ($species == 'dog') $sub_folder = "dogs/";
                elseif ($species == 'cat') $sub_folder = "cats/";
                else $sub_folder = "pets/";
            } elseif ($category == 'food') {
                $sub_folder = "food/";
            } elseif ($category == 'accessory') {
                $sub_folder = "accessory/";
            } elseif ($category == 'toy') {
                $sub_folder = "toy/";
            } elseif ($category == 'health') {
                $sub_folder = "health/";
            }

            $target_dir = "../assets/images/" . $sub_folder;
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }

            $new_filename = $sub_folder . strtolower($category) . "_" . time() . "." . $ext;
            $upload_path = "../assets/images/" . $new_filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $image_path = $new_filename;
            }
        }
    }

    // <--- 2. CẬP NHẬT CÂU SQL ĐỂ LƯU MÔ TẢ --->
    $sql = "INSERT INTO products (name, species, category, gender, price, image, rating, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt->execute([$name, $species, $category, $gender, $price, $image_path, $rating, $description])) {
        echo "<script>alert('Thêm thành công!'); window.location.href='products.php';</script>";
    } else {
        echo "<script>alert('Lỗi Database!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý sản phẩm</title>
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
        <h2>Thêm sản phẩm mới</h2>
        
        <div style="background: white; padding: 25px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 800px;">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Tên sản phẩm:</label>
                    <input type="text" name="name" required placeholder="Nhập tên...">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Danh mục:</label>
                        <select name="category" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="pet">Thú Cưng (Chó/Mèo)</option>
                            <option value="food">Thức ăn</option>
                            <option value="accessory">Phụ kiện</option>
                            <option value="toy">Đồ chơi</option>
                            <option value="health">Y tế</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Loài (Nếu là Thú Cưng):</label>
                        <select name="species" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="dog">Chó</option>
                            <option value="cat">Mèo</option>
                            <option value="all">Tất cả (Dùng chung)</option>
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div class="form-group">
                        <label>Giới tính:</label>
                        <select name="gender" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="unisex">Unisex (Hàng hóa)</option>
                            <option value="male">Đực</option>
                            <option value="female">Cái</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Đánh giá:</label>
                        <select name="rating" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="5">5 Sao (Tuyệt vời)</option>
                            <option value="4.5">4.5 Sao</option>
                            <option value="4">4 Sao (Tốt)</option>
                            <option value="3">3 Sao (Bình thường)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Giá (VNĐ):</label>
                    <input type="number" name="price" required placeholder="VD: 500000">
                </div>

                <div class="form-group">
                    <label>Mô tả chi tiết:</label>
                    <textarea name="description" rows="4" placeholder="Nhập công dụng, thành phần, đặc điểm..." style="width: 100%; padding: 10px; border: 1px solid #bdc3c7; border-radius: 4px; margin-top: 5px; font-family: inherit; resize: none"></textarea>
                </div>

                <div class="form-group">
                    <label>Hình ảnh:</label>
                    <input type="file" name="image" required accept="image/*" onchange="previewImage(this)">
                    <img id="imgPreview" class="upload-preview" src="#" alt="Preview" style="display: none; margin-top: 10px; max-width: 150px; border-radius: 5px; border: 1px solid #ddd;">
                </div>

                <button type="submit" name="add_product" class="btn" style="width: 100%; margin-top: 10px;">Thêm sản phẩm</button>
            </form>
        </div>

        <h2 style="margin-top: 40px;">Danh sách sản phẩm</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hình ảnh</th>
                    <th>Tên</th>
                    <th>Dành cho</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Sửa/Xóa</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><img src="../assets/images/<?= $row['image'] ?>" width="50" style="border-radius: 4px;"></td>
                    <td>
                        <?= htmlspecialchars($row['name']) ?> <br>
                        <span style="font-size: 11px; color: #f1c40f;">⭐ <?= $row['rating'] ?></span>
                    </td>
                    <td>
                        <?php 
                            if($row['species']=='dog') echo '<span style="color:#e67e22">Chó</span>';
                            elseif($row['species']=='cat') echo '<span style="color:#3498db">Mèo</span>';
                            else echo '<span style="color:#2ecc71">Chung</span>';
                        ?>
                    </td>
                    <td>
                        <?= ucfirst($row['category']) ?> <br>
                        <small style="color:#888"><?= $row['gender']=='unisex' ? '' : ($row['gender']=='male'?'Đực':'Cái') ?></small>
                    </td>
                    <td><?= number_format($row['price']) ?></td>
                    <td>
                        <a href="edit_product.php?id=<?=$row['id'] ?>" class="btn-edit">Sửa</a>
                        <a href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Xóa nhé?')" class="btn-danger">Xóa</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    function previewImage(input) {
        var preview = document.getElementById('imgPreview');
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
</body>
</html>
