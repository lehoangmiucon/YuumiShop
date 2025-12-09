<?php if (basename($_SERVER['PHP_SELF']) != 'index.php'): ?>
</div> <?php endif; ?>

</main>

<footer style="background: #2c3e50; color: #ecf0f1; padding-top: 50px; margin-top: auto; width: 100%;">
<div class="container" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 30px; margin-bottom: 30px;">
<div>
<h3 style="color: #2ecc71; margin-bottom: 20px;">YUUMI SHOP</h3>
<p style="font-size: 14px; color: #bdc3c7; line-height: 1.6;">
Chuyên cung cấp các sản phẩm tốt nhất cho thú cưng của bạn. Uy tín - Chất lượng - Tận tâm.
</p>
<div style="margin-top: 20px;">
<a href="#" style="color: white; margin-right: 15px;"><i class="fab fa-facebook fa-lg"></i></a>
<a href="#" style="color: white; margin-right: 15px;"><i class="fab fa-instagram fa-lg"></i></a>
<a href="#" style="color: white;"><i class="fab fa-tiktok fa-lg"></i></a>
</div>
</div>

<div>
<h4 style="margin-bottom: 20px;">Liên kết nhanh</h4>
<ul style="list-style: none;">
<li style="margin-bottom: 10px;"><a href="index.php" style="color: #bdc3c7; text-decoration: none;">Trang chủ</a></li>
<li style="margin-bottom: 10px;"><a href="products.php" style="color: #bdc3c7; text-decoration: none;">Sản phẩm</a></li>
<li style="margin-bottom: 10px;"><a href="#" style="color: #bdc3c7; text-decoration: none;">Giới thiệu</a></li>
</ul>
</div>

<div>
<h4 style="margin-bottom: 20px;">Chính sách</h4>
<ul style="list-style: none;">
<li style="margin-bottom: 10px;"><a href="#" style="color: #bdc3c7; text-decoration: none;">Chính sách đổi trả</a></li>
<li style="margin-bottom: 10px;"><a href="#" style="color: #bdc3c7; text-decoration: none;">Bảo mật thông tin</a></li>
<li style="margin-bottom: 10px;"><a href="#" style="color: #bdc3c7; text-decoration: none;">Thanh toán & Vận chuyển</a></li>
</ul>
</div>

<div>
<h4 style="margin-bottom: 20px;">Đăng ký nhận tin</h4>
<p style="font-size: 13px; color: #bdc3c7; margin-bottom: 15px;">Nhận mã giảm giá 10% ngay.</p>
<form style="display: flex;">
<input type="email" placeholder="Email..." style="padding: 10px; border: none; border-radius: 4px 0 0 4px; flex: 1; outline: none;">
<button style="background: #2ecc71; color: white; border: none; padding: 10px; border-radius: 0 4px 4px 0; cursor: pointer;"><i class="fas fa-paper-plane"></i></button>
</form>
<p style="margin-top: 20px; font-size: 14px;"><i class="fas fa-phone-alt"></i> Hotline: 1900 1000</p>
</div>
</div>

<div style="text-align:center; padding-bottom: 20px;">
<h4 style="margin-bottom: 20px;">Vị trí trên Google Maps</h4>
<div class="container">
<div class="agbg-border" style="width: 100%; padding: 5px;">
<div style="background:white; border-radius:10px; overflow:hidden;">
<iframe
src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.424229302633!2d106.69312131462254!3d10.778786292319224!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f385570472f%3A0x1787491df6ed1d64!2zRGluaCDEkOG7mWMgTOG6rXA!5e0!3m2!1svi!2s!4v1646812345678!5m2!1svi!2s"
width="100%"
height="200"
style="border:0; display:block;"
allowfullscreen=""
loading="lazy">
</iframe>
</div>
</div>
</div>
</div>

<div style="background: #22313f; text-align: center; padding: 20px;">
<p style="font-size: 13px;">© 2025 Yuumi Shop. All rights reserved.</p>
</div>
</footer>

<?php if (isset($_SESSION['flash_msg'])): ?>
<div id="flash-message"
data-msg="<?= htmlspecialchars($_SESSION['flash_msg']['msg']) ?>"
data-type="<?= htmlspecialchars($_SESSION['flash_msg']['type']) ?>"
style="display: none;"></div>
<?php unset($_SESSION['flash_msg']); // Xóa session ngay sau khi render ?>
<?php endif; ?>

<div id="toast-container"></div>

<div id="custom-confirm-modal" class="modal-overlay" style="display: none;">
<div class="modal-box">
<div class="modal-icon warning"><i class="fas fa-exclamation-triangle"></i></div>
<h3>Xác nhận hành động</h3>
<p id="confirm-message">Bạn có chắc chắn muốn thực hiện hành động này?</p>
<div class="modal-actions">
<button id="btn-cancel" class="btn-secondary">Hủy bỏ</button>
<button id="btn-confirm" class="btn-danger">Đồng ý</button>
</div>
</div>
</div>

<?php
// Kiểm tra xem đang ở thư mục admin hay root để chỉnh đường dẫn JS
$js_path = file_exists('assets/js/main.js') ? 'assets/js/main.js' : '../assets/js/main.js';
?>
<script src="<?= $js_path ?>"></script>

</body>
</html>
