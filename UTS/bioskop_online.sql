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

-- Update film yang sudah ada untuk mengisi field jadwal_tayang
UPDATE film SET jadwal_tayang = 'Sekarang Tayang' WHERE jadwal_tayang IS NULL OR jadwal_tayang = '';

-- Update film yang sudah ada untuk mengisi photo_poster dari poster
UPDATE film SET photo_poster = poster WHERE photo_poster IS NULL AND poster IS NOT NULL;

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

-- Hapus data kursi lama jika ada
DELETE FROM kursi;

-- Generate kursi untuk setiap studio (10x10 layout)
INSERT INTO kursi (id_studio, kode_kursi, status) VALUES
-- Studio 1 (100 kursi)
(1, 'A1', 'tersedia'), (1, 'A2', 'tersedia'), (1, 'A3', 'tersedia'), (1, 'A4', 'tersedia'), (1, 'A5', 'tersedia'), (1, 'A6', 'tersedia'), (1, 'A7', 'tersedia'), (1, 'A8', 'tersedia'), (1, 'A9', 'tersedia'), (1, 'A10', 'tersedia'),
(1, 'B1', 'tersedia'), (1, 'B2', 'tersedia'), (1, 'B3', 'tersedia'), (1, 'B4', 'tersedia'), (1, 'B5', 'tersedia'), (1, 'B6', 'tersedia'), (1, 'B7', 'tersedia'), (1, 'B8', 'tersedia'), (1, 'B9', 'tersedia'), (1, 'B10', 'tersedia'),
(1, 'C1', 'tersedia'), (1, 'C2', 'tersedia'), (1, 'C3', 'tersedia'), (1, 'C4', 'tersedia'), (1, 'C5', 'tersedia'), (1, 'C6', 'tersedia'), (1, 'C7', 'tersedia'), (1, 'C8', 'tersedia'), (1, 'C9', 'tersedia'), (1, 'C10', 'tersedia'),
(1, 'D1', 'tersedia'), (1, 'D2', 'tersedia'), (1, 'D3', 'tersedia'), (1, 'D4', 'tersedia'), (1, 'D5', 'tersedia'), (1, 'D6', 'tersedia'), (1, 'D7', 'tersedia'), (1, 'D8', 'tersedia'), (1, 'D9', 'tersedia'), (1, 'D10', 'tersedia'),
(1, 'E1', 'tersedia'), (1, 'E2', 'tersedia'), (1, 'E3', 'tersedia'), (1, 'E4', 'tersedia'), (1, 'E5', 'tersedia'), (1, 'E6', 'tersedia'), (1, 'E7', 'tersedia'), (1, 'E8', 'tersedia'), (1, 'E9', 'tersedia'), (1, 'E10', 'tersedia'),
(1, 'F1', 'tersedia'), (1, 'F2', 'tersedia'), (1, 'F3', 'tersedia'), (1, 'F4', 'tersedia'), (1, 'F5', 'tersedia'), (1, 'F6', 'tersedia'), (1, 'F7', 'tersedia'), (1, 'F8', 'tersedia'), (1, 'F9', 'tersedia'), (1, 'F10', 'tersedia'),
(1, 'G1', 'tersedia'), (1, 'G2', 'tersedia'), (1, 'G3', 'tersedia'), (1, 'G4', 'tersedia'), (1, 'G5', 'tersedia'), (1, 'G6', 'tersedia'), (1, 'G7', 'tersedia'), (1, 'G8', 'tersedia'), (1, 'G9', 'tersedia'), (1, 'G10', 'tersedia'),
(1, 'H1', 'tersedia'), (1, 'H2', 'tersedia'), (1, 'H3', 'tersedia'), (1, 'H4', 'tersedia'), (1, 'H5', 'tersedia'), (1, 'H6', 'tersedia'), (1, 'H7', 'tersedia'), (1, 'H8', 'tersedia'), (1, 'H9', 'tersedia'), (1, 'H10', 'tersedia'),
(1, 'I1', 'tersedia'), (1, 'I2', 'tersedia'), (1, 'I3', 'tersedia'), (1, 'I4', 'tersedia'), (1, 'I5', 'tersedia'), (1, 'I6', 'tersedia'), (1, 'I7', 'tersedia'), (1, 'I8', 'tersedia'), (1, 'I9', 'tersedia'), (1, 'I10', 'tersedia'),
(1, 'J1', 'tersedia'), (1, 'J2', 'tersedia'), (1, 'J3', 'tersedia'), (1, 'J4', 'tersedia'), (1, 'J5', 'tersedia'), (1, 'J6', 'tersedia'), (1, 'J7', 'tersedia'), (1, 'J8', 'tersedia'), (1, 'J9', 'tersedia'), (1, 'J10', 'tersedia')

