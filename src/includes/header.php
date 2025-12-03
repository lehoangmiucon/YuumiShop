<?php
require_once 'db.php';

// --- LOGIC ĐỒNG BỘ GIỎ HÀNG ---
if (isset($_SESSION['user_id'])) {
    // Nếu đã đăng nhập: LẤY TỪ DB GHI ĐÈ VÀO SESSION
    $stmtCart = $conn->prepare("SELECT c.product_id, c.quantity, p.name, p.price, p.image, p.category 
                                FROM cart c JOIN products p ON c.product_id = p.id 
                                WHERE c.user_id = ?");
    $stmtCart->execute([$_SESSION['user_id']]);
    
    $_SESSION['cart'] = []; // Reset cart session hiện tại
    while ($row = $stmtCart->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['cart'][$row['product_id']] = [
            'name' => $row['name'],
            'price' => $row['price'],
            'image' => $row['image'],
            'category' => $row['category'],
            'qty' => $row['quantity']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yuumi Shop - Thú Cưng & Phụ Kiện</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
<header>
    <div class="container header-wrapper">
        <div class="logo">
            <h1><a href="index.php">YUUMI SHOP</a></h1>
        </div>

        <div class="header-search">
            <form action="products.php" method="GET">
                <input type="text" name="q" placeholder="Tìm kiếm thú cưng, thức ăn..." value="<?= isset($_GET['q']) ? htmlspecialchars($_GET['q']) : '' ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <div class="header-actions">
            <nav>
                <ul>
                    <li><a href="index.php">Trang chủ</a></li>
                    <li><a href="products.php?category=pet" style="color: #e67e22; font-weight:bold;">Thú Cưng</a></li>
                    <li><a href="products.php?category=supplies">Cửa Hàng</a></li> 
                </ul>
            </nav>
            <div class="user-actions">
                <?php
                    // Tính tổng số lượng sản phẩm
                    $total_qty = 0;
                    if (isset($_SESSION['cart'])) {
                        foreach ($_SESSION['cart'] as $item) {
                            $total_qty += $item['qty'];
                        }
                    }
                ?>
                    <a href="cart.php" class="cart-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge"><?= $total_qty ?></span>
                    </a>
                    
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a href="profile.php" class="user-link"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($_SESSION['user_name']) ?></a>
                        <div class="dropdown-content">
                            <a href="profile.php">Hồ sơ</a>
                            <?php if($_SESSION['user_role'] == 'admin'): ?>
                                <a href="admin/index.php">Admin Panel</a>
                            <?php endif; ?>
                            <a href="logout.php">Đăng xuất</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn-login">Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>