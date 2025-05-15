<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kelas</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .success-message { color: green; font-weight: bold; }
        .error-message { color: red; font-weight: bold; }
    </style>
</head>
<body class="bg-gray-100">
    <?php
    session_start();
    require_once 'config.php';
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        header("Location: index.php");
        exit;
    }

    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_kelas = trim($_POST['nama_kelas'] ?? '');

        // Validasi input
        if (empty($nama_kelas)) {
            $message = '<p class="error-message">Nama kelas tidak boleh kosong.</p>';
        } else {
            // Cek apakah kelas sudah ada
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM kelas WHERE nama_kelas = ?");
            $stmt->execute([$nama_kelas]);
            if ($stmt->fetchColumn() > 0) {
                $message = '<p class="error-message">Kelas sudah ada.</p>';
            } else {
                // Simpan kelas baru
                $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas) VALUES (?)");
                try {
                    $stmt->execute([$nama_kelas]);
                    header("Location: dashboard_admin.php?success=Kelas '$nama_kelas' berhasil ditambahkan pada " . date('d-m-Y H:i:s'));
                    exit;
                } catch (PDOException $e) {
                    $message = '<p class="error-message">Terjadi kesalahan: ' . htmlspecialchars($e->getMessage()) . '</p>';
                }
            }
        }
    }
    ?>
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Tambah Kelas</h2>
        <?php echo $message; ?>
        <form action="add_kelas.php" method="POST" class="mb-4">
            <div class="mb-4">
                <label class="block text-gray-700">Nama Kelas</label>
                <input type="text" name="nama_kelas" class="w-full px-3 py-2 border rounded" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Tambah Kelas</button>
        </form>
        <a href="dashboard_admin.php" class="mt-4 inline-block bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">Kembali</a>
    </div>
</body>
</html>