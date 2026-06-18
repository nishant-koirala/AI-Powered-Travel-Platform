-- ============================================================
-- FULL DATABASE BACKUP — Travel Agency Management System
-- Run order: core → additional → itinerary → chatbot → seeds
-- Import into phpMyAdmin or run: mysql -u root < full_backup.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS travel_website_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE travel_website_db;

-- ============================================================
-- CORE TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    dob DATE,
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

CREATE TABLE IF NOT EXISTS packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_title (title),
    INDEX idx_price (price)
);

CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_number VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    location VARCHAR(255) NOT NULL,
    guests INT NOT NULL DEFAULT 1,
    arrivals DATE NOT NULL,
    leaving DATE NOT NULL,
    package VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_booking_number (booking_number),
    INDEX idx_email (email),
    INDEX idx_arrivals (arrivals),
    INDEX idx_package (package),
    INDEX idx_status (status)
);

CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    seen BOOLEAN DEFAULT FALSE,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_seen (seen),
    INDEX idx_submitted_at (submitted_at)
);

-- ============================================================
-- ADDITIONAL TABLES (payments, reviews, notifications, settings)
-- ============================================================

CREATE TABLE IF NOT EXISTS booking_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL,
    old_status VARCHAR(20),
    new_status VARCHAR(20) NOT NULL,
    changed_by VARCHAR(255),
    change_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    payment_method ENUM('cash', 'card', 'bank_transfer', 'online') DEFAULT 'cash',
    payment_status ENUM('pending', 'paid', 'refunded', 'failed') DEFAULT 'pending',
    amount DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(255),
    payment_date TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_payment_date (payment_date)
);

CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    booking_id INT NOT NULL UNIQUE,
    user_email VARCHAR(255) NOT NULL,
    package_name VARCHAR(255) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    approved BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking_id (booking_id),
    INDEX idx_user_email (user_email),
    INDEX idx_approved (approved),
    INDEX idx_rating (rating)
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('new_booking', 'payment_received', 'review_pending', 'system') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    booking_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_type (type),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_setting_key (setting_key)
);

INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
('site_name',              'Travel Agency',    'Website name'),
('contact_email',          'contact@travel.com','Main contact email'),
('contact_phone',          '+1234567890',      'Contact phone number'),
('currency',               'USD',              'Default currency'),
('tax_rate',               '0.10',             'Tax rate (decimal)'),
('auto_confirm_bookings',  '0',                'Auto-confirm bookings (0/1)'),
('enable_reviews',         '1',                'Enable customer reviews (0/1)'),
('admin_email',            'admin@travel.com', 'Admin notification email');

-- ============================================================
-- TRIGGERS
-- ============================================================

DROP TRIGGER IF EXISTS after_booking_insert;
DROP TRIGGER IF EXISTS before_booking_update;

DELIMITER //

CREATE TRIGGER after_booking_insert
AFTER INSERT ON bookings
FOR EACH ROW
BEGIN
    INSERT INTO notifications (type, title, message, booking_id)
    VALUES (
        'new_booking',
        CONCAT('New Booking: ', NEW.booking_number),
        CONCAT('New booking received from ', NEW.name, ' for ', NEW.package),
        NEW.id
    );
END//

CREATE TRIGGER before_booking_update
BEFORE UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        INSERT INTO booking_status_history (booking_id, old_status, new_status, change_reason)
        VALUES (OLD.id, OLD.status, NEW.status,
                CONCAT('Status changed from ', OLD.status, ' to ', NEW.status));
    END IF;
END//

DELIMITER ;

-- ============================================================
-- VIEWS
-- ============================================================

CREATE OR REPLACE VIEW booking_reports AS
SELECT
    b.id, b.booking_number, b.name, b.email, b.package,
    b.price, b.status, b.arrivals, b.leaving, b.guests, b.created_at,
    p.payment_status, p.payment_method,
    r.rating, r.approved AS review_approved
FROM bookings b
LEFT JOIN payments p ON b.id = p.booking_id
LEFT JOIN reviews  r ON b.id = r.booking_id
ORDER BY b.created_at DESC;

CREATE OR REPLACE VIEW dashboard_stats AS
SELECT
    COUNT(*)                                                      AS total_bookings,
    SUM(price)                                                    AS total_revenue,
    COUNT(DISTINCT email)                                         AS total_customers,
    COUNT(DISTINCT package)                                       AS total_packages,
    SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END)        AS pending_bookings,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END)        AS confirmed_bookings,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_bookings
FROM bookings;

-- ============================================================
-- ITINERARY TABLES
-- ============================================================

ALTER TABLE packages
    ADD COLUMN IF NOT EXISTS itinerary         TEXT          NULL,
    ADD COLUMN IF NOT EXISTS duration_days     INT           DEFAULT 1,
    ADD COLUMN IF NOT EXISTS includes          TEXT          NULL,
    ADD COLUMN IF NOT EXISTS excludes          TEXT          NULL,
    ADD COLUMN IF NOT EXISTS difficulty_level  ENUM('easy','moderate','challenging') DEFAULT 'easy',
    ADD COLUMN IF NOT EXISTS accommodation_type VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS transportation    VARCHAR(255)  NULL;

