<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] != 'POST') { header("Location: index.php"); exit; }

// Lưu thông tin vào Session
$_SESSION['temp_order'] = $_POST; // Đã bao gồm points_used và total_amount mới
$method = $_POST['payment_method'];
$amount = $_POST['total_amount'];

// Nếu chọn COD thì xử lý luôn, không cần hiện bảng VNPAY
if ($method == 'COD') {
    header("Location: process_order.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <title>Cổng thanh toán VNPAY (Demo)</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .vnpay-wrapper { background: #f0f0f0; min-height: 100vh; padding: 20px; font-family: Arial, sans-serif; }
        .vnpay-box { background: white; max-width: 900px; margin: 0 auto; display: flex; box-shadow: 0 5px 15px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        .vnpay-left { width: 40%; padding: 30px; border-right: 1px solid #eee; }
        .vnpay-right { width: 60%; padding: 30px; }
        .qr-box { text-align: center; border: 2px solid #0056b3; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .bank-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 20px; }
        .bank-item { border: 1px solid #ddd; border-radius: 4px; padding: 5px; cursor: pointer; transition: 0.2s; text-align: center;}
        .bank-item:hover { border-color: #0056b3; transform: scale(1.05); }
        .bank-item img { width: 100%; height: auto; }
        .timer { background: #333; color: white; padding: 5px 10px; border-radius: 4px; float: right; }
    </style>
</head>
<body>
    <div class="vnpay-wrapper">
        <div class="vnpay-box">
            <div class="vnpay-left">
                <img src="https://cdn.haitrieu.com/wp-content/uploads/2022/10/Icon-VNPAY-QR.png" alt="VNPAY" style="height: 40px; margin-bottom: 20px;">
                <h3>Thông tin đơn hàng</h3>
                <p style="color: #666; margin-top: 10px;">Số tiền thanh toán:</p>
                <h2 style="color: #0056b3;"><?= number_format($amount) ?> VND</h2>
                <p style="margin-top: 20px;">Nhà cung cấp: <strong>YUUMI SHOP</strong></p>
                
                <div class="qr-box">
                    <p>Quét mã qua App Ngân hàng / Ví điện tử</p>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ThanhToanDonHang_YuumiShop_<?= time() ?>" alt="QR Code">
                    <p style="margin-top: 10px; color: #0056b3; font-weight: bold;">Scan to Pay</p>
                </div>
                <form action="process_order.php" method="POST" style="margin-top: 20px;">
                     <button type="submit" class="btn" style="width: 100%; background: #0056b3;">Xác nhận đã thanh toán trên App</button>
                     <br><br>
                     <a href="cart.php" class="back-link">&larr; Hủy thanh toán, quay lại giỏ hàng</a>
                </form>
            </div>

            <div class="vnpay-right">
                <div style="overflow: hidden; margin-bottom: 20px;">
                    <span style="font-weight: bold;">Chọn phương thức thanh toán</span>
                    <span class="timer">Thời gian: 15:00</span>
                </div>
                
                    <div class="bank-grid">
                        <div class="bank-item"><img src="https://static.wixstatic.com/media/9d8ed5_b92082f54b6143f6bacafff11d0c1d98~mv2.png/v1/fit/w_500,h_500,q_90/file.png" alt="MB"></div>
                        <div class="bank-item"><img src="https://doozypack.vn/wp-content/uploads/2025/09/logo-techcombank-vector-3.jpg" alt="Techcom"></div>
                        <div class="bank-item"><img src="https://hienlaptop.com/wp-content/uploads/2024/12/logo-vietcombank-vector-11.png" alt="VCB"></div>
                        <div class="bank-item"><img src="https://taitailieu.net/wp-content/uploads/2025/10/bidv-logo-4.jpg" alt="BIDV"></div>
                        
                        <div class="bank-item"><img src="https://cdn.haitrieu.com/wp-content/uploads/2022/01/Logo-VietinBank-CTG-Ori.png" alt="VietinBank"></div>
                        <div class="bank-item"><img src="https://static.wixstatic.com/media/9d8ed5_4e857f9bd2dd4e35894dcce89168fc74~mv2.png/v1/fill/w_560,h_560,al_c,q_85,usm_0.66_1.00_0.01,enc_avif,quality_auto/9d8ed5_4e857f9bd2dd4e35894dcce89168fc74~mv2.png" alt="VIB"></div>
                        <div class="bank-item"><img src="https://static.wixstatic.com/media/9d8ed5_9b46446656b14efc88b058399cd81d9a~mv2.png/v1/fill/w_560,h_560,al_c,q_85,usm_0.66_1.00_0.01,enc_avif,quality_auto/9d8ed5_9b46446656b14efc88b058399cd81d9a~mv2.png" alt="TPBank"></div>
                        <div class="bank-item"><img src="https://cdn.haitrieu.com/wp-content/uploads/2022/01/Logo-ACB-Ori.png" alt="ACB"></div>

                        <div class="bank-item"><img src="https://static.wixstatic.com/media/9d8ed5_c1ff4912d7eb4f8b901802156088483d~mv2.png/v1/fit/w_500,h_500,q_90/file.png" alt="Sacombank"></div>
                        <div class="bank-item"><img src="https://dongphucvina.vn/wp-content/uploads/2023/05/logo-scb-dongphucvina.vn1_.png" alt="SCB"></div>
                        <div class="bank-item"><img src="https://cdn.haitrieu.com/wp-content/uploads/2022/01/Icon-VPBank.png" alt="VPBank"></div>
                        <div class="bank-item"><img src="https://cdn.haitrieu.com/wp-content/uploads/2022/01/Logo-HDBank-Ori.png" alt="HDBank"></div>
                    </div>

                <p style="margin-top: 20px; font-size: 13px; color: #888;">Lưu ý: Đây là giao diện giả lập (Demo) phục vụ đồ án. Không thực hiện giao dịch thật.</p>
            </div>
        </div>
    </div>

    <style>
        .back-link{
            display: inline-block;
            color: #666;
            text-decoration: none;
            transition: 0.2s;
        }

        .back-link:hover{
            color: #2ecc71;
        }
    </style>
</body>
</html>