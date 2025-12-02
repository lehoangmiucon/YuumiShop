<?php
require_once 'includes/db.php';

// --- XỬ LÝ CART (THÊM / XÓA / CẬP NHẬT) ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id']);

    // 1. THÊM VÀO GIỎ
    if ($action == 'add') {
        // ... (Giữ nguyên logic thêm vào giỏ như cũ của Ní) ...
        // Tui rút gọn đoạn này để tập trung vào phần Update bên dưới
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty']++;
            } else {
                $_SESSION['cart'][$id] = [
                    'name' => $product['name'], 'price' => $product['price'],
                    'image' => $product['image'], 'category' => $product['category'], 'qty' => 1
                ];
            }
            // Nếu đã login, lưu vào DB
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $check = $conn->prepare("SELECT id FROM cart WHERE user_id=? AND product_id=?");
                $check->execute([$user_id, $id]);
                if ($exist = $check->fetch()) {
                    $conn->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id=?")->execute([$exist['id']]);
                } else {
                    $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)")->execute([$user_id, $id]);
                }
            }
        }
    }

    // 2. CẬP NHẬT SỐ LƯỢNG (+/-) -> PHẦN MỚI
    if ($action == 'update_qty') {
        $type = $_POST['type']; // 'inc' (tăng) hoặc 'dec' (giảm)
        
        if (isset($_SESSION['cart'][$id])) {
            if ($type == 'inc') {
                $_SESSION['cart'][$id]['qty']++;
            } elseif ($type == 'dec') {
                $_SESSION['cart'][$id]['qty']--;
                if ($_SESSION['cart'][$id]['qty'] < 1) $_SESSION['cart'][$id]['qty'] = 1; // Giữ tối thiểu là 1
            }

            // Đồng bộ DB nếu đã login
            if (isset($_SESSION['user_id'])) {
                $new_qty = $_SESSION['cart'][$id]['qty'];
                $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id=? AND product_id=?")
                     ->execute([$new_qty, $_SESSION['user_id'], $id]);
            }
        }
    }
    
    // Redirect lại để tránh gửi form lại
    header("Location: cart.php");
    exit;
}

// Xóa sản phẩm (Logic cũ giữ nguyên)
if (isset($_GET['remove'])) {
    $id_remove = $_GET['remove'];
    unset($_SESSION['cart'][$id_remove]);
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
                    <span style="text-align: center;">Số lượng</span>
                    <span style="text-align: right;">Giá</span>
                </div>

                <?php 
                $total = 0;
                foreach ($_SESSION['cart'] as $id => $item): 
                    $subtotal = $item['price'] * $item['qty'];
                    $total += $subtotal;
                ?>
                <div class="cart-item-card">
                    <div class="cart-product-wrapper">
                        <img src="assets/images/<?= $item['image'] ?>" class="cart-item-img">
                        <div class="cart-item-info">
                            <h3><?= htmlspecialchars($item['name']) ?></h3>
                            <div class="cart-item-meta">Danh mục: <?= ucfirst($item['category']) ?></div>
                                <a style="color: red" href="cart.php?remove=<?= $id ?>" 
                                data-confirm="Xóa bé này khỏi giỏ hàng? Hành động này không thể hoàn tác.">
                                <i class="fas fa-trash"></i> Xóa
                                </a>
                        </div>
                    </div>

                    <div class="qty-control-wrapper">
                        <form method="POST" action="cart.php" style="display:inline;">
                            <input type="hidden" name="action" value="update_qty">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="type" value="dec">
                            <button type="submit" class="qty-btn">-</button>
                        </form>
                        
                        <input type="text" value="<?= $item['qty'] ?>" class="qty-input" readonly>
                        
                        <form method="POST" action="cart.php" style="display:inline;">
                            <input type="hidden" name="action" value="update_qty">
                            <input type="hidden" name="id" value="<?= $id ?>">
                            <input type="hidden" name="type" value="inc">
                            <button type="submit" class="qty-btn">+</button>
                        </form>
                    </div>

                    <div class="item-total-price">
                        <?= number_format($subtotal) ?> đ
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-summary-col">
                <h3 style="margin-bottom: 20px; border-bottom: 1px solid #eee;">Tóm tắt đơn hàng</h3>
                <div class="summary-row"><span>Tạm tính:</span><span><?= number_format($total) ?> đ</span></div>
                <div class="summary-row"><span>Phí vận chuyển:</span><span style="color: #2ecc71;">Miễn phí</span></div>
                <div class="summary-total"><span>Tổng:</span><span style="color: #e74c3c;"><?= number_format($total) ?> VND</span></div>
                <a href="checkout.php" class="btn-checkout">Tiến hành thanh toán</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
    .qty-control-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 40px; /* Căn chỉnh cho ngang hàng */
    }
    .qty-btn {
        width: 30px; height: 30px;
        background: #fff; border: 1px solid #ddd;
        cursor: pointer; font-weight: bold;
        transition: 0.2s;
    }
    .qty-btn:hover { background: #2ecc71; color: white; border-color: #2ecc71; }
    .qty-input {
        width: 40px; height: 30px;
        text-align: center; border: 1px solid #ddd;
        border-left: none; border-right: none;
        outline: none;
    }
    .item-total-price {
        text-align: right; font-weight: bold; color: #333;
        min-width: 120px; margin-top: 45px;
    }
</style>

<?php include 'includes/footer.php'; ?>