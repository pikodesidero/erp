<?php
require __DIR__ . '/../includes/header.php';
require __DIR__ . '/../config/db.php';

// Sadece adminlerin bu sayfaya erişebilmesini sağla
if ($_SESSION['rol'] !== 'Admin') {
    header("Location: /erp/index.php");
    exit();
}

// Rapor 1: Personel Performansı (Toplam gönderdikleri kargo sayısı)
$sql_personel = "SELECT 
                    p.ad_soyad, 
                    COUNT(k.id) AS toplam_kargo
                 FROM personel AS p
                 LEFT JOIN kargolar AS k ON p.id = k.gonderen_personel_id
                 GROUP BY p.id
                 ORDER BY toplam_kargo DESC";
$personel_performans = $pdo->query($sql_personel)->fetchAll();


// Rapor 2: Mağaza Performansı (Toplam gönderilen kargo sayısı)
$sql_magaza = "SELECT 
                   m.magaza_adi, 
                   COUNT(k.id) AS toplam_kargo
               FROM magazalar AS m
               LEFT JOIN kargolar AS k ON m.id = k.gonderen_magaza_id
               GROUP BY m.id
               ORDER BY toplam_kargo DESC";
$magaza_performans = $pdo->query($sql_magaza)->fetchAll();


// Rapor 3: Kargo Durumlarına Göre Dağılım
$sql_durum = "SELECT 
                  kargo_durumu, 
                  COUNT(id) AS sayi
              FROM kargolar
              GROUP BY kargo_durumu";
$kargo_durumlari = $pdo->query($sql_durum)->fetchAll(PDO::FETCH_KEY_PAIR);

?>

<div class="container-fluid">
    <h3 class="text-dark mb-4">Raporlar ve İstatistikler</h3>

    <!-- Kargo Durum Kartları -->
    <div class="row">
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-start-primary py-2">
                <div class="card-body">
                    <div class="text-uppercase text-primary fw-bold mb-1">Hazırlanıyor</div>
                    <div class="text-dark fw-bold h5 mb-0"><?= $kargo_durumlari['Hazırlanıyor'] ?? 0; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-start-info py-2">
                <div class="card-body">
                    <div class="text-uppercase text-info fw-bold mb-1">Yola Çıktı</div>
                    <div class="text-dark fw-bold h5 mb-0"><?= $kargo_durumlari['Yola Çıktı'] ?? 0; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-start-warning py-2">
                <div class="card-body">
                    <div class="text-uppercase text-warning fw-bold mb-1">Dağıtımda</div>
                    <div class="text-dark fw-bold h5 mb-0"><?= $kargo_durumlari['Dağıtımda'] ?? 0; ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card shadow border-start-success py-2">
                <div class="card-body">
                    <div class="text-uppercase text-success fw-bold mb-1">Teslim Edildi</div>
                    <div class="text-dark fw-bold h5 mb-0"><?= $kargo_durumlari['Teslim Edildi'] ?? 0; ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performans Tabloları -->
    <div class="row">
        <!-- Personel Performansı -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="text-primary fw-bold m-0">Personel Gönderi Sayıları</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Personel Adı</th><th>Toplam Kargo</th></tr></thead>
                            <tbody>
                                <?php foreach ($personel_performans as $personel): ?>
                                <tr>
                                    <td><?= htmlspecialchars($personel['ad_soyad']); ?></td>
                                    <td><strong><?= $personel['toplam_kargo']; ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mağaza Performansı -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="text-primary fw-bold m-0">Mağaza Gönderi Sayıları</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Mağaza Adı</th><th>Toplam Kargo</th></tr></thead>
                            <tbody>
                                <?php foreach ($magaza_performans as $magaza): ?>
                                <tr>
                                    <td><?= htmlspecialchars($magaza['magaza_adi']); ?></td>
                                    <td><strong><?= $magaza['toplam_kargo']; ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/../includes/footer.php';
?>
