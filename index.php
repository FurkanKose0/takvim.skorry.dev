<?php
require_once __DIR__ . '/db.php';

$aylar = [
    'January' => 'Ocak', 'February' => 'Şubat', 'March' => 'Mart',
    'April' => 'Nisan', 'May' => 'Mayıs', 'June' => 'Haziran',
    'July' => 'Temmuz', 'August' => 'Ağustos', 'September' => 'Eylül',
    'October' => 'Ekim', 'November' => 'Kasım', 'December' => 'Aralık'
];

$past_days_to_show = 146; // 146 gün geçmiş
$future_days_to_show = 180 - 146; // 34 gün de gelecek (toplam 180 gün karesi)

$today = new DateTime();

// Takvimin başlangıç tarihi
$start_date = clone $today;
$start_date->modify("-{$past_days_to_show} days");

// Takvimin bitiş tarihi (ileriki 34 gün)
$end_date = clone $today;
$end_date->modify("+{$future_days_to_show} days");

// Veritabanından tüm tasks'leri al (tarihe göre gruplayarak)
$stmt = $pdo->query("SELECT task_date, description FROM tasks ORDER BY task_date ASC");
$db_tasks = $stmt->fetchAll();

$tasks_by_date = [];
foreach ($db_tasks as $row) {
    if (!isset($tasks_by_date[$row['task_date']])) {
        $tasks_by_date[$row['task_date']] = [];
    }
    $tasks_by_date[$row['task_date']][] = $row['description'];
}

$calendar_data = [];
$current_date = clone $start_date;

while ($current_date <= $end_date) {
    $date_str = $current_date->format('Y-m-d');
    $month_en = $current_date->format('F');
    $display_date = $current_date->format('d ') . $aylar[$month_en] . $current_date->format(' Y, l');
    
    // İngilizce gün isimlerini Türkçeye çevir
    $display_date = str_replace(
        ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'],
        ['Pazartesi','Salı','Çarşamba','Perşembe','Cuma','Cumartesi','Pazar'],
        $display_date
    );

    // O gün için veritabanında iş var mı?
    $day_tasks = $tasks_by_date[$date_str] ?? [];
    
    $intensity = 0;
    if (count($day_tasks) == 1) $intensity = 1;
    elseif (count($day_tasks) == 2) $intensity = 2;
    elseif (count($day_tasks) >= 3) $intensity = 3;
    
    // Geçmiş mi, gelecek mi kontrolü
    $is_future = $current_date > $today;
    
    $calendar_data[$date_str] = [
        'date' => $date_str,
        'display_date' => $display_date,
        'tasks' => $day_tasks,
        'intensity' => $intensity,
        'is_future' => $is_future
    ];
    
    $current_date->modify('+1 day');
}

// Aylara göre gruplama işlemleri
$start_month = clone $start_date;
$start_month->modify('first day of this month');

$end_month = clone $end_date;
$end_month->modify('last day of this month');

$calendar_months = [];
$curr_m = clone $start_month;

while ($curr_m <= $end_month) {
    $m_key = $curr_m->format('Y-m');
    $m_name = $aylar[$curr_m->format('F')] . ' ' . $curr_m->format('Y');
    
    $num_days = $curr_m->format('t');
    $pad_start = $curr_m->format('N') - 1; // 1 (Pzt) -> 0 boşluk
    
    $days = [];
    $has_valid_day = false; // Bu ayın gösterilecek (180 gün içinde kalan) en az bir günü var mı?
    
    for ($i = 0; $i < $pad_start; $i++) {
        $days[] = null; // empty padding
    }
    
    for ($d = 1; $d <= $num_days; $d++) {
        $date_str = $m_key . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
        if (isset($calendar_data[$date_str])) {
            $days[] = $calendar_data[$date_str];
            $has_valid_day = true;
        } else {
            $days[] = ['out_of_range' => true, 'day_num' => $d];
        }
    }
    
    if ($has_valid_day) {
        $calendar_months[] = [
            'name' => $m_name,
            'days' => $days
        ];
    }
    
    $curr_m->modify('first day of next month');
}

