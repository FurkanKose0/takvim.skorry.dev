<?php
session_start();
require_once __DIR__ . '/db.php';

// Auth Check
if (!isset($_SESSION['admin_logged_in'])) {
    header('Location: admin-login.php');
    exit;
}

$action = $_GET['action'] ?? '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task'])) {
        $date = $_POST['task_date'] ?? '';
        $desc = $_POST['description'] ?? '';
        
        if ($date && $desc) {
            $stmt = $pdo->prepare("INSERT INTO tasks (task_date, description) VALUES (?, ?)");
            $stmt->execute([$date, $desc]);
            $msg = "Görev başarıyla eklendi!";
        } else {
            $msg = "Lütfen tüm alanları doldurun.";
        }
    } elseif (isset($_POST['delete_task'])) {
        $id = $_POST['task_id'] ?? '';
        if ($id) {
            $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
            $stmt->execute([$id]);
            $msg = "Görev silindi!";
        }
    }
}

// Tüm görevleri getir (en yeniden eskiye)
$stmt = $pdo->query("SELECT * FROM tasks ORDER BY task_date DESC, id DESC");
$tasks = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takvim Yönetim Paneli</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #09090b;
            --surface: #18181b;
            --border: #27272a;
            --text: #fafafa;
            --text-muted: #a1a1aa;
            --primary: #8b5cf6;
            --primary-hover: #a78bfa;
            --danger: #ef4444;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-bottom: 1px solid var(--border);
            padding-bottom: 20px;
        }
        h1, h2 { margin: 0; }
        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn:hover { background: var(--primary-hover); }
        .btn-danger { background: var(--danger); padding: 6px 12px; font-size: 0.8rem; }
        .btn-danger:hover { background: #f87171; }
        
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        input, textarea {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 10px;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
        }
        textarea { resize: vertical; min-height: 80px; }
        input:focus, textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .msg {
            background: rgba(139, 92, 246, 0.2);
            border: 1px solid var(--primary);
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            color: #d8b4fe;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        th {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
        }
        tr:hover {
            background: rgba(255,255,255,0.02);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Takvim Yönetim Paneli</h1>
        <div>
            <a href="index.php" class="btn" style="background:#27272a; margin-right:10px;">Takvime Dön</a>
            <a href="admin-login.php?logout=1" class="btn btn-danger">Çıkış Yap</a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="msg"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>

    <div class="card">
        <h2>Yeni Görev Ekle</h2>
        <form method="POST" style="margin-top: 20px;">
            <div class="form-group">
                <label>Tarih</label>
                <input type="date" name="task_date" required>
            </div>
            <div class="form-group">
                <label>Yapılan İş / Açıklama</label>
                <textarea name="description" required placeholder="Bugün neler yaptınız?"></textarea>
            </div>
            <button type="submit" name="add_task" class="btn">Görevi Kaydet</button>
        </form>
    </div>

    <div class="card">
        <h2>Kayıtlı Görevler</h2>
        <div style="overflow-x: auto; margin-top: 20px;">
            <table>
                <thead>
                    <tr>
                        <th width="120">Tarih</th>
                        <th>Açıklama</th>
                        <th width="80">İşlem</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tasks as $task): ?>
                        <tr>
                            <td><?= htmlspecialchars($task['task_date']) ?></td>
                            <td><?= htmlspecialchars($task['description']) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Bu görevi silmek istediğinize emin misiniz?');">
                                    <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                    <button type="submit" name="delete_task" class="btn btn-danger">Sil</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
