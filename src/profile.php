<?php
// 1. GỌI DB TRƯỚC (QUAN TRỌNG: KHÔNG GỌI HEADER Ở ĐÂY)
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = "";

// --- XỬ LÝ LOGIC (Code nằm ở đây sẽ không bị lỗi header) ---

// 1. Cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $new_pass = $_POST['new_password'];

    if (!empty($new_pass)) {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=?, password=? WHERE id=?");
        $stmt->execute([$name, $phone, $address, $hash, $user_id]);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
        $stmt->execute([$name, $phone, $address, $user_id]);
    }
    $msg = "Cập nhật thông tin thành công!";
    $_SESSION['user_name'] = $name;
}

// 2. Thêm Thú Cưng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_pet'])) {
    $pet_name = $_POST['pet_name'];
    $pet_type = $_POST['pet_type'];
    $pet_age = $_POST['pet_age'];
    $conn->prepare("INSERT INTO pets (user_id, name, type, age) VALUES (?, ?, ?, ?)")
         ->execute([$user_id, $pet_name, $pet_type, $pet_age]);
    $msg = "Đã thêm hồ sơ thú cưng!";
    // Refresh để tránh gửi lại form
    header("Location: profile.php?tab=pets");
    exit;
}

// 3. Xóa Thú Cưng (Đã fix lỗi header)
if (isset($_GET['del_pet'])) {
    $stmt = $conn->prepare("DELETE FROM pets WHERE id=? AND user_id=?");
    $stmt->execute([$_GET['del_pet'], $user_id]);
    header("Location: profile.php?tab=pets");
    exit;
}

// --- LẤY DỮ LIỆU HIỂN THỊ ---
$user = $conn->prepare("SELECT * FROM users WHERE id=?");
$user->execute([$user_id]);
$u = $user->fetch(PDO::FETCH_ASSOC);

$orders = $conn->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY id DESC");
$orders->execute([$user_id]);

$pets = $conn->prepare("SELECT * FROM pets WHERE user_id=?");
$pets->execute([$user_id]);

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// 2. GIỜ MỚI GỌI HEADER (HTML BẮT ĐẦU TỪ ĐÂY)
include 'includes/header.php';
?>

