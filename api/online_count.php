<?php
// api/online_count.php
header('Content-Type: application/json');

// Veritabanı Ayarları
$host = 'localhost';
$db = 'innoplace_pixels';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    // Hata olursa 1 kişi varmış gibi davran
    echo json_encode(['count' => 1]);
    exit;
}

// Javascript'ten gelen ziyaretçi ID'sini al
$input = json_decode(file_get_contents('php://input'), true);
$visitorId = isset($input['visitor_id']) ? $input['visitor_id'] : null;
$time = time();

if ($visitorId) {
    // 1. Ziyaretçiyi kaydet/güncelle
    $stmt = $pdo->prepare("REPLACE INTO online_visitors (visitor_id, last_seen) VALUES (?, ?)");
    $stmt->execute([$visitorId, $time]);

    // 2. 5 saniyedir haber alınamayanları (çıkanları) sil
    $stmt = $pdo->prepare("DELETE FROM online_visitors WHERE last_seen < ?");
    $stmt->execute([$time - 5]);
}

// 3. Toplam sayıyı çek
$stmt = $pdo->query("SELECT COUNT(*) FROM online_visitors");
$count = $stmt->fetchColumn();

echo json_encode(['count' => $count]);
?>