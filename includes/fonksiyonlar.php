<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Oturumun aktif olup olmadığını ve süresinin dolup dolmadığını kontrol eder
function oturumKontrol() {
    $oturum_suresi = 1800; // 30 dakika

    if (!isset($_SESSION['personel_id'])) {
        header("Location: /erp/login.php");
        exit();
    }

    if (isset($_SESSION['son_etkilesim']) && (time() - $_SESSION['son_etkilesim'] > $oturum_suresi)) {
        session_unset();
        session_destroy();
        header("Location: /erp/login.php?durum=oturum_sona_erdi");
        exit();
    }
    $_SESSION['son_etkilesim'] = time(); // Her işlemde oturum süresini sıfırla
}

// Sistemdeki önemli işlemleri loglar
function logla($pdo, $islem_tipi, $aciklama, $kayit_id = null) {
    $personel_id = $_SESSION['personel_id'] ?? 0; // Giriş yapılmamışsa 0 ata
    $ip_adresi = $_SERVER['REMOTE_ADDR'];

    $sql = "INSERT INTO islem_loglari (personel_id, islem_tipi, kayit_id, aciklama, ip_adresi) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$personel_id, $islem_tipi, $kayit_id, $aciklama, $ip_adresi]);
}
?>