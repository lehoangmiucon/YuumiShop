// assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Tự động ẩn thông báo (Alert) sau 3 giây
    const alertBox = document.querySelector('.alert');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = "opacity 0.5s ease";
            alertBox.style.opacity = "0";
            setTimeout(() => alertBox.remove(), 500);
        }, 3000);
    }

    // 2. Xác nhận trước khi xóa (Dùng cho cả Giỏ hàng và Admin)
    // Tìm tất cả các thẻ a hoặc button có chứa chữ "Xóa" hoặc class 'btn-danger'
    const deleteLinks = document.querySelectorAll('a[href*="remove"], a[href*="delete"], .btn-danger');
    
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('Ní có chắc chắn muốn xóa cái này không? Xóa là mất đó nha!')) {
                e.preventDefault(); // Hủy hành động nếu chọn Cancel
            }
        });
    });

    // 3. Hiệu ứng active menu (Tô đậm menu đang chọn)
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

});