-- ============================================================
-- E-Sheba Demo Seed Data (seeds.sql)
-- Run in phpMyAdmin or MySQL CLI after schema.sql:
-- mysql -u root -p esheba_db < seeds.sql
-- ============================================================
USE esheba_db;

-- 1. Add NID image column to workers table if not present
ALTER TABLE workers ADD COLUMN IF NOT EXISTS nid_image_path VARCHAR(255) NULL AFTER nid_number;

-- 2. Clean previous sample rows (keeps default Admin)
DELETE FROM reviews WHERE id > 0;
DELETE FROM bookings WHERE id > 0;
DELETE FROM subscriptions WHERE id > 0;
DELETE FROM worker_categories WHERE worker_id > 0;
DELETE FROM workers WHERE id > 0;
DELETE FROM users WHERE id > 1;

-- Reset auto-increments
ALTER TABLE users AUTO_INCREMENT = 2;
ALTER TABLE workers AUTO_INCREMENT = 1;
ALTER TABLE bookings AUTO_INCREMENT = 1;
ALTER TABLE subscriptions AUTO_INCREMENT = 1;

-- 3. Seed Users (Customers & Workers)
-- Password for all seed accounts: password123
-- Hash: $2y$10$w01p4.QxXyFq4/wKzVvYn.R3mFmY718q6z2t2.H4a9z/Pq7uP4bWy
INSERT INTO users (id, name, email, phone, password_hash, role, email_verified, is_active) VALUES
-- Customers
(2, 'Rahim Ahmed', 'rahim@gmail.com', '01711223344', '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC', 'customer', 1, 1),
(3, 'Fatema Begum', 'fatema@gmail.com', '01811334455', '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC', 'customer', 1, 1),
-- Workers
(4, 'Kabir Hossain', 'kabir.electric@gmail.com', '01711000001', '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC', 'worker', 1, 1),
(5, 'Mizanur Rahman', 'mizan.plumber@gmail.com', '01822000002', '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC', 'worker', 1, 1),
(6, 'Sabbir Ahmed', 'sabbir.ac@gmail.com', '01933000003', '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC', 'worker', 1, 1),
(7, 'Tanvir Hasan', 'tanvir.carpenter@gmail.com', '01644000004', '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC', 'worker', 1, 1),
(8, 'Rafiqul Islam', 'rafiq.cleaner@gmail.com', '01555000005', '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC', 'worker', 1, 1),
(9, 'Nurul Alam', 'nurul.painter@gmail.com', '01766000006', '$2y$10$1AAFEAEP1U4MZ2E1eduFeui15AUebsVggAX/3PmGgl9c6B5yPm5GC', 'worker', 1, 1);

-- 4. Seed Worker Profiles
INSERT INTO workers (id, user_id, nid_number, nid_image_path, bio, price_per_hour, working_area, district, thana, latitude, longitude, experience_years, is_approved, is_available, is_visible, avg_rating, total_reviews) VALUES
(1, 4, '19922691234567891', 'sample_nid_kabir.png', 'Certified electrical technician. Specializes in house wiring, circuit breaker tripping, short circuit repairs, and DB board maintenance with modern safety standards.', 400.00, 'Mirpur-1, Mirpur-2, Mirpur-10, Pallabi', 'Dhaka', 'Mirpur', 23.8067, 90.3667, 6, 1, 1, 1, 4.9, 12),
(2, 5, '19892691234567892', 'sample_nid_mizan.png', 'Master plumber with 8+ years experience. Expert in sanitary fittings, water motor pump servicing, PVC pipe leakage fixing, and overhead tank cleaning.', 350.00, 'Dhanmondi, Kalabagan, Panthapath, Jigatola', 'Dhaka', 'Dhanmondi', 23.7465, 90.3760, 8, 1, 1, 1, 4.8, 9),
(3, 6, '19952691234567893', 'sample_nid_sabbir.png', 'HVAC & Refrigeration technician. Split AC master chemical servicing, gas refilling, cooling issues, inverter compressor diagnostics, and fridge cooling problems.', 600.00, 'Sector 3, Sector 7, Sector 11, Uttara', 'Dhaka', 'Uttara', 23.8759, 90.3795, 4, 1, 1, 1, 5.0, 7),
(4, 7, '19902691234567894', 'sample_nid_tanvir.png', 'Custom furniture craftsman and wood specialist. Door lock repairs, sliding window tracks, kitchen cabinets, and varnish polishing.', 450.00, 'GEC Circle, Nasirabad, Chawkbazar, Muradpur', 'Chittagong', 'Panchlaish', 22.3569, 91.8210, 7, 1, 1, 1, 4.7, 5),
(5, 8, '19932691234567895', 'sample_nid_rafiq.png', 'Professional residential and commercial deep cleaning. Sofa shampooing, water tank disinfection, pest control, and cockroach spray treatments.', 300.00, 'Gulshan-1, Gulshan-2, Banani, Baridhara', 'Dhaka', 'Gulshan', 23.7925, 90.4078, 5, 1, 1, 1, 4.8, 8),
(6, 9, '19912691234567896', 'sample_nid_nurul.png', 'Wall painting specialist. Interior plastic paint, weather coat exterior painting, wall damp treatment, and putty finishing.', 350.00, 'Zindabazar, Amberkhana, Shibganj, Subidbazar', 'Sylhet', 'Kotwali', 24.8949, 91.8687, 6, 1, 1, 1, 4.6, 4);

