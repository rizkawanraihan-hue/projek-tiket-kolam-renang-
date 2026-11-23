<?php
session_start();
include 'koneksi.php';

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $query = mysqli_prepare($conn, 
        "SELECT * FROM tb_user WHERE username = ?");

    mysqli_stmt_bind_param($query, "s", $username);
    mysqli_stmt_execute($query);

    $result = mysqli_stmt_get_result($query);
    $user = mysqli_fetch_assoc($result);

    if ($user && $password === $user['pass']) {
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: ./dasboardd.php");
            exit;
        } else {
            header("Location: ./customer.php");
            exit;
        }
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Kolam Renang</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">

<div class="login-container">
    <h2>Kolam Renang</h2>
    <p>Silakan login untuk melanjutkan</p>

    <?php if ($error != "") { ?>
        <div class="error">
            <?= htmlspecialchars($error); ?>
        </div>
    <?php } ?>

    <form method="POST">
        <label>Username</label>
        <input type="text" name="username" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>
</div>

</body>
</html>