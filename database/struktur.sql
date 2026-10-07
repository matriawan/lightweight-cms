CREATE DATABASE IF NOT EXISTS lightweight_cms CHARACTER SET utf8mb4;
USE lightweight_cms;

CREATE TABLE IF NOT EXISTS t_setting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO t_setting (setting_key, setting_value) VALUES
('title', 'Lightweight CMS'),
('description', 'Website sederhana berbasis PHP'),
('per_page', '5');
