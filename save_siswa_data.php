<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['role'] == 'siswa') {
    $user_id = $_SESSION['user_id'];
    $id_kelas = $_POST['id_kelas'];

    $stmt = $pdo->prepare("UPDATE users SET id_kelas = ? WHERE id = ?");
    try {
        $stmt->execute([$id_kelas, $user_id]);
        header("Location: dashboard_siswa.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>