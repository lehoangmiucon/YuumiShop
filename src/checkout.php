<?php
require_once 'includes/db.php';
// Kiểm tra Login & Cart
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
if (empty($_SESSION['cart'])) { header("Location: index.php"); exit; }

// Lấy thông tin user để điền sẵn vào form
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Tính tổng tiền
$subtotal = 0;
foreach ($_SESSION['cart'] as $item) {
    $subtotal += $item['price'] * $item['qty'];
}
$shipping = 0; 
$total = $subtotal + $shipping;

// TÍNH TOÁN GIỚI HẠN DÙNG ĐIỂM (Safe Mode)
$max_discount_vnd = $total * 0.20; // 20% tổng đơn
$max_points_allowed = floor($max_discount_vnd / 100); 
$usable_points = min($user['points'], $max_points_allowed);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán - Yuumi Shop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body style="background: #f9f9f9;">

<div class="checkout-header">
    <div class="container">
        <div class="logo">
            <h1><a href="index.php" style="text-decoration: none; color: #2ecc71;">YUUMI SHOP</a> <span style="font-size: 18px; color: #333; font-weight: normal; margin-left: 10px;">| Thanh Toán |</span></h1>
        </div>
        <div class="secure-badge">
            <i class="fas fa-lock"></i> Thanh toán an toàn 100%
        </div>
    </div>
</div>

<div class="container">
    <form action="payment_gateway.php" method="POST" class="checkout-layout">
        
        <div class="checkout-form-section">
            <h3><i class="fas fa-map-marker-alt" style="color: #2ecc71;"></i> Địa chỉ nhận hàng</h3>
            
            <div class="form-grid">
                <div class="form-full">
                    <label class="checkout-label">Họ và tên người nhận</label>
                    <input type="text" name="fullname" class="checkout-input" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                
                <div>
                    <label class="checkout-label">Số điện thoại</label>
                    <input type="text" name="phone" class="checkout-input" value="<?= htmlspecialchars($user['phone']) ?>" placeholder="VD: 0912345678" required>
                </div>
                
                <div>
                    <label class="checkout-label">Email (nhận hóa đơn)</label>
                    <input type="email" class="checkout-input" value="<?= htmlspecialchars($user['email']) ?>" readonly style="background: #eee;">
                </div>

                <div class="form-full">
                    <label class="checkout-label">Địa chỉ chi tiết</label>
                    <input type="text" name="address" class="checkout-input" placeholder="Số nhà, tên đường, phường/xã, quận/huyện..." required>
                </div>
                
                <div class="form-full">
                    <label class="checkout-label">Ghi chú đơn hàng (Tùy chọn)</label>
                    <textarea  style="resize: none" name="note" class="checkout-input" rows="2" placeholder="Ví dụ: Giao giờ hành chính, gọi trước khi giao..."></textarea>
                </div>
            </div>

            <h3 style="margin-top: 30px;"><i class="fas fa-credit-card" style="color: #2ecc71;"></i> Phương thức thanh toán</h3>
            <div class="payment-methods">
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="COD" checked>
                    <div class="payment-icon"><i class="fas fa-money-bill-wave"></i></div>
                    <div>
                        <strong>Thanh toán khi nhận hàng</strong>
                        <div style="font-size: 12px; color: #666;">Kiểm tra hàng rồi mới thanh toán</div>
                    </div>
                </label>
                
                <label class="payment-option">
                    <input type="radio" name="payment_method" value="VNPAY">
                    <div class="payment-icon"><i class="fas fa-qrcode"></i></div>
                    <div>
                        <strong>Quét mã VNPAY / Ví điện tử</strong>
                        <div style="font-size: 12px; color: #666;">Giảm ngay 10k khi quét mã</div>
                    </div>
                </label>
            </div>
            
            <a href="cart.php" class="back-link">← Quay lại giỏ hàng</a>
        </div>

        <div class="order-summary-box">
            <h3 style="border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px;">Đơn hàng (<?= count($_SESSION['cart']) ?> sản phẩm)</h3>
            
            <div style="max-height: 300px; overflow-y: auto; margin-bottom: 20px;">
                <?php foreach ($_SESSION['cart'] as $item): ?>
                <div class="order-item">
                    <img src="assets/images/<?= $item['image'] ?>" class="order-img">
                    <div class="order-info">
                        <h4><?= htmlspecialchars($item['name']) ?></h4>
                        <p>SL: <strong><?= $item['qty'] ?></strong></p>
                    </div>
                    <div class="order-price">
                        <?= number_format($item['price'] * $item['qty']) ?> đ
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cost-row">
                <span>Tạm tính</span>
                <span><?= number_format($subtotal) ?> đ</span>
            </div>
            <div class="cost-row">
                <span>Phí vận chuyển</span>
                <span style="color: #2ecc71;">
                    <?= $shipping == 0 ? 'Miễn phí' : number_format($shipping).' đ' ?>
                </span>
            </div>

            <?php if ($user['points'] > 0): ?>
            <div class="points-section" style="margin: 15px 0; padding: 15px; background: #fff3cd; border-radius: 5px; border: 1px solid #ffeeba;">
                <div style="font-weight: bold; color: #856404; margin-bottom: 5px;">
                    <i class="fas fa-coins"></i> Yuumi Points
                </div>
                <p style="font-size: 13px; margin-bottom: 10px;">
                    Bạn có <strong><?= number_format($user['points']) ?></strong> điểm. 
                    (1 điểm = 100đ). <br>
                    Tối đa được dùng: <strong><?= number_format($usable_points) ?></strong> điểm cho đơn này (Max 20%).
                </p>
                
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="use_points" value="1" id="usePoints" onchange="updateTotal()">
                    <span style="margin-left: 10px; font-size: 14px;">
                        Dùng <strong><?= $usable_points ?></strong> điểm 
                        (Giảm <span style="color:red">-<?= number_format($usable_points * 100) ?>đ</span>)
                    </span>
                </label>
            </div>
            <?php endif; ?>

            <div class="total-row">
                <span>Tổng cộng</span>
                <span class="total-price" id="totalDisplay"><?= number_format($total) ?> VND</span>
            </div>

            <input type="hidden" name="total_amount" id="totalInput" value="<?= $total ?>">
            <input type="hidden" name="points_used" id="pointsInput" value="0">

            <button type="submit" class="btn-confirm">
                ĐẶT HÀNG NGAY
            </button>

            <div style="margin-top: 15px; text-align: center; font-size: 15px; color: #888;">
                Bằng việc đặt hàng, bạn đồng ý với <a href="#" style="color: #2ecc71;">Điều khoản sử dụng</a> của Yuumi Shop.
            </div>
        </div>

    </form>