-- Studio 2 (80 kursi - lebih kecil)
INSERT INTO kursi (id_studio, kode_kursi, status) VALUES
(2, 'A1', 'tersedia'), (2, 'A2', 'tersedia'), (2, 'A3', 'tersedia'), (2, 'A4', 'tersedia'), (2, 'A5', 'tersedia'), (2, 'A6', 'tersedia'), (2, 'A7', 'tersedia'), (2, 'A8', 'tersedia'),
(2, 'B1', 'tersedia'), (2, 'B2', 'tersedia'), (2, 'B3', 'tersedia'), (2, 'B4', 'tersedia'), (2, 'B5', 'tersedia'), (2, 'B6', 'tersedia'), (2, 'B7', 'tersedia'), (2, 'B8', 'tersedia'),
(2, 'C1', 'tersedia'), (2, 'C2', 'tersedia'), (2, 'C3', 'tersedia'), (2, 'C4', 'tersedia'), (2, 'C5', 'tersedia'), (2, 'C6', 'tersedia'), (2, 'C7', 'tersedia'), (2, 'C8', 'tersedia'),
(2, 'D1', 'tersedia'), (2, 'D2', 'tersedia'), (2, 'D3', 'tersedia'), (2, 'D4', 'tersedia'), (2, 'D5', 'tersedia'), (2, 'D6', 'tersedia'), (2, 'D7', 'tersedia'), (2, 'D8', 'tersedia'),
(2, 'E1', 'tersedia'), (2, 'E2', 'tersedia'), (2, 'E3', 'tersedia'), (2, 'E4', 'tersedia'), (2, 'E5', 'tersedia'), (2, 'E6', 'tersedia'), (2, 'E7', 'tersedia'), (2, 'E8', 'tersedia'),
(2, 'F1', 'tersedia'), (2, 'F2', 'tersedia'), (2, 'F3', 'tersedia'), (2, 'F4', 'tersedia'), (2, 'F5', 'tersedia'), (2, 'F6', 'tersedia'), (2, 'F7', 'tersedia'), (2, 'F8', 'tersedia'),
(2, 'G1', 'tersedia'), (2, 'G2', 'tersedia'), (2, 'G3', 'tersedia'), (2, 'G4', 'tersedia'), (2, 'G5', 'tersedia'), (2, 'G6', 'tersedia'), (2, 'G7', 'tersedia'), (2, 'G8', 'tersedia'),
(2, 'H1', 'tersedia'), (2, 'H2', 'tersedia'), (2, 'H3', 'tersedia'), (2, 'H4', 'tersedia'), (2, 'H5', 'tersedia'), (2, 'H6', 'tersedia'), (2, 'H7', 'tersedia'), (2, 'H8', 'tersedia'),
(2, 'I1', 'tersedia'), (2, 'I2', 'tersedia'), (2, 'I3', 'tersedia'), (2, 'I4', 'tersedia'), (2, 'I5', 'tersedia'), (2, 'I6', 'tersedia'), (2, 'I7', 'tersedia'), (2, 'I8', 'tersedia'),
(2, 'J1', 'tersedia'), (2, 'J2', 'tersedia'), (2, 'J3', 'tersedia'), (2, 'J4', 'tersedia'), (2, 'J5', 'tersedia'), (2, 'J6', 'tersedia'), (2, 'J7', 'tersedia'), (2, 'J8', 'tersedia')

