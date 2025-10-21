<?php
// ============================================
// FILE: hapus_film.php
// Hapus Film (DELETE)
// ============================================

require_once '../config.php';

$id = $_GET['id'];

$stmt = $pdo->prepare("DELETE FROM film WHERE id_film = ?");
$stmt->execute([$id]);

header('Location: index.php');
exit;
?>
