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
switch ($aksi) {
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
		break;
}
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold m-0">Data Pengguna Sistem</h5>
    <a href="index.php?page=user&aksi=tambah" class="btn btn-primary btn-sm rounded-pill">
        <i class="bi bi-person-plus-fill me-1"></i> Tambah User
    </a>
</div>

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
// Potongan kode di bagian paling bawah gambar
// if (isset($stmt)) {
//     $stmt->close();
// }
// break;
?>


//FROM INPUTAN TAMBAH USER
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
// break; // (terpotong di bagian bawah gambar)