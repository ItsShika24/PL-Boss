<?php
// Buat koneksi database manual untuk user
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "bioskop_online";

$user_conn = mysqli_connect($host, $user, $pass, $dbname);

// Check connection
if (!$user_conn) {
    echo json_encode([]);
    exit();
}

$studio_id = intval($_GET['studio_id'] ?? 0);

if ($studio_id > 0) {
    $sql = "SELECT * FROM kursi WHERE id_studio = ? ORDER BY kode_kursi";
    $stmt = mysqli_prepare($user_conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $studio_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $seats = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $seats[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($seats);
} else {
    echo json_encode([]);
}

mysqli_close($user_conn);
?>