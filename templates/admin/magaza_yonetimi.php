<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../config/db.php';

// Sadece adminlerin erişimi
if ($_SESSION['rol'] !== 'Admin') {
    header("Location: /erp/index.php");
    exit();
}

$hata_mesaji = '';
$basari_mesaji = '';
$duzenlenecek_magaza = null;

// Yeni mağaza ekleme veya güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $magaza_adi = trim($_POST['magaza_adi']);
    $sehir = trim($_POST['sehir']);
    $adres = trim($_POST['adres']);
    $magaza_id = $_POST['magaza_id'] ?? null;

    if (!empty($magaza_adi) && !empty($sehir)) {
        try {
            if ($magaza_id) { // Güncelleme
                $sql = "UPDATE magazalar SET magaza_adi = ?, sehir = ?, adres = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$magaza_adi, $sehir, $adres, $magaza_id]);
                logla($pdo, 'MAGAZA_DUZENLENDI', "$magaza_adi adlı mağaza güncellendi.", $magaza_id);
                $basari_mesaji = 'Mağaza başarıyla güncellendi!';
            } else { // Ekleme
                $sql = "INSERT INTO magazalar (magaza_adi, sehir, adres) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$magaza_adi, $sehir, $adres]);
                $yeni_magaza_id = $pdo->lastInsertId();
                logla($pdo, 'MAGAZA_EKLENDI', "$magaza_adi adlı mağaza eklendi.", $yeni_magaza_id);
                $basari_mesaji = 'Mağaza başarıyla eklendi!';
            }
        } catch (PDOException $e) {
            $hata_mesaji = "Veritabanı hatası: " . $e->getMessage();
        }
    } else {
        $hata_mesaji = "Mağaza adı ve şehir alanları boş bırakılamaz.";
    }
}

// Düzenleme için mağaza bilgilerini çek
if (isset($_GET['duzenle'])) {
    $id = filter_input(INPUT_GET, 'duzenle', FILTER_VALIDATE_INT);
    $stmt = $pdo->prepare("SELECT * FROM magazalar WHERE id = ?");
    $stmt->execute([$id]);
    $duzenlenecek_magaza = $stmt->fetch();
}

// Tüm mağazaları listele
$magazalar = $pdo->query("SELECT * FROM magazalar ORDER BY magaza_adi")->fetchAll();
?>

<div class="container-fluid">
    <h3 class="text-dark mb-4">Mağaza Yönetimi</h3>
    <div class="row">
        <div class="col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="text-primary fw-bold m-0">
                        <?= $duzenlenecek_magaza ? 'Mağaza Düzenle' : 'Yeni Mağaza Ekle'; ?>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if ($hata_mesaji) echo "<div class='alert alert-danger'>$hata_mesaji</div>"; ?>
                    <?php if ($basari_mesaji) echo "<div class='alert alert-success'>$basari_mesaji</div>"; ?>
                    <form method="POST" action="magaza_yonetimi.php">
                        <input type="hidden" name="magaza_id" value="<?= $duzenlenecek_magaza['id'] ?? ''; ?>">
                        <div class="mb-3">
                            <label class="form-label">Mağaza Adı</label>
                            <input type="text" name="magaza_adi" class="form-control" value="<?= htmlspecialchars($duzenlenecek_magaza['magaza_adi'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Şehir</label>
                            <input type="text" name="sehir" class="form-control" value="<?= htmlspecialchars($duzenlenecek_magaza['sehir'] ?? ''); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Adres</label>
                            <textarea name="adres" class="form-control" rows="3"><?= htmlspecialchars($duzenlenecek_magaza['adres'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <?= $duzenlenecek_magaza ? 'Güncelle' : 'Kaydet'; ?>
                        </button>
                        <?php if ($duzenlenecek_magaza): ?>
                            <a href="magaza_yonetimi.php" class="btn btn-secondary">İptal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card shadow">
                <div class="card-header py-3"><h6 class="text-primary fw-bold m-0">Mevcut Mağazalar</h6></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead><tr><th>Mağaza Adı</th><th>Şehir</th><th>İşlemler</th></tr></thead>
                            <tbody>
                                <?php foreach ($magazalar as $magaza): ?>
                                <tr>
                                    <td><?= htmlspecialchars($magaza['magaza_adi']); ?></td>
                                    <td><?= htmlspecialchars($magaza['sehir']); ?></td>
                                    <td>
                                        <a href="?duzenle=<?= $magaza['id']; ?>" class="btn btn-sm btn-info"><i class="bi bi-pencil-square"></i></a>
                                    </td>
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
require __DIR__ . '/../../includes/footer.php';
?>
