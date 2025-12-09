SET NAMES utf8mb4;

-- =============================================
-- 1. BẢNG USERS (Đã thêm reset_token)
-- =============================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('user', 'admin') DEFAULT 'user',
    points INT DEFAULT 0,
    -- Hai cột mới cho tính năng quên mật khẩu
    reset_token VARCHAR(255) NULL,
    reset_token_expire DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- 2. BẢNG PRODUCTS
-- =============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    species ENUM('dog', 'cat', 'all') NOT NULL, 
    category ENUM('food', 'accessory', 'health', 'toy', 'cage', 'pet') NOT NULL, 
    sub_category VARCHAR(50), 
    brand VARCHAR(50), 
    age_group ENUM('baby', 'adult', 'senior', 'all') DEFAULT 'all',
    gender ENUM('male', 'female', 'unisex') DEFAULT 'unisex',
    price DECIMAL(15, 0) NOT NULL,
    old_price DECIMAL(15, 0) DEFAULT 0,
    image VARCHAR(255),
    rating FLOAT DEFAULT 5,
    description TEXT,
    sold_count INT DEFAULT 0,
    is_new BOOLEAN DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- 3. BẢNG ORDERS
-- =============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    fullname VARCHAR(100),
    phone VARCHAR(20),
    address TEXT,
    note TEXT, -- Đã thêm cột note cho đơn hàng
    payment_method VARCHAR(50) DEFAULT 'COD',
    total_amount DECIMAL(15, 0) NOT NULL,
    status ENUM('pending', 'paid', 'shipped', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- 4. BẢNG ORDER ITEMS
-- =============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(15, 0) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- 5. BẢNG REVIEWS
-- =============================================
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    rating INT DEFAULT 5,
    comment TEXT,
    pros TEXT,
    cons TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- 6. BẢNG PETS
-- =============================================
CREATE TABLE IF NOT EXISTS pets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    type ENUM('dog', 'cat', 'other') NOT NULL,
    age INT,
    weight DECIMAL(5,2),
    favorite_food VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 7. BẢNG CART
CREATE TABLE IF NOT EXISTS cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- DATA MẪU
-- =============================================

-- 1. Admin (Pass: Admin@123)
INSERT INTO users (name, email, password, role, points) 
VALUES ('Administrator', 'admin@gmail.com', '$2b$10$JUw0.X0O8tN/MYxaGT9f7O8/Xi/mtqQ3KD2zOSKw6wRUXN65Z.lGC', 'admin', 1000);

-- 2. INSERT SẢN PHẨM: THÚ CƯNG
INSERT INTO products (id, name, species, category, sub_category, brand, age_group, gender, price, old_price, image, description, rating, sold_count) VALUES 
-- MÈO CŨ
(101, 'Mèo Anh Lông Ngắn', 'cat', 'pet', 'British Shorthair', 'Yuumi Farm', 'baby', 'male', 6500000, 7000000, 'cats/MeoALN.jpg', 'Mèo ALN thuần chủng, mặt bánh bao.', 5, 20),
(102, 'Mèo Munchkin Chân Ngắn', 'cat', 'pet', 'Munchkin', 'Yuumi Farm', 'baby', 'female', 12000000, 12500000, 'cats/MeoMunchkin.jpg', 'Chân ngắn siêu cute.', 5, 15),
(103, 'Mèo Sphynx', 'cat', 'pet', 'Sphynx', 'Import', 'baby', 'male', 15000000, 0, 'cats/MeoSphynx.jpg', 'Mèo không lông độc đáo.', 4.8, 5),
(104, 'Mèo Ragdoll', 'cat', 'pet', 'Ragdoll', 'Import', 'baby', 'female', 18000000, 20000000, 'cats/MeoRagdoll.jpg', 'Mắt xanh biếc, lông dài.', 5, 8),
(105, 'Mèo Ba Tư', 'cat', 'pet', 'Persian', 'Yuumi Farm', 'baby', 'male', 5500000, 0, 'cats/MeoBaTu.jpg', 'Mặt tịt quý tộc.', 4.5, 12),
(106, 'Mèo Bengal', 'cat', 'pet', 'Bengal', 'Import', 'baby', 'male', 20000000, 0, 'cats/MeoBengal.jpg', 'Họa tiết da báo.', 5, 3),
(107, 'Mèo Maine Coon', 'cat', 'pet', 'Maine Coon', 'Import', 'baby', 'male', 25000000, 28000000, 'cats/MeoMaineCoon.jpg', 'Khổng lồ thông minh.', 5, 4),
(108, 'Mèo Xiêm', 'cat', 'pet', 'Siamese', 'Yuumi Farm', 'baby', 'female', 3000000, 0, 'cats/MeoXiem.jpg', 'Hoàng gia Thái Lan.', 4.2, 30),
(109, 'Mèo Tai Cụp', 'cat', 'pet', 'Scottish Fold', 'Yuumi Farm', 'baby', 'female', 7000000, 7500000, 'cats/MeoTaiCup.jpg', 'Tai cụp đáng yêu.', 4.9, 25),
(110, 'Mèo Abyssinian', 'cat', 'pet', 'Abyssinian', 'Import', 'baby', 'male', 8000000, 0, 'cats/MeoAbyssinian.jpg', 'Dáng vẻ hoang dã.', 4.7, 6),
(111, 'Mèo Maine Coon', 'cat', 'pet', 'Maine Coon', 'Import', 'baby', 'female', 22000000, 0, 'cats/MeoMaineCoon2.jpg', 'Maine Coon lớn, thông minh.', 5, 0),
(112, 'Mèo Bengal', 'cat', 'pet', 'Bengal', 'Import', 'baby', 'male', 20000000, 0, 'cats/MeoBengal2.jpg', 'Mèo Bengal vằn da báo.', 4.9, 0),
(113, 'Mèo Ragdoll', 'cat', 'pet', 'Ragdoll', 'Import', 'baby', 'female', 18000000, 0, 'cats/MeoRagdoll2.jpg', 'Mèo Ragdoll hiền, thân thiện.', 5, 0),
(114, 'Mèo Siamese', 'cat', 'pet', 'Siamese', 'Yuumi Farm', 'baby', 'female', 3200000, 0, 'cats/MeoSiamese.jpg', 'Mèo Xiêm thông minh.', 4.5, 0),
(115, 'Mèo Norwegian Forest', 'cat', 'pet', 'Norwegian Forest', 'Import', 'baby', 'female', 12000000, 0, 'cats/MeoNorwegianForest.jpg', 'Mèo rừng Na Uy lông dài.', 4.8, 0),
(116, 'Mèo American Shorthair', 'cat', 'pet', 'American Shorthair', 'Yuumi Farm', 'baby', 'male', 5000000, 0, 'cats/MeoAmericanShorthair.jpg', 'Mèo ALN Mỹ, dễ nuôi.', 4.6, 0),
(117, 'Mèo Scottish Fold)', 'cat', 'pet', 'Scottish Fold', 'Yuumi Farm', 'baby', 'female', 6500000, 0, 'cats/MeoScottishFold2.jpg', 'Mèo tai cụp đáng yêu.', 4.7, 0),
(118, 'Mèo Abyssinian', 'cat', 'pet', 'Abyssinian', 'Import', 'baby', 'male', 8000000, 0, 'cats/MeoAbyssinian2.jpg', 'Mèo Abyssinian năng động.', 4.5, 0),
(119, 'Mèo American Bobtail', 'cat', 'pet', 'American Bobtail', 'Import', 'baby', 'female', 10000000, 0, 'cats/MeoAmericanBobtail.jpg', 'Mèo Bobtail đuôi ngắn.', 4.6, 0),
(120, 'Mèo Savannah', 'cat', 'pet', 'Savannah', 'Import', 'baby', 'male', 25000000, 0, 'cats/MeoSavannah.jpg', 'Mèo Savannah lai hoang dã.', 4.9, 0),

-- 3 MÈO MỚI
(121, 'Mèo Anh Lông Dài', 'cat', 'pet', 'British Longhair', 'Yuumi Farm', 'baby', 'male', 7500000, 8000000, 'cats/MeoALD.jpg', 'Phiên bản lông dài của ALN, cực sang chảnh.', 5, 5),
(122, 'Mèo Exotic', 'cat', 'pet', 'Exotic Shorthair', 'Import', 'baby', 'male', 9000000, 10000000, 'cats/MeoExotic.jpg', 'Mèo Ba Tư lông ngắn, mặt tịt siêu ngố.', 4.8, 2),
(123, 'Mèo Nga Mắt Xanh', 'cat', 'pet', 'Russian Blue', 'Import', 'baby', 'female', 11000000, 0, 'cats/MeoRussianBlue.jpg', 'Bộ lông xám xanh đặc trưng, mắt xanh ngọc bích.', 5, 1),


-- CHÓ CŨ
(151, 'Chó Poodle Toy', 'dog', 'pet', 'Poodle', 'Yuumi Farm', 'baby', 'male', 7000000, 0, 'dogs/ChoPoodle.jpg', 'Lông xoăn không rụng.', 5, 50),
(152, 'Chó Corgi Mông Bự', 'dog', 'pet', 'Corgi', 'Yuumi Farm', 'baby', 'female', 14000000, 15000000, 'dogs/ChoCorgi.jpg', 'Chân ngắn mông to.', 5, 35),
(153, 'Chó Alaska', 'dog', 'pet', 'Alaska', 'Yuumi Farm', 'baby', 'male', 11000000, 0, 'dogs/ChoAlaska.jpg', 'To lớn thân thiện.', 4.8, 20),
(154, 'Chó Shiba Inu', 'dog', 'pet', 'Shiba', 'Import', 'baby', 'male', 16000000, 0, 'dogs/ChoShiba.jpg', 'Quốc khuyển Nhật.', 5, 18),
(155, 'Chó Chihuahua', 'dog', 'pet', 'Chihuahua', 'Yuumi Farm', 'baby', 'female', 4000000, 0, 'dogs/ChoChihuahua.jpg', 'Nhỏ nhắn dễ nuôi.', 4.3, 40),
(156, 'Chó Pug Mặt Xệ', 'dog', 'pet', 'Pug', 'Yuumi Farm', 'baby', 'male', 5000000, 5500000, 'dogs/ChoPug.jpg', 'Mặt nhăn ăn nhiều.', 4.6, 22),
(157, 'Chó Doberman', 'dog', 'pet', 'Doberman', 'Import', 'baby', 'male', 15000000, 0, 'dogs/ChoDoberman.jpg', 'Giữ nhà cực tốt.', 4.9, 7),
(158, 'Chó Beagle', 'dog', 'pet', 'Beagle', 'Yuumi Farm', 'baby', 'female', 6000000, 0, 'dogs/ChoBeagle.jpg', 'Tai dài mũi thính.', 4.5, 15),
(159, 'Chó Bắc Kinh', 'dog', 'pet', 'Pekingese', 'Yuumi Farm', 'baby', 'female', 4500000, 0, 'dogs/ChoPekingese.jpg', 'Quý tộc cổ xưa.', 4.2, 10),
(160, 'Chó Poodle Tiny', 'dog', 'pet', 'Poodle', 'Yuumi Farm', 'baby', 'female', 8500000, 9000000, 'dogs/ChoPoodleTiny.jpg', 'Siêu nhỏ bỏ túi.', 5, 45),
(161, 'Chó Labrador Retriever', 'dog', 'pet', 'Labrador', 'Yuumi Farm', 'baby', 'male', 12000000, 0, 'dogs/ChoLabrador.jpg', 'Labrador thân thiện.', 5, 0),
(162, 'Chó German Shepherd', 'dog', 'pet', 'German Shepherd', 'Yuumi Farm', 'baby', 'male', 13000000, 0, 'dogs/ChoGermanShepherd.jpg', 'Chó Becgie thông minh.', 4.9, 0),
(163, 'Chó French Bulldog', 'dog', 'pet', 'French Bulldog', 'Yuumi Farm', 'baby', 'female', 9000000, 0, 'dogs/ChoFrenchBulldog.jpg', 'Frenchie nhỏ nhắn.', 4.8, 0),
(164, 'Chó Dachshund', 'dog', 'pet', 'Dachshund', 'Yuumi Farm', 'baby', 'female', 5500000, 0, 'dogs/ChoDachshund.jpg', 'Chó Lạp xưởng chân ngắn.', 4.5, 0),
(165, 'Chó Husky', 'dog', 'pet', 'Husky', 'Import', 'baby', 'male', 14000000, 0, 'dogs/ChoHusky.jpg', 'Husky năng động.', 4.7, 0),
(166, 'Chó Samoyed', 'dog', 'pet', 'Samoyed', 'Import', 'baby', 'female', 15000000, 0, 'dogs/ChoSamoyed.jpg', 'Samoyed thân thiện.', 4.8, 0),
(167, 'Chó Beagle', 'dog', 'pet', 'Beagle', 'Yuumi Farm', 'baby', 'male', 7000000, 0, 'dogs/ChoBeagle2.jpg', 'Beagle dễ thương.', 4.6, 0),
(168, 'Chó Pitbull', 'dog', 'pet', 'Pitbull', 'Import', 'baby', 'male', 16000000, 0, 'dogs/ChoPitbull.jpg', 'Pitbull mạnh mẽ.', 4.4, 0),
(169, 'Chó Phú Quốc', 'dog', 'pet', 'PhuQuoc', 'Yuumi Farm', 'adult', 'male', 6000000, 0, 'dogs/ChoPhuQuoc.jpg', 'Chó cỏ Việt Nam.', 4.3, 0),
(170, 'Chó Boxer', 'dog', 'pet', 'Boxer', 'Import', 'baby', 'female', 11000000, 0, 'dogs/ChoBoxer.jpg', 'Boxer khỏe mạnh.', 4.7, 0),

-- 2 CHÓ MỚI
(171, 'Chó Golden Retriever', 'dog', 'pet', 'Golden', 'Import', 'baby', 'male', 12500000, 14000000, 'dogs/ChoGolden.jpg', 'Giống chó gia đình số 1 thế giới, cực hiền lành.', 5, 10),
(172, 'Chó Phốc Sóc', 'dog', 'pet', 'Pomeranian', 'Yuumi Farm', 'baby', 'female', 6500000, 7000000, 'dogs/ChoPhocSoc.jpg', 'Cục bông di động, nhỏ nhắn xinh xắn.', 4.9, 15),


-- 3. SẢN PHẨM CŨ (GIỮ NGUYÊN)
(201, 'Royal Canin Mini Puppy', 'dog', 'food', 'dry', 'Royal Canin', 'baby', 'unisex', 180000, 200000, 'food/food_rc_puppy.jpg', 'Dinh dưỡng cho chó con.', 5, 120),
(202, 'Pedigree Adult Vị Bò', 'dog', 'food', 'wet', 'Pedigree', 'adult', 'unisex', 35000, 0, 'food/food_pedigree.jpg', 'Pate bò thơm ngon.', 4.5, 500),
(203, 'SmartHeart Power Pack', 'dog', 'food', 'dry', 'SmartHeart', 'adult', 'unisex', 450000, 500000, 'food/food_smartheart.jpg', 'Tăng cơ bắp cho chó.', 4, 80),
(204, 'Whiskas Mèo Con Vị Cá', 'cat', 'food', 'dry', 'Whiskas', 'baby', 'unisex', 110000, 130000, 'food/food_whiskas_kitten.jpg', 'Dinh dưỡng cho mèo con.', 4.8, 300),
(205, 'Pate Royal Canin', 'cat', 'food', 'wet', 'Royal Canin', 'adult', 'unisex', 45000, 0, 'food/food_rc_pate.jpg', 'Pate cao cấp.', 5, 1000),
(206, 'Hạt Me-O Vị Cá Ngừ', 'cat', 'food', 'dry', 'Me-O', 'adult', 'unisex', 90000, 0, 'food/food_meo.jpg', 'Vị cá ngừ hấp dẫn.', 4.2, 200),

-- SẢN PHẨM CŨ (ĐÃ FIX CATEGORY SANG HEALTH CHO Y TẾ)
(301, 'Cát Vệ Sinh Nhật Bản 5L', 'cat', 'health', 'hygiene', 'OEM', 'all', 'unisex', 60000, 80000, 'health/cat_litter.jpg', 'Khử mùi tốt.', 4.9, 2000),
(302, 'Sữa Tắm SOS Cho Chó', 'dog', 'health', 'hygiene', 'SOS', 'all', 'unisex', 120000, 0, 'health/shampoo_sos.jpg', 'Lưu hương lâu.', 4.7, 150),
(303, 'Vòng Cổ Chống Liếm', 'all', 'health', 'medical', 'OEM', 'all', 'unisex', 50000, 0, 'accessory/loa_co.jpg', 'Bảo vệ vết thương.', 4.5, 60),
(304, 'Chuồng Sắt Tĩnh Điện', 'dog', 'cage', 'cage', 'OEM', 'all', 'unisex', 850000, 1000000, 'accessory/cage_big.jpg', 'Bền đẹp chắc chắn.', 5, 10),
(305, 'Cần Câu Mèo Lông Vũ', 'cat', 'toy', 'toy', 'OEM', 'all', 'unisex', 25000, 0, 'toy/toy_cancau.jpg', 'Đồ chơi tương tác.', 4.8, 500),
(306, 'Xương Gặm Sạch Răng', 'dog', 'food', 'treat', 'Goodies', 'all', 'unisex', 30000, 0, 'toy/treat_bone.jpg', 'Làm sạch răng.', 4.6, 300),

-- 10 SẢN PHẨM MỚI (ACCESSORY, TOY, FOOD)
(307, 'Pate Nekko Jelly', 'cat', 'food', 'wet', 'Nekko', 'all', 'unisex', 18000, 20000, 'food/pate_nekko.jpg', 'Pate Thái Lan vị cá ngừ, nhiều vị lựa chọn.', 5, 100),
(308, 'Hạt Zenith Soft Dog Food', 'dog', 'food', 'dry', 'Zenith', 'all', 'unisex', 180000, 200000, 'food/hat_zenith.jpg', 'Hạt mềm cho chó kén ăn hoặc răng yếu.', 4.8, 50),
(309, 'Súp Thưởng Ciao Churu', 'cat', 'food', 'treat', 'Ciao', 'all', 'unisex', 55000, 0, 'food/soup_ciao.jpg', 'Món ăn vặt thần thánh cho mèo.', 5, 500),
(310, 'Banh Cao Su Kêu Chíp Chíp', 'dog', 'toy', 'toy', 'OEM', 'all', 'unisex', 35000, 0, 'toy/banh_cao_su.jpg', 'Đồ chơi giúp chó giải trí, giảm stress.', 4.5, 80),
(311, 'Chuột Giả Có Dây Cót', 'cat', 'toy', 'toy', 'OEM', 'all', 'unisex', 20000, 0, 'toy/chuot_gia.jpg', 'Đồ chơi rượt đuổi cho mèo năng động.', 4.2, 60),
(312, 'Dây Dắt Chó Đi Dạo', 'dog', 'accessory', 'walking', 'OEM', 'all', 'unisex', 85000, 100000, 'accessory/day_dat_cho.jpg', 'Dây dắt bền đẹp, kèm đai ngực.', 4.7, 40),
(313, 'Bát Ăn Chống Gù', 'cat', 'accessory', 'feeding', 'OEM', 'all', 'unisex', 120000, 150000, 'accessory/bat_an_chong_gu.jpg', 'Bát ăn nghiêng giúp bảo vệ cột sống mèo.', 5, 30),
(314, 'Nệm Tròn Lông Mịn', 'all', 'accessory', 'bedding', 'OEM', 'all', 'unisex', 250000, 300000, 'accessory/nem_tron.jpg', 'Nệm êm ái cho chó mèo ngủ ngon.', 4.9, 25),
(315, 'Bàn Cào Móng Carton', 'cat', 'toy', 'scratching', 'OEM', 'all', 'unisex', 45000, 0, 'toy/ban_cao_mong.jpg', 'Giúp mèo mài móng, bảo vệ sofa.', 4.6, 120),
(316, 'Balo Phi Hành Gia', 'cat', 'accessory', 'carrier', 'OEM', 'all', 'unisex', 350000, 400000, 'accessory/balo_phi_hanh_gia.jpg', 'Balo vận chuyển chó mèo tiện lợi.', 4.8, 15),

-- Sản phẩm Y tế - Dành cho Chó/Mèo
(317, 'Vaccine Phòng Dại Nobivac Rabies', 'dog', 'health', 'vaccine', 'Merck', 'all', 'unisex', 250000, 0, 'health/vaccine_rabies.jpg', 'Vaccine phòng dại an toàn, hiệu quả cao, bảo vệ 1 năm.', 4.9, 80),
(318, 'Thuốc Xổ Giun Bayer Drontal', 'cat', 'health', 'deworm', 'Bayer', 'all', 'unisex', 80000, 95000, 'health/thuoc_xo_giun.jpg', 'Thuốc xổ giun toàn diện cho mèo, an toàn, dễ sử dụng.', 4.7, 200),
(319, 'Dầu Gội Kháng Khuẩn Malaseb', 'dog', 'health', 'shampoo', 'Dermcare', 'all', 'unisex', 180000, 0, 'health/shampoo_khang_khuan.jpg', 'Dầu gội đặc trị viêm da, nấm ngứa, mẩn đỏ.', 4.8, 45),
(320, 'Băng Gạc Thú Y Self-adhesive', 'all', 'health', 'first_aid', 'OEM', 'all', 'unisex', 35000, 0, 'health/bang_gac_thu_y.jpg', 'Băng gạc tự dính, không dính lông, dễ thay.', 4.5, 120),
(321, 'Nước Răng Miệng cho Chó Tropiclean', 'dog', 'health', 'dental', 'Tropiclean', 'all', 'unisex', 120000, 150000, 'health/nuoc_ranh_mieng.jpg', 'Giảm mảng bám, hôi miệng, an toàn khi nuốt.', 4.6, 90),
(322, 'Kem Dưỡng Móng & Da Paw Balm', 'all', 'health', 'skin_care', 'OEM', 'all', 'unisex', 65000, 0, 'health/kem_duong_mong.jpg', 'Kem dưỡng ẩm móng, da chân, chống nứt nẻ.', 4.4, 60),

-- Sản phẩm Đồ chơi
(323, 'Bóng Tennis có Dây Đàn Hồi', 'dog', 'toy', 'ball', 'OEM', 'all', 'unisex', 45000, 0, 'toy/bong_tennis_day.jpg', 'Bóng tennis gắn dây đàn hồi, chơi một mình hoặc cùng chủ.', 4.8, 150),
(324, 'Máy Bắn Bóng Bi Tự Động cho Mèo', 'cat', 'toy', 'interactive_toy', 'OEM', 'all', 'unisex', 185000, 0, 'toy/may_ban_bong_bi.jpg', 'Máy tự động bắn bóng bi, kích thích mèo vận động, chống buồn chán.', 4.7, 65),
(325, 'Đồ Chơi Thông Minh Puzzle Feeder cho Chó', 'dog', 'toy', 'puzzle_toy', 'Nina Ottosson', 'all', 'unisex', 220000, 0, 'toy/puzzle_feeder.jpg', 'Đồ chơi thử thách trí tuệ, giúp chó giải đố để lấy thức ăn, chống buồn chán.', 4.9, 75),
(326, 'Lồng 3 Tầng Cao Cấp cho Mèo', 'cat', 'cage', 'multi_level', 'OEM', 'all', 'unisex', 850000, 950000, 'toy/long_meo_3_tang.jpg', 'Lồng 3 tầng với cột cào móng, giường ngủ, đồ chơi treo, không gian thoáng mát cho mèo vui chơi và nghỉ ngơi.', 4.9, 40),
(327, 'Đĩa Bay Ném cho Chó', 'dog', 'toy', 'frisbee', 'OEM', 'all', 'unisex', 75000, 0, 'toy/dia_bay_nem.jpg', 'Đĩa bay nhựa dẻo, nhẹ, bay xa, chơi ngoài trời.', 4.6, 75),

-- Sản phẩm Phụ kiện
(328, 'Vòng Cổ LED Phát Sáng Đa Màu', 'dog', 'accessory', 'collar', 'OEM', 'all', 'unisex', 95000, 120000, 'accessory/vong_co_led.jpg', 'Vòng cổ LED chống nước, sáng đêm, an toàn khi đi dạo.', 4.8, 60),
(329, 'Balo Vận Chuyển Thông Minh', 'cat', 'accessory', 'carrier', 'OEM', 'all', 'unisex', 320000, 0, 'accessory/balo_thong_minh.jpg', 'Balo có cửa sổ lưới, túi đựng đồ, đệm lót êm ái.', 4.9, 40),
(330, 'Yếm Ăn Chống Vấy Bẩn', 'all', 'accessory', 'bib', 'OEM', 'all', 'unisex', 45000, 0, 'accessory/yem_an_chong_vay.jpg', 'Yếm ăn chống thấm, dễ giặt, bảo vệ lông khi ăn uống.', 4.5, 180),
(331, 'Dây Dắt Thông Minh Tự Cuốn', 'dog', 'accessory', 'leash', 'OEM', 'all', 'unisex', 140000, 0, 'accessory/day_dat_tu_cuon.jpg', 'Dây dắt tự cuốn 5m, nút khóa an toàn, tay cầm êm.', 4.7, 55),
(332, 'Áo Phao Cứu Sinh cho Chó Mèo Đi Bơi', 'all', 'accessory', 'life_vest', 'OEM', 'all', 'unisex', 150000, 180000, 'accessory/ao_phao_cuu_sinh.jpg', 'Áo phao cứu sinh chống nước, có dây an toàn và đai phản quang, bảo vệ thú cưng khi đi bơi hoặc du thuyền.', 4.7, 55);