<?php
// Bu dosya her sayfanın başında çağrılacak.
// Oturum kontrolü gibi işlemler burada yapılır.
require_once __DIR__ . '/fonksiyonlar.php';
oturumKontrol();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP Kargo Yönetim Paneli</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Özel CSS -->
    <link rel="stylesheet" href="/erp/public/css/style.css">
</head>
<body>

<div class="wrapper">
    <!-- Sidebar -->
   <nav id="sidebar">
        <div class="sidebar-header"><h3>ERP Sistemi</h3></div>
        <ul class="list-unstyled components">
            <p>Navigasyon</p>
            <li><a href="/erp/index.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li><a href="/erp/templates/kargo_ekle_adim1.php"><i class="bi bi-box-seam"></i> Yeni Kargo</a></li>
            <li><a href="/erp/templates/kargo_listele.php"><i class="bi bi-list-ul"></i> Kargoları Listele</a></li>
            <li><a href="/erp/templates/raporlar.php"><i class="bi bi-bar-chart-line"></i> Raporlar</a></li>
            <?php if ($_SESSION['rol'] === 'Admin'): ?>
            <li>
                <a href="#adminMenu" data-bs-toggle="collapse" aria-expanded="false" class="dropdown-toggle"><i class="bi bi-shield-lock"></i> Yönetim</a>
                <ul class="collapse list-unstyled" id="adminMenu">
                    <li><a href="/erp/templates/admin/personel_yonetimi.php">Personel Yönetimi</a></li>
                    <li><a href="/erp/templates/admin/magaza_yonetimi.php">Mağaza Yönetimi</a></li>
                    <li><a href="/erp/templates/admin/islem_loglari.php">İşlem Logları</a></li>
                </ul>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Sayfa İçeriği -->
    <div id="content">
       <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <!-- ARAMA FORMU GÜNCELLENDİ -->
                <div class="d-flex me-auto position-relative">
                    <div class="input-group">
                        <input class="form-control" type="search" id="liveSearchInput" placeholder="Takip Kodu veya IMEI ile ara..." autocomplete="off">
                        <span class="input-group-text"><i class="bi bi-search"></i></span>
                    </div>
                    <!-- Canlı arama sonuçlarının gösterileceği alan -->
                    <div id="liveSearchResults" class="live-search-results"></div>
                </div>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <!-- ... (Kullanıcı menüsü aynı) ... -->
                </div>
            </div>
        </nav>
        <!-- Buradan sonra her sayfanın kendi içeriği gelecek -->
