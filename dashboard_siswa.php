<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa</title>
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
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
        header("Location: index.php");
        exit;
    }

    // Inisialisasi $_SESSION['user'] sebagai array jika belum ada
    if (!isset($_SESSION['user']) || !is_array($_SESSION['user'])) {
        $_SESSION['user'] = [];
    }

    $user_id = $_SESSION['user_id'];
    $nama = $_SESSION['nama'] ?? 'Siswa';

    // Debugging: Log sesi saat membuka dashboard
    $debug_session = "User ID: {$user_id}, Session: " . print_r($_SESSION, true) . "\n";
    file_put_contents('debug_dashboard.txt', $debug_session, FILE_APPEND);

    // Tampilkan pesan sukses atau error
    $message = '';
    if (isset($_GET['success'])) {
        $message = '<p class="success-message">' . htmlspecialchars(urldecode($_GET['success'])) . '</p>';
    } elseif (isset($_GET['error'])) {
        $message = '<p class="error-message">' . htmlspecialchars(urldecode($_GET['error'])) . '</p>';
    }
    ?>
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Dashboard Siswa - <?php echo htmlspecialchars($nama); ?></h2>
        
        <?php echo $message; ?>

        <?php if (empty($_SESSION['user']['kelas'])): ?>
            <form action="save_kelas.php" method="POST" class="mb-4">
                <div class="mb-4">
                    <label class="block text-gray-700">Pilih Kelas <span class="wajib">(wajib)</span></label>
                    <select name="kelas" class="w-full px-3 py-2 border rounded" required>
                        <option value="" disabled selected>Pilih kelas</option>
                        <?php
                        $kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas")->fetchAll();
                        foreach ($kelas_list as $k) {
                            echo "<option value='" . htmlspecialchars($k['nama_kelas']) . "'>" . htmlspecialchars($k['nama_kelas']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Simpan Kelas</button>
            </form>
        <?php else: ?>
            <p class="mb-4">Kelas Anda: <strong><?php echo htmlspecialchars($_SESSION['user']['kelas']); ?></strong> 
            <a href="save_kelas.php?reset=true" class="text-red-500 hover:text-red-700 ml-2">Ubah Kelas</a></p>
        <?php endif; ?>

        <h3 class="text-xl font-bold mb-2">Absensi Salat Dzuhur</h3>
        <form action="save_absensi.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label class="block text-gray-700">Gender</label>
                <select name="gender" id="gender" class="w-full px-3 py-2 border rounded" onchange="toggleAbsenOptions()" required>
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border rounded" onchange="toggleFields()" required>
                    <option value="hadir">Hadir</option>
                    <option value="tidak hadir alasan lain">Tidak Hadir karena Alasan Lain</option>
                    <option value="tidak hadir karena haid">Tidak Hadir karena Haid</option>
                </select>
            </div>
            <div id="materi_field" class="mb-4 hidden">
                <label class="block text-gray-700">Materi Keputrian</label>
                <textarea name="materi" class="w-full px-3 py-2 border rounded"></textarea>
            </div>
            <div id="bukti_field" class="mb-4 hidden">
                <label class="block text-gray-700">Bukti Foto</label>
                <input type="file" name="bukti_foto" class="w-full px-3 py-2 border rounded" accept="image/*">
            </div>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Kirim Absensi</button>
        </form>
        <a href="logout.php" class="mt-4 inline-block bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">Logout</a>
    </div>
    <script>
        function toggleAbsenOptions() {
            const gender = document.getElementById('gender').value;
            const statusSelect = document.getElementById('status');
            statusSelect.innerHTML = '';
            if (gender === 'Perempuan') {
                statusSelect.innerHTML = `
                    <option value="hadir">Hadir</option>
                    <option value="tidak hadir karena haid">Tidak Hadir karena Haid</option>
                    <option value="tidak hadir alasan lain">Tidak Hadir karena Alasan Lain</option>
                `;
            } else {
                statusSelect.innerHTML = `
                    <option value="hadir">Hadir</option>
                    <option value="tidak hadir alasan lain">Tidak Hadir karena Alasan Lain</option>
                `;
            }
            toggleFields();
        }

        function toggleFields() {
            const status = document.getElementById('status').value;
            const materiField = document.getElementById('materi_field');
            const buktiField = document.getElementById('bukti_field');
            materiField.classList.add('hidden');
            buktiField.classList.add('hidden');
            if (status === 'tidak hadir karena haid') {
                materiField.classList.remove('hidden');
            } else if (status === 'hadir' || status === 'tidak hadir alasan lain') {
                buktiField.classList.remove('hidden');
            }
        }

        // Initialize form on page load
        toggleAbsenOptions();
    </script>
</body>
</html>