<?php
// header.php zaten oturum kontrolü yapıyor ve veritabanını dahil ediyor.
require __DIR__ . '/includes/header.php';
require __DIR__ . '/config/db.php'; // Veritabanı bağlantısı

// Örnek dashboard verileri çekme (gerçek verilerle değiştirilecek)
$toplam_kargo = $pdo->query("SELECT count(id) from kargolar")->fetchColumn();
$bekleyen_kargo = $pdo->query("SELECT count(id) from kargolar WHERE kargo_durumu = 'Hazırlanıyor'")->fetchColumn();
$teslim_edilen = $pdo->query("SELECT count(id) from kargolar WHERE kargo_durumu = 'Teslim Edildi'")->fetchColumn();

// Son 5 işlemi çekme
$son_islemler = $pdo->query("SELECT * FROM islem_loglari ORDER BY tarih DESC LIMIT 5")->fetchAll();
?>

<div class="container-fluid">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark mb-0">Dashboard</h3>
    </div>

    <!-- İstatistik Kartları -->
    <div class="row">
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-start-primary py-2">
                <div class="card-body">
                    <div class="row align-items-center no-gutters">
                        <div class="col me-2">
                            <div class="text-uppercase text-primary fw-bold text-xs mb-1"><span>Toplam Gönderi</span></div>
                            <div class="text-dark fw-bold h5 mb-0"><span><?= $toplam_kargo; ?></span></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-box-seam card-icon text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-start-warning py-2">
                <div class="card-body">
                    <div class="row align-items-center no-gutters">
                        <div class="col me-2">
                            <div class="text-uppercase text-warning fw-bold text-xs mb-1"><span>Hazırlanıyor</span></div>
                            <div class="text-dark fw-bold h5 mb-0"><span><?= $bekleyen_kargo; ?></span></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-clock-history card-icon text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-start-success py-2">
                <div class="card-body">
                    <div class="row align-items-center no-gutters">
                        <div class="col me-2">
                            <div class="text-uppercase text-success fw-bold text-xs mb-1"><span>Teslim Edilen</span></div>
                            <div class="text-dark fw-bold h5 mb-0"><span><?= $teslim_edilen; ?></span></div>
                        </div>
                        <div class="col-auto"><i class="bi bi-check-circle-fill card-icon text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Son İşlemler Tablosu -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="text-primary fw-bold m-0">Son Sistem Hareketleri</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>İşlem Tipi</th>
                            <th>Açıklama</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($son_islemler as $log): ?>
                        <tr>
                            <td><?= date('d.m.Y H:i', strtotime($log['tarih'])); ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($log['islem_tipi']); ?></span></td>
                            <td><?= htmlspecialchars($log['aciklama']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/includes/footer.php';
?>
