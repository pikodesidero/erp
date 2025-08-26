<?php
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../config/db.php';

// Filtre dropdown'ları için verileri çek
$personeller = $pdo->query("SELECT id, ad_soyad FROM personel WHERE aktif_mi = 1 ORDER BY ad_soyad")->fetchAll();
$magazalar = $pdo->query("SELECT id, magaza_adi FROM magazalar ORDER BY magaza_adi")->fetchAll();
$durumlar = ['Hazırlanıyor', 'Yola Çıktı', 'Dağıtımda', 'Teslim Edildi', 'İade'];
?>

<div class="container-fluid">
    <h3 class="text-dark mb-4">Kargo Listesi (Modern)</h3>

    <!-- Filtreleme Formu -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="text-primary fw-bold m-0">Filtrele</h6>
        </div>
        <div class="card-body">
            <form id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3"><input type="text" name="alici" class="form-control" placeholder="Alıcı Adı..."></div>
                    <div class="col-md-3"><input type="text" name="sehir" class="form-control" placeholder="Alıcı Şehir..."></div>
                    <div class="col-md-3"><select name="durum" class="form-select"><option value="">Tüm Durumlar</option><?php foreach($durumlar as $d): echo "<option value='$d'>$d</option>"; endforeach; ?></select></div>
                    <?php if ($_SESSION['rol'] === 'Admin'): ?>
                    <div class="col-md-3"><select name="personel_id" class="form-select"><option value="">Tüm Personeller</option><?php foreach($personeller as $p): echo "<option value='{$p['id']}'>".htmlspecialchars($p['ad_soyad'])."</option>"; endforeach; ?></select></div>
                    <div class="col-md-3"><select name="magaza_id" class="form-select"><option value="">Tüm Mağazalar</option><?php foreach($magazalar as $m): echo "<option value='{$m['id']}'>".htmlspecialchars($m['magaza_adi'])."</option>"; endforeach; ?></select></div>
                    <?php endif; ?>
                    <div class="col-md-3"><label class="form-label-sm">Başlangıç Tarihi</label><input type="date" name="tarih_bas" class="form-control"></div>
                    <div class="col-md-3"><label class="form-label-sm">Bitiş Tarihi</label><input type="date" name="tarih_son" class="form-control"></div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel-fill"></i> Filtrele</button>
                    <button type="reset" class="btn btn-secondary"><i class="bi bi-x-lg"></i> Temizle</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kargo Listesi Tablosu -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Takip Kodu</th><th>Alıcı</th><th>Gönderen Mağaza</th><th>Gönderen Personel</th><th>Durum</th><th>Tarih</th><th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody id="kargoTableBody">
                        <!-- Veriler JavaScript ile buraya yüklenecek -->
                    </tbody>
                </table>
            </div>
            <!-- Sayfalama linkleri JavaScript ile buraya eklenecek -->
            <div id="paginationContainer" class="mt-3"></div>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/../includes/footer.php';
?>
