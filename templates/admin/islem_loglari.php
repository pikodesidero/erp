<?php
require __DIR__ . '/../../includes/header.php';

// Sadece adminlerin bu sayfaya erişebilmesini sağla
if ($_SESSION['rol'] !== 'Admin') {
    header("Location: /erp/index.php");
    exit();
}
?>

<div class="container-fluid">
    <h3 class="text-dark mb-4">Sistem İşlem Kayıtları (Loglar)</h3>

    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="text-primary fw-bold m-0">Tüm Hareketler</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Tarih</th>
                            <th>Personel</th>
                            <th>İşlem Tipi</th>
                            <th>Açıklama</th>
                            <th>IP Adresi</th>
                        </tr>
                    </thead>
                    <tbody id="logTableBody">
                        <!-- Veriler JavaScript ile buraya yüklenecek -->
                    </tbody>
                </table>
            </div>
            <!-- Sayfalama linkleri JavaScript ile buraya eklenecek -->
            <div id="logPaginationContainer" class="mt-3"></div>
        </div>
    </div>
</div>

<?php
require __DIR__ . '/../../includes/footer.php';
?>
