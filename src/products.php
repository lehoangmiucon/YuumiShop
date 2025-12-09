<?php include 'includes/header.php'; ?>

<?php
// --- 1. LOGIC XỬ LÝ DỮ LIỆU & BỘ LỌC ---
$where = "WHERE 1=1";
$params = [];

// Xác định Mode: Xem Thú Cưng hay Xem Hàng Hóa?
$is_pet_page = false;
if (isset($_GET['category']) && $_GET['category'] == 'pet') {
    $is_pet_page = true;
    $where .= " AND category = 'pet'";
} elseif (isset($_GET['category']) && $_GET['category'] == 'supplies') {
    // Supplies nghĩa là tất cả trừ pet
    $where .= " AND category != 'pet'";
} elseif (isset($_GET['category'])) {
    // Chọn danh mục cụ thể (food, accessory, health...)
    $where .= " AND category = ?";
    $params[] = $_GET['category'];
}

// Bộ lọc chung (Tìm kiếm)
if (!empty($_GET['q'])) {
    $where .= " AND name LIKE ?";
    $params[] = "%" . $_GET['q'] . "%";
}

// Bộ lọc riêng cho Thú Cưng
if ($is_pet_page) {
    if (!empty($_GET['species'])) {
        $where .= " AND species = ?";
        $params[] = $_GET['species'];
    }
    if (!empty($_GET['gender'])) {
        $where .= " AND gender = ?";
        $params[] = $_GET['gender'];
    }
    if (!empty($_GET['age_group'])) {
        $where .= " AND age_group = ?";
        $params[] = $_GET['age_group'];
    }
} 
// Bộ lọc riêng cho Hàng Hóa
else {
    if (!empty($_GET['species']) && $_GET['species'] != 'all') {
        $where .= " AND (species = ? OR species = 'all')"; 
        $params[] = $_GET['species'];
    }
    if (!empty($_GET['brand'])) {
        $where .= " AND brand = ?";
        $params[] = $_GET['brand'];
    }
}

// Lọc giá (Chung)
if (!empty($_GET['price_range'])) {
    $range = explode('-', $_GET['price_range']);
    if(count($range) == 2) {
        $where .= " AND price BETWEEN ? AND ?";
        $params[] = $range[0];
        $params[] = $range[1];
    }
}

// Sắp xếp
$order_by = "ORDER BY id DESC";
if (isset($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_asc': $order_by = "ORDER BY price ASC"; break;
        case 'price_desc': $order_by = "ORDER BY price DESC"; break;
    }
}

// --- 2. LOGIC PHÂN TRANG (PAGINATION) ---
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12; // Số sản phẩm trên 1 trang (bạn có thể sửa số này)
$offset = ($page - 1) * $limit;

// Bước 2.1: Đếm tổng số sản phẩm trước để chia trang
$sql_count = "SELECT COUNT(*) FROM products $where";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$total_products = $stmt_count->fetchColumn();
$total_pages = ceil($total_products / $limit);

// Bước 2.2: Query lấy dữ liệu có LIMIT và OFFSET
$sql = "SELECT * FROM products $where $order_by LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div style="background: #f4fcf7; padding: 30px 0; text-align: center; border-bottom: 1px solid #e1e1e1;">
    <h1 style="color: #2c3e50; font-size: 28px;">
        <?php 
            if($is_pet_page) echo "Tìm kiếm Thú Cưng"; 
            elseif(isset($_GET['category']) && $_GET['category']=='health') echo "Y Tế & Sức Khỏe";
            elseif(isset($_GET['category']) && $_GET['category']=='food') echo "Thức Ăn Dinh Dưỡng";
            elseif(isset($_GET['category']) && $_GET['category']=='accessory') echo "Phụ Kiện & Đồ Chơi";
            else echo "Cửa Hàng Yuumi";
        ?>
    </h1>
    <p>Tìm thấy <strong><?= $total_products ?></strong> kết quả (Trang <?= $page ?>/<?= $total_pages > 0 ? $total_pages : 1 ?>)</p>
</div>

