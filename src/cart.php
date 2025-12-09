<?php
require_once 'includes/db.php';

// Kiểm tra nếu request là AJAX
$is_ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
if (isset($_POST['ajax_mode'])) {
    $is_ajax = true;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $id = intval($_POST['id']);

    // --- CASE 1: THÊM VÀO GIỎ ---
    if ($action == 'add') {

            // --- BẮT BUỘC LOGIN ---
        if (!isset($_SESSION['user_id'])) {
            if ($is_ajax) {
                // Trả về JSON để JS xử lý chuyển hướng
                echo json_encode([
                    'status' => 'login_required', 
                    'message' => 'Vui lòng đăng nhập để mua hàng!',
                    'redirect' => 'login.php'
                ]);
                exit;
            } else {
                // Chuyển hướng trực tiếp nếu không dùng Ajax (nút Mua ngay)
                header("Location: login.php");
                exit;
            }
        }

        $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
        if ($qty < 1) $qty = 1;

        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product) {
            // Logic thêm vào Session
            if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

            if (isset($_SESSION['cart'][$id])) {
                $_SESSION['cart'][$id]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$id] = [
                    'name' => $product['name'],
                    'price' => $product['price'],
                    'image' => $product['image'],
                    'category' => $product['category'],
                    'qty' => $qty
                ];
            }

            // Logic thêm vào DB nếu đã login
            if (isset($_SESSION['user_id'])) {
                $user_id = $_SESSION['user_id'];
                $check = $conn->prepare("SELECT id FROM cart WHERE user_id=? AND product_id=?");
                $check->execute([$user_id, $id]);
                $exist = $check->fetch();

                if ($exist) {
                    $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE id=?")->execute([$qty, $exist['id']]);
                } else {
                    $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")->execute([$user_id, $id, $qty]);
                }
            }

            // --- TRẢ VỀ JSON NẾU LÀ AJAX ---
            if ($is_ajax) {
                $total_items = 0;
                foreach($_SESSION['cart'] as $c) $total_items += $c['qty'];

                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Đã thêm ' . $product['name'] . ' vào giỏ!',
                    'total_qty' => $total_items
                ]);
                exit;
            } 
            // --- REDIRECT NẾU KHÔNG PHẢI AJAX (Mua ngay) ---
            else {
                $redirect_loc = (isset($_POST['redirect']) && $_POST['redirect'] == 'cart') ? 'cart.php' : 'products.php';
                header("Location: " . $redirect_loc);
                exit;
            }
        }
    }

    // --- CASE 2: CẬP NHẬT SỐ LƯỢNG ---
    elseif ($action == 'update_qty') {
        $type = $_POST['type']; 
        if (isset($_SESSION['cart'][$id])) {
            if ($type == 'inc') {
                $_SESSION['cart'][$id]['qty']++;
            } else {
                $_SESSION['cart'][$id]['qty']--;
                if ($_SESSION['cart'][$id]['qty'] < 1) $_SESSION['cart'][$id]['qty'] = 1;
            }
            if (isset($_SESSION['user_id'])) {
                $conn->prepare("UPDATE cart SET quantity = ? WHERE user_id=? AND product_id=?")
                     ->execute([$_SESSION['cart'][$id]['qty'], $_SESSION['user_id'], $id]);
            }
        }
        header("Location: cart.php");
        exit;
    }
}

// --- XỬ LÝ XÓA SẢN PHẨM ---
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
                        <a style="color: red" href="cart.php?remove=<?= $id ?>" data-confirm="Bạn chắc chứ? Hành động này không thể hoàn tác.">
                            <i class="fas fa-trash"></i> Xóa
                        </a>
                    </div>
                </div>
                
                <div class="qty-control-wrapper" style="margin-left: 30px">
                    <form method="POST" action="cart.php" style="display: inline;">
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
    .qty-control-wrapper { display: flex; align-items: center; justify-content: center; margin-top: 40px; }
    .qty-btn { width: 30px; height: 30px; background: #fff; border: 1px solid #ddd; cursor: pointer; font-weight: bold; transition: 0.2s; }
    .qty-btn:hover { background: #2ecc71; color: white; border-color: #2ecc71; }
    .qty-input { width: 40px; height: 30px; text-align: center; border: 1px solid #ddd; border-left: none; border-right: none; outline: none; }
    .item-total-price { text-align: right; font-weight: bold; color: #333; min-width: 120px; margin-top: 45px; }
</style>
<?php include 'includes/footer.php'; ?>