</div>

<script>
    const originalTotal = <?= $total ?>;
    const usablePoints = <?= isset($usable_points) ? $usable_points : 0 ?>; 
    const pointValue = 100; 

    function updateTotal() {
        const checkbox = document.getElementById('usePoints');
        const totalDisplay = document.getElementById('totalDisplay');
        const totalInput = document.getElementById('totalInput');
        const pointsInput = document.getElementById('pointsInput');

        if (checkbox && checkbox.checked) {
            const discount = usablePoints * pointValue;
            const newTotal = originalTotal - discount;
            
            // Format tiền Việt Nam
            totalDisplay.innerHTML = new Intl.NumberFormat('vi-VN').format(newTotal) + ' VND';
            totalDisplay.innerHTML += ` <br><small style='color:green; font-size:12px; font-weight:normal'>(Đã giảm -${new Intl.NumberFormat('vi-VN').format(discount)}đ)</small>`;
            
            totalInput.value = newTotal;
            pointsInput.value = usablePoints; 
        } else {
            totalDisplay.innerHTML = new Intl.NumberFormat('vi-VN').format(originalTotal) + ' VND';
            totalInput.value = originalTotal;
            pointsInput.value = 0;
        }
    }
</script>

<style>
.back-link {
    display: inline-block;
    margin-top: 20px;
    color: #666;
    text-decoration: none;
    transition: 0.2s;
}

.back-link:hover {
    color: #2ecc71;
}
</style>

</body>
</html>