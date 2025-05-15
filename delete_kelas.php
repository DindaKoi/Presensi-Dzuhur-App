<?php
session_start();
require_once 'config.php';

if ($_SESSION['role'] == 'admin') {
    $id_kelas = $_GET['id_kelas'];
    $stmt = $pdo->prepare("DELETE FROM kelas WHERE id_kelas = ?");
    try {
        $stmt->execute([$id_kelas]);
        header("Location: dashboard_admin.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>