<?php
session_start();
require_once 'config.php';

// Inisialisasi $_SESSION['user'] sebagai array jika belum ada
if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
    $_SESSION['user'] = [];
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit;
}

// Reset kelas jika diminta
if (isset($_GET['reset']) && $_GET['reset'] === 'true') {
    unset($_SESSION['user']['id_kelas']);
    unset($_SESSION['user']['kelas']);
    $debug_reset = "Kelas di-reset untuk User ID: {$_SESSION['user_id']}, Session: " . print_r($_SESSION, true) . "\n";
    file_put_contents('debug_kelas.txt', $debug_reset, FILE_APPEND);
    header("Location: dashboard_siswa.php?success=Kelas telah direset. Silakan pilih kelas baru.");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $kelas = isset($_POST['kelas']) ? trim($_POST['kelas']) : '';

    // Debugging: Log data yang diterima dari form
    $debug_input = "Received POST: " . print_r($_POST, true) . "\n";
    file_put_contents('debug_kelas.txt', $debug_input, FILE_APPEND);

    // Validasi input kelas
    if (empty($kelas)) {
        $error_message = "Error: Nama kelas tidak boleh kosong.";
        file_put_contents('debug_kelas.txt', $error_message . "\n", FILE_APPEND);
        header("Location: dashboard_siswa.php?error=" . urlencode($error_message));
        exit;
    }

    // Cari id_kelas berdasarkan nama kelas yang dipilih
    $stmt = $pdo->prepare("SELECT id_kelas FROM kelas WHERE nama_kelas = ?");
    $stmt->execute([$kelas]);
    $kelas_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$kelas_data) {
        $error_message = "Error: Kelas '$kelas' tidak ditemukan di database.";
        file_put_contents('debug_kelas.txt', $error_message . "\n", FILE_APPEND);
        header("Location: dashboard_siswa.php?error=" . urlencode($error_message));
        exit;
    }

    $id_kelas = $kelas_data['id_kelas'];

    // Simpan id_kelas dan nama kelas ke sesi
    $_SESSION['user']['id_kelas'] = $id_kelas;
    $_SESSION['user']['kelas'] = $kelas;

    // Debugging: Log sesi setelah menyimpan kelas
    $debug_session = "User ID: {$user_id}, id_kelas: {$id_kelas}, Kelas: {$kelas}, Session: " . print_r($_SESSION, true) . "\n";
    file_put_contents('debug_kelas.txt', $debug_session, FILE_APPEND);

    // Pesan sukses dengan waktu
    $success_message = "Kelas '$kelas' berhasil disimpan pada " . date('d-m-Y H:i:s') . ".";
    header("Location: dashboard_siswa.php?success=" . urlencode($success_message));
    exit;
} else {
    $error_message = "Error: Permintaan tidak valid atau data tidak lengkap.";
    file_put_contents('debug_kelas.txt', $error_message . "\n", FILE_APPEND);
    header("Location: dashboard_siswa.php?error=" . urlencode($error_message));
    exit;
}
?>