<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kelas</title>
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
    $id_kelas = $_GET['id_kelas'];
    $stmt = $pdo->prepare("SELECT * FROM kelas WHERE id_kelas = ?");
    $stmt->execute([$id_kelas]);
    $kelas = $stmt->fetch();
    ?>
    <div class="container mx-auto p-4">
        <h2 class="text-2xl font-bold mb-4">Edit Kelas</h2>
        <form action="update_kelas.php" method="POST">
            <input type="hidden" name="id_kelas" value="<?php echo $kelas['id_kelas']; ?>">
            <div class="mb-4">
                <label class="block text-gray-700">Nama Kelas</label>
                <input type="text" name="nama_kelas" value="<?php echo $kelas['nama_kelas']; ?>" class="w-full px-3 py-2 border rounded" required>
            </div>
            <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Update Kelas</button>
        </form>
        <a href="dashboard_admin.php" class="mt-4 inline-block bg-gray-500 text-white py-2 px-4 rounded hover:bg-gray-600">Kembali</a>
    </div>
</body>
</html>