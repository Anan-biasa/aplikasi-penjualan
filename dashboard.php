<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm border-0 border-start border-primary border-4">
            <div class="card-body p-4">
                <h4 class="mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']); ?>!</h4>
                <p class="text-muted mb-0">
                    saat ini anda login sebagai 
                    <strong><?= htmlspecialchars($_SESSION['role']); ?></strong>.
                </p>
            </div>
        </div>
    </div>
</div>