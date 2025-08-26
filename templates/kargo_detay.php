<?php
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../config/db.php';

$kargo_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$kargo_id) {
    header("Location: /erp/templates/kargo_listele.php");
    exit();
}

// Sayfa ilk yüklendiğinde gerekli verileri çek
$sql_kargo = "SELECT k.*, m.magaza_adi, p.ad_soyad AS personel_adi 
              FROM kargolar AS k
              JOIN magazalar AS m ON k.gonderen_magaza_id = m.id
              JOIN personel AS p ON k.gonderen_personel_id = p.id
              WHERE k.id = ?";
$stmt_kargo = $pdo->prepare($sql_kargo);
$stmt_kargo->execute([$kargo_id]);
$kargo = $stmt_kargo->fetch();

if (!$kargo) {
    header("Location: /erp/templates/kargo_listele.php");
    exit();
}

$stmt_urunler = $pdo->prepare("SELECT * FROM gonderilen_urunler WHERE kargo_id = ?");
$stmt_urunler->execute([$kargo_id]);
$urunler = $stmt_urunler->fetchAll();
?>

<div class="container-fluid">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark mb-0">Kargo Detayları: <?= htmlspecialchars($kargo['takip_kodu']); ?></h3>
        <a href="/erp/templates/kargo_listele.php" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Listeye Geri Dön</a>
    </div>

    <!-- Bildirimlerin gösterileceği alan -->
    <div id="detayBildirimAlani"></div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="text-primary fw-bold m-0">Gönderi Bilgileri</h6></div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><strong>Takip Kodu:</strong> <?= htmlspecialchars($kargo['takip_kodu']); ?></li>
                        <li class="list-group-item"><strong>Alıcı:</strong> <?= htmlspecialchars($kargo['alici']); ?></li>
                        <li class="list-group-item"><strong>Gönderen Mağaza:</strong> <?= htmlspecialchars($kargo['magaza_adi']); ?></li>
                        <li class="list-group-item"><strong>Gönderen Personel:</strong> <?= htmlspecialchars($kargo['personel_adi']); ?></li>
                        <li class="list-group-item"><strong>Mevcut Durum:</strong> 
                            <!-- Durum etiketine ID eklendi -->
                            <span id="mevcutDurumBadge" class="badge bg-success fs-6"><?= htmlspecialchars($kargo['kargo_durumu']); ?></span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="text-primary fw-bold m-0">Durumu Güncelle</h6></div>
                <div class="card-body">
                    <!-- Forma ID eklendi -->
                    <form id="durumGuncelleForm">
                        <input type="hidden" name="kargo_id" value="<?= $kargo_id; ?>">
                        <div class="input-group">
                            <select class="form-select" name="kargo_durumu">
                                <option value="Hazırlanıyor" <?= $kargo['kargo_durumu'] == 'Hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                                <option value="Yola Çıktı" <?= $kargo['kargo_durumu'] == 'Yola Çıktı' ? 'selected' : '' ?>>Yola Çıktı</option>
                                <option value="Dağıtımda" <?= $kargo['kargo_durumu'] == 'Dağıtımda' ? 'selected' : '' ?>>Dağıtımda</option>
                                <option value="Teslim Edildi" <?= $kargo['kargo_durumu'] == 'Teslim Edildi' ? 'selected' : '' ?>>Teslim Edildi</option>
                                <option value="İade" <?= $kargo['kargo_durumu'] == 'İade' ? 'selected' : '' ?>>İade</option>
                            </select>
                            <button type="submit" class="btn btn-primary">Güncelle</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="text-primary fw-bold m-0">Kargo İçeriğindeki Ürünler (Toplam: <?= count($urunler); ?>)</h6></div>
                <div class="card-body"><div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light"><tr><th>#</th><th>IMEI</th><th>Model</th></tr></thead>
                        <tbody>
                            <?php if (empty($urunler)): ?>
                                <tr><td colspan="3" class="text-center">Bu kargonun içinde ürün bulunmuyor.</td></tr>
                            <?php else: ?>
                                <?php foreach ($urunler as $index => $urun): ?>
                                <tr>
                                    <td><?= $index + 1; ?></td>
                                    <td><?= htmlspecialchars($urun['imei']); ?></td>
                                    <td><?= htmlspecialchars($urun['model']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div></div>
            </div>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/../includes/footer.php';
?>
