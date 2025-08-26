<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../config/db.php';

if ($_SESSION['rol'] !== 'Admin') {
    header("Location: /erp/index.php");
    exit();
}

$personeller = $pdo->query("SELECT p.*, m.magaza_adi FROM personel p LEFT JOIN magazalar m ON p.magaza_id = m.id ORDER BY p.ad_soyad ASC")->fetchAll();
$magazalar = $pdo->query("SELECT id, magaza_adi FROM magazalar ORDER BY magaza_adi")->fetchAll();
?>

<div class="container-fluid">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark mb-0">Personel Yönetimi</h3>
        <button id="yeniPersonelBtn" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Yeni Personel Ekle</button>
    </div>

    <div class="card shadow"><div class="card-body"><div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead><tr><th>Adı Soyadı</th><th>Kullanıcı Adı</th><th>Mağaza</th><th>Rol</th><th>Durum</th><th>İşlemler</th></tr></thead>
            <tbody id="personelTableBody">
                <?php foreach ($personeller as $personel): ?>
                <!-- GÜNCELLENEN SATIR: data-personel-id eklendi -->
                <tr data-personel-id="<?= $personel['id']; ?>">
                    <td><?= htmlspecialchars($personel['ad_soyad']); ?></td>
                    <td><?= htmlspecialchars($personel['kullanici_adi']); ?></td>
                    <td><?= htmlspecialchars($personel['magaza_adi']); ?></td>
                    <td><span class="badge bg-<?= $personel['rol'] == 'Admin' ? 'danger' : 'secondary'; ?>"><?= htmlspecialchars($personel['rol']); ?></span></td>
                    <td><span class="badge bg-<?= $personel['aktif_mi'] ? 'success' : 'warning'; ?>"><?= $personel['aktif_mi'] ? 'Aktif' : 'Pasif'; ?></span></td>
                    <td><button class="btn btn-sm btn-info edit-btn" data-id="<?= $personel['id']; ?>"><i class="bi bi-pencil-square"></i></button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div></div></div>
</div>

<div class="modal fade" id="personelModal" tabindex="-1" aria-labelledby="personelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="personelModalLabel">Yeni Personel Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="personelForm">
                    <input type="hidden" id="personel_id" name="personel_id">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Adı Soyadı</label><input type="text" name="ad_soyad" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Personel Kodu</label><input type="text" id="personel_kodu" name="personel_kodu" class="form-control" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Kullanıcı Adı</label><input type="text" name="kullanici_adi" class="form-control" required></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Şifre</label><input type="password" name="sifre" class="form-control" placeholder="Değiştirmek istemiyorsanız boş bırakın"></div>
                    </div>
                    <div class="mb-3"><label class="form-label">E-posta</label><input type="email" name="email" class="form-control" required></div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label class="form-label">Mağaza</label><select name="magaza_id" class="form-select" required><option value="">Seçiniz...</option><?php foreach ($magazalar as $m): echo "<option value='{$m['id']}'>".htmlspecialchars($m['magaza_adi'])."</option>"; endforeach; ?></select></div>
                        <div class="col-md-6 mb-3"><label class="form-label">Pozisyon</label><input type="text" name="pozisyon" class="form-control"></div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3" id="ise_giris_tarihi_group"><label class="form-label">İşe Giriş Tarihi</label><input type="date" name="ise_giris_tarihi" class="form-control" value="<?= date('Y-m-d'); ?>"></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Rol</label><select name="rol" class="form-select" required><option value="Personel">Personel</option><option value="Admin">Admin</option></select></div>
                        <div class="col-md-4 mb-3"><label class="form-label">Durum</label><select name="aktif_mi" class="form-select" required><option value="1">Aktif</option><option value="0">Pasif</option></select></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="submit" form="personelForm" class="btn btn-primary">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>

