// assets/js/main.js

// Hàm hiển thị Toast Notification
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) {
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

    (container || document.getElementById('toast-container')).appendChild(toast);

    // Tự động tắt sau 3s
    setTimeout(() => {
        toast.style.animation = 'fadeOut 0.5s ease forwards';
        setTimeout(() => toast.remove(), 500);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', function() {

    // 1. Tự động ẩn thông báo Alert tĩnh
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }, 3000);
    }

    // 2. Hiệu ứng active menu
    const currentPath = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll('nav a, .sidebar a');

    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.style.fontWeight = 'bold';
            if (link.closest('.sidebar')) {
                link.style.backgroundColor = '#34495e';
                link.style.borderLeft = '3px solid #f1c40f';
            }
        }
    });

    // 3. Kiểm tra Session Flash Message
    const flashMsg = document.getElementById('flash-message');
    if (flashMsg) {
        const msg = flashMsg.getAttribute('data-msg');
        const type = flashMsg.getAttribute('data-type');
        setTimeout(() => {
            showToast(msg, type);
        }, 100);
    }

    // 4. XỬ LÝ CUSTOM CONFIRM MODAL
    const modal = document.getElementById('custom-confirm-modal');
    const msgBox = document.getElementById('confirm-message');
    const btnConfirm = document.getElementById('btn-confirm');
    const btnCancel = document.getElementById('btn-cancel');

    let targetUrl = null;
    let targetForm = null;

    function openModal(message, url = null, form = null) {
        if (!modal) return;
        if (msgBox) msgBox.textContent = message;
        targetUrl = url;
        targetForm = form;
        modal.style.display = 'flex';
    }

    function closeModal() {
        if (modal) modal.style.display = 'none';
        targetUrl = null;
        targetForm = null;
    }

    if (btnCancel) btnCancel.onclick = closeModal;
    if (modal) {
        window.onclick = function(event) {
            if (event.target == modal) closeModal();
        }
    }

    if (btnConfirm) {
        btnConfirm.onclick = function() {
            if (targetUrl) {
                window.location.href = targetUrl;
            } else if (targetForm) {
                targetForm.submit();
            }
            closeModal();
        };
    }

    // Gắn sự kiện cho các nút xóa
    const confirmLinks = document.querySelectorAll('a[href*="remove"], a[href*="delete"], a.btn-danger, a.btn-remove, a[onclick*="confirm"]');
    confirmLinks.forEach(link => {
        link.removeAttribute('onclick');
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-confirm') || "Bạn có chắc chắn muốn thực hiện hành động này không?";
            openModal(message, this.href, null);
        });
    });

    const confirmBtns = document.querySelectorAll('button[onclick*="confirm"], form button.btn-danger');
    confirmBtns.forEach(btn => {
        btn.removeAttribute('onclick');
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const message = this.getAttribute('data-confirm') || "Bạn có chắc chắn muốn xóa mục này không?";
            const parentForm = this.closest('form');
            if (parentForm) {
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

    // --- AJAX ADD TO CART (Đã sửa logic Mua Ngay) ---
    // Chọn tất cả form cart
    const allCartForms = document.querySelectorAll('form[action="cart.php"]');
    
    allCartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // QUAN TRỌNG: Kiểm tra nếu form có class 'no-ajax' thì BỎ QUA logic Ajax -> để nó submit bình thường
            if (this.classList.contains('no-ajax')) {
                return; // Thoát khỏi hàm, cho phép form submit chuyển trang
            }

            // Chỉ xử lý Ajax nếu action là 'add' (để nút +/- trong giỏ hàng hoạt động riêng)
            const actionInput = this.querySelector('input[name="action"]');
            if (actionInput && actionInput.value === 'add') {
                e.preventDefault(); // Chặn reload trang chỉ khi là Ajax

                const formData = new FormData(this);
                formData.append('ajax_mode', '1');

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
                        if (data.status === 'success') {
                            showToast(data.message, 'success');
                            const cartBadge = document.querySelector('.cart-btn .badge');
                            if (cartBadge) {
                                cartBadge.textContent = data.total_qty;
                                cartBadge.style.transform = 'scale(1.5)';
                                setTimeout(() => cartBadge.style.transform = 'scale(1)', 200);
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error))
                    .finally(() => {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    });
            }
        });
    });

});