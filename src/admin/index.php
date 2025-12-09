<?php
require_once '../includes/db.php';

// Check quyền Admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    if($_SESSION['user_role'] == 'admin') {
        header("Location: admin/index.php");
    }else{
        header("Location: index.php");   
    }
    exit;
}

// 1. Thống kê tổng quan
$userCount = $conn->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$productCount = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
$orderCount = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$revenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status='paid'")->fetchColumn() ?: 0;

// 2. Lấy 5 đơn hàng mới nhất
$recentOrders = $conn->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5");

// 3. --- LOGIC BIỂU ĐỒ: ĐIỀN ĐẦY ĐỦ DỮ LIỆU 7 NGÀY ---
// Tạo mảng 7 ngày gần nhất với giá trị mặc định là 0
$last7Days = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $last7Days[$date] = 0;
}

// Lấy dữ liệu từ DB
$sqlChart = "
    SELECT DATE(created_at) as date, SUM(total_amount) as total 
    FROM orders 
    WHERE status = 'paid' 
    AND created_at >= DATE(NOW()) - INTERVAL 7 DAY
    GROUP BY DATE(created_at)
";
$chartData = $conn->query($sqlChart)->fetchAll(PDO::FETCH_ASSOC);

// Gộp dữ liệu DB vào mảng 7 ngày (Ghi đè những ngày có doanh thu)
foreach($chartData as $data) {
    $last7Days[$data['date']] = (int)$data['total'];
}

// Tách ra 2 mảng để đưa vào Chart.js
$dates = [];
$totals = [];
foreach ($last7Days as $date => $total) {
    $dates[] = date('d/m', strtotime($date));
    $totals[] = $total;
}

$jsonDates = json_encode($dates);
$jsonTotals = json_encode($totals);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-layout">
    <div class="sidebar">
        <h3>Yuumi Admin</h3>
        <a href="index.php" style="background: #34495e; border-left: 3px solid #f1c40f;">Dashboard</a>
        <a href="products.php">Quản lý Sản phẩm</a>
        <a href="orders.php">Quản lý Đơn hàng</a>
        <a href="reviews.php">Quản lý Đánh giá</a>
        <a href="users.php">Quản lý Khách hàng</a>
        <a href="../index.php">Về trang chủ</a>
        <a href="../logout.php">Đăng xuất</a>
    </div>

    <div class="content">
        <h2 style="margin-bottom: 30px;">Tổng quan kinh doanh</h2>
        
        <div class="grid-stats">
            <div class="stat-box" style="border-left: 4px solid #3498db;">
                <div class="stat-icon" style="background: #eaf2f8; color: #3498db;"><i class="fas fa-users"></i></div>
                <div>
                    <div class="stat-number"><?= number_format($userCount) ?></div>
                    <div class="stat-label">Khách hàng</div>
                </div>
            </div>
            <div class="stat-box" style="border-left: 4px solid #2ecc71;">
                <div class="stat-icon" style="background: #eafaf1; color: #2ecc71;"><i class="fas fa-box"></i></div>
                <div>
                    <div class="stat-number"><?= number_format($productCount) ?></div>
                    <div class="stat-label">Sản phẩm</div>
                </div>
            </div>
            <div class="stat-box" style="border-left: 4px solid #f1c40f;">
                <div class="stat-icon" style="background: #fef9e7; color: #f1c40f;"><i class="fas fa-shopping-cart"></i></div>
                <div>
                    <div class="stat-number"><?= number_format($orderCount) ?></div>
                    <div class="stat-label">Tổng đơn hàng</div>
                </div>
            </div>
            <div class="stat-box" style="border-left: 4px solid #e74c3c;">
                <div class="stat-icon" style="background: #fdedec; color: #e74c3c;"><i class="fas fa-money-bill-wave"></i></div>
                <div>
                    <div class="stat-number"><?= number_format($revenue) ?> đ</div>
                    <div class="stat-label">Doanh thu</div>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 30px;">
            <div class="chart-box" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3><i class="fas fa-chart-line"></i> Biểu đồ doanh thu (7 ngày qua)</h3>
                <canvas id="revenueChart"></canvas>
            </div>

            <div class="recent-orders" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
                <h3><i class="fas fa-clock"></i> Đơn hàng mới</h3>
                <ul style="list-style: none; padding: 0; margin-top: 15px;">
                    <?php while($ord = $recentOrders->fetch(PDO::FETCH_ASSOC)): ?>
                    <li style="border-bottom: 1px solid #eee; padding: 10px 0; display: flex; justify-content: space-between;">
                        <div>
                            <strong>#<?= $ord['id'] ?></strong> - <?= htmlspecialchars($ord['fullname']) ?>
                            <br><small style="color: #888;"><?= date('d/m/Y H:i', strtotime($ord['created_at'])) ?></small>
                        </div>
                        <div style="text-align: right;">
                            <span style="font-weight: bold;"><?= number_format($ord['total_amount']) ?> đ</span>
                            <br><span class="status-badge status-<?= $ord['status'] ?>" style="font-size: 10px;"><?= ucfirst($ord['status']) ?></span>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <a href="orders.php" style="display: block; text-align: center; margin-top: 15px; color: #2ecc71; font-weight: bold;">Xem tất cả</a>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Nhận dữ liệu từ PHP (Đã encode JSON)
    const labels = <?= $jsonDates ?>;
    const data = <?= $jsonTotals ?>;

    // Nếu chưa có dữ liệu (Web mới chạy), tạo data giả để chart không bị lỗi
    if(labels.length === 0) {
        labels.push('Hôm nay');
        data.push(0);
    }

    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Doanh thu thực tế (VNĐ)',
                data: data,
                borderColor: '#2ecc71', // Màu xanh thương hiệu
                backgroundColor: 'rgba(46, 204, 113, 0.2)',
                borderWidth: 2,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#2ecc71',
                pointRadius: 4,
                fill: true,
                tension: 0.4 // Đường cong mềm mại
            }]
        },
        options: { 
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        // Format tiền Việt Nam Đồng
                        callback: function(value) {
                            return new Intl.NumberFormat('vi-VN').format(value) + ' đ';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('vi-VN').format(context.parsed.y) + ' đ';
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
</script>

<style>
    .grid-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
    .stat-box { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); display: flex; align-items: center; gap: 15px; }
    .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    .stat-number { font-size: 20px; font-weight: bold; color: #333; }
    .stat-label { font-size: 13px; color: #777; }
    
    /* Responsive cho mobile */
    @media (max-width: 768px) {
        .grid-stats { grid-template-columns: 1fr 1fr; }
        .content > div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
    }
</style>
</body>
</html>