<div class="container" style="margin-top: 30px; margin-bottom: 50px;">
    <div class="profile-layout">
        <div class="profile-sidebar">
            <div class="user-badge">
                <i class="fas fa-user-circle fa-4x" style="color: #2ecc71;"></i>
                <h3><?= htmlspecialchars($u['name']) ?></h3>
                <p>Thành viên Yuumi Shop</p>
                <div class="points-badge"><i class="fas fa-star"></i> <?= $u['points'] ?> Điểm</div>
            </div>
            <ul class="profile-menu">
                <li><a href="?tab=overview" class="<?= $tab=='overview'?'active':'' ?>"><i class="fas fa-home"></i> Tổng quan</a></li>
                <li><a href="?tab=orders" class="<?= $tab=='orders'?'active':'' ?>"><i class="fas fa-shopping-bag"></i> Đơn hàng</a></li>
                <li><a href="?tab=pets" class="<?= $tab=='pets'?'active':'' ?>"><i class="fas fa-paw"></i> Hồ sơ thú cưng</a></li>
                <li><a href="?tab=settings" class="<?= $tab=='settings'?'active':'' ?>"><i class="fas fa-cog"></i> Cài đặt</a></li>
                <li><a href="logout.php" style="color: red;"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
            </ul>
        </div>

        <div class="profile-content">
            <?php if($msg) echo "<div class='alert' style='background:#d4edda; color:#155724; padding:10px; margin-bottom:20px;'>$msg</div>"; ?>

            <?php if ($tab == 'overview'): ?>
                <h2>👋 Chào mừng, <?= htmlspecialchars($u['name']) ?>!</h2>
                <div class="dashboard-stats">
                    <div class="stat-card"><h3><?= $orders->rowCount() ?></h3><p>Đơn hàng</p></div>
                    <div class="stat-card"><h3><?= $pets->rowCount() ?></h3><p>Thú cưng</p></div>
                    <div class="stat-card"><h3><?= $u['points'] ?></h3><p>Điểm thưởng</p></div>
                </div>

            <?php elseif ($tab == 'orders'): ?>
                <h2>Lịch sử đơn hàng</h2>
                <table class="profile-table">
                    <thead><tr><th>Mã đơn</th><th>Ngày</th><th>Tổng</th><th>Trạng thái</th><th>Chi tiết</th></tr></thead>
                    <tbody>
                        <?php while($ord = $orders->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td>#<?= $ord['id'] ?></td>
                            <td><?= date('d/m/Y', strtotime($ord['created_at'])) ?></td>
                            <td><?= number_format($ord['total_amount']) ?> đ</td>
                            <td><span class="status-badge status-<?= $ord['status'] ?>"><?= ucfirst($ord['status']) ?></span></td>
                            <td><a href="order_success.php?id=<?= $ord['id'] ?>" class="btn-sm">Xem</a></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            <?php elseif ($tab == 'pets'): ?>
                <h2>Hồ sơ thú cưng <button onclick="document.getElementById('addPetForm').style.display='block'" class="btn-sm" style="float:right; margin-left: 5px; margin-top: 5px">+ Thêm bé</button></h2>
                <div id="addPetForm" style="display:none; background:#f9f9f9; padding:15px; margin-bottom:20px; border-radius:8px;">
                    <form method="POST">
                        <input type="text" name="pet_name" placeholder="Tên bé" required style="width:30%; padding:5px;">
                        <select name="pet_type" style="width:30%; padding:5px;"><option value="cat">Mèo</option><option value="dog">Chó</option></select>
                        <input type="number" name="pet_age" placeholder="Tuổi" style="width:20%; padding:5px;">
                        <button type="submit" name="add_pet" class="btn-sm">Lưu</button>
                    </form>
                </div>
                <div class="pet-grid">
                    <?php while($p = $pets->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="pet-card">
                        <div class="pet-icon"><i class="fas fa-<?= $p['type']=='dog'?'dog':'cat' ?>"></i></div>
                        <h4><?= htmlspecialchars($p['name']) ?></h4>
                        <p><?= $p['age'] ?> tuổi</p>
                        <a href="?del_pet=<?= $p['id'] ?>" class="text-danger" style="color:red"  onclick="return confirm('Xóa bé này?');">Xóa</a>
                    </div>
                    <?php endwhile; ?>
                </div>

            <?php elseif ($tab == 'settings'): ?>
                <h2>Cài đặt tài khoản</h2>
                <form method="POST" class="settings-form">
                    <div class="form-group"><label>Họ tên:</label><input type="text" name="name" value="<?= htmlspecialchars($u['name']) ?>" required></div>
                    <div class="form-group"><label>SĐT:</label><input type="text" name="phone" value="<?= htmlspecialchars($u['phone']) ?>"></div>
                    <div class="form-group"><label>Địa chỉ:</label><textarea name="address" rows="3"><?= htmlspecialchars($u['address']) ?></textarea></div>
                    <div class="form-group"><label>Mật khẩu mới:</label><input type="password" name="new_password"></div>
                    <button type="submit" name="update_profile" class="btn">Lưu thay đổi</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .profile-layout { display: flex; gap: 30px; }
    .profile-sidebar { width: 250px; flex-shrink: 0; }
    .profile-content { flex-grow: 1; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .user-badge { text-align: center; margin-bottom: 20px; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .points-badge { background: #f1c40f; color: #333; display: inline-block; padding: 5px 10px; border-radius: 15px; font-size: 14px; font-weight: bold; margin-top: 5px; }
    .profile-menu { list-style: none; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
    .profile-menu li a { display: block; padding: 15px 20px; color: #555; text-decoration: none; border-bottom: 1px solid #eee; transition: 0.3s; }
    .profile-menu li a:hover, .profile-menu li a.active { background: #f8f9fa; color: #2ecc71; border-left: 4px solid #2ecc71; }
    .dashboard-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: #f8f9fa; padding: 20px; text-align: center; border-radius: 10px; border: 1px solid #eee; }
    .stat-card h3 { color: #2ecc71; font-size: 28px; margin-bottom: 5px; }
    .profile-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    .profile-table th, .profile-table td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    .status-badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; color: white; }
    .status-pending { background: #f39c12; } 
    .status-paid { background: #2ecc71;  } 
    .status-shipped { background: #3498db; } 
    .status-cancelled { background: #e74c3c; }
    .pet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top: 20px; }
    .pet-card { border: 2px dashed #ddd; border-radius: 10px; padding: 15px; text-align: center; cursor: pointer; transition: 0.3s; }
    .pet-card:hover { border-color: #2ecc71; background: #f0fff4; }
    .pet-icon { font-size: 40px; color: #aaa; margin-bottom: 10px; }
    .settings-form input, .settings-form textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; }
    .btn-sm { background: #2ecc71; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; text-decoration: none; font-size: 13px; }
    @media (max-width: 768px) { .profile-layout { flex-direction: column; } .profile-sidebar { width: 100%; } }
</style>

<?php include 'includes/footer.php'; ?>