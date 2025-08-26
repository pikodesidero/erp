<?php
// Hata ayıklama için hata gösterimini açıyoruz.
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Gerekli dosyaları ve oturum kontrolünü dahil et
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/fonksiyonlar.php';

// API'ye özel oturum kontrolü. Başarısız olursa script'i durdurur.
try {
    oturumKontrol();
} catch (Exception $e) {
    http_response_code(401); // Unauthorized
    echo json_encode(['status' => 'error', 'message' => 'Oturum geçerli değil.']);
    exit();
}

header('Content-Type: application/json');
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Geçersiz işlem.'];

switch ($action) {
    case 'urun_ekle':
        $kargo_id = filter_input(INPUT_POST, 'kargo_id', FILTER_VALIDATE_INT);
        $imei = trim($_POST['imei'] ?? '');
        $model = trim($_POST['model'] ?? '');

        if ($kargo_id && !empty($imei) && !empty($model)) {
            try {
                $sql = "INSERT INTO gonderilen_urunler (kargo_id, imei, model) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$kargo_id, $imei, $model]);
                $yeni_urun_id = $pdo->lastInsertId();
                logla($pdo, 'URUN_EKLENDI_AJAX', "$kargo_id ID'li kargoya $imei IMEI'li ürün eklendi.", $kargo_id);
                $response = ['status' => 'success', 'message' => 'Ürün başarıyla eklendi!', 'urun' => ['id' => $yeni_urun_id, 'imei' => $imei, 'model' => $model]];
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    $response['message'] = "Hata: Bu IMEI numarası zaten aktif bir gönderide mevcut!";
                } else {
                    $response['message'] = "Veritabanı hatası: " . $e->getMessage();
                }
            }
        } else {
            $response['message'] = "Gerekli alanlar (kargo_id, imei, model) boş bırakılamaz.";
        }
        break;

    case 'durum_guncelle':
        $kargo_id = filter_input(INPUT_POST, 'kargo_id', FILTER_VALIDATE_INT);
        $yeni_durum = trim($_POST['kargo_durumu'] ?? '');
        $gecerli_durumlar = ['Hazırlanıyor', 'Yola Çıktı', 'Dağıtımda', 'Teslim Edildi', 'İade'];

        if ($kargo_id && !empty($yeni_durum) && in_array($yeni_durum, $gecerli_durumlar)) {
            try {
                $sql = "UPDATE kargolar SET kargo_durumu = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$yeni_durum, $kargo_id]);
                logla($pdo, 'DURUM_GUNCELLEME_AJAX', "$kargo_id ID'li kargonun durumu '$yeni_durum' olarak güncellendi.", $kargo_id);
                $response = ['status' => 'success', 'message' => 'Kargo durumu başarıyla güncellendi!', 'yeni_durum' => $yeni_durum];
            } catch (PDOException $e) {
                $response['message'] = "Veritabanı hatası: " . $e->getMessage();
            }
        } else {
            $response['message'] = "Geçersiz kargo ID veya durum bilgisi.";
        }
        break;

    case 'live_search':
        $term = trim($_GET['term'] ?? '');
        $results = [];

        if (strlen($term) > 2) {
            $sql_imei = "SELECT u.imei, k.id as kargo_id, k.takip_kodu FROM gonderilen_urunler AS u JOIN kargolar AS k ON u.kargo_id = k.id WHERE u.imei LIKE ?";
            $stmt_imei = $pdo->prepare($sql_imei);
            $stmt_imei->execute(["%$term%"]);
            foreach ($stmt_imei->fetchAll() as $res) {
                $results[] = ['type' => 'IMEI', 'text' => "IMEI: " . htmlspecialchars($res['imei']), 'subtext' => "Kargo: " . htmlspecialchars($res['takip_kodu']), 'url' => "/erp/templates/kargo_detay.php?id=" . $res['kargo_id']];
            }

            $sql_kargo = "SELECT id, takip_kodu, alici FROM kargolar WHERE takip_kodu LIKE ?";
            $stmt_kargo = $pdo->prepare($sql_kargo);
            $stmt_kargo->execute(["%$term%"]);
            foreach ($stmt_kargo->fetchAll() as $res) {
                $results[] = ['type' => 'Kargo', 'text' => "Takip Kodu: " . htmlspecialchars($res['takip_kodu']), 'subtext' => "Alıcı: " . htmlspecialchars($res['alici']), 'url' => "/erp/templates/kargo_detay.php?id=" . $res['id']];
            }
        }
        $response = ['status' => 'success', 'results' => $results];
        break;

    case 'get_kargolar':
        try {
            $limit = 20;
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
            $offset = ($page - 1) * $limit;

            $filters = $_GET;
            
            $baseSql = "FROM kargolar AS k JOIN magazalar AS m ON k.gonderen_magaza_id = m.id JOIN personel AS p ON k.gonderen_personel_id = p.id";
            $whereClauses = [];
            $params = [];

            if (!empty($filters['tarih_bas'])) { $whereClauses[] = "DATE(k.gonderim_tarihi) >= ?"; $params[] = $filters['tarih_bas']; }
            if (!empty($filters['tarih_son'])) { $whereClauses[] = "DATE(k.gonderim_tarihi) <= ?"; $params[] = $filters['tarih_son']; }
            if (!empty($filters['personel_id'])) { $whereClauses[] = "k.gonderen_personel_id = ?"; $params[] = $filters['personel_id']; }
            if (!empty($filters['magaza_id'])) { $whereClauses[] = "k.gonderen_magaza_id = ?"; $params[] = $filters['magaza_id']; }
            if (!empty($filters['sehir'])) { $whereClauses[] = "k.alici_sehir LIKE ?"; $params[] = '%' . $filters['sehir'] . '%'; }
            if (!empty($filters['alici'])) { $whereClauses[] = "k.alici LIKE ?"; $params[] = '%' . $filters['alici'] . '%'; }
            if (!empty($filters['durum'])) { $whereClauses[] = "k.kargo_durumu = ?"; $params[] = $filters['durum']; }
            if ($_SESSION['rol'] !== 'Admin') { $whereClauses[] = "k.gonderen_personel_id = ?"; $params[] = $_SESSION['personel_id']; }

            $whereSql = !empty($whereClauses) ? ' WHERE ' . implode(' AND ', $whereClauses) : '';

            $totalRecordsStmt = $pdo->prepare("SELECT COUNT(k.id) " . $baseSql . $whereSql);
            $totalRecordsStmt->execute($params);
            $totalRecords = $totalRecordsStmt->fetchColumn();
            $totalPages = ceil($totalRecords / $limit);

            $dataSql = "SELECT k.id, k.takip_kodu, k.alici, k.kargo_durumu, k.gonderim_tarihi, m.magaza_adi, p.ad_soyad AS personel_adi " 
                     . $baseSql . $whereSql 
                     . " ORDER BY k.gonderim_tarihi DESC LIMIT ? OFFSET ?";
            
            $dataStmt = $pdo->prepare($dataSql);
            
            // Parametreleri döngü ile ve doğru sırada bağla
            $paramIndex = 1;
            foreach ($params as $value) {
                $dataStmt->bindValue($paramIndex++, $value);
            }
            
            // Limit ve Offset'i en son ve doğru tiple bağla
            $dataStmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
            $dataStmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
            
            $dataStmt->execute();
            
            $response = [
                'status' => 'success',
                'kargolar' => $dataStmt->fetchAll(PDO::FETCH_ASSOC),
                'pagination' => ['total_records' => $totalRecords, 'total_pages' => $totalPages, 'current_page' => $page]
            ];

        } catch (PDOException $e) {
            $response = ['status' => 'error', 'message' => 'Veritabanı Sorgu Hatası: ' . $e->getMessage()];
        }
        break;

      case 'get_logs':
        // Bu bölüme sadece Admin rolündeki kullanıcılar erişebilir
        if ($_SESSION['rol'] !== 'Admin') {
            $response = ['status' => 'error', 'message' => 'Bu işlem için yetkiniz yok.'];
            break;
        }

        try {
            $limit = 25; // Loglar için sayfa başına daha fazla kayıt gösterebiliriz
            $page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, ['options' => ['default' => 1, 'min_range' => 1]]);
            $offset = ($page - 1) * $limit;

            // Toplam kayıt sayısını al
            $totalRecords = $pdo->query("SELECT COUNT(id) FROM islem_loglari")->fetchColumn();
            $totalPages = ceil($totalRecords / $limit);

            // Mevcut sayfanın verisini çek (personel adıyla birlikte)
            $sql = "SELECT 
                        l.islem_tipi, l.aciklama, l.tarih, l.ip_adresi,
                        p.ad_soyad AS personel_adi
                    FROM islem_loglari AS l
                    JOIN personel AS p ON l.personel_id = p.id
                    ORDER BY l.tarih DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $response = [
                'status' => 'success',
                'logs' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'pagination' => ['total_records' => $totalRecords, 'total_pages' => $totalPages, 'current_page' => $page]
            ];

        } catch (PDOException $e) {
            $response = ['status' => 'error', 'message' => 'Loglar çekilirken bir veritabanı hatası oluştu: ' . $e->getMessage()];
        }
        break;
  case 'get_personel_detay':
        if ($_SESSION['rol'] !== 'Admin') { break; }
        $personel_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        if ($personel_id) {
            $stmt = $pdo->prepare("SELECT * FROM personel WHERE id = ?");
            $stmt->execute([$personel_id]);
            $personel = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($personel) {
                $response = ['status' => 'success', 'personel' => $personel];
            } else {
                $response['message'] = 'Personel bulunamadı.';
            }
        }
        break;

    case 'personel_kaydet':
        if ($_SESSION['rol'] !== 'Admin') { break; }
        
        $personel_id = filter_input(INPUT_POST, 'personel_id', FILTER_VALIDATE_INT);
        $sifre = $_POST['sifre'] ?? '';
        
        try {
            if ($personel_id) { // Güncelleme
                if (!empty($sifre)) {
                    $hash = password_hash($sifre, PASSWORD_DEFAULT);
                    $sql = "UPDATE personel SET ad_soyad=?, email=?, magaza_id=?, pozisyon=?, kullanici_adi=?, sifre=?, rol=?, aktif_mi=? WHERE id=?";
                    $params = [$_POST['ad_soyad'], $_POST['email'], $_POST['magaza_id'], $_POST['pozisyon'], $_POST['kullanici_adi'], $hash, $_POST['rol'], $_POST['aktif_mi'], $personel_id];
                } else {
                    $sql = "UPDATE personel SET ad_soyad=?, email=?, magaza_id=?, pozisyon=?, kullanici_adi=?, rol=?, aktif_mi=? WHERE id=?";
                    $params = [$_POST['ad_soyad'], $_POST['email'], $_POST['magaza_id'], $_POST['pozisyon'], $_POST['kullanici_adi'], $_POST['rol'], $_POST['aktif_mi'], $personel_id];
                }
                $log_mesaj = $_POST['ad_soyad'] . ' adlı personel güncellendi.';
                $log_tipi = 'PERSONEL_DUZENLENDI';
            } else { // Ekleme
                $hash = password_hash($sifre, PASSWORD_DEFAULT);
                $sql = "INSERT INTO personel (personel_kodu, ad_soyad, email, magaza_id, pozisyon, ise_giris_tarihi, kullanici_adi, sifre, rol, aktif_mi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $params = [$_POST['personel_kodu'], $_POST['ad_soyad'], $_POST['email'], $_POST['magaza_id'], $_POST['pozisyon'], $_POST['ise_giris_tarihi'], $_POST['kullanici_adi'], $hash, $_POST['rol'], $_POST['aktif_mi']];
                $log_mesaj = $_POST['ad_soyad'] . ' adlı personel eklendi.';
                $log_tipi = 'PERSONEL_EKLENDI';
            }

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $kayit_id = $personel_id ?: $pdo->lastInsertId();
            logla($pdo, $log_tipi, $log_mesaj, $kayit_id);
            
            // GÜNCELLENEN KISIM: İşlem sonrası personelin tam bilgisini çekip döndür
            $sql_get_personel = "SELECT p.*, m.magaza_adi FROM personel p LEFT JOIN magazalar m ON p.magaza_id = m.id WHERE p.id = ?";
            $stmt_get = $pdo->prepare($sql_get_personel);
            $stmt_get->execute([$kayit_id]);
            $guncel_personel = $stmt_get->fetch(PDO::FETCH_ASSOC);

            $response = ['status' => 'success', 'message' => 'İşlem başarıyla tamamlandı!', 'personel' => $guncel_personel];

        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $response['message'] = 'Hata: Bu kullanıcı adı, e-posta veya personel kodu zaten mevcut.';
            } else {
                $response['message'] = 'Veritabanı hatası: ' . $e->getMessage();
            }
        }
        break;
}

echo json_encode($response);
exit();
?>

