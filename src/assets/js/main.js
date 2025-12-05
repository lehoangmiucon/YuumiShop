// assets/js/main.js

// Hàm hiển thị Toast Notification (Đặt ngoài DOMContentLoaded để gọi được từ bất cứ đâu nếu cần)
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) {
        // Tạo container nếu chưa có (phòng hờ)
        const div = document.createElement('div');
        div.id = 'toast-container';
        document.body.appendChild(div);
    }

    const toast = document.createElement('div');
    toast.classList.add('toast', type);
    
    // Icon tùy loại
    let icon = 'fa-check-circle';
    if (type === 'error') icon = 'fa-exclamation-circle';
    if (type === 'info') icon = 'fa-info-circle';

    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${icon} toast-icon"></i>
            <span class="toast-msg">${message}</span>
        </div>
        <i class="fas fa-times toast-close" onclick="this.parentElement.remove()"></i>
    `;

    // Nếu container chưa tồn tại lúc gọi hàm, dòng này sẽ lấy container vừa tạo
    (container || document.getElementById('toast-container')).appendChild(toast);

    // Tự động tắt sau 3s (hiện 3s + 0.5s fadeOut = 3.5s)
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.5s ease forwards';
        setTimeout(() => toast.remove(), 500);
    }, 4000); 
}

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Tự động ẩn thông báo Alert tĩnh (của Bootstrap hoặc code cũ) sau 3 giây
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }, 3000);
    }

    // 2. Hiệu ứng active menu (Tô đậm menu đang chọn)
    const currentPath = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll('nav a, .sidebar a');
    
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.style.fontWeight = 'bold';
            link.style.textDecoration = 'underline';
            if(link.closest('.sidebar')) {
                link.style.backgroundColor = '#34495e';
                link.style.borderLeft = '3px solid #f1c40f';
            }
        }
    });

    // 3. Kiểm tra Session Flash Message từ PHP (Toast sau khi chuyển trang)
    const flashMsg = document.getElementById('flash-message');
    if (flashMsg) {
        const msg = flashMsg.getAttribute('data-msg');
        const type = flashMsg.getAttribute('data-type');
        
        // Thêm delay nhỏ để đảm bảo DOM đã render xong và hiệu ứng trượt ra mượt mà hơn
        setTimeout(() => {
            showToast(msg, type);
        }, 100); 
    }

    // 4. XỬ LÝ CUSTOM CONFIRM MODAL (Thay thế confirm() mặc định)
    const modal = document.getElementById('custom-confirm-modal');
    const msgBox = document.getElementById('confirm-message');
    const btnConfirm = document.getElementById('btn-confirm');
    const btnCancel = document.getElementById('btn-cancel');
    
    let targetUrl = null; // Lưu URL nếu là thẻ <a>
    let targetForm = null; // Lưu form nếu là nút submit/button trong form

    // Hàm mở modal
    function openModal(message, url = null, form = null) {
        if (!modal) return; // Phòng hờ footer chưa có modal
        if (msgBox) msgBox.textContent = message;
        targetUrl = url;
        targetForm = form;
        modal.style.display = 'flex';
    }

    // Hàm đóng modal
    function closeModal() {
        if (modal) modal.style.display = 'none';
        targetUrl = null;
        targetForm = null;
    }

    // Gán sự kiện cho nút Cancel và click ra ngoài overlay
    if (btnCancel) btnCancel.onclick = closeModal;
    if (modal) {
        window.onclick = function(event) {
            if (event.target == modal) closeModal();
        }
    }

    // Gán sự kiện cho nút Confirm (Đồng ý hành động)
    if (btnConfirm) {
        btnConfirm.onclick = function() {
            if (targetUrl) {
                // Nếu là link <a>: Chuyển trang
                window.location.href = targetUrl; 
            } else if (targetForm) {
                // Nếu là form: Submit form đó
                // Tạo một input ẩn tạm thời để giả lập việc click nút submit (để PHP bắt được isset($_POST['...']))
                // Tuy nhiên, cách đơn giản nhất cho các form xóa trong project này là submit thẳng form.
                targetForm.submit(); 
            }
            closeModal();
        };
    }

    // --- TỰ ĐỘNG GẮN SỰ KIỆN CHO CÁC NÚT XÓA ---
    
    // A. Đối với thẻ <a> (Link xóa)
    // Selector tìm: 
    // 1. Link có href chứa "remove" hoặc "delete"
    // 2. Link có class "btn-danger" hoặc "btn-remove"
    // 3. Link có thuộc tính onclick chứa "confirm" (code cũ)
    const confirmLinks = document.querySelectorAll('a[href*="remove"], a[href*="delete"], a.btn-danger, a.btn-remove, a[onclick*="confirm"]');
    
    confirmLinks.forEach(link => {
        // Gỡ bỏ sự kiện onclick cũ (cái alert mặc định) để tránh xung đột
        link.removeAttribute('onclick'); 
        
        // Gán sự kiện click mới hiện Modal
        link.addEventListener('click', function(e) {
            e.preventDefault(); // Chặn chuyển trang ngay lập tức
            
            // Lấy thông báo từ data-confirm (nếu có), không thì dùng mặc định
            const message = this.getAttribute('data-confirm') || "Bạn có chắc chắn muốn thực hiện hành động này không? Hành động này không thể hoàn tác.";
            
            openModal(message, this.href, null);
        });
    });

    // B. Đối với thẻ <button> (Nút xóa trong Form, ví dụ Admin Reviews)
    // Selector tìm các nút có onclick chứa "confirm" hoặc class btn-danger nằm trong form
    const confirmBtns = document.querySelectorAll('button[onclick*="confirm"], form button.btn-danger');
    
    confirmBtns.forEach(btn => {
        btn.removeAttribute('onclick');
        
        btn.addEventListener('click', function(e) {
            e.preventDefault(); // Chặn submit form ngay lập tức
            
            const message = this.getAttribute('data-confirm') || "Bạn có chắc chắn muốn xóa mục này không?";
            
            // Tìm form cha gần nhất
            const parentForm = this.closest('form');
            if (parentForm) {
                // Nếu nút này có name và value (ví dụ name="delete_product"), ta cần đảm bảo
                // khi submit form, dữ liệu đó vẫn được gửi đi.
                // Cách an toàn: Thêm input hidden vào form trước khi submit (nếu chưa có)
                if (this.name && this.value) {
                    let hiddenInput = parentForm.querySelector(`input[name="${this.name}"]`);
                    if (!hiddenInput) {
                        hiddenInput = document.createElement('input');
                        hiddenInput.type = 'hidden';
                        hiddenInput.name = this.name;
                        parentForm.appendChild(hiddenInput);
                    }
                    hiddenInput.value = this.value;
                }
                
                openModal(message, null, parentForm);
            }
        });
    });

        // --- AJAX ADD TO CART ---
    // Tìm tất cả các form thêm vào giỏ hàng
    const addCartForms = document.querySelectorAll('form[action="cart.php"]');

    addCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Chỉ chặn submit mặc định nếu action là 'add' (để nút Remove trong giỏ hàng vẫn chạy bình thường)
            const actionInput = this.querySelector('input[name="action"]');
            if (actionInput && actionInput.value === 'add') {
                e.preventDefault(); // Chặn reload trang
                
                const formData = new FormData(this);
                formData.append('ajax_mode', '1'); // Đánh dấu đây là request Ajax

                // Hiệu ứng loading cho nút bấm (Optional)
                const btn = this.querySelector('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
                btn.disabled = true;

                fetch('cart.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        // 1. Hiện thông báo Toast
                        showToast(data.message, 'success');
                        
                        // 2. Cập nhật số lượng trên icon giỏ hàng header
                        const cartBadge = document.querySelector('.cart-btn .badge');
                        if(cartBadge) {
                            cartBadge.textContent = data.total_qty;
                            // Hiệu ứng nảy lên
                            cartBadge.style.transform = 'scale(1.5)';
                            setTimeout(() => cartBadge.style.transform = 'scale(1)', 200);
                        }
                    }
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    // Trả lại nút bấm như cũ
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
            }
        });
    });

});