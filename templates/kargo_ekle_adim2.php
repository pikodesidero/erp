<?php
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../config/db.php';

$kargo_id = filter_input(INPUT_GET, 'kargo_id', FILTER_VALIDATE_INT);
if (!$kargo_id) {
    header("Location: /erp/index.php");
    exit();
}

// Sayfa ilk yüklendiğinde mevcut bilgileri çek
$stmt_kargo = $pdo->prepare("SELECT * FROM kargolar WHERE id = ?");
$stmt_kargo->execute([$kargo_id]);
$kargo = $stmt_kargo->fetch();

$stmt_urunler = $pdo->prepare("SELECT * FROM gonderilen_urunler WHERE kargo_id = ?");
$stmt_urunler->execute([$kargo_id]);
$urunler = $stmt_urunler->fetchAll();

if (!$kargo) {
    header("Location: /erp/index.php");
    exit();
}
?>

<div class="container-fluid">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark mb-0">Kargoya Ürün Ekle (Modern)</h3>
        <a href="/erp/index.php" class="btn btn-success"><i class="bi bi-check-circle"></i> Gönderiyi Tamamla</a>
    </div>

    <div class="card shadow mb-4"><div class="card-body">
        <h5 class="card-title">Kargo Bilgileri</h5>
        <div class="row">
            <div class="col-md-4"><strong>Takip Kodu:</strong> <?= htmlspecialchars($kargo['takip_kodu']); ?></div>
            <div class="col-md-4"><strong>Alıcı:</strong> <?= htmlspecialchars($kargo['alici']); ?></div>
            <div class="col-md-4"><strong>Alıcı Şehir:</strong> <?= htmlspecialchars($kargo['alici_sehir']); ?></div>
        </div>
    </div></div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="text-primary fw-bold m-0">Yeni Ürün Ekle</h6></div>
                <div class="card-body">
                    <!-- Bildirimlerin gösterileceği alan -->
                    <div id="bildirimAlani"></div>
                    <!-- Form ID'si eklendi ve action kaldırıldı -->
                    <form id="urunEkleForm">
                        <input type="hidden" name="kargo_id" value="<?= $kargo_id; ?>">
                        <div class="mb-3">
                            <label for="imei" class="form-label">IMEI Numarası</label>
                            <input type="text" class="form-control" id="imei" name="imei" required maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label for="model" class="form-label">Telefon Modeli</label>
                            <input type="text" class="form-control" id="model" name="model" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Ekle</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="text-primary fw-bold m-0">Kargo İçeriği (Toplam: <span id="urunSayac"><?= count($urunler); ?></span> ürün)</h6>
                </div>
                <div class="card-body"><div class="table-responsive">
                    <table class="table table-bordered">
                        <thead><tr><th>IMEI</th><th>Model</th></tr></thead>
                        <!-- tbody ID'si eklendi -->
                        <tbody id="urunListesiBody">
                            <?php if (empty($urunler)): ?>
                                <tr class="no-product-row"><td colspan="2" class="text-center">Bu kargoya henüz ürün eklenmedi.</td></tr>
                            <?php else: ?>
                                <?php foreach ($urunler as $urun): ?>
                                <tr>
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
