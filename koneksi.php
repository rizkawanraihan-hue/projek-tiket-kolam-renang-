<?php
$host     = "127.0.0.1";
$username = "root";
$password = "root";
$database = "db_kolamrenang";
$port     = 3306;             

$conn = mysqli_connect($host, $username, $password, $database, 3306);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>