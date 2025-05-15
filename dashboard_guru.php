<?php
session_start();
require_once 'config.php';
date_default_timezone_set('Asia/Jakarta');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'guru') {
    header("Location: index.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT nama, role FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$guru = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$guru) {
    echo "<div class='p-4 bg-red-100 border-l-4 border-red-500 text-red-700'>Error: Data guru tidak ditemukan.</div>";
    exit;
}

$kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
if (empty($kelas)) {
    echo "<div class='p-4 bg-red-100 border-l-4 border-red-500 text-red-700'>Error: Tidak ada data kelas.</div>";
    exit;
}

// Otomatisasi filter kelas
if (!empty($_GET['id_kelas'])) {
    $_SESSION['id_kelas'] = (int)$_GET['id_kelas'];
} elseif (!isset($_SESSION['id_kelas']) && !empty($kelas)) {
    $_SESSION['id_kelas'] = $kelas[0]['id_kelas']; // Set default ke kelas pertama
}
$id_kelas = $_SESSION['id_kelas'] ?? null;

if (!$id_kelas || !in_array($id_kelas, array_column($kelas, 'id_kelas'))) {
    echo "<div class='p-4 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700'>";
    echo "Silakan pilih kelas:<br>";
    echo "<form method='GET'>";
    echo "<select name='id_kelas' onchange='this.form.submit()' class='w-full px-3 py-2 border rounded'>";
    echo "<option value='' disabled " . ($id_kelas === null ? 'selected' : '') . ">Pilih Kelas</option>";
    foreach ($kelas as $k) {
        echo "<option value='{$k['id_kelas']}' " . ($id_kelas == $k['id_kelas'] ? 'selected' : '') . ">" . htmlspecialchars($k['nama_kelas']) . "</option>";
    }
    echo "</select>";
    echo "</form>";
    echo "</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>.thumbnail{max-width:100px;max-height:100px;object-fit:cover;}</style>
</head>
<body class="bg-gray-100">
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Dashboard Guru - <?php echo htmlspecialchars($guru['nama']); ?></h2>
    <h3 class="text-xl font-bold mb-2">Absensi Siswa</h3>
    <form method="GET" class="mb-4">
        <label class="block text-gray-700">Pilih Kelas</label>
        <select name="id_kelas" onchange="this.form.submit()" class="w-full px-3 py-2 border rounded">
            <?php foreach ($kelas as $k): ?>
                <option value="<?= $k['id_kelas'] ?>" <?= $id_kelas == $k['id_kelas'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama_kelas']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
    <table class="w-full border-collapse border">
        <thead><tr class="bg-gray-200"><th class="border p-2">Nama</th><th class="border p-2">Kelas</th><th class="border p-2">Tanggal</th><th class="border p-2">Waktu</th><th class="border p-2">Gender</th><th class="border p-2">Status</th><th class="border p-2">Materi</th><th class="border p-2">Bukti Foto</th></tr></thead>
        <tbody>
        <?php
        try {
            $stmt = $pdo->prepare("SELECT DISTINCT a.*, u.nama, k.nama_kelas FROM absensi a INNER JOIN users u ON a.id_user = u.id INNER JOIN kelas k ON a.id_kelas = k.id_kelas WHERE a.id_kelas = ? ORDER BY a.tanggal DESC, a.waktu_absen DESC");
            $stmt->bindParam(1, $id_kelas, PDO::PARAM_INT);
            $stmt->execute();
            $absensi = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($absensi)) {
                echo "<tr><td colspan='8' class='border p-2 text-center'>Tidak ada data absensi.</td></tr>";
            } else {
                foreach ($absensi as $a) {
                    echo "<tr><td class='border p-2'>" . htmlspecialchars($a['nama']) . "</td><td class='border p-2'>" . htmlspecialchars($a['nama_kelas']) . "</td><td class='border p-2'>" . htmlspecialchars($a['tanggal']) . "</td><td class='border p-2'>" . htmlspecialchars($a['waktu_absen']) . "</td><td class='border p-2'>" . htmlspecialchars($a['gender']) . "</td><td class='border p-2'>" . htmlspecialchars($a['status']) . "</td><td class='border p-2'>" . ($a['materi'] ?: '-') . "</td><td class='border p-2 text-center'>";
                    if ($a['bukti_foto'] && file_exists($a['bukti_foto'])) echo "<img src='{$a['bukti_foto']}' alt='Bukti' class='thumbnail mx-auto'>";
                    else echo "Tidak ada foto";
                    echo "</td></tr>";
                }
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='8' class='border p-2 text-center text-red-700'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
        ?>
        </tbody>
    </table>
    <a href="export_word.php?id_kelas=<?= htmlspecialchars($id_kelas) ?>" class="mt-4 inline-block bg-green-500 text-white py-2 px-4 rounded hover:bg-green-600">Cetak Rekap</a>
    <a href="logout.php" class="mt-4 inline-block bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">Logout</a>
</div>
</body>
</html>