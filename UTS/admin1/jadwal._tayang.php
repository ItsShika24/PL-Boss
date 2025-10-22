<?php
require_once '../config.php';

// Ambil semua film
$stmt = $pdo->query("SELECT id_film FROM film");
$films = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua studio
$stmt = $pdo->query("SELECT id_studio FROM studio");
$studios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate jadwal untuk 7 hari ke depan
for($i = 0; $i < 7; $i++) {
    $date = date('Y-m-d', strtotime("+$i days"));
    
    foreach($films as $film) {
        foreach($studios as $studio) {
            // Random jam tayang
            $times = ['10:00:00', '13:30:00', '16:00:00', '19:00:00'];
            $random_time = $times[array_rand($times)];
            
            // Cek apakah jadwal sudah ada
            $check = $pdo->prepare("SELECT id_jadwal FROM jadwal_tayang 
                                  WHERE id_film = ? AND id_studio = ? AND tanggal_tayang = ? AND jam_tayang = ?");
            $check->execute([$film['id_film'], $studio['id_studio'], $date, $random_time]);
            
            if($check->rowCount() == 0) {
                // Insert jadwal baru
                $insert = $pdo->prepare("INSERT INTO jadwal_tayang (id_film, id_studio, tanggal_tayang, jam_tayang) 
                                       VALUES (?, ?, ?, ?)");
                $insert->execute([$film['id_film'], $studio['id_studio'], $date, $random_time]);
            }
        }
    }
}

echo "Jadwal berhasil digenerate!";
?>
