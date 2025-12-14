<?php
// api/place_get.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

// YENİ VERİTABANI BİLGİLERİ
// YENİ VERİTABANI BİLGİLERİ
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "innoplace_pixels";

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    // Güvenlik için detaylı hatayı gizleyip genel hata dönüyoruz
    die(json_encode(["status" => "error", "message" => "Veritabanı bağlantı hatası"]));
}

$result = $conn->query("SELECT x, y, color FROM innoplace_pixels");

$pixels = [];
while ($row = $result->fetch_assoc()) {
    $pixels[] = $row;
}

echo json_encode($pixels);
$conn->close();
?>