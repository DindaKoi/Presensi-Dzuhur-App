<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['nama'] = $user['nama'];

        if ($user['role'] == 'siswa') {
            header("Location: dashboard_siswa.php");
        } elseif ($user['role'] == 'guru') {
            header("Location: dashboard_guru.php");
        } elseif ($user['role'] == 'admin') {
            header("Location: dashboard_admin.php");
        }
    } else {
        echo "Username atau password salah.";
    }
}
?>