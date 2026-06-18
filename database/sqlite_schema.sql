-- ============================================================
-- SQLite Fallback Schema — Travel Agency Management System
-- Used automatically when MySQL is unavailable.
-- SQLite file lives at: database/travel_backup.sqlite
-- ============================================================

PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;

-- ============================================================
-- CORE TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS users (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    name            TEXT    NOT NULL,
    email           TEXT    NOT NULL UNIQUE,
    password        TEXT    NOT NULL,
    address         TEXT,
    phone           TEXT,
    dob             TEXT,
    role            TEXT    NOT NULL DEFAULT 'user' CHECK (role IN ('user','admin')),
    created_at      TEXT    DEFAULT (datetime('now')),
    updated_at      TEXT    DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_users_email ON users (email);
CREATE INDEX IF NOT EXISTS idx_users_role  ON users (role);

CREATE TABLE IF NOT EXISTS packages (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    title               TEXT    NOT NULL,
    description         TEXT    NOT NULL,
    price               REAL    NOT NULL,
    image               TEXT,
    category            TEXT,
    itinerary           TEXT,
    duration_days       INTEGER DEFAULT 1,
    includes            TEXT,
    excludes            TEXT,
    difficulty_level    TEXT    DEFAULT 'easy' CHECK (difficulty_level IN ('easy','moderate','challenging')),
    accommodation_type  TEXT,
    transportation      TEXT,
    created_at          TEXT    DEFAULT (datetime('now')),
    updated_at          TEXT    DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_packages_title ON packages (title);
CREATE INDEX IF NOT EXISTS idx_packages_price ON packages (price);

CREATE TABLE IF NOT EXISTS bookings (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_number  TEXT    NOT NULL UNIQUE,
    name            TEXT    NOT NULL,
    email           TEXT    NOT NULL,
    phone           TEXT,
    address         TEXT,
    location        TEXT    NOT NULL,
    guests          INTEGER NOT NULL DEFAULT 1,
    arrivals        TEXT    NOT NULL,
    leaving         TEXT    NOT NULL,
    package         TEXT    NOT NULL,
    price           REAL    NOT NULL,
    status          TEXT    NOT NULL DEFAULT 'pending'
                            CHECK (status IN ('pending','confirmed','cancelled','completed')),
    created_at      TEXT    DEFAULT (datetime('now')),
    updated_at      TEXT    DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_bookings_number  ON bookings (booking_number);
CREATE INDEX IF NOT EXISTS idx_bookings_email   ON bookings (email);
CREATE INDEX IF NOT EXISTS idx_bookings_status  ON bookings (status);
CREATE INDEX IF NOT EXISTS idx_bookings_arrives ON bookings (arrivals);

CREATE TABLE IF NOT EXISTS messages (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT    NOT NULL,
    email        TEXT    NOT NULL,
    phone        TEXT,
    message      TEXT    NOT NULL,
    seen         INTEGER DEFAULT 0,
    submitted_at TEXT    DEFAULT (datetime('now')),
    created_at   TEXT    DEFAULT (datetime('now')),
    updated_at   TEXT    DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_messages_email ON messages (email);
CREATE INDEX IF NOT EXISTS idx_messages_seen  ON messages (seen);

-- ============================================================
-- ADDITIONAL TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS booking_status_history (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id    INTEGER NOT NULL,
    old_status    TEXT,
    new_status    TEXT    NOT NULL,
    changed_by    TEXT,
    change_reason TEXT,
    created_at    TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_bsh_booking ON booking_status_history (booking_id);

CREATE TABLE IF NOT EXISTS payments (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id     INTEGER NOT NULL UNIQUE,
    payment_method TEXT    DEFAULT 'cash'
                           CHECK (payment_method IN ('cash','card','bank_transfer','online')),
    payment_status TEXT    DEFAULT 'pending'
                           CHECK (payment_status IN ('pending','paid','refunded','failed')),
    amount         REAL    NOT NULL,
    transaction_id TEXT,
    payment_date   TEXT,
    notes          TEXT,
    created_at     TEXT    DEFAULT (datetime('now')),
    updated_at     TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_payments_booking ON payments (booking_id);
CREATE INDEX IF NOT EXISTS idx_payments_status  ON payments (payment_status);

CREATE TABLE IF NOT EXISTS reviews (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    booking_id   INTEGER NOT NULL UNIQUE,
    user_email   TEXT    NOT NULL,
    package_name TEXT    NOT NULL,
    rating       INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text  TEXT,
    approved     INTEGER DEFAULT 0,
    created_at   TEXT    DEFAULT (datetime('now')),
    updated_at   TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_reviews_booking ON reviews (booking_id);
CREATE INDEX IF NOT EXISTS idx_reviews_email   ON reviews (user_email);

CREATE TABLE IF NOT EXISTS notifications (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    type       TEXT    NOT NULL
                       CHECK (type IN ('new_booking','payment_received','review_pending','system')),
    title      TEXT    NOT NULL,
    message    TEXT    NOT NULL,
    is_read    INTEGER DEFAULT 0,
    booking_id INTEGER NULL,
    created_at TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
);
CREATE INDEX IF NOT EXISTS idx_notif_type    ON notifications (type);
CREATE INDEX IF NOT EXISTS idx_notif_is_read ON notifications (is_read);

CREATE TABLE IF NOT EXISTS settings (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key   TEXT    NOT NULL UNIQUE,
    setting_value TEXT,
    description   TEXT,
    created_at    TEXT    DEFAULT (datetime('now')),
    updated_at    TEXT    DEFAULT (datetime('now'))
);

INSERT OR IGNORE INTO settings (setting_key, setting_value, description) VALUES
('site_name',             'Travel Agency',     'Website name'),
('contact_email',         'contact@travel.com','Main contact email'),
('contact_phone',         '+1234567890',       'Contact phone number'),
('currency',              'USD',               'Default currency'),
('tax_rate',              '0.10',              'Tax rate (decimal)'),
('auto_confirm_bookings', '0',                 'Auto-confirm bookings (0/1)'),
('enable_reviews',        '1',                 'Enable customer reviews (0/1)'),
('admin_email',           'admin@travel.com',  'Admin notification email');

-- ============================================================
-- ITINERARY TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS itinerary_details (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    package_id    INTEGER NOT NULL,
    day_number    INTEGER NOT NULL,
    title         TEXT    NOT NULL,
    description   TEXT    NOT NULL,
    meals         TEXT,
    activities    TEXT,
    accommodation TEXT,
    created_at    TEXT    DEFAULT (datetime('now')),
    updated_at    TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE,
    UNIQUE (package_id, day_number)
);
CREATE INDEX IF NOT EXISTS idx_itin_package ON itinerary_details (package_id);
CREATE INDEX IF NOT EXISTS idx_itin_day     ON itinerary_details (day_number);

CREATE TABLE IF NOT EXISTS package_inclusions (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    package_id     INTEGER NOT NULL,
    inclusion_type TEXT    NOT NULL CHECK (inclusion_type IN ('inclusion','exclusion')),
    item           TEXT    NOT NULL,
    created_at     TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_incl_package ON package_inclusions (package_id);

CREATE TABLE IF NOT EXISTS package_images (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    package_id    INTEGER NOT NULL,
    image_name    TEXT    NOT NULL,
    image_caption TEXT,
    is_primary    INTEGER DEFAULT 0,
    sort_order    INTEGER DEFAULT 0,
    created_at    TEXT    DEFAULT (datetime('now')),
    FOREIGN KEY (package_id) REFERENCES packages(id) ON DELETE CASCADE
);
CREATE INDEX IF NOT EXISTS idx_imgs_package    ON package_images (package_id);
CREATE INDEX IF NOT EXISTS idx_imgs_is_primary ON package_images (is_primary);

-- ============================================================
-- CHATBOT TABLES
-- ============================================================

CREATE TABLE IF NOT EXISTS chatbot_messages (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    session_id   TEXT    NOT NULL DEFAULT '',
    message      TEXT    NOT NULL,
    sender       TEXT    NOT NULL CHECK (sender IN ('user','ai')),
    message_type TEXT    NOT NULL DEFAULT 'general',
    intent       TEXT    NOT NULL DEFAULT 'GENERAL',
    category     TEXT    NOT NULL DEFAULT '',
    ai_used      INTEGER NOT NULL DEFAULT 0,
    created_at   TEXT    DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_cm_session ON chatbot_messages (session_id);
CREATE INDEX IF NOT EXISTS idx_cm_created ON chatbot_messages (created_at);

CREATE TABLE IF NOT EXISTS chatbot_history (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NULL,
    username   TEXT    NULL,
    message    TEXT    NOT NULL,
    reply      TEXT    NOT NULL,
    ai_used    INTEGER NOT NULL DEFAULT 0,
    created_at TEXT    DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_ch_created ON chatbot_history (created_at);
CREATE INDEX IF NOT EXISTS idx_ch_user    ON chatbot_history (user_id);

CREATE TABLE IF NOT EXISTS chatbot_api_usage (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id    INTEGER NULL,
    username   TEXT    NULL,
    message    TEXT    NOT NULL,
    ai_used    INTEGER NOT NULL DEFAULT 0,
    created_at TEXT    DEFAULT (datetime('now'))
);
CREATE INDEX IF NOT EXISTS idx_cau_created ON chatbot_api_usage (created_at);
CREATE INDEX IF NOT EXISTS idx_cau_user    ON chatbot_api_usage (user_id);

CREATE TABLE IF NOT EXISTS chatbot_system_quota (
    id              INTEGER PRIMARY KEY DEFAULT 1,
    api_call_limit  INTEGER NOT NULL DEFAULT 500,
    period_calls    INTEGER NOT NULL DEFAULT 0,
    updated_at      TEXT    DEFAULT (datetime('now'))
);
INSERT OR IGNORE INTO chatbot_system_quota (id, api_call_limit, period_calls) VALUES (1, 500, 0);

-- ============================================================
-- TRIGGERS (SQLite syntax — || for string concat)
-- ============================================================

CREATE TRIGGER IF NOT EXISTS after_booking_insert
AFTER INSERT ON bookings
BEGIN
    INSERT INTO notifications (type, title, message, booking_id)
    VALUES (
        'new_booking',
        'New Booking: ' || NEW.booking_number,
        'New booking received from ' || NEW.name || ' for ' || NEW.package,
        NEW.id
    );
END;

CREATE TRIGGER IF NOT EXISTS before_booking_update
BEFORE UPDATE ON bookings
WHEN OLD.status != NEW.status
BEGIN
    INSERT INTO booking_status_history (booking_id, old_status, new_status, change_reason)
    VALUES (
        OLD.id, OLD.status, NEW.status,
        'Status changed from ' || OLD.status || ' to ' || NEW.status
    );
END;

-- ============================================================
-- SEED DATA
-- ============================================================

INSERT OR IGNORE INTO users (name, email, password, role) VALUES
('Admin User', 'admin@travel.com',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT OR IGNORE INTO packages (title, description, price, image) VALUES
('Beach Paradise',    'Enjoy a relaxing beach vacation with pristine white sand beaches.',  599.99,  'beach1.jpg'),
('Mountain Adventure','Experience mountain climbing and hiking with breathtaking views.',   799.99,  'mountain1.jpg'),
('City Explorer',     'Discover vibrant city life with guided tours to landmarks.',         449.99,  'city1.jpg'),
('Safari Experience', 'Get up close with wildlife. Guided safari tours included.',         1299.99, 'safari1.jpg'),
('Island Getaway',    'Tropical paradise with island hopping adventures.',                  899.99,  'island1.jpg'),
('Everest Base Camp Trek — 14 Days',  'Classic Himalayan trek to Everest Base Camp.',     1899.00, 'everest-trek.jpg'),
('Annapurna Circuit Trek — 16 Days',  'Full circuit crossing Thorong La Pass.',           1650.00, 'annapurna-circuit.jpg'),
('Langtang Valley Trek — 10 Days',    'Lush forests, Tamang culture, Langtang views.',    980.00,  'langtang.jpg'),
('Mardi Himal Trek — 9 Days',         'Close-up views of Machhapuchhre and Annapurna.',   890.00,  'mardi-himal.jpg'),
('Ghorepani Poon Hill Trek — 7 Days', 'Sunrise over Annapurna. Great for beginners.',     650.00,  'poon-hill.jpg'),
('Manaslu Circuit Trek — 18 Days',    'Remote circuit around the eighth-highest peak.',   2199.00, 'manaslu.jpg'),
('Upper Mustang Trek — 14 Days',      'Forbidden Kingdom; ancient monasteries.',          2450.00, 'mustang.jpg'),
('Helambu Trek — 8 Days',             'Easy–moderate trek near Kathmandu.',               720.00,  'helambu.jpg');
