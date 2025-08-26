<?php
require __DIR__ . '/../includes/header.php'; // Panel yapısını dahil et
require __DIR__ . '/../config/db.php';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $alici = trim($_POST['alici']);
    $alici_sehir = trim($_POST['alici_sehir']);
    $kargo_turu = $_POST['kargo_turu'];
    $aciklama = trim($_POST['aciklama']);

    // Session'dan gönderici bilgilerini al
    $gonderen_magaza_id = $_SESSION['magaza_id'];
    $gonderen_personel_id = $_SESSION['personel_id'];

    // Benzersiz bir takip kodu oluştur
    $takip_kodu = 'KG' . strtoupper(uniqid());

    try {
        // Veritabanına ana kargo kaydını ekle
        $sql = "INSERT INTO kargolar (takip_kodu, gonderen_magaza_id, gonderen_personel_id, alici, alici_sehir, kargo_turu, aciklama) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $takip_kodu, 
            $gonderen_magaza_id, 
            $gonderen_personel_id, 
            $alici, 
            $alici_sehir, 
            $kargo_turu, 
            $aciklama
        ]);

        // Eklenen son kaydın ID'sini al
        $yeni_kargo_id = $pdo->lastInsertId();

        // İşlemi logla
        logla($pdo, 'KARGO_OLUSTURULDU', "$takip_kodu takip kodlu kargo oluşturuldu.", $yeni_kargo_id);

        // Kullanıcıyı ürün ekleme adımına (adım 2) yönlendir
        header("Location: /erp/templates/kargo_ekle_adim2.php?kargo_id=" . $yeni_kargo_id);
        exit();

    } catch (PDOException $e) {
        $hata_mesaji = "Hata: Kargo oluşturulamadı. " . $e->getMessage();
    }
}
?>

<div class="container-fluid">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark mb-0">Yeni Kargo Oluştur (Adım 1/2)</h3>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="text-primary fw-bold m-0">Kargo Genel Bilgileri</h6>
        </div>
        <div class="card-body">
            <?php if (isset($hata_mesaji)): ?>
                <div class="alert alert-danger"><?= $hata_mesaji; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="alici" class="form-label">Alıcı Adı Soyadı</label>
                        <input type="text" class="form-control" id="alici" name="alici" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="alici_sehir" class="form-label">Alıcı Şehir</label>
                        <input type="text" class="form-control" id="alici_sehir" name="alici_sehir" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="kargo_turu" class="form-label">Kargo Türü</label>
                    <select class="form-select" id="kargo_turu" name="kargo_turu" required>
                        <option value="Garanti">Garanti</option>
                        <option value="MOS">MOS</option>
                        <option value="Buyback">Buyback</option>
                        <option value="Diğer">Diğer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="aciklama" class="form-label">Genel Açıklama (Opsiyonel)</label>
                    <textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    Kaydet ve Ürün Ekle <i class="bi bi-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/../includes/footer.php';
?>
