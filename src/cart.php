<?php 
require_once 'includes/db.php';

// Logic Xử lý Cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add') {
    $id = $_POST['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // 1. Lưu vào Session
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        if (isset($_SESSION['cart'][$id])) {
            $_SESSION['cart'][$id]['qty']++;
        } else {
            $_SESSION['cart'][$id] = [
                'name' => $product['name'], 
                'price' => $product['price'], 
                'image' => $product['image'], 
                'category' => $product['category'],
                'qty' => 1
            ];
        }

        // 2. Nếu đã đăng nhập -> Lưu vào DB
        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
            // Check xem sản phẩm đã có trong DB chưa
            $check = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id=? AND product_id=?");
            $check->execute([$user_id, $id]);
            $exist = $check->fetch();

            if ($exist) {
                // Có rồi thì tăng số lượng
                $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id=?")->execute([$exist['id']]);
            } else {
                // Chưa có thì thêm mới
                $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)")->execute([$user_id, $id]);
            }
            if (isset($_GET['buynow'])) {
                header("Location: checkout.php");
            exit;
            }
        }
    }
    header("Location: cart.php");
    exit;
}

if (isset($_GET['remove'])) {
    $id_remove = $_GET['remove'];
    
    // Xóa khỏi Session
    unset($_SESSION['cart'][$id_remove]);

    // Xóa khỏi DB nếu đang đăng nhập
    if (isset($_SESSION['user_id'])) {
        $conn->prepare("DELETE FROM cart WHERE user_id=? AND product_id=?")->execute([$_SESSION['user_id'], $id_remove]);
    }

    header("Location: cart.php");
    exit;
}

include 'includes/header.php'; 
?>

<div class="container">
    <h2 style="margin-top: 30px; font-size: 28px;">Giỏ hàng của bạn</h2>

    <?php if (empty($_SESSION['cart'])): ?>
        <div style="text-align:center; padding: 80px 0;">
            <img src="assets/images/empty-cart.jpg" style="width: 150px; opacity: 0.5; margin-bottom: 20px;">
            <p style="font-size: 18px; color: #666;">Giỏ hàng đang trống trơn nè!</p>
            <a href="products.php" class="btn" style="margin-top: 15px;">Let's go!</a>
        </div>
    <?php else: ?>
        
        <div class="cart-page-wrapper">
            <div class="cart-items-col">
                <div class="cart-header-row">
                    <span>Sản phẩm</span>
                    <span>Số lượng</span>
                    <span>Giá</span>
                </div>

                <?php 
                $total = 0; 
                foreach ($_SESSION['cart'] as $id => $item): 
                    $subtotal = $item['price'] * $item['qty']; 
                    $total += $subtotal; 
                ?>
                    <div class="cart-item-card">
                        
                        <div class="cart-product-wrapper">
                            <img src="assets/images/<?= $item['image'] ?>" class="cart-item-img" alt="<?= htmlspecialchars($item['name']) ?>">
                            
                            <div class="cart-item-info">
                                <h3><?= htmlspecialchars($item['name']) ?></h3>
                                <div class="cart-item-meta">
                                    Danh mục: <?= ucfirst($item['category']) ?>
                                </div>
                                <a href="cart.php?remove=<?= $id ?>" class="btn-remove" onclick="return confirm('Xóa bé này khỏi giỏ?')">
                                    <i class="fas fa-trash"></i> Xóa
                                </a>
                            </div>
                        </div>

                        <div style="text-align: center; margin-top: 50px; margin-right:30px"> <input type="number" value="<?= $item['qty'] ?>" class="qty-input" min="1" readonly>
                    </div>

                        <div style="text-align: right; font-weight: bold; color: #333; min-width: 120px; margin-top: 55px;">
                            <?= number_format($item['price'] * $item['qty']) ?> đ
                        </div>

                    </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 20px;">
                    <a href="products.php" style="color: #2ecc71; font-weight: 600;">&larr; Tiếp tục mua sắm</a>
                </div>
            </div>

            <div class="cart-summary-col">
                <h3 style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 15px;">Tóm tắt đơn hàng</h3>
                
                <div class="summary-row">
                    <span>Tạm tính:</span>
                    <span><?= number_format($total) ?> đ</span>
                </div>
                <div class="summary-row">
                    <span>Phí vận chuyển:</span>
                    <span style="color: #2ecc71;">Miễn phí</span>
                </div>
                <div class="summary-row">
                    <span>Thuế:</span>
                    <span>Đã bao gồm</span>
                </div>

                <div class="summary-total">
                    <span>Tổng:</span>
                    <span style="color: #e74c3c;"><?= number_format($total) ?> VND</span>
                </div>

                <a href="checkout.php" class="btn-checkout">Tiến hành thanh toán</a>
                
                <div style="margin-top: 20px; font-size: 13px; color: #777; text-align: center;">
                    <i class="fas fa-shield-alt"></i> Bảo mật thanh toán 100%
                </div>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>