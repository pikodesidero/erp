<?php
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../config/db.php';

$arama_terimi = trim($_GET['q'] ?? '');
$sonuclar_takip_kodu = [];
$sonuclar_imei = [];

if (!empty($arama_terimi)) {
    // 1. Takip Koduna Göre Arama
    $sql_takip = "SELECT id FROM kargolar WHERE takip_kodu = ?";
    $stmt_takip = $pdo->prepare($sql_takip);
    $stmt_takip->execute([$arama_terimi]);
    $kargo_bulundu = $stmt_takip->fetch();

    // Eğer doğrudan takip kodu bulunursa, direkt o kargonun detayına yönlendir
    if ($kargo_bulundu) {
        header("Location: /erp/templates/kargo_detay.php?id=" . $kargo_bulundu['id']);
        exit();
    }

    // 2. IMEI Numarasına Göre Arama
    $sql_imei = "SELECT 
                    u.imei, u.model,
                    k.id as kargo_id, k.takip_kodu, k.alici, k.kargo_durumu
                 FROM gonderilen_urunler AS u
                 JOIN kargolar AS k ON u.kargo_id = k.id
                 WHERE u.imei LIKE ?";
    $stmt_imei = $pdo->prepare($sql_imei);
    $stmt_imei->execute(["%$arama_terimi%"]);
    $sonuclar_imei = $stmt_imei->fetchAll();
}
?>

<div class="container-fluid">
    <h3 class="text-dark mb-4">Arama Sonuçları: "<?= htmlspecialchars($arama_terimi); ?>"</h3>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="text-primary fw-bold m-0">Bulunan Sonuçlar</h6>
        </div>
        <div class="card-body">
            <?php if (empty($arama_terimi)): ?>
                <div class="alert alert-warning">Lütfen bir arama terimi girin.</div>
            <?php elseif (empty($sonuclar_imei)): ?>
                <div class="alert alert-info">Aradığınız kriterlere uygun sonuç bulunamadı.</div>
            <?php else: ?>
                <p>Aşağıda aramanızla eşleşen IMEI numaralarını içeren kargolar listelenmiştir.</p>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Bulunan IMEI</th>
                                <th>Telefon Modeli</th>
                                <th>İlgili Kargo Takip Kodu</th>
                                <th>Alıcı</th>
                                <th>Kargo Durumu</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sonuclar_imei as $sonuc): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($sonuc['imei']); ?></strong></td>
                                <td><?= htmlspecialchars($sonuc['model']); ?></td>
                                <td><?= htmlspecialchars($sonuc['takip_kodu']); ?></td>
                                <td><?= htmlspecialchars($sonuc['alici']); ?></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars($sonuc['kargo_durumu']); ?></span></td>
                                <td>
                                    <a href="/erp/templates/kargo_detay.php?id=<?= $sonuc['kargo_id']; ?>" class="btn btn-sm btn-info" title="Kargo Detayını Gör">
                                        <i class="bi bi-eye"></i> Detay
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/../includes/footer.php';
?>
