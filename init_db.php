<?php
require_once __DIR__ . '/db.php';

$aylar = [
    'January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart',
    'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran',
    'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül',
    'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'
];

$days_to_show = 146; // Geçmiş gün sayısı
$today = new DateTime();
$start_date = clone $today;
$start_date->modify("-$days_to_show days");
$current_date = clone $start_date;

$tasks_pool = [
    "Frontend UI dizaynı yapıldı ve modern CSS ile stillendirildi.",
    "Veritabanı optimizasyonu ve indeksleme işlemleri tamamlandı.",
    "API endpointleri yazıldı ve Postman üzerinden test edildi.",
    "Yeni özellikleri test etmek için unit testler (Jest) yazıldı.",
    "Müşteri toplantısı yapıldı ve projenin yeni gereksinimleri analiz edildi.",
    "Docker konteynerleri konfigüre edildi ve CI/CD pipeline güncellendi.",
    "Eski PHP kodları refactor edildi ve daha modüler hale getirildi.",
    "Proje dokümantasyonu güncellendi ve README dosyası düzenlendi.",
    "Frontend tarafında performans iyileştirmeleri yapıldı (Lighthouse: 98).",
    "Tailwind CSS ile responsive ve mobil uyumlu tasarımlar yapıldı.",
    "Bug fix: Kullanıcı girişi sırasında yaşanan yetkilendirme hatası giderildi.",
    "Code review yapıldı ve takım arkadaşlarına PR üzerinden geri bildirim verildi.",
    "GraphQL query ve mutation'ları için araştırmalar yapıldı.",
    "React Context API ve Zustand ile state management yapısı kuruldu.",
    "Redis caching mekanizması projeye dahil edilerek sorgu hızları artırıldı.",
    "Payment gateway entegrasyonu (Stripe) başarıyla test ortamında tamamlandı.",
    "Kullanıcı testleri (A/B testing) için Google Analytics 4 kuruldu.",
    "AWS S3 bucket ayarları yapılandırıldı ve güvenli dosya yükleme servisi yazıldı.",
    "WebSockets kullanılarak gerçek zamanlı mesajlaşma altyapısı eklendi.",
    "Güvenlik açığı taraması (Pen-Test) yapıldı ve gerekli XSS/CSRF önlemleri alındı.",
    "Loglama mekanizması (Monolog) kurularak hata takibi iyileştirildi.",
    "Sunucu bakımı yapıldı ve güncellemeler yüklendi."
];

mt_srand(2026); // Verilerin her yenilemede değişmemesi için sabit seed

echo "Veritabanı başlatılıyor...\n";

// Tabloları temizle
$pdo->exec("DELETE FROM tasks");
$pdo->exec("DELETE FROM users");

// Admin kullanıcısı ekle
$stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->execute(['admin', password_hash('password', PASSWORD_DEFAULT)]);
echo "Admin kullanıcısı oluşturuldu (Kullanıcı: admin, Şifre: password)\n";

$inserted_tasks = 0;

while ($current_date <= $today) {
    $date_str = $current_date->format('Y-m-d');
    
    // %75 ihtimalle o gün çalışılmış olsun
    $did_work = mt_rand(1, 100) > 25;
    
    if ($did_work) {
        $num_tasks = mt_rand(1, 4);
        $keys = array_rand($tasks_pool, $num_tasks);
        
        $stmt = $pdo->prepare("INSERT INTO tasks (task_date, description) VALUES (?, ?)");
        
        if (is_array($keys)) {
            foreach ($keys as $k) {
                $stmt->execute([$date_str, $tasks_pool[$k]]);
                $inserted_tasks++;
            }
        } else {
            $stmt->execute([$date_str, $tasks_pool[$keys]]);
            $inserted_tasks++;
        }
    }
    
    $current_date->modify('+1 day');
}

echo "Toplam {$inserted_tasks} adet geçmiş görev (son 146 gün için) oluşturuldu.\n";
echo "İşlem tamamlandı!\n";
?>