-- 5. Link Categories (category_id references seeds in schema.sql)
INSERT INTO worker_categories (worker_id, category_id) VALUES
(1, 2), -- Kabir: Electrician
(2, 1), -- Mizanur: Plumber
(3, 7), -- Sabbir: AC Repair
(3, 9), -- Sabbir: Fridge Repair
(4, 5), -- Tanvir: Carpenter
(5, 6), -- Rafiqul: Cleaner
(5, 12),-- Rafiqul: Pest Control
(6, 4); -- Nurul: Painter

-- 6. Seed Active 30-Day Subscriptions (All 6 workers visible immediately)
INSERT INTO subscriptions (worker_id, amount, currency, status, payment_method, transaction_id, starts_at, expires_at) VALUES
(1, 399.00, 'BDT', 'active', 'sslcommerz', 'SUB_SSL_KABIR_001', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 28 DAY)),
(2, 399.00, 'BDT', 'active', 'sslcommerz', 'SUB_SSL_MIZAN_002', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 25 DAY)),
(3, 399.00, 'BDT', 'active', 'sslcommerz', 'SUB_SSL_SABBIR_003', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 29 DAY)),
(4, 399.00, 'BDT', 'active', 'sslcommerz', 'SUB_SSL_TANVIR_004', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 21 DAY)),
(5, 399.00, 'BDT', 'active', 'sslcommerz', 'SUB_SSL_RAFIQ_005', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 27 DAY)),
(6, 399.00, 'BDT', 'active', 'sslcommerz', 'SUB_SSL_NURUL_006', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 19 DAY));

-- 7. Seed Sample Bookings
INSERT INTO bookings (id, customer_id, worker_id, category_id, description, scheduled_at, status, total_amount, pay_method, pay_status, pay_txn_id, address) VALUES
(1, 2, 1, 2, 'Main circuit breaker replacement and 2 bedroom light switch board wiring.', DATE_SUB(NOW(), INTERVAL 2 DAY), 'completed', 800.00, 'sslcommerz', 'paid', 'BOOK_TXN_001', 'House 14, Road 5, Mirpur-10, Dhaka'),
(2, 2, 3, 7, 'Living room 1.5 Ton General Split AC master cleaning and gas top-up.', DATE_ADD(NOW(), INTERVAL 1 DAY), 'accepted', 1200.00, 'cash', 'unpaid', NULL, 'Flat 4B, Green Road, Dhanmondi, Dhaka'),
(3, 3, 2, 1, 'Bathroom concealed pipe leakage and sink faucet replacement.', NOW(), 'in_progress', 500.00, 'cash', 'unpaid', NULL, 'Plot 8, Road 2, Banani, Dhaka');

-- 8. Seed Verified Customer Reviews
INSERT INTO reviews (id, booking_id, customer_id, worker_id, rating, comment, created_at) VALUES
(1, 1, 2, 1, 5, 'Kabir bhai arrived on time with all his tools and multimeter. Fixed the short circuit within an hour and explained everything clearly. Very polite and honest technician!', DATE_SUB(NOW(), INTERVAL 1 DAY));