-- Studio 3 (120 kursi - lebih besar)
INSERT INTO kursi (id_studio, kode_kursi, status) VALUES
(3, 'A1', 'tersedia'), (3, 'A2', 'tersedia'), (3, 'A3', 'tersedia'), (3, 'A4', 'tersedia'), (3, 'A5', 'tersedia'), (3, 'A6', 'tersedia'), (3, 'A7', 'tersedia'), (3, 'A8', 'tersedia'), (3, 'A9', 'tersedia'), (3, 'A10', 'tersedia'), (3, 'A11', 'tersedia'), (3, 'A12', 'tersedia'),
(3, 'B1', 'tersedia'), (3, 'B2', 'tersedia'), (3, 'B3', 'tersedia'), (3, 'B4', 'tersedia'), (3, 'B5', 'tersedia'), (3, 'B6', 'tersedia'), (3, 'B7', 'tersedia'), (3, 'B8', 'tersedia'), (3, 'B9', 'tersedia'), (3, 'B10', 'tersedia'), (3, 'B11', 'tersedia'), (3, 'B12', 'tersedia'),
(3, 'C1', 'tersedia'), (3, 'C2', 'tersedia'), (3, 'C3', 'tersedia'), (3, 'C4', 'tersedia'), (3, 'C5', 'tersedia'), (3, 'C6', 'tersedia'), (3, 'C7', 'tersedia'), (3, 'C8', 'tersedia'), (3, 'C9', 'tersedia'), (3, 'C10', 'tersedia'), (3, 'C11', 'tersedia'), (3, 'C12', 'tersedia'),
(3, 'D1', 'tersedia'), (3, 'D2', 'tersedia'), (3, 'D3', 'tersedia'), (3, 'D4', 'tersedia'), (3, 'D5', 'tersedia'), (3, 'D6', 'tersedia'), (3, 'D7', 'tersedia'), (3, 'D8', 'tersedia'), (3, 'D9', 'tersedia'), (3, 'D10', 'tersedia'), (3, 'D11', 'tersedia'), (3, 'D12', 'tersedia'),
(3, 'E1', 'tersedia'), (3, 'E2', 'tersedia'), (3, 'E3', 'tersedia'), (3, 'E4', 'tersedia'), (3, 'E5', 'tersedia'), (3, 'E6', 'tersedia'), (3, 'E7', 'tersedia'), (3, 'E8', 'tersedia'), (3, 'E9', 'tersedia'), (3, 'E10', 'tersedia'), (3, 'E11', 'tersedia'), (3, 'E12', 'tersedia'),
(3, 'F1', 'tersedia'), (3, 'F2', 'tersedia'), (3, 'F3', 'tersedia'), (3, 'F4', 'tersedia'), (3, 'F5', 'tersedia'), (3, 'F6', 'tersedia'), (3, 'F7', 'tersedia'), (3, 'F8', 'tersedia'), (3, 'F9', 'tersedia'), (3, 'F10', 'tersedia'), (3, 'F11', 'tersedia'), (3, 'F12', 'tersedia'),
(3, 'G1', 'tersedia'), (3, 'G2', 'tersedia'), (3, 'G3', 'tersedia'), (3, 'G4', 'tersedia'), (3, 'G5', 'tersedia'), (3, 'G6', 'tersedia'), (3, 'G7', 'tersedia'), (3, 'G8', 'tersedia'), (3, 'G9', 'tersedia'), (3, 'G10', 'tersedia'), (3, 'G11', 'tersedia'), (3, 'G12', 'tersedia'),
(3, 'H1', 'tersedia'), (3, 'H2', 'tersedia'), (3, 'H3', 'tersedia'), (3, 'H4', 'tersedia'), (3, 'H5', 'tersedia'), (3, 'H6', 'tersedia'), (3, 'H7', 'tersedia'), (3, 'H8', 'tersedia'), (3, 'H9', 'tersedia'), (3, 'H10', 'tersedia'), (3, 'H11', 'tersedia'), (3, 'H12', 'tersedia'),
(3, 'I1', 'tersedia'), (3, 'I2', 'tersedia'), (3, 'I3', 'tersedia'), (3, 'I4', 'tersedia'), (3, 'I5', 'tersedia'), (3, 'I6', 'tersedia'), (3, 'I7', 'tersedia'), (3, 'I8', 'tersedia'), (3, 'I9', 'tersedia'), (3, 'I10', 'tersedia'), (3, 'I11', 'tersedia'), (3, 'I12', 'tersedia'),
(3, 'J1', 'tersedia'), (3, 'J2', 'tersedia'), (3, 'J3', 'tersedia'), (3, 'J4', 'tersedia'), (3, 'J5', 'tersedia'), (3, 'J6', 'tersedia'), (3, 'J7', 'tersedia'), (3, 'J8', 'tersedia'), (3, 'J9', 'tersedia'), (3, 'J10', 'tersedia'), (3, 'J11', 'tersedia'), (3, 'J12', 'tersedia')

-- Studio VIP (50 kursi - premium)
INSERT INTO kursi (id_studio, kode_kursi, status) VALUES
(4, 'A1', 'tersedia'), (4, 'A2', 'tersedia'), (4, 'A3', 'tersedia'), (4, 'A4', 'tersedia'), (4, 'A5', 'tersedia'),
(4, 'B1', 'tersedia'), (4, 'B2', 'tersedia'), (4, 'B3', 'tersedia'), (4, 'B4', 'tersedia'), (4, 'B5', 'tersedia'),
(4, 'C1', 'tersedia'), (4, 'C2', 'tersedia'), (4, 'C3', 'tersedia'), (4, 'C4', 'tersedia'), (4, 'C5', 'tersedia'),
(4, 'D1', 'tersedia'), (4, 'D2', 'tersedia'), (4, 'D3', 'tersedia'), (4, 'D4', 'tersedia'), (4, 'D5', 'tersedia'),
(4, 'E1', 'tersedia'), (4, 'E2', 'tersedia'), (4, 'E3', 'tersedia'), (4, 'E4', 'tersedia'), (4, 'E5', 'tersedia'),
(4, 'F1', 'tersedia'), (4, 'F2', 'tersedia'), (4, 'F3', 'tersedia'), (4, 'F4', 'tersedia'), (4, 'F5', 'tersedia'),
(4, 'G1', 'tersedia'), (4, 'G2', 'tersedia'), (4, 'G3', 'tersedia'), (4, 'G4', 'tersedia'), (4, 'G5', 'tersedia'),
(4, 'H1', 'tersedia'), (4, 'H2', 'tersedia'), (4, 'H3', 'tersedia'), (4, 'H4', 'tersedia'), (4, 'H5', 'tersedia'),
(4, 'I1', 'tersedia'), (4, 'I2', 'tersedia'), (4, 'I3', 'tersedia'), (4, 'I4', 'tersedia'), (4, 'I5', 'tersedia'),
(4, 'J1', 'tersedia'), (4, 'J2', 'tersedia'), (4, 'J3', 'tersedia'), (4, 'J4', 'tersedia'), (4, 'J5', 'tersedia')