CREATE TABLE IF NOT EXISTS itinerary_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    day_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    meals VARCHAR(255),
    activities TEXT,
    accommodation VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    INDEX idx_package_id (package_id),
    INDEX idx_day_number (day_number),
    UNIQUE KEY unique_package_day (package_id, day_number)
);

CREATE TABLE IF NOT EXISTS package_inclusions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    inclusion_type ENUM('inclusion', 'exclusion') NOT NULL,
    item VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    INDEX idx_package_id (package_id),
    INDEX idx_inclusion_type (inclusion_type)
);

CREATE TABLE IF NOT EXISTS package_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    package_id INT NOT NULL,
    image_name VARCHAR(255) NOT NULL,
    image_caption VARCHAR(255),
    is_primary BOOLEAN DEFAULT FALSE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    INDEX idx_package_id (package_id),
    INDEX idx_is_primary (is_primary)
);

-- ============================================================
-- CHATBOT TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS chatbot_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    session_id VARCHAR(64) NOT NULL DEFAULT '',
    message TEXT NOT NULL,
    sender ENUM('user', 'ai') NOT NULL,
    message_type VARCHAR(32) NOT NULL DEFAULT 'general',
    intent VARCHAR(64) NOT NULL DEFAULT 'GENERAL',
    category VARCHAR(128) NOT NULL DEFAULT '',
    ai_used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session (session_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chatbot_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(255) NULL,
    message TEXT NOT NULL,
    reply TEXT NOT NULL,
    ai_used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chatbot_api_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(255) NULL,
    message TEXT NOT NULL,
    ai_used TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created (created_at),
    INDEX idx_user (user_id),
    INDEX idx_ai (ai_used)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chatbot_system_quota (
    id INT PRIMARY KEY DEFAULT 1,
    api_call_limit INT NOT NULL DEFAULT 500,
    period_calls INT NOT NULL DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO chatbot_system_quota (id, api_call_limit, period_calls) VALUES (1, 500, 0);

ALTER TABLE packages ADD COLUMN IF NOT EXISTS category VARCHAR(128) NULL DEFAULT NULL;

-- ============================================================
-- SEED DATA — Default admin + sample packages
-- ============================================================

INSERT IGNORE INTO users (name, email, password, role) VALUES
('Admin User', 'admin@travel.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT IGNORE INTO packages (title, description, price, image) VALUES
('Beach Paradise',    'Enjoy a relaxing beach vacation with pristine white sand beaches and crystal clear waters.',     599.99,  'beach1.jpg'),
('Mountain Adventure','Experience the thrill of mountain climbing and hiking with breathtaking views.',                 799.99,  'mountain1.jpg'),
('City Explorer',     'Discover the vibrant city life with guided tours to famous landmarks and museums.',              449.99,  'city1.jpg'),
('Safari Experience', 'Get up close with wildlife in their natural habitat. Guided safari tours included.',            1299.99, 'safari1.jpg'),
('Island Getaway',    'Escape to a tropical paradise with beautiful beaches and island hopping adventures.',            899.99,  'island1.jpg');

-- Nepal trekking packages
INSERT IGNORE INTO packages (title, description, price, image) VALUES
('Everest Base Camp Trek — 14 Days',  'Classic Himalayan trek from Lukla to Everest Base Camp (5364m).',             1899.00, 'everest-trek.jpg'),
('Annapurna Circuit Trek — 16 Days',  'Full circuit crossing Thorong La Pass (5416m). Iconic Nepal experience.',     1650.00, 'annapurna-circuit.jpg'),
('Langtang Valley Trek — 10 Days',    'Closer to Kathmandu; lush forests, Tamang culture, Langtang Lirung views.',   980.00, 'langtang.jpg'),
('Mardi Himal Trek — 9 Days',         'Close-up views of Machhapuchhre and Annapurna. Ideal for couples.',           890.00, 'mardi-himal.jpg'),
('Ghorepani Poon Hill Trek — 7 Days', 'Gentle trek; sunrise over Annapurna from Poon Hill. Great for beginners.',    650.00, 'poon-hill.jpg'),
('Manaslu Circuit Trek — 18 Days',    'Remote circuit around the eighth-highest peak with restricted area permit.',  2199.00, 'manaslu.jpg'),
('Upper Mustang Trek — 14 Days',      'Forbidden Kingdom; ancient monasteries and unique Tibetan culture.',          2450.00, 'mustang.jpg'),
('Helambu Trek — 8 Days',             'Easy–moderate trek near Kathmandu; Sherpa villages and Buddhist stupas.',      720.00, 'helambu.jpg');
