<?php

if (!isset($_SESSION['id_user'])) {
	exit('Silakan login terlebih dahulu.');
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
	echo '<div class="alert alert-danger shadow-sm mt-4">
		<h5><i class="bi bi-shield-lock-fill me-2"></i>Akses Ditolak!</h5>
		<p class="mb-0">Hanya Admin yang dapat mengelola data pengguna.</p>
	</div>';
	return;
}

$aksi = isset($_GET['aksi']) ? $_GET['aksi'] : 'view';
$pesan = ''; // Inisialisasi variabel pesan agar tidak error saat pertama kali dimuat

switch ($aksi) {

    //CASE UNTUK MENAMPILKAN DATA USER
    case 'view':
        $keyword = isset($_GET['cari']) ? trim($_GET['cari']) : '';

        if ($keyword !== '') {
            $param = '%' . $keyword . '%';
            $stmt = $koneksi->prepare(
                ' SELECT id_user, nama, username, role
                  FROM user
                  WHERE nama LIKE ? OR username LIKE ?
                  ORDER BY id_user DESC'
            );
            $stmt->bind_param('ss', $param, $param);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $koneksi->query(
                ' SELECT id_user, nama, username, role
                  FROM user
                  ORDER BY id_user DESC'
            );
        }
        ?>
        
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0">Data Pengguna Sistem</h5>
            <a href="index.php?page=user&aksi=tambah" class="btn btn-primary btn-sm rounded-pill">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah User
            </a>
        </div>

        // Form Pencarian
        <form action="index.php" method="GET" class="mb-4">
            <input type="hidden" name="page" value="user">
            <div class="input-group shadow-sm">
                <input type="text" name="cari" class="form-control" placeholder="Cari nama atau username.." value="<?= htmlspecialchars($keyword); ?>">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>

        <div class="row g-3">
            <?php if ($result && $result->num_rows > 0) { ?>
                <?php while ($row = $result->fetch_assoc()) { 
                    $warna = ($row['role'] === 'Admin') ? 'bg-danger' : 'bg-primary';
                ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-light rounded-circle d-flex justify-content-center align-items-center me-3 border" style="width: 45px; height: 45px;">
                                        <i class="bi bi-person-badge fs-5"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-0"><?= htmlspecialchars($row['nama']); ?></h6>
                                        <span class="badge <?= $warna; ?>"><?= htmlspecialchars($row['role']); ?></span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-at me-1"></i><?= htmlspecialchars($row['username']); ?>
                                </p>
                                <div class="d-flex gap-2">
                                    <a class="btn btn-outline-warning btn-sm rounded-pill w-50" href="index.php?page=user&aksi=edit&id=<?= (int)$row['id_user']; ?>">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <a class="btn btn-outline-danger btn-sm rounded-pill w-50" href="index.php?page=user&aksi=hapus&id=<?= (int)$row['id_user']; ?>" onclick="return confirm('Yakin ingin menghapus pengguna ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="col-12">
                    <div class="alert alert-light border text-center">
                        Data pengguna tidak ditemukan.
                    </div>
                </div>
            <?php } ?>
        </div>
        <?php
        if (isset($stmt)) {
            $stmt->close();
        }
        break;

        //CASE UNTUK TAMBAH USER
    case 'tambah':
        // Proses Simpan Data
        if (isset($_POST['simpan'])) {
            $nama = trim($_POST['nama']);
            $username = trim($_POST['username']);
            $password_input = $_POST['password'];
            $role = $_POST['role'];

            // Pastikan role sesuai pilihan yang diizinkan database.
            if (!in_array($role, ['Admin', 'Kasir'], true)) {
                $pesan = 'Role yang dipilih tidak valid.';
            } elseif ($nama === '' || $username === '' || $password_input === '') {
                $pesan = 'Semua kolom wajib diisi.';
            } else {
                // Periksa username sudah digunakan
                $cek = $koneksi->prepare("SELECT id_user FROM user WHERE username = ?");
                $cek->bind_param("s", $username);
                $cek->execute();
                $sudah_ada = $cek->get_result()->num_rows > 0;
                $cek->close();

                if ($sudah_ada) {
                    $pesan = 'Username sudah digunakan. Silakan pilih username lain.';
                } else {
                    // Mengikuti skema autentikasi Modul 02.
                    $password = md5($password_input);

                    $stmt = $koneksi->prepare(
                        "INSERT INTO user (nama, username, password, role) VALUES (?, ?, ?, ?)"
                    );
                    $stmt->bind_param("ssss", $nama, $username, $password, $role);

                    if ($stmt->execute()) {
                        $stmt->close();
                        echo "<script>
                            alert('Pengguna berhasil ditambahkan!');
                            window.location='index.php?page=user';
                        </script>";
                        exit;
                    } else {
                        $pesan = 'Gagal menyimpan data pengguna.';
                    }
                }
            }
            if (isset($stmt)) $stmt->close();
        }
        ?>

        <!-- Tampilan Form Tambah Pengguna -->
        <h5 class="fw-bold mb-3">Tambah Pengguna Baru</h5>
        <?php if ($pesan !== '') { ?>
            <div class="alert alert-warning"><?= htmlspecialchars($pesan); ?></div>
        <?php } ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="index.php?page=user&aksi=tambah" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Hak Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="Admin">Admin</option>
                            <option value="Kasir">Kasir</option>
                        </select>
                    </div>
                    <button type="submit" name="simpan" class="btn btn-primary w-100 mb-2">Simpan Pengguna</button>
                    <a href="index.php?page=user" class="btn btn-light w-100">Batal</a>
                </form>
            </div>
        </div>
        
        <?php
        break;

        //CASE UNTUK EDIT USER
    case 'edit':
        
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        
        if (!$id || $id < 1) {
            echo '<div class="alert alert-warning">ID pengguna tidak valid.</div>';
            break;
        }

        // Ambil data pengguna dari database berdasarkan ID
        $stmt_get = $koneksi->prepare("SELECT id_user, nama, username, role FROM user WHERE id_user = ?");
        $stmt_get->bind_param("i", $id);
        $stmt_get->execute();
        $data = $stmt_get->get_result()->fetch_assoc();
        $stmt_get->close();

        
        if (!$data) {
            echo '<div class="alert alert-warning">Data pengguna tidak ditemukan.</div>';
            break;
        }
        
                if (isset($_POST['update'])) {
            $nama = trim($_POST['nama']);
            $username = trim($_POST['username']);
            $password_input = trim($_POST['password']);
            $role = $_POST['role'];

            if (!in_array($role, ['Admin', 'Kasir'], true)) {
                $pesan = 'Role yang dipilih tidak valid.';
            } elseif ($nama === '' || $username === '') {
                $pesan = 'Nama dan username wajib diisi.';
            } else {
                // Username boleh tetap sama, tetapi tidak boleh dipakai oleh pengguna lain.
                $cek = $koneksi->prepare("SELECT id_user FROM user WHERE username = ? AND id_user <> ?");
                $cek->bind_param("si", $username, $id);
                $cek->execute();
                $username_dipakai = $cek->get_result()->num_rows > 0;
                $cek->close();

                if ($username_dipakai) {
                    $pesan = 'Username sudah digunakan oleh pengguna lain.';
                } else {
                    if ($password_input !== '') {
                        $password = md5($password_input);
                        $stmt = $koneksi->prepare(
                            "UPDATE user SET nama = ?, username = ?, password = ?, role = ? WHERE id_user = ?"
                        );
                        $stmt->bind_param("ssssi", $nama, $username, $password, $role, $id);
                    } else {
                        // Password kosong: jangan ubah kolom password.
                        $stmt = $koneksi->prepare(
                            "UPDATE user SET nama = ?, username = ?, role = ? WHERE id_user = ?"
                        );
                        $stmt->bind_param("sssi", $nama, $username, $role, $id);
                    }

                    if ($stmt->execute()) {
                        $stmt->close();
                        echo "<script>
                            alert('Data pengguna berhasil diperbarui!');
                            window.location='index.php?page=user';
                        </script>";
                        exit;
                    } else {
                        $pesan = 'Gagal memperbarui data pengguna.';
                    }
                }
            }
            if (isset($stmt)) $stmt->close();
            
            // Isi form dengan data yang baru dikirim jika validasi gagal.
            $data['nama'] = $nama;
            $data['username'] = $username;
            $data['role'] = $role;
        }
        ?>


        <h5 class="fw-bold mb-3">Edit Pengguna</h5>
        <?php if ($pesan !== '') { ?>
            <div class="alert alert-warning"><?= htmlspecialchars($pesan); ?></div>
        <?php } ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="index.php?page=user&aksi=edit&id=<?= (int)$id; ?>" method="POST">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($data['nama']); ?>" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($data['username']); ?>" maxlength="50" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak ingin mengubah password">
                        <div class="form-text">Password lama tetap digunakan jika kolom ini kosong.</div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Hak Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="Admin" <?= $data['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                            <option value="Kasir" <?= $data['role'] === 'Kasir' ? 'selected' : ''; ?>>Kasir</option>
                        </select>
                    </div>
                    <button type="submit" name="update" class="btn btn-success w-100 mb-2">Update Pengguna</button>
                    <a href="index.php?page=user" class="btn btn-light w-100">Batal</a>
                </form>
            </div>
        </div>
        
        <?php
        break;

        //CASE UNTUK HAPUS USER
    case 'hapus': 
        // Ambil ID dari URL dan pastikan itu angka (integer)
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        
        if (!$id || $id < 1) {
            echo "<script>
                alert('ID pengguna tidak valid.');
                window.location='index.php?page=user';
            </script>";
            exit;
        }

        // Self-deletion guard: Admin tidak boleh menghapus akun sendiri.
        if ($id == $_SESSION['id_user']) {
            echo "<script>
                alert('Gagal! Anda tidak dapat menghapus akun yang sedang digunakan.');
                window.location='index.php?page=user';
            </script>";
            exit;
        }

        $stmt = $koneksi->prepare("DELETE FROM user WHERE id_user = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $pesan_hapus = 'Pengguna berhasil dihapus.';
        } else {
            $pesan_hapus = 'Pengguna gagal dihapus atau data tidak ditemukan.';
        }
        $stmt->close();

        echo "<script>
            alert(" . json_encode($pesan_hapus) . ");
            window.location='index.php?page=user';
        </script>";
        exit;

    default:
        echo '<div class="alert alert-warning">
            Aksi tidak dikenali. Silakan kembali ke daftar pengguna.
        </div>';
        break;
    
} 
?>