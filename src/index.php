<?php include 'includes/header.php'; ?>

<!-- ================= HERO SECTION ================= -->
<div class="hero">
    <div class="hero-overlay"></div>
    <div class="hero-content">
        <h1>Đón Boss Về Nhà</h1>
        <p>Thú cưng thuần chủng • Phụ kiện chính hãng • Uy tín hàng đầu</p>
        <a href="products.php?category=pet" class="btn-hero">Tìm Thú Cưng Ngay</a>
    </div>
</div>

<div class="container">

    <!-- ================= DANH MỤC ================= -->
    <h2 class="section-title">Danh Mục Nổi Bật</h2>
    <div class="category-grid">

        <a href="products.php?category=pet" class="category-card cat-pet">
            <i class="fas fa-paw"></i>
            <h3>Thú Cưng</h3>
            <p>Chó, mèo thuần chủng</p>
        </a>

        <a href="products.php?category=food" class="category-card cat-food">
            <i class="fas fa-bone"></i>
            <h3>Thức Ăn</h3>
            <p>Dinh dưỡng cho thú cưng</p>
        </a>

        <a href="products.php?category=accessory" class="category-card cat-accessory">
            <i class="fas fa-tshirt"></i>
            <h3>Phụ Kiện</h3>
            <p>Vòng cổ, đồ chơi, chuồng</p>
        </a>

        <a href="products.php?category=health" class="category-card cat-health">
            <i class="fas fa-user-md fa-3x" style="color: #e74c3c;"></i>
            <h3>Y tế</h3>
            <p>Thuốc, Chăm sóc</p>
        </a>

    </div>

    <!-- ================= THÚ CƯNG MỚI VỀ ================= -->
<h2 class="section-title">Thú Cưng Mới Về</h2>
    <div class="product-grid">
        <?php
        // 1. Lấy 4 bé Chó mới nhất
        $dogs = $conn->query("SELECT * FROM products WHERE category='pet' AND species='dog' ORDER BY id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);
        
        // 2. Lấy 4 bé Mèo mới nhất
        $cats = $conn->query("SELECT * FROM products WHERE category='pet' AND species='cat' ORDER BY id DESC LIMIT 4")->fetchAll(PDO::FETCH_ASSOC);

        // Gộp lại thành 1 mảng (8 bé)
        $new_pets = array_merge($dogs, $cats);

        foreach ($new_pets as $row):
        ?>
            <div class="product-card">
                <a href="product_detail.php?id=<?= $row['id'] ?>">
                    <img src="assets/images/<?= $row['image'] ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                </a>
                
                <div class="product-info">
                    <div style="font-size: 12px; color: #999; margin-bottom: 5px; text-transform: uppercase; font-weight: bold;">
                        <?= $row['species']=='dog' ? '🐶 Chó Cưng' : '🐱 Mèo Cưng' ?>
                    </div>
                    
                    <h3><a href="product_detail.php?id=<?= $row['id'] ?>" style="color:#333; text-decoration:none;"><?= htmlspecialchars($row['name']) ?></a></h3>
                    
                    <p class="product-meta">
                        <?= $row['gender']=='male' ? '♂ Đực' : '♀ Cái' ?> • 
                        <?= $row['age_group']=='baby' ? 'Thú con' : 'Trưởng thành' ?>
                    </p>
                    
                    <span class="price"><?= number_format($row['price']) ?> đ</span>
                    
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn-buy" style="flex: 1; text-align: center; background: #fff; border: 1px solid #2ecc71; color: #2ecc71;">
                            Xem
                        </a>
                        
                        <form action="cart.php" method="POST" style="flex: 2;">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <input type="hidden" name="qty" value="1"> 
                            <input type="hidden" name="redirect" value="cart">
                            <button type="submit" class="btn-buy" style="width: 100%; cursor: pointer; border: none;">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                            </button>
                        </form>

                    </div>
                </div>
            </div>
        <?php endforeach; ?>
</div>

<!-- ================= CSS TÙY CHỈNH ================= -->
<style>
/* HERO */
.hero {
    background: url('assets/images/banner1.png') center/cover no-repeat;
    height: 450px; position: relative; display:flex; justify-content:center; align-items:center;
}
.hero-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.5); }
.hero-content { position:relative; color:white; text-align:center; max-width:700px; padding:20px; }
.hero-content h1 { font-size:52px; font-weight:700; text-shadow:0 3px 12px rgba(0,0,0,0.6); }
.hero-content p { font-size:20px; margin:15px 0 25px; opacity:.9; }
.btn-hero {
    padding: 15px 45px;
    background: #2ecc71;
    border-radius: 40px;
    font-size: 18px;
    font-weight: 600;
    color: white;
    box-shadow: 0 5px 15px rgba(46,204,113,0.4);
    transition: .3s;
}
.btn-hero:hover { background:#27ae60; box-shadow:0 8px 20px rgba(46,204,113,0.6); }

/* CATEGORY */
.section-title { text-align:center; margin:50px 0 25px; font-size:28px; font-weight:700; color:#2c3e50; }
.category-grid { display:flex; justify-content:center; gap:30px; flex-wrap:wrap; }
.category-card {
    width:240px; padding:30px; border-radius:15px; text-align:center;
    background:white; border:1px solid #eaeaea; text-decoration:none;
    color:#333; transition:.3s; box-shadow:0 3px 10px rgba(0,0,0,0.05);
}
.category-card:hover { transform:translateY(-8px); box-shadow:0 10px 22px rgba(0,0,0,0.12); }
.category-card i { font-size:36px; margin-bottom:12px; }

/* Category colors */
.cat-pet i { color:#2ecc71; }
.cat-food i { color:#e67e22; }
.cat-accessory i { color:#3498db; }

/* PRODUCTS */
.product-grid {
    display:grid;
    grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));
    gap:25px;
}
.product-card { 
    background:white; border-radius:10px; padding-bottom:15px;
    border:1px solid #eee; box-shadow:0 4px 12px rgba(0,0,0,0.05);
    transition:.3s;
}
.product-card:hover { transform:translateY(-6px); }

.product-card img {
    width:100%; height:260px; object-fit:cover;
    border-radius:10px 10px 0 0;
}

.product-info { padding:15px; text-align:center; }
.product-info h3 { font-size:18px; margin-bottom:8px; }
.product-meta { color:#777; font-size:14px; margin-bottom:10px; }

.price { font-size:20px; color:#e74c3c; font-weight:600; }

.btn-buy {
    display:block; width:100%; margin-top:10px;
    padding:10px 0; background:#2ecc71; color:white;
    border-radius:6px; text-decoration:none; font-weight:600;
    transition:.3s;
}
.btn-buy:hover { background:#27ae60; }

@media (max-width: 768px) {
    .hero-content h1 { font-size:36px; }
}
</style>

</div> <?php include 'includes/footer.php'; ?>
