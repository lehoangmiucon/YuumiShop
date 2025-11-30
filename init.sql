SET NAMES utf8mb4;

-- =============================================
-- 1. BẢNG USERS
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
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- =============================================
-- 2. BẢNG PRODUCTS (Full Option)
-- =============================================
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    species ENUM('dog', 'cat', 'all') NOT NULL, 
    category ENUM('food', 'accessory', 'health', 'toy', 'cage', 'pet') NOT NULL, 
    sub_category VARCHAR(50), 
    brand VARCHAR(50), 
    age_group ENUM('baby', 'adult', 'senior', 'all') DEFAULT 'all',
    gender ENUM('male', 'female', 'unisex') DEFAULT 'unisex', -- Cột quan trọng để lọc đực/cái
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
-- 6. BẢNG PETS (Hồ sơ thú cưng của khách)
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

-- 7. BẢNG CART (Lưu giỏ hàng lâu dài)
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
-- MÈO CŨ (10 con - ID 101-110)
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

-- +10 MÈO MỚI (ID 111-120)
(111, 'Mèo Maine Coon', 'cat', 'pet', 'Maine Coon', 'Import', 'baby', 'female', 22000000, 0, 'cats/MeoMaineCoon2.jpg', 'Maine Coon lớn, thông minh, dễ thương, lông dài.', 5, 0),
(112, 'Mèo Bengal', 'cat', 'pet', 'Bengal', 'Import', 'baby', 'male', 20000000, 0, 'cats/MeoBengal2.jpg', 'Mèo Bengal vằn da báo, hoạt bát, cá tính.', 4.9, 0),
(113, 'Mèo Ragdoll', 'cat', 'pet', 'Ragdoll', 'Import', 'baby', 'female', 18000000, 0, 'cats/MeoRagdoll2.jpg', 'Mèo Ragdoll hiền, thân thiện, phù hợp gia đình.', 5, 0),
(114, 'Mèo Siamese', 'cat', 'pet', 'Siamese', 'Yuumi Farm', 'baby', 'female', 3200000, 0, 'cats/MeoSiamese.jpg', 'Mèo Xiêm thông minh, hiền, dễ nuôi.', 4.5, 0),
(115, 'Mèo Norwegian Forest', 'cat', 'pet', 'Norwegian Forest', 'Import', 'baby', 'female', 12000000, 0, 'cats/MeoNorwegianForest.jpg', 'Mèo rừng Na Uy lông dài, thân thiện, dễ chăm.', 4.8, 0),
(116, 'Mèo American Shorthair', 'cat', 'pet', 'American Shorthair', 'Yuumi Farm', 'baby', 'male', 5000000, 0, 'cats/MeoAmericanShorthair.jpg', 'Mèo ALN Mỹ, dễ nuôi, thân thiện.', 4.6, 0),
(117, 'Mèo Scottish Fold)', 'cat', 'pet', 'Scottish Fold', 'Yuumi Farm', 'baby', 'female', 6500000, 0, 'cats/MeoScottishFold2.jpg', 'Mèo tai cụp đáng yêu, hiền.', 4.7, 0),
(118, 'Mèo Abyssinian', 'cat', 'pet', 'Abyssinian', 'Import', 'baby', 'male', 8000000, 0, 'cats/MeoAbyssinian2.jpg', 'Mèo Abyssinian năng động, lanh lợi.', 4.5, 0),
(119, 'Mèo American Bobtail', 'cat', 'pet', 'American Bobtail', 'Import', 'baby', 'female', 10000000, 0, 'cats/MeoAmericanBobtail.jpg', 'Mèo Bobtail đuôi ngắn, thân thiện, hiếm.', 4.6, 0),
(120, 'Mèo Savannah', 'cat', 'pet', 'Savannah', 'Import', 'baby', 'male', 25000000, 0, 'cats/MeoSavannah.jpg', 'Mèo Savannah lai hoang dã, vẻ ngoài độc đáo.', 4.9, 0),

-- CHÓ CŨ (10 con - ID 151-160)
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

