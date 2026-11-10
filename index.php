<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>App Penjualan Ritel</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body { 
            background-color: #c5dbf2; 
            padding-top: 65px; 
            padding-bottom: 25px; 
        }
        .offcanvas-body .list-group-item { 
            font-size: 1.05rem; 
            padding: 13px 20px; 
        }
    </style>
</head>
<body>

    <!-- NAVBAR ATAS -->
    <nav class="navbar navbar-dark bg-primary fixed-top shadow-sm">
        <div class="container-fluid">
            <!-- Tombol Hamburger untuk Offcanvas -->
            <button class="navbar-toggler border-0 focus-ring focus-ring-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuSamping">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <a class="navbar-brand me-auto ms-2 fw-bold" href="index.php">APK PENJUALAN</a>
            
            <!-- Info User Login -->
            <div class="text-white small d-flex align-items-center">
                <i class="bi bi-person-circle fs-5 me-2"></i>
                <span class="d-none d-sm-inline fw-semibold"><?= htmlspecialchars($_SESSION['nama']); ?></span>
                <span class="badge bg-light text-primary ms-2"><?= htmlspecialchars($_SESSION['role']); ?></span>
            </div>
        </div>
    </nav>

    <!-- OFFCANVAS MENU (Laci Navigasi Kiri) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="menuSamping" style="max-width: 280px;">
        <div class="offcanvas-header bg-primary text-white">
            <h5 class="offcanvas-title"><i class="bi bi-shop me-2"></i>Menu Navigasi</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <div class="list-group list-group-flush mt-2">
                <a href="index.php" class="list-group-item list-group-item-action border-0">
                    <i class="bi bi-speedometer2 me-3 text-primary"></i> Dashboard
                </a>
                
                <!-- Menu Data Pengguna HANYA muncul untuk Role Admin -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') { ?>
                    <a href="index.php?page=user" class="list-group-item list-group-item-action border-0">
                        <i class="bi bi-shield-lock me-3 text-danger"></i> Data Pengguna
                    </a>
                <?php } ?>

                <a href="index.php?page=produk" class="list-group-item list-group-item-action border-0">
                    <i class="bi bi-box-seam me-3 text-primary"></i> Data Produk
                </a>
                <a href="index.php?page=pelanggan" class="list-group-item list-group-item-action border-0">
                    <i class="bi bi-people me-3 text-primary"></i> Data Pelanggan
                </a>
                <a href="index.php?page=pesan" class="list-group-item list-group-item-action border-0">
                    <i class="bi bi-cart me-3 text-primary"></i> Transaksi Pesanan
                </a>
                <a href="index.php?page=faktur" class="list-group-item list-group-item-action border-0">
                    <i class="bi bi-receipt me-3 text-primary"></i> Faktur &amp; Kuitansi
                </a>
            </div>
            
            <!-- Tombol Logout di bagian paling bawah -->
            <div class="mt-auto p-3">
                <a href="logout.php" class="btn btn-outline-danger w-100 rounded-pill">
                    <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                </a>
            </div>
        </div>
    </div>

    <!-- KONTEN UTAMA DINAMIS -->
    <div class="container-fluid px-3">
        <?php
        // Logika Routing Dinamis
        $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

        if ($page == 'dashboard') {
            include 'dashboard.php';
        } else {
            // Sanitasi nama file untuk mencegah error/path traversal
            $page = basename($page); 
            $file = "modul/" . $page . "/index.php";
            
            if (file_exists($file)) {
                include $file;
            } else {
                // Pesan jika folder modul belum dibuat
                echo '<div class="alert alert-warning mt-4 shadow-sm border-0">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Modul <strong>' . htmlspecialchars($page) . '</strong> belum tersedia.
                      </div>';
            }
        }
        ?>
    </div>

    <!-- Bootstrap JS Bundle (Wajib ada agar menu Offcanvas bisa bergeser) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>