<div class="container page-layout">
    <aside class="sidebar">
        <form action="products.php" method="GET" id="filterForm">
            
            <?php if(isset($_GET['q'])): ?><input type="hidden" name="q" value="<?= htmlspecialchars($_GET['q']) ?>"><?php endif; ?>
            <?php if($is_pet_page): ?><input type="hidden" name="category" value="pet"><?php endif; ?>

            <div class="sidebar-section">
                <h3><i class="fas fa-paw"></i> Dành cho</h3>
                <div class="filter-list">
                    <label><input type="radio" name="species" value="" <?= empty($_GET['species'])?'checked':'' ?> onchange="this.form.submit()"> Tất cả</label>
                    <label><input type="radio" name="species" value="dog" <?= (isset($_GET['species']) && $_GET['species']=='dog')?'checked':'' ?> onchange="this.form.submit()"> Chó</label>
                    <label><input type="radio" name="species" value="cat" <?= (isset($_GET['species']) && $_GET['species']=='cat')?'checked':'' ?> onchange="this.form.submit()"> Mèo</label>
                </div>
            </div>

            <?php if($is_pet_page): ?>
                <div class="sidebar-section">
                    <h3><i class="fas fa-venus-mars"></i> Giới tính</h3>
                    <div class="filter-list">
                        <label><input type="radio" name="gender" value="" <?= empty($_GET['gender'])?'checked':'' ?> onchange="this.form.submit()"> Tất cả</label>
                        <label><input type="radio" name="gender" value="male" <?= (isset($_GET['gender']) && $_GET['gender']=='male')?'checked':'' ?> onchange="this.form.submit()"> Đực (Male)</label>
                        <label><input type="radio" name="gender" value="female" <?= (isset($_GET['gender']) && $_GET['gender']=='female')?'checked':'' ?> onchange="this.form.submit()"> Cái (Female)</label>
                    </div>
                </div>
                <div class="sidebar-section">
                    <h3><i class="fas fa-birthday-cake"></i> Độ tuổi</h3>
                    <div class="filter-list">
                        <label><input type="radio" name="age_group" value="baby" <?= (isset($_GET['age_group']) && $_GET['age_group']=='baby')?'checked':'' ?> onchange="this.form.submit()"> Thú con (Baby)</label>
                        <label><input type="radio" name="age_group" value="adult" <?= (isset($_GET['age_group']) && $_GET['age_group']=='adult')?'checked':'' ?> onchange="this.form.submit()"> Trưởng thành</label>
                    </div>
                </div>

            <?php else: ?>
                <div class="sidebar-section">
                    <h3><i class="fas fa-list-ul"></i> Danh mục</h3>
                    <div class="filter-list">
                        <label><input type="radio" name="category" value="supplies" <?= (isset($_GET['category']) && $_GET['category']=='supplies')?'checked':'' ?> onchange="this.form.submit()"> Tất cả</label>
                        <label><input type="radio" name="category" value="food" <?= (isset($_GET['category']) && $_GET['category']=='food')?'checked':'' ?> onchange="this.form.submit()"> Thức ăn</label>
                        <label><input type="radio" name="category" value="accessory" <?= (isset($_GET['category']) && $_GET['category']=='accessory')?'checked':'' ?> onchange="this.form.submit()"> Phụ kiện</label>
                        <label><input type="radio" name="category" value="toy" <?= (isset($_GET['category']) && $_GET['category']=='toy')?'checked':'' ?> onchange="this.form.submit()"> Đồ chơi</label>
                        <label><input type="radio" name="category" value="health" <?= (isset($_GET['category']) && $_GET['category']=='health')?'checked':'' ?> onchange="this.form.submit()"> Y tế</label>
                    </div>
                </div>
            <?php endif; ?>

            <div class="sidebar-section">
                <h3><i class="fas fa-money-bill-wave"></i> Khoảng giá</h3>
                <div class="filter-list">
                    <label><input type="radio" name="price_range" value="" <?= empty($_GET['price_range'])?'checked':'' ?> onchange="this.form.submit()"> Tất cả</label>
                    <label><input type="radio" name="price_range" value="0-100000" <?= (isset($_GET['price_range']) && $_GET['price_range']=='0-100000')?'checked':'' ?> onchange="this.form.submit()"> Dưới 100k</label>
                    <label><input type="radio" name="price_range" value="100000-500000" <?= (isset($_GET['price_range']) && $_GET['price_range']=='100000-500000')?'checked':'' ?> onchange="this.form.submit()"> 100k - 500k</label>
                    <label><input type="radio" name="price_range" value="500000-2000000" <?= (isset($_GET['price_range']) && $_GET['price_range']=='500000-2000000')?'checked':'' ?> onchange="this.form.submit()"> 500k - 2 triệu</label>
                    <label><input type="radio" name="price_range" value="2000000-10000000" <?= (isset($_GET['price_range']) && $_GET['price_range']=='2000000-10000000')?'checked':'' ?> onchange="this.form.submit()"> 2 triệu - 10 triệu</label>
                    <label><input type="radio" name="price_range" value="10000000-100000000" <?= (isset($_GET['price_range']) && $_GET['price_range']=='10000000-100000000')?'checked':'' ?> onchange="this.form.submit()"> Trên 10 triệu</label>
                </div>
            </div>

            <a href="products.php<?= $is_pet_page ? '?category=pet' : '?category=supplies' ?>" class="btn" style="width:100%; text-align:center; background:#bdc3c7; margin-top:10px;">Xóa bộ lọc</a>
        </form>
    </aside>

    <main>
        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
            <select onchange="document.getElementById('sortInput').value=this.value; document.getElementById('filterForm').submit();" style="padding: 8px; border-radius: 5px; border: 1px solid #ddd;">
                <option value="newest">Mới nhất</option>
                <option value="price_asc" <?= (isset($_GET['sort']) && $_GET['sort']=='price_asc')?'selected':'' ?>>Giá thấp đến cao</option>
                <option value="price_desc" <?= (isset($_GET['sort']) && $_GET['sort']=='price_desc')?'selected':'' ?>>Giá cao đến thấp</option>
            </select>
            <input type="hidden" name="sort" id="sortInput" form="filterForm" value="<?= isset($_GET['sort']) ? htmlspecialchars($_GET['sort']) : '' ?>">
        </div>

        <div class="product-grid">
            <?php if (count($products) > 0): ?>
                <?php foreach ($products as $row): ?>
                    <div class="product-card">
                        <?php if($row['old_price'] > 0): ?><span class="badge badge-sale">SALE</span><?php endif; ?>
                        
                        <div class="product-img-wrapper">
                            <a href="product_detail.php?id=<?= $row['id'] ?>">
                                <img src="assets/images/<?= htmlspecialchars($row['image']) ?>" loading="lazy">
                            </a>
                            <div class="product-actions">
                                <a href="product_detail.php?id=<?= $row['id'] ?>" class="btn" title="Xem chi tiết"><i class="fas fa-eye"></i></a>

                                <form action="cart.php" method="POST" style="display:inline;">
                                    <input type="hidden" name="action" value="add">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <input type="hidden" name="redirect" value="cart"> 
                                    <button class="btn" title="Thêm vào giỏ">
                                        <i class="fas fa-cart-plus"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="product-info">
                            <div class="rating">
                                <i class="fas fa-star"></i> <?= $row['rating'] ?> 
                                <?php if($row['category'] == 'pet'): ?>
                                    | <span style="color:#e67e22; font-size:12px;"><?= $row['gender']=='male'?'♂ Đực':'♀ Cái' ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <h3><a href="product_detail.php?id=<?= $row['id'] ?>" style="color:#333; text-decoration:none;"><?= htmlspecialchars($row['name']) ?></a></h3>
                            <div>
                                <span class="price"><?= number_format($row['price']) ?> đ</span>
                                <?php if($row['old_price'] > 0): ?><span class="old-price"><?= number_format($row['old_price']) ?></span><?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 50px; grid-column: 1/-1;">
                    <i class="fas fa-box-open fa-3x" style="color: #ccc;"></i>
                    <p>Không tìm thấy sản phẩm nào!</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
        <div class="pagination" style="margin-top: 40px; display: flex; justify-content: center; gap: 8px;">
            <?php 
            // Giữ lại các tham số lọc trên URL khi chuyển trang
            $query_params = $_GET;
            ?>
            
            <?php if ($page > 1): 
                $query_params['page'] = $page - 1; 
            ?>
                <a href="?<?= http_build_query($query_params) ?>" class="page-link">&laquo;</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++): 
                $query_params['page'] = $i;
            ?>
                <a href="?<?= http_build_query($query_params) ?>" 
                   class="page-link <?= $page == $i ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages): 
                $query_params['page'] = $page + 1; 
            ?>
                <a href="?<?= http_build_query($query_params) ?>" class="page-link">&raquo;</a>
            <?php endif; ?>
        </div>
        
        <style>
            .page-link {
                padding: 8px 14px;
                border: 1px solid #ddd;
                border-radius: 5px;
                text-decoration: none;
                color: #333;
                transition: 0.3s;
                background: white;
            }
            .page-link:hover, .page-link.active {
                background: #2ecc71;
                color: white;
                border-color: #2ecc71;
            }
        </style>
        <?php endif; ?>

        <div class="article-section">
            <div class="article-card">
                <h4 style="color: #2c3e50;"><i class="fas fa-lightbulb"></i> Mẹo chăm sóc</h4>
                <p style="font-size: 13px; color: #666; margin-top: 10px;">Cách chọn thức ăn phù hợp cho chó mèo con để đảm bảo dinh dưỡng phát triển toàn diện...</p>
                <a href="#" style="font-size: 13px; color: #2ecc71;">Đọc thêm &rarr;</a>
            </div>
            <div class="article-card">
                <h4 style="color: #2c3e50;"><i class="fas fa-shipping-fast"></i> Chính sách vận chuyển</h4>
                <p style="font-size: 13px; color: #666; margin-top: 10px;">Miễn phí vận chuyển cho đơn hàng thú cưng trong nội thành. Bảo hành sức khỏe 7 ngày...</p>
                <a href="#" style="font-size: 13px; color: #2ecc71;">Chi tiết &rarr;</a>
            </div>
        </div>

    </main>
</div>

<?php include 'includes/footer.php'; ?>