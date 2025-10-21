-- Database Bioskop Online
CREATE DATABASE bioskop_online;
USE bioskop_online;

-- Tabel User
CREATE TABLE user (
    id_user INT PRIMARY KEY AUTO_INCREMENT,
    nama VARCHAR(100) NOT NULL,
    pass VARCHAR(255) NOT NULL, -- Menyimpan hash MD5
    no_telp VARCHAR(20),
    email VARCHAR(100) UNIQUE
);

-- Tabel Film
CREATE TABLE film (
    id_film INT PRIMARY KEY AUTO_INCREMENT,
    poster VARCHAR(255),
    nama VARCHAR(100) NOT NULL,
    deskripsi TEXT,
    genre VARCHAR(50),
    jadwal_tayang VARCHAR(50),
    harga DECIMAL(10,2),
    durasi INT
);

-- Tabel Studio
CREATE TABLE studio (
    id_studio INT PRIMARY KEY AUTO_INCREMENT,
    nama_studio VARCHAR(100) NOT NULL,
    kapasitas INT
);

-- Tabel Jadwal Tayang
CREATE TABLE jadwal_tayang (
    id_jadwal INT PRIMARY KEY AUTO_INCREMENT,
    id_film INT,
    id_studio INT,
    tanggal_tayang DATE,
    jam_tayang TIME,
    FOREIGN KEY (id_film) REFERENCES film(id_film) ON DELETE CASCADE,
    FOREIGN KEY (id_studio) REFERENCES studio(id_studio) ON DELETE CASCADE
);

-- Tabel Kursi
CREATE TABLE kursi (
    id_kursi INT PRIMARY KEY AUTO_INCREMENT,
    id_studio INT,
    kode_kursi VARCHAR(10) NOT NULL,
    status ENUM('tersedia', 'terisi') DEFAULT 'tersedia',
    FOREIGN KEY (id_studio) REFERENCES studio(id_studio) ON DELETE CASCADE
);

-- Tabel Booking
CREATE TABLE booking (
    id_booking INT PRIMARY KEY AUTO_INCREMENT,
    id_user INT,
    id_jadwal INT,
    id_kursi INT,
    tanggal_pesan DATE,
    jumlah_tiket INT,
    total_harga DECIMAL(10,2),
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_jadwal) REFERENCES jadwal_tayang(id_jadwal) ON DELETE CASCADE,
    FOREIGN KEY (id_kursi) REFERENCES kursi(id_kursi) ON DELETE CASCADE
);

ALTER TABLE film ADD COLUMN photo_poster VARCHAR(255) AFTER harga;

-- Insert data contoh untuk User
INSERT INTO user (nama, pass, no_telp, email) VALUES
('Admin Bioskop', MD5('123'), '081234567890', 'admin@bioskop.com'), -- Akun Admin: email 'admin@bioskop.com', password '123'
('John Doe', MD5('password'), '082345678901', 'john@email.com'),
('Jane Smith', MD5('password'), '083456789012', 'jane@email.com');

-- Insert data contoh untuk Film
INSERT INTO film (poster, nama, deskripsi, genre, jadwal_tayang, harga, durasi) VALUES
('spiderman.jpg', 'Spider-Man: Beyond', 'Petualangan Spider-Man melawan musuh baru', 'Action', 'Sekarang Tayang', 50000, 148),
('loveletter.jpg', 'The Love Letter', 'Kisah cinta yang menyentuh hati', 'Romance', 'Sekarang Tayang', 45000, 120),
('darkmystery.jpg', 'Dark Mystery', 'Misteri pembunuhan yang mencekam', 'Thriller', 'Sekarang Tayang', 50000, 135),
('spaceadv.jpg', 'Space Adventure', 'Petualangan luar angkasa yang epik', 'Sci-Fi', 'Sekarang Tayang', 55000, 142);

-- Insert data contoh untuk Studio
INSERT INTO studio (nama_studio, kapasitas) VALUES
('Studio 1', 100),
('Studio 2', 80),
('Studio 3', 120),
('Studio VIP', 50);

-- Insert data contoh untuk Jadwal Tayang
INSERT INTO jadwal_tayang (id_film, id_studio, tanggal_tayang, jam_tayang) VALUES
(1, 1, '2024-11-01', '10:00:00'),
(1, 1, '2024-11-01', '13:30:00'),
(1, 2, '2024-11-01', '16:00:00'),
(2, 2, '2024-11-01', '11:00:00'),
(3, 3, '2024-11-01', '14:00:00'),
(4, 4, '2024-11-01', '17:00:00');

-- Insert data contoh untuk Kursi (Studio 1 - 50 kursi)
INSERT INTO kursi (id_studio, kode_kursi, status) VALUES
(1, 'A1', 'tersedia'), (1, 'A2', 'tersedia'), (1, 'A3', 'tersedia'), (1, 'A4', 'tersedia'), (1, 'A5', 'tersedia'),
(1, 'B1', 'tersedia'), (1, 'B2', 'terisi'), (1, 'B3', 'tersedia'), (1, 'B4', 'tersedia'), (1, 'B5', 'tersedia'),
(1, 'C1', 'tersedia'), (1, 'C2', 'tersedia'), (1, 'C3', 'terisi'), (1, 'C4', 'tersedia'), (1, 'C5', 'tersedia');

-- Insert data contoh untuk Booking
INSERT INTO booking (id_user, id_jadwal, id_kursi, tanggal_pesan, jumlah_tiket, total_harga) VALUES
(2, 1, 7, '2024-10-20', 1, 50000),
(3, 2, 13, '2024-10-20', 1, 50000);