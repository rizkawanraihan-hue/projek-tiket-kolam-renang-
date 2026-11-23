<?php
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$tiket = mysqli_query($conn, "SELECT * FROM tb_tiket");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Customer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <h2>Kolam Renang</h2>
    <a href="customer.php" class="active">Beranda</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">

    <div class="header">
        <h1>Halaman Customer</h1>
        <p>
            Selamat datang,
            <?= htmlspecialchars($_SESSION['nama']); ?>
        </p>
    </div>

    <div class="cards">

        <?php while ($row = mysqli_fetch_assoc($tiket)) { ?>

        <div class="card">
            <h3><?= htmlspecialchars($row['kategori']); ?></h3>

            <p>
                Rp <?= number_format(
                    $row['harga'],
                    0,
                    ',',
                    '.'
                ); ?>
            </p>

            <p class="description">
                Harga tiket <?= htmlspecialchars($row['kategori']); ?>
            </p>
        </div>

        <?php } ?>

    </div>

</div>

</body>
</html>