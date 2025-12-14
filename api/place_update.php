<?php
// api/place_update.php
header('Content-Type: application/json');

// Veritabanı Bağlantısı
$host = 'localhost';
$db = 'innoplace_pixels';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Veritabanı hatası']));
}

// Verileri Al
$input = json_decode(file_get_contents('php://input'), true);
$x = isset($input['x']) ? (int) $input['x'] : null;
$y = isset($input['y']) ? (int) $input['y'] : null;
$color = isset($input['color']) ? $input['color'] : '#000000';
$visitorId = isset($input['visitor_id']) ? $input['visitor_id'] : null;

// HATA KONTROLLERİ
if ($visitorId === null) {
    die(json_encode(['status' => 'error', 'message' => 'Kimlik hatası (Sayfayı yenileyin)']));
}

// Koordinat Sınırı (150x150 için)
if ($x < 0 || $x >= 150 || $y < 0 || $y >= 150) {
    die(json_encode(['status' => 'error', 'message' => 'Geçersiz koordinat']));
}

// COOLDOWN (BEKLEME SÜRESİ) KONTROLÜ
$currentTime = time();
$cooldownTime = 5; // Kaç saniyede bir tıklanabilir?

$stmt = $pdo->prepare("SELECT last_pixel_time FROM guest_cooldowns WHERE visitor_id = ?");
$stmt->execute([$visitorId]);
$lastTime = $stmt->fetchColumn();

if ($lastTime && ($currentTime - $lastTime) < $cooldownTime) {
    $wait = $cooldownTime - ($currentTime - $lastTime);
    die(json_encode(['status' => 'error', 'code' => 'COOLDOWN', 'wait_seconds' => $wait, 'message' => "Bekle: $wait sn"]));
}

// 1. PİKSELİ KAYDET (pixels tablosu olduğunu varsayıyoruz)
// Eğer yoksa: CREATE TABLE pixels (x INT, y INT, color VARCHAR(10), PRIMARY KEY(x,y));
$stmt = $pdo->prepare("REPLACE INTO pixels (x, y, color) VALUES (?, ?, ?)");
$stmt->execute([$x, $y, $color]);

// 2. SÜREYİ GÜNCELLE
$stmt = $pdo->prepare("REPLACE INTO guest_cooldowns (visitor_id, last_pixel_time) VALUES (?, ?)");
$stmt->execute([$visitorId, $currentTime]);

echo json_encode(['status' => 'success']);
?>