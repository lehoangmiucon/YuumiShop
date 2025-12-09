<?php
include 'includes/header.php';
if(!isset($_GET['id'])) die("Không tìm thấy đơn hàng");
$order_id = $_GET['id'];

// Lấy thông tin đơn
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order) die("Đơn hàng không tồn tại");

// Lấy chi tiết sản phẩm
$stmt_items = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$stmt_items->execute([$order_id]);

// Gợi ý sản phẩm khác (Upsell - Lấy ngẫu nhiên 4 món phụ kiện)
$upsell = $conn->query("SELECT * FROM products WHERE category IN ('accessory', 'food') ORDER BY RAND() LIMIT 4");
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">

<div style="background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; max-width: 900px; margin: 0 auto;">

<div style="background: #2ecc71; padding: 40px; text-align: center; color: white;">
<div style="background: white; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
<i class="fas fa-check" style="font-size: 40px; color: #2ecc71;"></i>
</div>
<h1 style="font-size: 32px; margin-bottom: 10px;">Đặt Hàng Thành Công!</h1>
<p style="font-size: 16px; opacity: 0.9;">Cảm ơn bạn đã tin tưởng Yuumi Shop. Đơn hàng <strong>#<?= $order['id'] ?></strong> đã được ghi nhận.</p>
<p style="font-size: 14px; margin-top: 5px;"><i class="fas fa-envelope"></i> Email xác nhận đã được gửi tới: <?= $_SESSION['user_email'] ?? 'bạn' ?></p>
</div>

<div style="padding: 40px;">
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">

<div>
<h3 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
<i class="fas fa-map-marker-alt" style="color: #2ecc71; margin-right: 10px;"></i> Thông tin nhận hàng
</h3>
<p><strong>Người nhận:</strong> <?= htmlspecialchars($order['fullname']) ?></p>
<p><strong>Số điện thoại:</strong> <?= htmlspecialchars($order['phone']) ?></p>
<p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['address']) ?></p>
<p><strong>Phương thức:</strong> <?= $order['payment_method'] == 'COD' ? 'Thanh toán khi nhận hàng' : 'Đã thanh toán qua VNPAY' ?></p>
<div style="margin-top: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; border: 1px dashed #2ecc71;">
<i class="fas fa-truck" style="color: #2ecc71;"></i> Dự kiến giao hàng: <strong>2 - 3 ngày tới</strong>
</div>
</div>

<div>
<h3 style="color: #2c3e50; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px;">
<i class="fas fa-shopping-bag" style="color: #2ecc71; margin-right: 10px;"></i> Sản phẩm đã mua
</h3>
<div style="max-height: 250px; overflow-y: auto; padding-right: 10px">
<?php while($row = $stmt_items->fetch(PDO::FETCH_ASSOC)): ?>
<div style="display: flex; gap: 15px; margin-bottom: 15px; border-bottom: 1px solid #f5f5f5; padding-bottom: 10px;">
<img src="assets/images/<?= $row['image'] ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
<div style="flex: 1;">
<h4 style="font-size: 14px; margin: 0; color: #333;"><?= htmlspecialchars($row['name']) ?></h4>
<p style="font-size: 12px; color: #777; margin: 2px 0;">Số lượng: <?= $row['quantity'] ?></p>
</div>
<div style="font-weight: bold; font-size: 14px; color: #333;">
<?= number_format($row['price'] * $row['quantity']) ?> đ
</div>
</div>
<?php endwhile; ?>
</div>

<div style="display: flex; justify-content: space-between; margin-top: 20px; font-size: 18px; font-weight: bold; color: #2ecc71;">
<span>Tổng thanh toán:</span>
<span><?= number_format($order['total_amount']) ?> VND</span>
</div>
</div>
</div>

<div style="text-align: center; margin-top: 40px;">
<a href="index.php" class="btn" style="padding: 12px 35px; font-size: 16px; border-radius: 50px; box-shadow: 0 5px 15px rgba(46,204,113,0.3);">
<i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
</a>
</div>
</div>
</div>

<div style="margin-top: 60px;">
<h3 style="text-align: center; margin-bottom: 30px; color: #2c3e50;">
<i class="fas fa-gift" style="color: #e74c3c;"></i> Có thể Boss sẽ thích thêm
</h3>
<div class="product-grid">
<?php while($up = $upsell->fetch(PDO::FETCH_ASSOC)): ?>
<div class="product-card">
<a href="product_detail.php?id=<?= $up['id'] ?>">
<img src="assets/images/<?= $up['image'] ?>" style="height: 300px;">
</a>
<div class="product-info" style="padding: 15px;">
<h3><a href="product_detail.php?id=<?= $up['id'] ?>" style="font-size: 15px;"><?= $up['name'] ?></a></h3>
<span class="price" style="font-size: 16px;"><?= number_format($up['price']) ?> đ</span>
<form action="cart.php" method="POST" style="margin-top: 10px;">
<input type="hidden" name="action" value="add">
<input type="hidden" name="id" value="<?= $up['id'] ?>">
<button class="btn" style="width: 100%; padding: 8px; font-size: 13px;">Thêm vào giỏ</button>
</form>
</div>
</div>
<?php endwhile; ?>
</div>
</div>

<div style="margin-top: 50px; text-align: center; color: #777; font-size: 14px;">
<p><i class="fas fa-headset"></i> Cần hỗ trợ? Gọi ngay: <strong>1900 1000</strong> (8:00 - 22:00)</p>
<p style="margin-top: 5px;"><i class="fas fa-shield-alt"></i> Yuumi Shop cam kết bảo hành sức khỏe thú cưng 7 ngày.</p>
</div>

</div>

<?php include 'includes/footer.php'; ?>
