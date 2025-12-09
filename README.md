🐾 Yuumi Shop - Website Thương Mại Điện Tử Thú Cưng
- Chào mừng bạn đến với Yuumi Shop! Đây là dự án website bán thú cưng (chó, mèo), thức ăn và phụ kiện trọn gói.
- Dự án được xây dựng bằng PHP thuần, MySQL và chạy trên môi trường Docker, đảm bảo dễ dàng cài đặt và triển khai.

🚀 Tính Năng Nổi Bật
🛒 Phía Khách Hàng (Frontend)
- Giao diện hiện đại: Thiết kế UI/UX thân thiện, responsive, tông màu xanh lá chủ đạo tạo cảm giác tươi mới.

- Tìm kiếm & Lọc thông minh:

    - Tìm kiếm theo tên sản phẩm.

    - Bộ lọc chi tiết: Loài (Chó/Mèo), Giống, Độ tuổi, Giới tính, Khoảng giá, Thương hiệu.

- Giỏ hàng thông minh:

    - Tự động lưu giỏ hàng vào Database khi đăng nhập (không bị mất khi đổi thiết bị).

    - Cập nhật số lượng, tính tổng tiền tự động.

- Thanh toán (Checkout):

    - Giao diện thanh toán tập trung (Distraction-free).

    - Hỗ trợ phương thức COD và Quét mã QR (giả lập VNPAY).

- Tích điểm: Tích điểm thưởng sau mỗi đơn hàng thành công.

- Trang cá nhân (Profile):

    - Quản lý thông tin cá nhân, đổi mật khẩu.

    - Xem lịch sử đơn hàng và trạng thái vận chuyển (Real-time).

    - Quản lý hồ sơ thú cưng (Pet Profile).

👑 Phía Quản Trị (Admin Panel)
- Dashboard: Thống kê doanh thu, số lượng khách hàng, đơn hàng, biểu đồ doanh thu trực quan (Chart.js).

- Quản lý Sản phẩm: Thêm, Sửa, Xóa sản phẩm với đầy đủ thông tin (Ảnh, Mô tả, Danh mục, Giới tính...).

- Quản lý Đơn hàng: Xem chi tiết đơn hàng, cập nhật trạng thái (Pending -> Shipped -> Paid).

- Hệ thống tự động: Tự động cộng điểm thưởng cho khách khi đơn hàng hoàn tất.

🛠️ Yêu Cầu Hệ Thống
- Để chạy dự án này, bạn chỉ cần cài đặt:

Docker và Docker Compose.

(Không cần cài XAMPP, WAMP hay PHP, MySQL thủ công trên máy)

📦 Hướng Dẫn Cài Đặt & Chạy Dự Án
Bước 1: Clone hoặc Tải dự án về
Giải nén thư mục dự án (ví dụ: YuumiShop). Cấu trúc thư mục chuẩn sẽ như sau:

- YuumiShop/
- ├── docker-compose.yml
- ├── Dockerfile
- ├── init.sql           <-- Chứa dữ liệu mẫu
- └── src/               <-- Mã nguồn website
    - ├── admin/
    - ├── assets/
    - ├── includes/
    - ├── index.php
    - └── ...
Bước 2: Khởi chạy Docker
Mở Terminal (hoặc CMD/PowerShell) tại thư mục YuumiShop và chạy lệnh:


docker-compose up -d --build
Lệnh này sẽ tự động tải PHP, MySQL, cấu hình server và import dữ liệu từ init.sql.

Chờ khoảng 1-2 phút cho lần chạy đầu tiên.

Bước 3: Truy cập Website
Sau khi Docker chạy xong, mở trình duyệt và truy cập:

Trang chủ: http://localhost:8080

Trang Admin: http://localhost:8080/admin/login.php

🔑 Tài Khoản Mặc Định
- 1. Quản Trị Viên (Admin)
        - Email: admin@gmail.com

        - Mật khẩu: Admin@123 tùy phiên bản init.sql bạn nạp

- 2. Khách Hàng (Customer)
        - Bạn có thể tự Đăng ký tài khoản mới tại trang Đăng ký.

❓ Câu Hỏi Thường Gặp (FAQ)
- Q: Tôi thêm sản phẩm mới nhưng ảnh không hiện? A: Hãy đảm bảo bạn đã copy file ảnh vào đúng thư mục trong src/assets/images/ tương ứng với danh mục (ví dụ: thêm Mèo thì ảnh phải nằm trong src/assets/images/cats/).

- Q: Làm sao để reset lại dữ liệu về ban đầu? A: Chạy lệnh sau trong terminal để xóa sạch container và dữ liệu cũ, sau đó chạy lại:
        - docker-compose down -v
        - docker-compose up -d --build
- Q: Tôi muốn thay đổi cổng chạy web (không thích 8080)? A: Mở file docker-compose.yml, sửa dòng - "8080:80" thành cổng bạn muốn (ví dụ: - "8888:80").

🤝 Đóng Góp
- Dự án được phát triển với mục đích học tập và thực hành Full-stack PHP. Mọi ý kiến đóng góp xin vui lòng liên hệ hoặc tạo Pull Request.

Happy Coding! 🐶🐱
