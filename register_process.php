<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $nama = $_POST['nama'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("INSERT INTO users (username, nama, password, role) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$username, $nama, $password, $role]);
        header("Location: index.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>