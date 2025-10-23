<?php
session_start();
require_once '../config.php';

if(!isset($_SESSION['user_id'])) {
    die('Unauthorized');
}

$jadwal_id = $_GET['jadwal_id'] ?? 0;

if($jadwal_id) {
    // Ambil kursi tersedia untuk jadwal tertentu
    $stmt = $pdo->prepare("SELECT k.* FROM kursi k 
                          JOIN jadwal_tayang jt ON jt.id_studio = k.id_studio
                          WHERE jt.id_jadwal = ?
                          ORDER BY k.kode_kursi");
    $stmt->execute([$jadwal_id]);
    $kursi_list = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach($kursi_list as $kursi) {
        $terisi = $kursi['status'] === 'terisi';
        $class = $terisi ? 'kursi-item terisi' : 'kursi-item';
        $data = $terisi ? '' : 'data-kursi="' . $kursi['id_kursi'] . '"';
        
        echo '<div class="' . $class . '" ' . $data . '>';
        echo $kursi['kode_kursi'];
        if($terisi) {
            echo '<br><small>❌ Terisi</small>';
        } else {
            echo '<br><small>✅ Tersedia</small>';
        }
        echo '</div>';
    }
}
?>