// JSON formatında frontend'e aktarmak için
$js_calendar_data = json_encode($calendar_data);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Çalışma Takvimi - 180 Gün</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #09090b;
            --surface: #18181b;
            --border: #27272a;
            --text: #fafafa;
            --text-muted: #a1a1aa;
            --primary: #8b5cf6;
            --primary-hover: #a78bfa;
            --primary-light: rgba(139, 92, 246, 0.15);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 60px 20px;
        }
        .admin-link {
            position: absolute;
            top: 20px;
            right: 20px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            padding: 8px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: var(--surface);
            transition: all 0.2s;
        }
        .admin-link:hover {
            border-color: var(--primary);
            color: var(--text);
        }
        .header {
            text-align: center;
            margin-bottom: 60px;
            margin-top: 20px;
        }
        .header h1 {
            font-size: 3.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #a78bfa 0%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
            letter-spacing: -1px;
        }
        .header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        .calendar-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }
        .month {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .month:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: rgba(139, 92, 246, 0.3);
        }
        .month-name {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--text);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .month-name::before {
            content: '';
            display: block;
            width: 8px;
            height: 20px;
            background: var(--primary);
            border-radius: 4px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
        }
        .day-label {
            text-align: center;
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            padding-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .day {
            aspect-ratio: 1;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            user-select: none;
        }
        .day.empty {
            visibility: hidden;
        }
        .day.out-of-range {
            background: rgba(255, 255, 255, 0.02);
            color: rgba(255, 255, 255, 0.1);
            cursor: default;
        }
        .day.future {
            background: transparent;
            color: rgba(255, 255, 255, 0.2);
            border: 1px dashed rgba(255, 255, 255, 0.1);
        }
        .day.future:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.3);
            color: rgba(255, 255, 255, 0.5);
        }
        .day.no-work {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text-muted);
            border: 1px solid transparent;
        }
        .day.no-work:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: rgba(255,255,255,0.1);
        }
        
        .day.intensity-1 {
            background: rgba(139, 92, 246, 0.2);
            color: #d8b4fe;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }
        .day.intensity-1:hover { 
            background: rgba(139, 92, 246, 0.4); 
            transform: scale(1.15); 
            z-index: 10; 
            box-shadow: 0 0 15px rgba(139, 92, 246, 0.3);
        }
        
        .day.intensity-2 {
            background: rgba(139, 92, 246, 0.5);
            color: #fff;
            border: 1px solid rgba(139, 92, 246, 0.6);
        }
        .day.intensity-2:hover { 
            background: rgba(139, 92, 246, 0.7); 
            transform: scale(1.15); 
            z-index: 10; 
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.5); 
        }
        
        .day.intensity-3 {
            background: #8b5cf6;
            color: #fff;
            border: 1px solid #a78bfa;
            box-shadow: 0 0 10px rgba(139, 92, 246, 0.4);
        }
        .day.intensity-3:hover { 
            background: #9333ea; 
            transform: scale(1.15); 
            z-index: 10; 
            box-shadow: 0 0 25px rgba(139, 92, 246, 0.7); 
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 100;
            padding: 20px;
        }
        .modal-overlay.active {
            opacity: 1;
            pointer-events: all;
        }
        .modal-content {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 24px;
            width: 100%;
            max-width: 500px;
            padding: 40px;
            transform: translateY(20px) scale(0.95);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .modal-overlay.active .modal-content {
            transform: translateY(0) scale(1);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 25px;
        }
        .modal-title-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .modal-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
        }
        .modal-subtitle {
            color: var(--primary);
            font-weight: 500;
            font-size: 0.95rem;
        }
        .close-btn {
            background: #27272a;
            border: none;
            color: var(--text-muted);
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .close-btn:hover { 
            background: #3f3f46;
            color: var(--text);
            transform: rotate(90deg);
        }
        
        .task-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .task-item {
            background: rgba(255,255,255,0.02);
            padding: 18px;
            border-radius: 16px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
            border: 1px solid rgba(255,255,255,0.05);
            transition: all 0.2s;
        }
        .task-item:hover {
            border-color: rgba(139, 92, 246, 0.4);
            background: rgba(139, 92, 246, 0.05);
            transform: translateX(5px);
        }
        .task-icon {
            color: var(--primary);
            flex-shrink: 0;
            margin-top: 2px;
        }
        .task-text {
            color: var(--text);
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .empty-state {
            text-align: center;
            padding: 40px 0;
        }
        .empty-icon {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        .empty-text {
            color: var(--text-muted);
            font-size: 1.1rem;
        }
        
        /* Stats Bar */
        .stats-bar {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-muted);
            background: var(--surface);
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid var(--border);
        }
        .stat-color {
            width: 12px;
            height: 12px;
            border-radius: 3px;
        }
    </style>
</head>
<body>

<a href="admin.php" class="admin-link">Yönetim Paneli</a>

<div class="container">
    <div class="header">
        <h1>Çalışma Günlüğü</h1>
        <p>180 günlük aktif çalışma durumu grafiği. Geçmiş 146 gün ve gelecek.</p>
    </div>

    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-color" style="border: 1px dashed rgba(255,255,255,0.2);"></div>
            Gelecek
        </div>
        <div class="stat-item">
            <div class="stat-color" style="background: rgba(255, 255, 255, 0.04);"></div>
            Dinlenme
        </div>
        <div class="stat-item">
            <div class="stat-color" style="background: rgba(139, 92, 246, 0.3);"></div>
            Hafif
        </div>
        <div class="stat-item">
            <div class="stat-color" style="background: rgba(139, 92, 246, 0.6);"></div>
            Normal
        </div>
        <div class="stat-item">
            <div class="stat-color" style="background: #8b5cf6; box-shadow: 0 0 5px #8b5cf6;"></div>
            Yoğun
        </div>
    </div>

    <div class="calendar-container">
        <?php foreach ($calendar_months as $month): ?>
            <div class="month">
                <div class="month-name"><?php echo htmlspecialchars($month['name']); ?></div>
                <div class="grid">
                    <div class="day-label">Pzt</div>
                    <div class="day-label">Sal</div>
                    <div class="day-label">Çar</div>
                    <div class="day-label">Per</div>
                    <div class="day-label">Cum</div>
                    <div class="day-label">Cmt</div>
                    <div class="day-label">Paz</div>
                    
                    <?php foreach ($month['days'] as $day): ?>
                        <?php if ($day === null): ?>
                            <div class="day empty"></div>
                        <?php elseif (isset($day['out_of_range'])): ?>
                            <div class="day out-of-range"><?= $day['day_num'] ?></div>
                        <?php else: ?>
                            <?php 
                                $class = 'no-work';
                                if ($day['is_future']) {
                                    $class = 'future';
                                    if ($day['intensity'] > 0) { // Gelecekte planlanmış iş varsa
                                        if ($day['intensity'] == 1) $class = 'intensity-1';
                                        elseif ($day['intensity'] == 2) $class = 'intensity-2';
                                        elseif ($day['intensity'] == 3) $class = 'intensity-3';
                                    }
                                } else {
                                    if ($day['intensity'] == 1) $class = 'intensity-1';
                                    elseif ($day['intensity'] == 2) $class = 'intensity-2';
                                    elseif ($day['intensity'] == 3) $class = 'intensity-3';
                                }
                            ?>
                            <div class="day <?= $class ?>" onclick="openModal('<?= $day['date'] ?>')" title="<?= $day['display_date'] ?>">
                                <?= date('j', strtotime($day['date'])) ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="modalOverlay" onclick="closeModal(event)">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <div class="modal-title-group">
                <div class="modal-title" id="modalTitle">Detaylar</div>
                <div class="modal-subtitle" id="modalSubtitle">Neler yapıldı?</div>
            </div>
            <button class="close-btn" onclick="closeModal()">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M13 1L1 13M1 1L13 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
        </div>
        <div id="modalBody"></div>
        
        <div style="margin-top: 20px; text-align: right;">
            <a href="admin.php" class="admin-link" style="position: static; display: inline-block;">Bu Güne Görev Ekle</a>
        </div>
    </div>
</div>

<script>
    const calendarData = <?= $js_calendar_data ?>;
    const modalOverlay = document.getElementById('modalOverlay');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');

    function openModal(dateStr) {
        const data = calendarData[dateStr];
        if (!data) return;

        modalTitle.textContent = data.display_date;
        
        let html = '';
        if (data.tasks.length > 0) {
            html += '<ul class="task-list">';
            data.tasks.forEach(task => {
                html += `
                    <li class="task-item">
                        <div class="task-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8 12L11 15L16 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="task-text">${task}</div>
                    </li>
                `;
            });
            html += '</ul>';
        } else {
            if (data.is_future) {
                html = `
                    <div class="empty-state">
                        <div class="empty-icon">⏳</div>
                        <div class="empty-text">Bu tarih henüz gelmedi. Bir görev planlayabilirsiniz.</div>
                    </div>
                `;
            } else {
                html = `
                    <div class="empty-state">
                        <div class="empty-icon">☕</div>
                        <div class="empty-text">Bu gün herhangi bir çalışma kaydedilmedi.</div>
                    </div>
                `;
            }
        }

        modalBody.innerHTML = html;
        modalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(e) {
        if (e && e.target !== modalOverlay && e.target.closest('.close-btn') === null) {
            return;
        }
        modalOverlay.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay.classList.contains('active')) {
            closeModal();
        }
    });
</script>

</body>
</html>
