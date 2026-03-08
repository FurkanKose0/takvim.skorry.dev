<?php

// Lütfen kendi sunucunuzdaki veritabanı bilgilerinizle bu alanları değiştirin
// Örnek bağlantı dosyası, gerçek bilgilerinizi db.php dosyasına giriniz
$host = 'localhost';
$dbname = 'veritabani_adi';
$username = 'kullanici_adi';
$password = 'sifre';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

?>
