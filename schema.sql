-- ============================================================
-- E-Sheba Database Schema  (MySQL 8.x)
-- Run:  mysql -u root -p esheba_db < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS esheba_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE esheba_db;

-- ── Users (shared login table) ───────────────────────────────
CREATE TABLE IF NOT EXISTS users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(120)         NOT NULL,
    email         VARCHAR(180)         NOT NULL UNIQUE,
    phone         VARCHAR(20)          NULL,
    password_hash VARCHAR(255)         NOT NULL,
    role          ENUM('customer','worker','admin') NOT NULL DEFAULT 'customer',
    avatar        VARCHAR(300)         NULL,
    email_verified TINYINT(1)          NOT NULL DEFAULT 0,
    is_active     TINYINT(1)           NOT NULL DEFAULT 1,
    created_at    TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role  (role)
) ENGINE=InnoDB;

-- ── OTP Tokens ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS otp_tokens (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    token      VARCHAR(255) NOT NULL,
    type       ENUM('email_verify','password_reset') NOT NULL DEFAULT 'email_verify',
    expires_at DATETIME     NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_user_type (user_id, type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Service Categories ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL UNIQUE,
    icon        VARCHAR(60)  NULL COMMENT 'Font Awesome class',
    description TEXT         NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    sort_order  INT          NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Seed categories
INSERT IGNORE INTO categories (name, icon, sort_order) VALUES
('Plumber',           'fa-wrench',              1),
('Electrician',       'fa-bolt',                2),
('Mechanic',          'fa-car',                 3),
('Painter',           'fa-paint-roller',        4),
('Carpenter',         'fa-hammer',              5),
('Cleaner',           'fa-broom',               6),
('AC Repair',         'fa-wind',                7),
('TV Repair',         'fa-tv',                  8),
('Fridge Repair',     'fa-temperature-low',     9),
('Washing Machine',   'fa-sync',               10),
('Gas Stove Repair',  'fa-fire',               11),
('Pest Control',      'fa-bug',                12),
('Gardener',          'fa-seedling',           13),
('Security Guard',    'fa-shield-alt',         14),
('Driver',            'fa-taxi',               15),
('Cook / Chef',       'fa-utensils',           16),
('Tutor',             'fa-chalkboard-teacher', 17),
('Nurse / Caregiver', 'fa-hospital-user',      18),
('Photographer',      'fa-camera',             19),
('Event Manager',     'fa-calendar-alt',       20),
('Mason / Brickwork', 'fa-hard-hat',           21),
('Welder',            'fa-fire-alt',           22),
('IT / Computer',     'fa-laptop',             23),
('Tailor',            'fa-cut',                24),
('Generator Repair',  'fa-plug',               25);

-- ── Workers (service givers) ─────────────────────────────────
CREATE TABLE IF NOT EXISTS workers (
    id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id          INT UNSIGNED NOT NULL UNIQUE,
    nid_number       VARCHAR(20)  NULL COMMENT 'encrypted',
    nid_image_path   VARCHAR(255) NULL,
    bio              TEXT         NULL,
    price_per_hour   DECIMAL(8,2) NOT NULL DEFAULT 0,
    working_area     VARCHAR(200) NOT NULL,
    district         VARCHAR(80)  NOT NULL,
    thana            VARCHAR(80)  NOT NULL,
    latitude         DECIMAL(10,7) NULL,
    longitude        DECIMAL(10,7) NULL,
    experience_years TINYINT      NOT NULL DEFAULT 0,
    is_approved      TINYINT(1)   NOT NULL DEFAULT 0 COMMENT '1=admin approved',
    is_available     TINYINT(1)   NOT NULL DEFAULT 1,
    is_visible       TINYINT(1)   NOT NULL DEFAULT 1 COMMENT '0=hidden (subscription expired)',
    avg_rating       DECIMAL(3,2) NOT NULL DEFAULT 0,
    total_reviews    INT UNSIGNED NOT NULL DEFAULT 0,
    created_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_district (district),
    INDEX idx_approved (is_approved),
    INDEX idx_available(is_available)
) ENGINE=InnoDB;

-- ── Worker ↔ Category (many-to-many) ────────────────────────
CREATE TABLE IF NOT EXISTS worker_categories (
    worker_id   INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (worker_id, category_id),
    FOREIGN KEY (worker_id)   REFERENCES workers(id)    ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Subscriptions ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS subscriptions (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    worker_id     INT UNSIGNED NOT NULL,
    amount        DECIMAL(8,2) NOT NULL DEFAULT 399,
    currency      VARCHAR(5)   NOT NULL DEFAULT 'BDT',
    status        ENUM('pending','active','expired','cancelled') NOT NULL DEFAULT 'pending',
    payment_method ENUM('sslcommerz','card','manual')           NOT NULL DEFAULT 'sslcommerz',
    transaction_id VARCHAR(100) NULL,
    starts_at     DATE         NULL,
    expires_at    DATE         NULL,
    created_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES workers(id) ON DELETE CASCADE,
    INDEX idx_worker_status (worker_id, status)
) ENGINE=InnoDB;

-- ── Bookings ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS bookings (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id  INT UNSIGNED NOT NULL,
    worker_id    INT UNSIGNED NOT NULL,
    category_id  INT UNSIGNED NOT NULL,
    description  TEXT         NULL,
    scheduled_at DATETIME     NULL,
    status       ENUM('pending','accepted','in_progress','completed','cancelled') NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(8,2) NULL,
    pay_method   ENUM('sslcommerz','card','cash') NULL,
    pay_status   ENUM('unpaid','cash_pending','paid','refunded') NOT NULL DEFAULT 'unpaid',
    pay_txn_id   VARCHAR(100) NULL,
    address      VARCHAR(300) NULL,
    notes        TEXT         NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (worker_id)   REFERENCES workers(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    INDEX idx_customer (customer_id),
    INDEX idx_worker   (worker_id)
) ENGINE=InnoDB;

-- ── Reviews ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reviews (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id  INT UNSIGNED NOT NULL UNIQUE,
    customer_id INT UNSIGNED NOT NULL,
    worker_id   INT UNSIGNED NOT NULL,
    rating      TINYINT      NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT         NULL,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id)  REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id),
    FOREIGN KEY (worker_id)   REFERENCES workers(id)
) ENGINE=InnoDB;

-- ── Chat Messages ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS messages (
    id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sender_id   INT UNSIGNED NOT NULL,
    receiver_id INT UNSIGNED NOT NULL,
    booking_id  INT UNSIGNED NULL,
    body        TEXT         NOT NULL,
    is_read     TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id)   REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_thread (sender_id, receiver_id),
    INDEX idx_unread (receiver_id, is_read)
) ENGINE=InnoDB;

-- ── Call Logs ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS call_logs (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    caller_id   INT UNSIGNED NOT NULL,
    callee_id   INT UNSIGNED NOT NULL,
    channel     VARCHAR(100) NOT NULL COMMENT 'Agora channel name',
    started_at  DATETIME     NULL,
    ended_at    DATETIME     NULL,
    duration_sec INT         NULL,
    status      ENUM('initiated','answered','missed','declined') NOT NULL DEFAULT 'initiated',
    FOREIGN KEY (caller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (callee_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── Notifications ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS notifications (
    id         BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id    INT UNSIGNED NOT NULL,
    type       VARCHAR(60)  NOT NULL,
    title      VARCHAR(200) NOT NULL,
    body       TEXT         NULL,
    data       JSON         NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB;

-- ── Default Admin user ────────────────────────────────────────
-- Password: Admin@1234  (bcrypt hash below — change after first login)
INSERT IGNORE INTO users (name, email, password_hash, role, email_verified)
VALUES ('Admin', 'admin@e-sheba.com',
        '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC',
        'admin', 1);
