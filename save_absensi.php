<?php
session_start();
require_once 'config.php';

// Set timezone to WIB (Western Indonesia Time, UTC+7)
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'siswa') {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['status']) && !empty($_POST['gender'])) {
    $user_id = $_SESSION['user_id'];
    $id_kelas = $_SESSION['user']['id_kelas'] ?? null; // Ambil id_kelas dari sesi user
    $status = strtolower(trim($_POST['status'])); // Normalisasi status
    $gender = strtolower(trim($_POST['gender'])); // Normalisasi gender
    $materi = isset($_POST['materi']) ? trim($_POST['materi']) : null;
    $tanggal = date('Y-m-d');
    $waktu = date('H:i:s');

    // Debugging: Log the current time, id_kelas, and session data
    $debug_message = "Recorded Time: {$tanggal} {$waktu} (Timezone: " . date_default_timezone_get() . "), id_kelas: {$id_kelas}, Session: " . print_r($_SESSION, true) . "\n";
    file_put_contents('debug_absensi.txt', $debug_message, FILE_APPEND);

    // Pastikan id_kelas ada
    if (!$id_kelas) {
        header("Location: dashboard_siswa.php?error=Kelas belum dipilih. Silakan pilih kelas di dashboard.");
        exit;
    }

    // Check for existing attendance for the same user and date
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE id_user = ? AND tanggal = ?");
    $stmt->execute([$user_id, $tanggal]);
    if ($stmt->fetchColumn() > 0) {
        header("Location: dashboard_siswa.php?error=Anda sudah melakukan absensi untuk hari ini.");
        exit;
    }

    $bukti_foto = null;
    if (isset($_FILES['bukti_foto']) && $_FILES['bukti_foto']['size'] > 0) {
        $upload_dir = 'Uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $bukti_foto = $upload_dir . uniqid() . '_' . basename($_FILES['bukti_foto']['name']);
        if (!move_uploaded_file($_FILES['bukti_foto']['tmp_name'], $bukti_foto)) {
            header("Location: dashboard_siswa.php?error=Gagal mengunggah foto.");
            exit;
        }
    }

    $valid_statuses = ($gender === 'perempuan') 
        ? ['hadir', 'tidak hadir karena haid', 'tidak hadir alasan lain']
        : ['hadir', 'tidak hadir alasan lain'];
    if (!in_array($status, $valid_statuses)) {
        header("Location: dashboard_siswa.php?error=Status absensi tidak valid.");
        exit;
    }

    if ($status === 'tidak hadir karena haid' && empty($materi)) {
        header("Location: dashboard_siswa.php?error=Materi keputrian harus diisi untuk status 'Tidak Hadir karena Haid'.");
        exit;
    }

    if (in_array($status, ['hadir', 'tidak hadir alasan lain']) && empty($bukti_foto)) {
        header("Location: dashboard_siswa.php?error=Bukti foto harus diunggah untuk status '$status'.");
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO absensi (id_user, id_kelas, tanggal, waktu_absen, status, gender, materi, bukti_foto) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $id_kelas, $tanggal, $waktu, $status, $gender, $materi, $bukti_foto]);

        // Pesan sukses jika absensi berhasil disimpan
        $success_message = "Absensi berhasil terkirim pada " . date('d-m-Y H:i:s') . ". Terima kasih!";
        header("Location: dashboard_siswa.php?success=" . urlencode($success_message));
        exit;
    } catch (PDOException $e) {
        // Log error untuk debugging
        file_put_contents('debug_absensi.txt', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
        header("Location: dashboard_siswa.php?error=Terjadi kesalahan: " . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: dashboard_siswa.php?error=Permintaan tidak valid atau data tidak lengkap.");
    exit;
}
?>