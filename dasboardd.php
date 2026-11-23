<?php
session_start();

// CEK LOGIN ADMIN
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// ================= EDIT TIKET =================
if (isset($_POST['edit_tiket'])) {
    $id = (int) ($_POST['id_tiket'] ?? 0);
    $kategori = $_POST['kategori'] ?? '';
    $harga = (int) ($_POST['harga'] ?? 0);

    if ($id > 0 && in_array($kategori, ['Umum', 'Membership']) && $harga > 0) {
        $update = mysqli_prepare($conn, 
            "UPDATE tb_tiket SET kategori = ?, harga = ? WHERE id_tiket = ?"
        );

        mysqli_stmt_bind_param($update, "sii", $kategori, $harga, $id);
        mysqli_stmt_execute($update);
        mysqli_stmt_close($update);

        header("Location: dashboard.php?pesan=" . urlencode("Tiket berhasil diubah!"));
        exit;
    }
}

// ================= HAPUS TIKET =================
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];

    if ($id > 0) {
        $cek = mysqli_prepare($conn,
            "SELECT COUNT(*) AS jumlah FROM tb_transaksi WHERE id_tiket = ?"
        );

        mysqli_stmt_bind_param($cek, "i", $id);
        mysqli_stmt_execute($cek);

        $hasil_cek = mysqli_stmt_get_result($cek);
        $data_cek = mysqli_fetch_assoc($hasil_cek);
        mysqli_stmt_close($cek);

        if ($data_cek['jumlah'] > 0) {
            header("Location: dashboard.php?pesan=" . urlencode("Tiket tidak dapat dihapus karena sudah digunakan dalam transaksi!"));
            exit;
        }

        $hapus = mysqli_prepare($conn,
            "DELETE FROM tb_tiket WHERE id_tiket = ?"
        );

        mysqli_stmt_bind_param($hapus, "i", $id);
        mysqli_stmt_execute($hapus);
        mysqli_stmt_close($hapus);

        header("Location: dashboard.php?pesan=" . urlencode("Tiket berhasil dihapus!"));
        exit;
    }
}

// ================= STATISTIK =================
$user = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM tb_user");
$data_user = mysqli_fetch_assoc($user);

$tiket = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM tb_tiket");
$data_tiket = mysqli_fetch_assoc($tiket);

$transaksi = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM tb_transaksi");
$data_transaksi = mysqli_fetch_assoc($transaksi);

$pendapatan = mysqli_query($conn, "SELECT SUM(total_harga) AS total FROM tb_transaksi");
$data_pendapatan = mysqli_fetch_assoc($pendapatan);

// ================= DATA TIKET =================
$data_tiket_list = mysqli_query($conn, "SELECT * FROM tb_tiket ORDER BY id_tiket ASC");

// ================= DATA TRANSAKSI =================
$data = mysqli_query($conn, "
    SELECT tb_transaksi.*, tb_user.nama, tb_tiket.kategori
    FROM tb_transaksi
    INNER JOIN tb_user ON tb_transaksi.id_user = tb_user.id_user
    INNER JOIN tb_tiket ON tb_transaksi.id_tiket = tb_tiket.id_tiket
    ORDER BY tb_transaksi.id_transaksi DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="sidebar">
    <h2>Kolam Renang</h2>
    <a href="dashboard.php" class="active">Dashboard</a>
    <a href="logout.php">Logout</a>
</div>

<div class="content">

    <div class="header">
        <h1>Dashboard</h1>
        <p>Selamat datang, <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></p>
    </div>

    <!-- STATISTIK -->
    <div class="cards">

        <div class="card">
            <h3>Jumlah User</h3>
            <p><?= $data_user['jumlah'] ?? 0; ?></p>
        </div>

        <div class="card">
            <h3>Jumlah Tiket</h3>
            <p><?= $data_tiket['jumlah'] ?? 0; ?></p>
        </div>

        <div class="card">
            <h3>Jumlah Transaksi</h3>
            <p><?= $data_transaksi['jumlah'] ?? 0; ?></p>
        </div>

        <div class="card">
            <h3>Total Pendapatan</h3>
            <p>
                Rp <?= number_format(
                    $data_pendapatan['total'] ?? 0,
                    0,
                    ',',
                    '.'
                ); ?>
            </p>
        </div>

    </div>

    <!-- PESAN -->
    <?php if (isset($_GET['pesan'])) { ?>
        <div class="pesan">
            <?= htmlspecialchars($_GET['pesan']); ?>
        </div>
    <?php } ?>

    <!-- DATA TIKET -->
    <div class="table-container">
        <h2>Data Tiket</h2>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $no = 1;
                if ($data_tiket_list) {
                    while ($row = mysqli_fetch_assoc($data_tiket_list)) {
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['kategori']); ?></td>
                    <td>
                        Rp <?= number_format($row['harga'], 0, ',', '.'); ?>
                    </td>
                    <td>
                        <button type="button"
                            class="btn-edit"
                            onclick="editTiket(
                                <?= (int)$row['id_tiket']; ?>,
                                '<?= htmlspecialchars($row['kategori'], ENT_QUOTES); ?>',
                                <?= (int)$row['harga']; ?>
                            )">
                            Edit
                        </button>

                        <a class="btn-hapus"
                           href="dashboard.php?hapus=<?= (int)$row['id_tiket']; ?>"
                           onclick="return confirm('Yakin ingin menghapus tiket ini?');">
                           Hapus
                        </a>
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

    <!-- FORM EDIT TIKET -->
    <div class="table-container edit-form" id="formEdit">
        <h2>Edit Data Tiket</h2>

        <form method="POST" action="dashboard.php">
            <input type="hidden" name="id_tiket" id="id_tiket">

            <label>Kategori Tiket</label>
            <select name="kategori" id="kategori" required>
                <option value="Umum">Umum</option>
                <option value="Membership">Membership</option>
            </select>

            <label>Harga Tiket</label>
            <input type="number" name="harga" id="harga" min="1" required>

            <button type="submit" name="edit_tiket" class="btn-simpan">
                Simpan Perubahan
            </button>

            <button type="button" class="btn-kembali" onclick="tutupEdit()">
                Batal
            </button>
        </form>
    </div>

    <!-- DATA TRANSAKSI -->
    <div class="table-container">
        <h2>Data Transaksi</h2>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Kategori</th>
                    <th>Jumlah</th>
                    <th>Total Harga</th>
                </tr>
            </thead>

            <tbody>
                <?php
                $no = 1;
                if ($data) {
                    while ($row = mysqli_fetch_assoc($data)) {
                ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['nama']); ?></td>
                    <td><?= htmlspecialchars($row['kategori']); ?></td>
                    <td><?= (int)$row['jml_tiket']; ?></td>
                    <td>
                        Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>
                    </td>
                </tr>
                <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function editTiket(id, kategori, harga) {
    document.getElementById('id_tiket').value = id;
    document.getElementById('kategori').value = kategori;
    document.getElementById('harga').value = harga;

    document.getElementById('formEdit').style.display = 'block';

    document.getElementById('formEdit').scrollIntoView({
        behavior: 'smooth'
    });
}

function tutupEdit() {
    document.getElementById('formEdit').style.display = 'none';
}
</script>

</body>
</html>