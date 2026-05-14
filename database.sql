CREATE DATABASE IF NOT EXISTS metro_analyzer;
USE metro_analyzer;

DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS travel_logs;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    source_station VARCHAR(100) NOT NULL,
    dest_station VARCHAR(100) NOT NULL,
    fare DECIMAL(8, 2) NOT NULL,
    payment_method VARCHAR(50),
    payment_status ENUM('pending', 'paid') DEFAULT 'pending',
    qr_data VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Default Admin Account (password: admin123)
INSERT INTO users (username, email, password_hash, role) 
VALUES ('SuperAdmin', 'admin@bengaluru.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Default User Account (password: password)
INSERT INTO users (username, email, password_hash, role) 
VALUES ('NammaCommuter', 'test@bengaluru.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Sample Tickets for the User
INSERT INTO tickets (user_id, source_station, dest_station, fare, payment_method, payment_status, qr_data, created_at) VALUES
(2, 'Whitefield (Kadugodi)', 'Nadaprabhu Kempegowda Station, Majestic', 60.00, 'UPI', 'paid', 'TKT-1001-QR', CURDATE() - INTERVAL 1 DAY),
(2, 'Nadaprabhu Kempegowda Station, Majestic', 'Indiranagar', 35.00, 'Card', 'paid', 'TKT-1002-QR', CURDATE() - INTERVAL 1 DAY),
(2, 'Silk Institute', 'Jayanagar', 45.00, 'NetBanking', 'paid', 'TKT-1003-QR', CURDATE() - INTERVAL 2 DAY),
(2, 'MG Road', 'Baiyappanahalli', 30.00, 'UPI', 'paid', 'TKT-1004-QR', CURDATE() - INTERVAL 3 DAY);
