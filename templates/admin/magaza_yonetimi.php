<?php
require __DIR__ . '/../../includes/header.php';
require __DIR__ . '/../../config/db.php';

if ($_SESSION['rol'] !== 'Admin') {
    header("Location: /erp/index.php");
    exit();
}

// Sayfa ilk yüklendiğinde mağazaları çek
$magazalar = $pdo->query("SELECT * FROM magazalar ORDER BY magaza_adi ASC")->fetchAll();
?>

<div class="container-fluid">
    <div class="d-sm-flex justify-content-between align-items-center mb-4">
        <h3 class="text-dark mb-0">Mağaza Yönetimi</h3>
        <button id="yeniMagazaBtn" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Yeni Mağaza Ekle</button>
    </div>

    <div class="card shadow"><div class="card-body"><div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead><tr><th>Mağaza Adı</th><th>Şehir</th><th>İşlemler</th></tr></thead>
            <tbody id="magazaTableBody">
                <?php foreach ($magazalar as $magaza): ?>
                <tr data-magaza-id="<?= $magaza['id']; ?>">
                    <td><?= htmlspecialchars($magaza['magaza_adi']); ?></td>
                    <td><?= htmlspecialchars($magaza['sehir']); ?></td>
                    <td><button class="btn btn-sm btn-info edit-btn-magaza" data-id="<?= $magaza['id']; ?>"><i class="bi bi-pencil-square"></i></button></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div></div></div>
</div>

<!-- Mağaza Ekle/Düzenle Modal -->
<div class="modal fade" id="magazaModal" tabindex="-1" aria-labelledby="magazaModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="magazaModalLabel">Yeni Mağaza Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="magazaForm">
                    <input type="hidden" id="magaza_id" name="magaza_id">
                    <div class="mb-3">
                        <label class="form-label">Mağaza Adı</label>
                        <input type="text" name="magaza_adi" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Şehir</label>
                        <input type="text" name="sehir" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Adres</label>
                        <textarea name="adres" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                <button type="submit" form="magazaForm" class="btn btn-primary">Kaydet</button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../includes/footer.php'; ?>