-- +10 CHÓ MỚI (ID 161-170)
(161, 'Chó Labrador Retriever', 'dog', 'pet', 'Labrador', 'Yuumi Farm', 'baby', 'male', 12000000, 0, 'dogs/ChoLabrador.jpg', 'Labrador thân thiện, hiền, phù hợp gia đình, thân thiện với trẻ em.', 5, 0),
(162, 'Chó German Shepherd', 'dog', 'pet', 'German Shepherd', 'Yuumi Farm', 'baby', 'male', 13000000, 0, 'dogs/ChoGermanShepherd.jpg', 'Chó Becgie thông minh, trung thành, bảo vệ gia đình tốt.', 4.9, 0),
(163, 'Chó French Bulldog', 'dog', 'pet', 'French Bulldog', 'Yuumi Farm', 'baby', 'female', 9000000, 0, 'dogs/ChoFrenchBulldog.jpg', 'Frenchie nhỏ nhắn, thân thiện, sống tốt trong căn hộ.', 4.8, 0),
(164, 'Chó Dachshund', 'dog', 'pet', 'Dachshund', 'Yuumi Farm', 'baby', 'female', 5500000, 0, 'dogs/ChoDachshund.jpg', 'Chó Lạp xưởng chân ngắn, thân thiện, dễ nuôi.', 4.5, 0),
(165, 'Chó Husky', 'dog', 'pet', 'Husky', 'Import', 'baby', 'male', 14000000, 0, 'dogs/ChoHusky.jpg', 'Husky năng động, ngoại hình đẹp, thích vận động.', 4.7, 0),
(166, 'Chó Samoyed', 'dog', 'pet', 'Samoyed', 'Import', 'baby', 'female', 15000000, 0, 'dogs/ChoSamoyed.jpg', 'Samoyed thân thiện, lông trắng, đáng yêu.', 4.8, 0),
(167, 'Chó Beagle', 'dog', 'pet', 'Beagle', 'Yuumi Farm', 'baby', 'male', 7000000, 0, 'dogs/ChoBeagle2.jpg', 'Beagle dễ thương, năng động, dễ huấn luyện.', 4.6, 0),
(168, 'Chó Pitbull', 'dog', 'pet', 'Pitbull', 'Import', 'baby', 'male', 16000000, 0, 'dogs/ChoPitbull.jpg', 'Pitbull mạnh mẽ, cảnh giác, phù hợp người muốn chó bảo vệ.', 4.4, 0),
(169, 'Chó Phú Quốc', 'dog', 'pet', 'PhuQuoc', 'Yuumi Farm', 'adult', 'male', 6000000, 0, 'dogs/ChoPhuQuoc.jpg', 'Chó cỏ Việt Nam, dễ nuôi, trung thành.', 4.3, 0),
(170, 'Chó Boxer', 'dog', 'pet', 'Boxer', 'Import', 'baby', 'female', 11000000, 0, 'dogs/ChoBoxer.jpg', 'Boxer khỏe mạnh, hoạt bát, thân thiện với gia đình.', 4.7, 0),

-- 3. INSERT SẢN PHẨM: THỨC ĂN & PHỤ KIỆN (2xx)
(201, 'Royal Canin Mini Puppy', 'dog', 'food', 'dry', 'Royal Canin', 'baby', 'unisex', 180000, 200000, 'dogs/food_rc_puppy.jpg', 'Dinh dưỡng cho chó con.', 5, 120),
(202, 'Pedigree Adult Vị Bò', 'dog', 'food', 'wet', 'Pedigree', 'adult', 'unisex', 35000, 0, 'dogs/food_pedigree.jpg', 'Pate bò thơm ngon.', 4.5, 500),
(203, 'SmartHeart Power Pack', 'dog', 'food', 'dry', 'SmartHeart', 'adult', 'unisex', 450000, 500000, 'dogs/food_smartheart.jpg', 'Tăng cơ bắp cho chó.', 4, 80),
(204, 'Whiskas Mèo Con Vị Cá', 'cat', 'food', 'dry', 'Whiskas', 'baby', 'unisex', 110000, 130000, 'cats/food_whiskas_kitten.jpg', 'Dinh dưỡng cho mèo con.', 4.8, 300),
(205, 'Pate Royal Canin', 'cat', 'food', 'wet', 'Royal Canin', 'adult', 'unisex', 45000, 0, 'cats/food_rc_pate.jpg', 'Pate cao cấp.', 5, 1000),
(206, 'Hạt Me-O Vị Cá Ngừ', 'cat', 'food', 'dry', 'Me-O', 'adult', 'unisex', 90000, 0, 'cats/food_meo.jpg', 'Vị cá ngừ hấp dẫn.', 4.2, 200),

-- PHỤ KIỆN & VỆ SINH (3xx)
(301, 'Cát Vệ Sinh Nhật Bản 5L', 'cat', 'accessory', 'hygiene', 'OEM', 'all', 'unisex', 60000, 80000, 'cats/cat_litter.jpg', 'Khử mùi tốt.', 4.9, 2000),
(302, 'Sữa Tắm SOS Cho Chó', 'dog', 'accessory', 'hygiene', 'SOS', 'all', 'unisex', 120000, 0, 'dogs/shampoo_sos.jpg', 'Lưu hương lâu.', 4.7, 150),
(303, 'Vòng Cổ Chống Liếm', 'all', 'health', 'medical', 'OEM', 'all', 'unisex', 50000, 0, 'accessory/loa_co.jpg', 'Bảo vệ vết thương.', 4.5, 60),
(304, 'Chuồng Sắt Tĩnh Điện', 'dog', 'cage', 'cage', 'OEM', 'all', 'unisex', 850000, 1000000, 'dogs/cage_big.jpg', 'Bền đẹp chắc chắn.', 5, 10),
(305, 'Cần Câu Mèo Lông Vũ', 'cat', 'toy', 'toy', 'OEM', 'all', 'unisex', 25000, 0, 'cats/toy_cancau.jpg', 'Đồ chơi tương tác.', 4.8, 500),
(306, 'Xương Gặm Sạch Răng', 'dog', 'food', 'treat', 'Goodies', 'all', 'unisex', 30000, 0, 'dogs/treat_bone.jpg', 'Làm sạch răng.', 4.6, 300);