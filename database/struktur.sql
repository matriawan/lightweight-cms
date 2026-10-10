CREATE DATABASE IF NOT EXISTS lightweight_cms CHARACTER SET utf8mb4;
USE lightweight_cms;

CREATE TABLE IF NOT EXISTS t_user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) NOT NULL,
    bio TEXT NULL,
    role ENUM('admin', 'author') NOT NULL DEFAULT 'author',
    must_change_password TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS t_category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS t_post (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NULL,
    title VARCHAR(255) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    excerpt VARCHAR(500) NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_post_status_published (status, published_at),
    FOREIGN KEY (user_id) REFERENCES t_user (id) ON DELETE RESTRICT,
    FOREIGN KEY (category_id) REFERENCES t_category (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS t_single (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content MEDIUMTEXT NOT NULL,
    status ENUM('draft', 'published') NOT NULL DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_single_status (status),
    FOREIGN KEY (user_id) REFERENCES t_user (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS t_comment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    author_email VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_comment_post_status (post_id, status),
    FOREIGN KEY (post_id) REFERENCES t_post (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS t_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size INT UNSIGNED NOT NULL,
    alt_text VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES t_user (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS t_setting (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample content for the public pages. Change it on the Settings page.
-- The sample banner and favicon are files, not rows: copy database/sample/header.png
-- and database/sample/favicon.png to public/sites/ to see them.
INSERT IGNORE INTO t_setting (setting_key, setting_value) VALUES
('title', 'Lightweight CMS'),
('description', 'Website sederhana berbasis PHP'),
('per_page', '5'),
('header_text', '<p><strong>Selamat datang di Lightweight CMS</strong><br>Sistem manajemen konten yang sederhana, cepat, dan mudah dirawat.</p>'),
('footer_text', '<p>&copy; 2026 Lightweight CMS. Dibuat dengan PHP dan MariaDB.</p>');

-- Default admin (password: admin). Must change the password at first login.
INSERT IGNORE INTO t_user (username, email, password_hash, display_name, role, must_change_password) VALUES
('admin', 'admin@example.com', '$2y$12$a4K.x0MbB0lbdS8oDB6GGOBp.bpMK0N8IFKSjAjXqAEOz/Nt8hkR6', 'Administrator', 'admin', 1);

-- Sample content for the public home page, owned by the default admin.
-- Safe to import more than once: categories are unique by name, and singles and posts are only added when their title does not exist yet.
INSERT IGNORE INTO t_category (name, description) VALUES
('Technology', 'Artikel seputar teknologi dan pemrograman'),
('Travel', 'Cerita dan panduan perjalanan'),
('Food', 'Resep dan dunia kuliner'),
('Health', 'Tips hidup sehat'),
('Education', 'Belum ada artikel');

INSERT INTO t_single (user_id, title, content, status)
SELECT u.id, 'About', '<p>Lightweight CMS adalah sistem manajemen konten yang sederhana, ditulis dengan PHP native dan MariaDB.</p>', 'published' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_single s WHERE s.title = 'About');
INSERT INTO t_single (user_id, title, content, status)
SELECT u.id, 'Contact', '<p>Hubungi kami melalui email <strong>admin@example.com</strong>.</p>', 'published' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_single s WHERE s.title = 'Contact');
INSERT INTO t_single (user_id, title, content, status)
SELECT u.id, 'Privacy Policy', '<p>Kami menyimpan data seperlunya dan tidak membagikannya kepada pihak lain.</p>', 'published' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_single s WHERE s.title = 'Privacy Policy');
INSERT INTO t_single (user_id, title, content, status)
SELECT u.id, 'Terms of Service', '<p>Draft ketentuan layanan, belum dipublikasikan.</p>', 'draft' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_single s WHERE s.title = 'Terms of Service');

INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Technology'), 'Mengenal PHP Native untuk Pemula', '<p>PHP native cocok untuk belajar dasar web. Selalu lakukan <strong>sanitasi</strong> input dan gunakan prepared statement.</p>', 'Panduan singkat memulai pemrograman web dengan PHP tanpa framework.', 'published', '2026-10-09 08:30:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Mengenal PHP Native untuk Pemula');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Technology'), 'Tips Menjaga Keamanan Aplikasi Web', '<p>Lindungi form dengan token <em>CSRF</em>, escape semua output, dan simpan password dalam bentuk hash.</p><p>Periksa juga hak akses di sisi server.</p>', NULL, 'published', '2026-10-07 14:00:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Tips Menjaga Keamanan Aplikasi Web');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Technology'), 'Draft: Rencana Rilis Fitur Baru', '<p>Rencana ini masih rahasia dan belum boleh dipublikasikan.</p>', NULL, 'draft', NULL FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Draft: Rencana Rilis Fitur Baru');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Travel'), 'Liburan Akhir Pekan di Yogyakarta', '<p>Mulai pagi dengan sarapan <strong>gudeg</strong>, lalu berjalan kaki menyusuri Malioboro.</p>', 'Tiga hari menjelajahi kota budaya dengan anggaran terjangkau.', 'published', '2026-10-05 10:15:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Liburan Akhir Pekan di Yogyakarta');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Travel'), 'Panduan Mendaki Gunung Bromo', '<p>Berangkat dini hari agar sempat menikmati <strong>sunrise</strong> dari Penanjakan. Bawa jaket tebal dan masker.</p>', NULL, 'published', '2026-09-28 05:45:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Panduan Mendaki Gunung Bromo');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Food'), 'Resep Nasi Goreng Kampung', '<p>Resep ini memakai bawang merah, cabai, dan sedikit kecap manis.</p>', 'Nasi goreng sederhana dengan bumbu rempah dapur.', 'published', '2026-10-08 12:00:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Resep Nasi Goreng Kampung');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Food'), 'Kopi Nusantara dan Cara Menyeduhnya', '<p>Biji dari Gayo, Toraja, dan Flores punya karakter rasa yang berbeda. Seduh dengan air 92 derajat.</p>', NULL, 'published', '2026-09-30 07:20:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Kopi Nusantara dan Cara Menyeduhnya');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Food'), 'Draft: Kumpulan Resep Kue Lebaran', '<p>Daftar ini masih rahasia sampai bulan depan.</p>', NULL, 'draft', NULL FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Draft: Kumpulan Resep Kue Lebaran');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Health'), 'Manfaat Olahraga Ringan Setiap Hari', '<p>Olahraga ringan menurunkan stres dan memperbaiki kualitas tidur.</p>', 'Berjalan kaki 30 menit sehari sudah membantu menjaga kebugaran.', 'published', '2026-10-02 06:30:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Manfaat Olahraga Ringan Setiap Hari');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Health'), 'Pola Tidur yang Baik untuk Produktivitas', '<p>Tidur tujuh sampai delapan jam membantu fokus. Hindari layar satu jam sebelum tidur.</p>', NULL, 'published', '2026-09-22 21:00:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Pola Tidur yang Baik untuk Produktivitas');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, NULL, 'Pengumuman Pemeliharaan Situs', '<p>Selama <strong>pemeliharaan</strong>, beberapa fitur dapat tidak tersedia sementara.</p>', 'Situs akan diperbarui pada akhir pekan ini.', 'published', '2026-10-06 09:00:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Pengumuman Pemeliharaan Situs');
INSERT INTO t_post (user_id, category_id, title, content, excerpt, status, published_at)
SELECT u.id, (SELECT c.id FROM t_category c WHERE c.name = 'Technology'), 'Belajar SQL: Memahami JOIN', '<p>INNER JOIN menggabungkan baris dari dua tabel yang memiliki nilai kunci yang sama.</p>', NULL, 'published', '2026-09-18 16:40:00' FROM t_user u
WHERE u.username = 'admin' AND NOT EXISTS (SELECT 1 FROM t_post p WHERE p.title = 'Belajar SQL: Memahami JOIN');
