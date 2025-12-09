<?php
// 1. GỌI DB TRƯỚC
require_once 'includes/db.php';
$id = $_GET['id'] ?? 0;

// 2. XỬ LÝ GỬI ĐÁNH GIÁ
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_review'])) {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['flash_msg'] = ['msg' => 'Vui lòng đăng nhập để đánh giá!', 'type' => 'error'];
    } else {
        $rating = $_POST['rating'];
        $comment = $_POST['comment'];
        $pros = $_POST['pros'] ?? '';
        $cons = $_POST['cons'] ?? '';

        $sql = "INSERT INTO reviews (user_id, product_id, rating, comment, pros, cons) VALUES (?, ?, ?, ?, ?, ?)";
        if ($conn->prepare($sql)->execute([$_SESSION['user_id'], $id, $rating, $comment, $pros, $cons])) {
            $_SESSION['flash_msg'] = ['msg' => 'Gửi đánh giá thành công!', 'type' => 'success'];
        } else {
            $_SESSION['flash_msg'] = ['msg' => 'Có lỗi xảy ra, vui lòng thử lại.', 'type' => 'error'];
        }
    }
    header("Location: product_detail.php?id=$id");
    exit;
}

// 3. XỬ LÝ XÓA BÌNH LUẬN
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_review_id'])) {
    if (!isset($_SESSION['user_id'])) die("Lỗi xác thực!");
    $del_id = $_POST['delete_review_id'];
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['user_role'] ?? 'user';

    if ($user_role == 'admin') {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$del_id]);
    } else {
        $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ? AND user_id = ?");
        $stmt->execute([$del_id, $user_id]);
    }
    $_SESSION['flash_msg'] = ['msg' => 'Đã xóa bình luận thành công!', 'type' => 'success'];
    header("Location: product_detail.php?id=$id");
    exit;
}

// 4. LẤY THÔNG TIN SẢN PHẨM
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) die("Sản phẩm không tồn tại!");

$related = $conn->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
$related->execute([$product['category'], $id]);

include 'includes/header.php';
?>

