<?php
session_start();
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/fonksiyonlar.php';

// Zaten giriş yapmışsa ana sayfaya yönlendir
if (isset($_SESSION['personel_id'])) {
    header("Location: index.php");
    exit();
}

$hata_mesaji = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kullanici_adi = trim($_POST['kullanici_adi']);
    $sifre = $_POST['sifre'];

    $stmt = $pdo->prepare("SELECT * FROM personel WHERE kullanici_adi = ? AND aktif_mi = 1");
    $stmt->execute([$kullanici_adi]);
    $personel = $stmt->fetch();

    if ($personel && password_verify($sifre, $personel['sifre'])) {
        // Oturum bilgilerini ayarla
        session_regenerate_id(true);
        $_SESSION['personel_id'] = $personel['id'];
        $_SESSION['ad_soyad'] = $personel['ad_soyad'];
        $_SESSION['rol'] = $personel['rol'];
        $_SESSION['magaza_id'] = $personel['magaza_id'];
        $_SESSION['son_etkilesim'] = time();

        // Son giriş bilgilerini güncelle
        $pdo->prepare("UPDATE personel SET son_giris_tarihi = NOW(), son_giris_ip = ? WHERE id = ?")
            ->execute([$_SERVER['REMOTE_ADDR'], $personel['id']]);

        // Giriş işlemini logla
        logla($pdo, 'GIRIS_YAPTI', $personel['ad_soyad'] . ' sisteme giriş yaptı.');

        header("Location: index.php");
        exit();
    } else {
        logla($pdo, 'GIRIS_BASARISIZ', $kullanici_adi . ' kullanıcı adı ile başarısız giriş denemesi.', null);
        $hata_mesaji = 'Kullanıcı adı veya şifre hatalı!';
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Giriş Yap</title>
    </head>
<body>
    <h2>Kargo Takip Sistemi - Personel Girişi</h2>
    <?php if ($hata_mesaji): ?>
        <p style="color:red;"><?= $hata_mesaji; ?></p>
    <?php endif; ?>
    <form method="post">
        Kullanıcı Adı: <input type="text" name="kullanici_adi" required><br>
        Şifre: <input type="password" name="sifre" required><br>
        <button type="submit">Giriş Yap</button>
    </form>
</body>
</html>