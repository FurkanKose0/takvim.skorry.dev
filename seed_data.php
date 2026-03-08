<?php
require_once __DIR__ . '/db.php';

echo "Geçmiş veriler siliniyor...\n";
$pdo->exec("DELETE FROM tasks");

// SADECE yazılım/teknoloji ile ilgili özel tarihler ve olaylar (kendi ağzımdan "yaptım", "ettim")
$special_milestones = [
    '2025-10-11' => ['Kariyerimi Yapay Zeka üzerine inşa etme kararı aldım ve 180 günlük büyük gelişim programımı başlattım.'],
    '2025-10-15' => ['Python temelleri ve algoritma mantığı üzerine yoğunlaştım.'], 
    '2025-11-12' => ['Python kütüphaneleri ve veri manipülasyonu üzerine pratikler yaptım.'],
    '2025-11-20' => ['PHP tabanlı Kişisel Web Sitesi ve Teknoloji Bloğu projeme başladım.'],
    '2025-11-21' => ['Blogum için admin paneli hiyerarşisini ve navigasyon yapısını kurdum.'],
    '2025-11-22' => ['Bloğuma yazılım içerikleri için Syntax Highlighting (kod renklendirme) ve metin editörü entegre ettim.'],
    '2025-12-05' => ['Siber güvenlik dünyasına giriş yaptım; temel ağ ve sistem güvenliği kavramlarını çalıştım.'],
    '2025-12-10' => ['Deep Learning (Derin Öğrenme) modelleri ve AI mimarisi üzerine projelere başladım.'],
    '2025-12-18' => ['PHP sitemdeki teknik hataları giderip backend mantığını sağlamlaştırdım.'],
    '2025-12-26' => ['Kali Linux ve siber güvenlik araçları üzerinde komut satırı pratikleri yaptım.'],
    '2025-12-30' => ['Web tabanlı Vision Board uygulamamı (React & Node.js altyapısı ile) kodladım.'],
    '2026-01-03' => ['Frontend tarafında modernleşme kararı alarak React (Hooks, Router, Context API) öğrenmeye başladım.'],
    '2026-01-05' => ['Kapsamlı bir E-ticaret Veritabanı Tasarımı (PostgreSQL) sürecine girdim.'],
    '2026-01-10' => ['E-ticaret projemin ER diyagramlarını çizmeye başladım.'],
    '2026-01-12' => ['Veritabanı projem için SQL Stored Procedure ve Trigger yapılarını kodladım.'],
    '2026-01-14' => ['E-ticaret projemin ER diyagramlarını SQL şemalarına dökerek tamamladım.'],
    '2026-01-15' => ['QR Menü projemin temellerini attım; restoranlar için dinamik çözüm üretme fikrini geliştirdim.'],
    '2026-01-18' => ['QR Menü projesinin veritabanı mimarisini modelledim ve REST API uçlarını (endpoints) tasarladım.'],
    '2026-01-20' => ['QR Menü projesinin dinamik menü ekleme ve listeleme özelliklerini kodladım.'],
    '2026-02-04' => [
        'StudyMatch (Study Buddy) web uygulamasının sistem mimarisini tasarladım.',
        'Projenin UI/UX tasarımlarını Figma üzerinde hazırladım.'
    ],
    '2026-02-10' => ['Next.js (App Router) mimarisiyle projelerimi daha profesyonel bir SSR seviyesine taşıdım.'],
    '2026-02-20' => ['Python (Matplotlib/NumPy) ile 3D matematiksel modelleme ve görselleştirme çalışmaları yaptım.'],
    '2026-03-01' => ['Kali Linux ortamımı optimize ettim ve sunucu yönetimi için shell scriptleri yazdım.'],
    '2026-03-02' => [
        '142 günlük yazılım geliştirme sürecimin kod analizini yaptım.',
        'Takvimime admin paneli ekledim, PDO kullanımıyla veritabanını SQLite ile bağladım.'
    ]
];

// Yazılımla alakalı doldurucu (filler) görevler havuzu (kendi ağzımdan "yaptım", "ettim")
$filler_tasks = [
    "Python OOP (Nesne Yönelimli Programlama) konseptlerini projemde uyguladım.",
    "Github üzerinde eski projelerimin kodlarını (refactoring) temizledim ve dokümante ettim.",
    "Linux terminal komutları pratiği yaptım; shell scripting ile otomasyonlar yazdım.",
    "SQL'de karmaşık JOIN işlemleri, View ve indexleme üzerine denemeler yaptım.",
    "Veri yapıları algoritma çözümleri (LeetCode/HackerRank) üzerine kod yazdım.",
    "Büyük ölçekli sistemler için API tasarımları (REST vs GraphQL) çalıştım.",
    "React projelerim için modern state management kütüphanelerini araştırdım.",
    "Yapay Zeka modellerini (TensorFlow/PyTorch) test etmek için ortam kurdum.",
    "Veri bilimi projeleri için test verilerini (Pandas/NumPy) işledim ve temizledim.",
    "Web Socketler kullanarak anlık veri aktarımı (real-time data) testleri yaptım.",
    "Ölçeklenebilirlik için Docker yapılandırmaları ve Container orkestrasyonu çalıştım.",
    "CI/CD pipelinelarını GitHub Actions ile entegre etme senaryoları denedim.",
    "Geliştirdiğim uygulamalar için Jest/Mocha ile birim testleri (unit tests) yazdım.",
    "Kimlik doğrulama sistemleri (JWT & OAuth2.0) entegrasyonlarını inceledim.",
    "NoSQL veritabanı (MongoDB/Redis) kullanım senaryolarını test ettim."
];

$past_days_to_show = 146; // Bugün dahil son 146 gün
$today = clone (new DateTime('2026-03-02')); 
$start_date = clone $today;
$start_date->modify("-$past_days_to_show days");
$current_date = clone $start_date;

mt_srand(2026);

echo "Veriler oluşturuluyor...\n";
$inserted = 0;

$stmt = $pdo->prepare("INSERT INTO tasks (task_date, description) VALUES (?, ?)");

while ($current_date <= $today) {
    $date_str = $current_date->format('Y-m-d');
    
    // Eğer o tarih için özel bir milestone varsa
    if (isset($special_milestones[$date_str])) {
        foreach ($special_milestones[$date_str] as $task_desc) {
            $stmt->execute([$date_str, $task_desc]);
            $inserted++;
        }
        
        $should_add_filler = mt_rand(1, 100) > 60; // %40 ihtimalle ekle
        if ($should_add_filler && count($special_milestones[$date_str]) < 3) {
            $filler = $filler_tasks[array_rand($filler_tasks)];
            $stmt->execute([$date_str, $filler]);
            $inserted++;
        }
    } 
    // Özel milestone YOKSA doldurucu ile tamamen yazılım dolu göster
    else {
        // En az 1, en fazla 4 görev
        $num_tasks = mt_rand(1, 4);
        
        if ($num_tasks == 1 && mt_rand(1, 100) > 40) {
            $num_tasks = 2; // Bir tık daha yoğun olsun
        }

        $keys = array_rand($filler_tasks, $num_tasks);
        
        if (is_array($keys)) {
            foreach ($keys as $k) {
                $stmt->execute([$date_str, $filler_tasks[$k]]);
                $inserted++;
            }
        } else {
            $stmt->execute([$date_str, $filler_tasks[$keys]]);
            $inserted++;
        }
    }
    
    $current_date->modify('+1 day');
}

echo "Toplam $inserted adet birinci tekil şahıs görev başarıyla eklendi!\n";
?>