<div class="container" style="margin-top: 40px; margin-bottom: 60px;">
    <div class="product-detail-wrapper">
        
        <div class="detail-gallery">
            <div class="main-img">
                <img src="assets/images/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>">
            </div>
            
            <form action="cart.php" method="POST" class="no-ajax" style="margin-top: 20px;">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                <input type="hidden" name="qty" value="1">
                <input type="hidden" name="redirect" value="cart">
                <button type="submit" class="btn-big-buy">
                    <i class="fas fa-shopping-bag"></i> MUA NGAY
                </button>
            </form>
        </div>

        <div class="detail-info">
            <div class="product-breadcrumb">
                <a href="index.php">Trang chủ</a> / 
                <a href="products.php?category=<?= $product['category'] ?>"><?= ucfirst($product['category']) ?></a> / 
                <span><?= htmlspecialchars($product['name']) ?></span>
            </div>
            
            <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>
            
            <div class="product-meta-row">
                <div class="rating-stars">
                    <span style="color: #f1c40f; font-size: 18px;">
                        <?= str_repeat('★', floor($product['rating'])) ?><?= str_repeat('☆', 5 - floor($product['rating'])) ?>
                    </span>
                    <span style="color: #777; font-size: 14px;">(<?= rand(10,50) ?> đánh giá)</span>
                </div>
                <div class="stock-status">
                    <i class="fas fa-check-circle" style="color: #2ecc71;"></i> Còn hàng
                </div>
            </div>

            <div class="product-price">
                <span class="current-price"><?= number_format($product['price']) ?> ₫</span>
                <?php if($product['old_price'] > 0): ?>
                    <span class="old-price"><?= number_format($product['old_price']) ?> ₫</span>
                    <span class="discount-badge">-<?= round((($product['old_price'] - $product['price'])/$product['old_price'])*100) ?>%</span>
                <?php endif; ?>
            </div>

            <?php if($product['category'] == 'pet'): ?>
            <div class="pet-attributes">
                <div class="attr-item"><i class="fas fa-venus-mars"></i> <strong>Giới tính:</strong> <?= $product['gender']=='male'?'Đực':'Cái' ?></div>
                <div class="attr-item"><i class="fas fa-birthday-cake"></i> <strong>Độ tuổi:</strong> <?= $product['age_group']=='baby'?'Thú con':'Trưởng thành' ?></div>
                <div class="attr-item"><i class="fas fa-paw"></i> <strong>Giống:</strong> <?= $product['sub_category'] ?? 'Lai' ?></div>
                <div class="attr-item"><i class="fas fa-syringe"></i> <strong>Tiêm phòng:</strong> Đã tiêm 1 mũi</div>
            </div>
            <?php endif; ?>

            <div class="product-actions">
                <form action="cart.php" method="POST" style="display: flex; gap: 15px; width: 100%; align-items: stretch;">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                    
                    <div class="qty-control">
                        <button type="button" onclick="this.nextElementSibling.stepDown()">-</button>
                        <input type="number" name="qty" value="1" min="1" max="10" readonly>
                        <button type="button" onclick="this.previousElementSibling.stepUp()">+</button>
                    </div>

                    <button type="submit" class="btn-add-cart" style="background: white; color: #2ecc71; border: 1px solid #2ecc71;">
                        <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                    </button>
                </form>
            </div>

            <div class="trust-badges">
                <div class="trust-item">
                    <i class="fas fa-truck"></i>
                    <div><strong>Miễn phí vận chuyển</strong><p>Cho đơn hàng từ 500k</p></div>
                </div>
                <div class="trust-item">
                    <i class="fas fa-shield-alt"></i>
                    <div><strong>Bảo hành sức khỏe</strong><p>7 ngày đổi trả nếu có bệnh</p></div>
                </div>
                <div class="trust-item">
                    <i class="fas fa-headset"></i>
                    <div><strong>Hỗ trợ 24/7</strong><p>Tư vấn chăm sóc trọn đời</p></div>
                </div>
            </div>
        </div>
    </div>

    <div class="product-tabs">
        <div class="tabs-header">
            <button class="tab-btn active" onclick="openTab(event, 'desc')">Mô tả chi tiết</button>
            <button class="tab-btn" onclick="openTab(event, 'reviews')">Đánh giá khách hàng</button>
        </div>

        <div id="desc" class="tab-content" style="display: block;">
            <div class="desc-content">
                <h3>Thông tin sản phẩm</h3>
                <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            </div>
        </div>

        <div id="reviews" class="tab-content" style="display: none;">
            <div style="background: #f9f9f9; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h4 style="margin-bottom: 15px;">Viết đánh giá của bạn</h4>
                <form method="POST">
                    <div style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:5px; font-weight:bold;">Đánh giá sao:</label>
                        <div class="star-rating">
                            <input type="radio" id="star5" name="rating" value="5" checked /><label for="star5" title="Tuyệt vời">★</label>
                            <input type="radio" id="star4" name="rating" value="4" /><label for="star4" title="Tốt">★</label>
                            <input type="radio" id="star3" name="rating" value="3" /><label for="star3" title="Bình thường">★</label>
                            <input type="radio" id="star2" name="rating" value="2" /><label for="star2" title="Kém">★</label>
                            <input type="radio" id="star1" name="rating" value="1" /><label for="star1" title="Tệ">★</label>
                        </div>
                    </div>
                    <textarea name="comment" placeholder="Chia sẻ trải nghiệm của bạn..." style="resize: none; width: 100%; padding: 10px; height: 80px; border: 1px solid #ddd; border-radius: 5px; margin-bottom: 15px;" required></textarea>
                    <button type="submit" name="submit_review" class="btn">Gửi đánh giá</button>
                </form>
            </div>

            <?php
            $stmt_rev = $conn->prepare("SELECT r.*, u.name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.product_id = ? ORDER BY r.id DESC");
            $stmt_rev->execute([$id]);

            if ($stmt_rev->rowCount() > 0):
                while($rev = $stmt_rev->fetch(PDO::FETCH_ASSOC)):
                    $is_mine = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $rev['user_id']);
                    $is_admin = (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin');
            ?>
            <div class="review-item" style="border-bottom: 1px solid #eee; padding: 20px 0; position: relative;">
                <?php if($is_mine || $is_admin): ?>
                <form method="POST" style="position: absolute; top: 20px; right: 0;">
                    <input type="hidden" name="delete_review_id" value="<?= $rev['id'] ?>">
                    <button type="submit" onclick="return confirm('Xóa bình luận này?')" style="background: none; border: none; color: red; cursor: pointer;" title="Xóa bình luận"><i class="fas fa-trash"></i></button>
                </form>
                <?php endif; ?>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
                    <strong><?= htmlspecialchars($rev['name']) ?></strong>
                    <span style="color: #f1c40f; margin-right: 30px"><?= str_repeat('★', $rev['rating']) ?></span>
                </div>
                <div style="font-size: 13px; color: #2ecc71; margin-bottom: 8px;">
                    <i class="fas fa-check-circle"></i> Đã comment về sản phẩm này
                </div>
                <p style="margin-top: 10px; font-style: italic; color: #555;">"<?= htmlspecialchars($rev['comment']) ?>"</p>
                <small style="color: #999; display: block; margin-top: 5px;"><?= date('d/m/Y H:i', strtotime($rev['created_at'])) ?></small>
            </div>
            <?php endwhile; else: echo "<p style='text-align:center; color:#888;'>Chưa có đánh giá nào. Hãy là người đầu tiên!</p>"; endif; ?>
        </div>
    </div>

    <div class="related-products" style="margin-top: 60px;">
        <h3 style="border-bottom: 2px solid #2ecc71; display: inline-block; padding-bottom: 5px; margin-bottom: 30px;">Sản phẩm liên quan</h3>
        <div class="product-grid">
            <?php while($rel = $related->fetch(PDO::FETCH_ASSOC)): ?>
            <div class="product-card">
                <div class="product-img-wrapper">
                    <a href="product_detail.php?id=<?= $rel['id'] ?>">
                        <img src="assets/images/<?= htmlspecialchars($rel['image']) ?>">
                    </a>
                    <div class="product-actions">
                        <a href="product_detail.php?id=<?= $rel['id'] ?>" class="btn" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                        <form action="cart.php" method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="id" value="<?= $rel['id'] ?>">
                            <button class="btn" title="Thêm vào giỏ"><i class="fas fa-shopping-cart"></i></button>
                        </form>
                    </div>
                </div>
                <div class="product-info">
                    <h3><a href="product_detail.php?id=<?= $rel['id'] ?>" style="color:#333; text-decoration:none;"><?= htmlspecialchars($rel['name']) ?></a></h3>
                    <div style="margin-bottom: 10px;">
                        <span class="price"><?= number_format($rel['price']) ?> đ</span>
                    </div>
                    <form action="cart.php" method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="id" value="<?= $rel['id'] ?>">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn-buy" style="width: 100%; border: none; cursor: pointer; padding: 8px; font-size: 14px;">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </form>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>

    <style>
        .btn-buy { display:block; width:100%; margin-top:10px; padding:10px 0; background:#2ecc71; color:white; border-radius:6px; text-decoration:none; font-weight:600; transition:.3s; }
        .btn-buy:hover { background:#27ae60; }
        .btn-big-buy{ background: #2ecc71; color: white; border: none; border-radius: 8px; font-size: 22px; font-weight: 800; cursor: pointer; transition: 0.3s; padding: 18px 28px; display: flex; align-items: center; justify-content: center; gap: 12px; letter-spacing: 1px; text-transform: uppercase; }
        .btn-big-buy:hover{ background: #27ae60; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(60, 231, 86, 0.4); }
        .product-detail-wrapper { display: grid; grid-template-columns: 1fr 1fr; gap: 50px; }
        .main-img img { width: 100%; border-radius: 10px; border: 1px solid #eee; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .product-breadcrumb { color: #888; font-size: 14px; margin-bottom: 15px; }
        .product-title { font-size: 32px; margin-bottom: 10px; color: #2c3e50; }
        .product-meta-row { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .current-price { font-size: 32px; color: #e74c3c; font-weight: bold; }
        .old-price { text-decoration: line-through; color: #999; margin-left: 10px; font-size: 18px; }
        .discount-badge { background: #e74c3c; color: white; padding: 2px 8px; border-radius: 4px; font-size: 14px; margin-left: 10px; vertical-align: top; }
        .pet-attributes { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; background: #f9f9f9; padding: 20px; border-radius: 8px; margin: 25px 0; border: 1px dashed #ddd; }
        .attr-item i { color: #2ecc71; width: 20px; text-align: center; margin-right: 5px; }
        .qty-control { display: flex; align-items: center; border: 1px solid #ddd; border-radius: 5px; overflow: hidden; }
        .qty-control button { background: #f1f1f1; border: none; width: 40px; height: 45px; cursor: pointer; font-size: 18px; }
        .qty-control input { width: 50px; height: 45px; text-align: center; border: none; font-size: 16px; outline: none; }
        .btn-add-cart { flex: 1; background: #2ecc71; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-add-cart:hover { background: #27ae60; }
        .trust-badges { margin-top: 30px; display: grid; grid-template-columns: 1fr; gap: 15px; }
        .trust-item { display: flex; gap: 15px; align-items: center; border: 1px solid #eee; padding: 15px; border-radius: 8px; }
        .trust-item i { font-size: 24px; color: #2ecc71; }
        .trust-item h4 { margin: 0; font-size: 14px; }
        .trust-item p { margin: 0; font-size: 12px; color: #666; }
        .product-tabs { margin-top: 50px; }
        .tabs-header { border-bottom: 2px solid #eee; margin-bottom: 25px; }
        .tab-btn { background: none; border: none; padding: 10px 30px; font-size: 16px; cursor: pointer; font-weight: 600; color: #888; border-bottom: 2px solid transparent; margin-bottom: -2px; }
        .tab-btn.active { color: #2ecc71; border-bottom-color: #2ecc71; }
        .tab-content { line-height: 1.8; color: #555; }
        @media (max-width: 768px) { .product-detail-wrapper { grid-template-columns: 1fr; } }
    </style>
    
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) { tabcontent[i].style.display = "none"; }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) { tablinks[i].className = tablinks[i].className.replace(" active", ""); }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }
    </script>
</div>
<?php include 'includes/footer.php'; ?>