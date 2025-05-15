<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <?php
    session_start();
    require_once 'config.php';
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
        header("Location: index.php");
        exit;
    }
    ?>
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Dashboard Admin - <?php echo $_SESSION['nama']; ?></h2>
        <h3 class="text-xl font-bold mb-2">Manajemen Kelas</h3>
        <form action="update_kelas.php" method="POST" class="mb-4">
            <div class="mb-4">
                <label class="block text-gray-700">Nama Kelas</label>
                <input type="text" name="nama_kelas" class="w-full px-3 py-2 border rounded" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Tambah Kelas</button>
        </form>
        <table class="w-full border-collapse border">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">Nama Kelas</th>
                    <th class="border p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $kelas = $pdo->query("SELECT * FROM kelas")->fetchAll();
                foreach ($kelas as $k) {
                    echo "<tr>";
                    echo "<td class='border p-2'>{$k['nama_kelas']}</td>";
                    echo "<td class='border p-2'>
                        <a href='edit_kelas.php?id_kelas={$k['id_kelas']}' class='text-blue-500'>Edit</a> |
                        <a href='delete_kelas.php?id_kelas={$k['id_kelas']}' class='text-red-500' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a>
                    </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="logout.php" class="mt-4 inline-block bg-red-500 text-white py-2 px-4 rounded hover:bg-red-600">Logout</a>
    </div>
</body>
</html>