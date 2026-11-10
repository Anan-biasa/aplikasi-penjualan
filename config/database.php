<?php
// config/database.php

$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'penjualan';

// Inisialisasi koneksi dengan MySQLi secara Object Oriented
$koneksi = new mysqli($host, $user, $pass, $db);

// Error handling (tambahan agar sesuai tujuan praktikum: penanganan error)
if ($koneksi->connect_error) {
    die('koneksi gagal cihuy: ' . $koneksi->connect_error);
} else {
    echo 'koneksi berhasil cihuyyyyyyyyyyy';
}