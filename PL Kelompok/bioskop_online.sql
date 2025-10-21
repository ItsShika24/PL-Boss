-- Database untuk Sistem Pemesanan Tiket Bioskop Online
-- Kelompok 2: Fatika, Devina, Raihandy

CREATE DATABASE bioskop_online;
USE bioskop_online;

-- Tabel User
CREATE TABLE user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    pass VARCHAR(255) NOT NULL,
    no_telp VARCHAR(20),
    email VARCHAR(100) UNIQUE NOT NULL
);

-- Tabel Film
CREATE TABLE film (
    id_film INT AUTO_INCREMENT PRIMARY KEY,
    poster VARCHAR(255),
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    genre VARCHAR(50),
    jadwal_tayang VARCHAR(50),
    harga DECIMAL(10,2) NOT NULL,
    durasi INT
);

-- Tabel Studio
CREATE TABLE studio (
    id_studio INT AUTO_INCREMENT PRIMARY KEY,
    nama_studio VARCHAR(100) NOT NULL,
    kapasitas INT NOT NULL
);

-- Tabel Jadwal Tayang
CREATE TABLE jadwal_tayang (
    id_jadwal INT AUTO_INCREMENT PRIMARY KEY,
    id_film INT NOT NULL,
    id_studio INT NOT NULL,
    tanggal_tayang DATE NOT NULL,
    jam_tayang TIME NOT NULL,
    FOREIGN KEY (id_film) REFERENCES film(id_film) ON DELETE CASCADE,
    FOREIGN KEY (id_studio) REFERENCES studio(id_studio) ON DELETE CASCADE
);

-- Tabel Kursi
CREATE TABLE kursi (
    id_kursi INT AUTO_INCREMENT PRIMARY KEY,
    id_studio INT NOT NULL,
    kode_kursi VARCHAR(10) NOT NULL,
    status ENUM('tersedia', 'terisi') DEFAULT 'tersedia',
    FOREIGN KEY (id_studio) REFERENCES studio(id_studio) ON DELETE CASCADE,
    UNIQUE KEY unique_kursi (id_studio, kode_kursi)
);

-- Tabel Booking
CREATE TABLE booking (
    id_booking INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_jadwal INT NOT NULL,
    id_kursi INT NOT NULL,
    tanggal_pesan DATE NOT NULL,
    jumlah_tiket INT DEFAULT 1,
    total_harga DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_tayang(id_jadwal) ON DELETE CASCADE,
    FOREIGN KEY (id_kursi) REFERENCES kursi(id_kursi) ON DELETE CASCADE
);

-- Insert Data Contoh untuk Testing

-- Insert User
INSERT INTO user (nama, pass, no_telp, email) VALUES
('Admin Bioskop', MD5('admin123'), '081234567890', 'admin@bioskop.com'),
('Budi Santoso', MD5('budi123'), '082345678901', 'budi@email.com'),
('Siti Nurhaliza', MD5('siti123'), '083456789012', 'siti@email.com');

-- Insert Film
INSERT INTO film (poster, nama, deskripsi, genre, jadwal_tayang, harga, durasi) VALUES
('spiderman.jpg', 'Spider-Man: Beyond', 'Petualangan Spider-Man melawan musuh baru', 'Action', 'Sedang Tayang', 50000, 148),
('love_letter.jpg', 'The Love Letter', 'Kisah cinta yang mengharukan', 'Romance', 'Sedang Tayang', 45000, 120),
('dark_mystery.jpg', 'Dark Mystery', 'Misteri pembunuhan yang mendebarkan', 'Thriller', 'Sedang Tayang', 50000, 135),
('space_adventure.jpg', 'Space Adventure', 'Petualangan luar angkasa yang epik', 'Sci-Fi', 'Coming Soon', 55000, 142);

-- Insert Studio
INSERT INTO studio (nama_studio, kapasitas) VALUES
('Studio 1', 100),
('Studio 2', 80),
('Studio 3', 60);

-- Insert Jadwal Tayang
INSERT INTO jadwal_tayang (id_film, id_studio, tanggal_tayang, jam_tayang) VALUES
(1, 1, '2024-12-25', '10:00:00'),
(1, 1, '2024-12-25', '13:30:00'),
(1, 2, '2024-12-25', '16:00:00'),
(2, 2, '2024-12-25', '11:00:00'),
(2, 3, '2024-12-25', '14:00:00'),
(3, 1, '2024-12-26', '09:30:00'),
(3, 3, '2024-12-26', '15:30:00'),
(4, 2, '2024-12-27', '11:00:00');

-- Insert Kursi untuk Studio 1 (10 baris x 10 kolom = 100 kursi)
INSERT INTO kursi (id_studio, kode_kursi, status) VALUES
-- Baris A
(1, 'A1', 'tersedia'), (1, 'A2', 'tersedia'), (1, 'A3', 'tersedia'), (1, 'A4', 'tersedia'), (1, 'A5', 'tersedia'),
(1, 'A6', 'tersedia'), (1, 'A7', 'tersedia'), (1, 'A8', 'tersedia'), (1, 'A9', 'tersedia'), (1, 'A10', 'tersedia'),
-- Baris B
(1, 'B1', 'tersedia'), (1, 'B2', 'terisi'), (1, 'B3', 'terisi'), (1, 'B4', 'tersedia'), (1, 'B5', 'tersedia'),
(1, 'B6', 'tersedia'), (1, 'B7', 'tersedia'), (1, 'B8', 'terisi'), (1, 'B9', 'tersedia'), (1, 'B10', 'tersedia'),
-- Baris C (contoh, bisa dilanjutkan)
(1, 'C1', 'tersedia'), (1, 'C2', 'tersedia'), (1, 'C3', 'tersedia'), (1, 'C4', 'terisi'), (1, 'C5', 'terisi'),
(1, 'C6', 'tersedia'), (1, 'C7', 'tersedia'), (1, 'C8', 'tersedia'), (1, 'C9', 'tersedia'), (1, 'C10', 'tersedia');

-- Insert Booking Contoh
INSERT INTO booking (id_user, id_jadwal, id_kursi, tanggal_pesan, jumlah_tiket, total_harga) VALUES
(2, 1, 12, '2024-12-20', 1, 50000),
(2, 1, 13, '2024-12-20', 1, 50000),
(3, 4, 24, '2024-12-21', 1, 45000);

-- Query untuk cek relasi
SELECT 
    b.id_booking,
    u.nama AS nama_user,
    f.nama AS nama_film,
    s.nama_studio,
    jt.tanggal_tayang,
    jt.jam_tayang,
    k.kode_kursi,
    b.total_harga
FROM booking b
JOIN user u ON b.id_user = u.id_user
JOIN jadwal_tayang jt ON b.id_jadwal = jt.id_jadwal
JOIN film f ON jt.id_film = f.id_film
JOIN studio s ON jt.id_studio = s.id_studio
JOIN kursi k ON b.id_kursi = k.id_kursi;