<?php
// Bu dosya, sadece ilk admin kullanıcısını oluşturmak için kullanılır.
// İşlem bittikten sonra GÜVENLİK için bu dosyayı sunucudan SİLİN!

require __DIR__ . '/config/db.php';

// --- GEREKLİ BİLGİLERİ BURADAN DOLDURUN ---

// 1. Admin'in atanacağı mağazayı seçin.
// Önce bir mağaza eklenmiş olmalı. Manuel olarak DB'den ID'sini alın.
$magaza_id = 1; // Örneğin, ID'si 1 olan M1 AVM Mağazası

// 2. Admin bilgilerini tanımlayın.
$admin_bilgileri = [
    'personel_kodu'     => 'ADM001',
    'ad_soyad'          => 'Yönetici Admin',
    'tc_kimlik_no'      => null,
    'dogum_tarihi'      => null,
    'email'             => 'admin@sirket.com',
    'telefon'           => null,
    'pozisyon'          => 'Sistem Yöneticisi',
    'ise_giris_tarihi'  => date('Y-m-d'),
    'kullanici_adi'     => 'admin',
    'sifre'             => '15051979ToM!', // Buraya güvenli bir şifre yazın
    'rol'               => 'Admin',
    'aktif_mi'          => 1
];

// --- KODUN GERİ KALANI ---

echo "<pre>"; // Daha okunaklı bir çıktı için

// Şifreyi güvenli bir şekilde hash'le
$hashlenmis_sifre = password_hash($admin_bilgileri['sifre'], PASSWORD_DEFAULT);
echo "Şifre başarıyla hash'lendi.\n";

// Veritabanına ekleme sorgusu
$sql = "INSERT INTO personel (
            personel_kodu, ad_soyad, tc_kimlik_no, dogum_tarihi, email, telefon, 
            magaza_id, pozisyon, ise_giris_tarihi, kullanici_adi, sifre, rol, aktif_mi
        ) VALUES (
            :personel_kodu, :ad_soyad, :tc_kimlik_no, :dogum_tarihi, :email, :telefon, 
            :magaza_id, :pozisyon, :ise_giris_tarihi, :kullanici_adi, :sifre, :rol, :aktif_mi
        )";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        ':personel_kodu'    => $admin_bilgileri['personel_kodu'],
        ':ad_soyad'         => $admin_bilgileri['ad_soyad'],
        ':tc_kimlik_no'     => $admin_bilgileri['tc_kimlik_no'],
        ':dogum_tarihi'     => $admin_bilgileri['dogum_tarihi'],
        ':email'            => $admin_bilgileri['email'],
        ':telefon'          => $admin_bilgileri['telefon'],
        ':magaza_id'        => $magaza_id,
        ':pozisyon'         => $admin_bilgileri['pozisyon'],
        ':ise_giris_tarihi' => $admin_bilgileri['ise_giris_tarihi'],
        ':kullanici_adi'    => $admin_bilgileri['kullanici_adi'],
        ':sifre'            => $hashlenmis_sifre, // Hash'lenmiş şifreyi kullan
        ':rol'              => $admin_bilgileri['rol'],
        ':aktif_mi'         => $admin_bilgileri['aktif_mi']
    ]);
    
    echo "Başarılı! Admin kullanıcısı oluşturuldu.\n";
    echo "Kullanıcı Adı: " . htmlspecialchars($admin_bilgileri['kullanici_adi']) . "\n";
    echo "Şifre: Belirlediğiniz şifre\n\n";
    echo "!!! ÖNEMLİ: BU DOSYAYI ŞİMDİ SUNUCUDAN SİLİN !!!\n";

} catch (PDOException $e) {
    if ($e->errorInfo[1] == 1062) { // Duplicate entry hatası
        echo "HATA: Bu kullanıcı adı, e-posta veya personel kodu zaten mevcut.\n";
    } else {
        echo "Veritabanı Hatası: " . $e->getMessage() . "\n";
    }
}

echo "</pre>";
?>
