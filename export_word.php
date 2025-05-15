<?php
session_start();
require_once 'config.php';

// Set timezone to WIB (Western Indonesia Time, UTC+7)
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'guru') {
    header("Location: index.php");
    exit;
}

// Ambil parameter id_kelas dari URL
$id_kelas = isset($_GET['id_kelas']) ? (int)$_GET['id_kelas'] : '';
if (empty($id_kelas)) {
    die("Kelas harus diisi!");
}

// Ambil nama kelas untuk judul
$stmt = $pdo->prepare("SELECT nama_kelas FROM kelas WHERE id_kelas = ?");
$stmt->execute([$id_kelas]);
$kelas = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$kelas) {
    die("Kelas tidak ditemukan!");
}

// Query untuk mengambil data dari tabel absensi dan users berdasarkan id_kelas dari absensi
$query = "SELECT a.tanggal, a.waktu_absen, a.gender, a.status, a.materi, a.bukti_foto, u.nama 
          FROM absensi a 
          JOIN users u ON a.id_user = u.id 
          WHERE a.id_kelas = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$id_kelas]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Debugging: Log hasil query
file_put_contents('debug_export.txt', "Query executed for id_kelas: {$id_kelas}, Result: " . print_r($result, true) . "\n", FILE_APPEND);

// Atur header untuk menghasilkan file Word
header("Content-Type: application/vnd.ms-word");
header("Content-Disposition: attachment; filename=Absensi_Kelas_{$kelas['nama_kelas']}_" . date('d-m-Y') . ".doc");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

// Mulai output dokumen Word (HTML yang kompatibel dengan MS Word)
echo "<html xmlns:o='urn:schemas-microsoft-com:office:office' 
            xmlns:w='urn:schemas-microsoft-com:office:word' 
            xmlns='http://www.w3.org/TR/REC-html40'>";
echo "<head><meta charset='utf-8'><title>Absensi Siswa</title></head>";
echo "<body>";
echo "<h2>Laporan Absensi Salat Dzuhur - Kelas {$kelas['nama_kelas']}</h2>";
echo "<table border='1' cellspacing='0' cellpadding='5'>";
echo "<tr>
        <th>Nama</th>
        <th>Tanggal</th>
        <th>Waktu</th>
        <th>Gender</th>
        <th>Status</th>
        <th>Materi</th>
        <th>Bukti Foto</th>
      </tr>";

if (count($result) > 0) {
    foreach ($result as $row) {
        $materi = empty($row['materi']) ? '-' : htmlspecialchars($row['materi']);
        $bukti_foto = empty($row['bukti_foto']) ? '-' : $row['bukti_foto'];
        echo "<tr>
                <td>" . htmlspecialchars($row['nama']) . "</td>
                <td>" . htmlspecialchars($row['tanggal']) . "</td>
                <td>" . htmlspecialchars($row['waktu_absen']) . "</td>
                <td>" . htmlspecialchars($row['gender']) . "</td>
                <td>" . htmlspecialchars($row['status']) . "</td>
                <td>" . $materi . "</td>
                <td>";
        if (!empty($row['bukti_foto']) && file_exists($row['bukti_foto'])) {
            // Baca file gambar dan konversi ke base64
            $image_path = $row['bukti_foto'];
            $image_data = file_get_contents($image_path);
            $image_base64 = base64_encode($image_data);
            $image_type = pathinfo($image_path, PATHINFO_EXTENSION);
            // Sematkan gambar menggunakan base64
            echo "<img src='data:image/$image_type;base64,$image_base64' width='100' height='100' alt='Bukti Foto'>";
        } else {
            echo "-";
        }
        echo "</td></tr>";
    }
} else {
    echo "<tr><td colspan='7'>Tidak ada data absensi untuk kelas ini pada tanggal " . date('d-m-Y') . ".</td></tr>";
}

echo "</table>";
echo "</body>";
echo "</html>";
?>