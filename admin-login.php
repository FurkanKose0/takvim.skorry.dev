<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin-login.php');
    exit;
}

if (isset($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı!';
        }
    } else {
        $error = 'Lütfen tüm alanları doldurun.';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .login-box {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        h1 { margin-top: 0; margin-bottom: 20px; font-size: 1.8rem; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; margin-bottom: 8px; color: var(--text-muted); font-size: 0.9rem; }
        input {
            width: 100%;
            background: var(--bg);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 12px;
            border-radius: 6px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            box-sizing: border-box;
        }
        input:focus { outline: none; border-color: var(--primary); }
        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            font-size: 1rem;
            margin-top: 10px;
        }
        .btn:hover { background: var(--primary-hover); }
        .error {
            color: var(--danger);
            margin-bottom: 15px;
            font-size: 0.9rem;
            background: rgba(239, 68, 68, 0.1);
            padding: 10px;
            border-radius: 6px;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
    </style>
</head>
<body>

<div class="login-box">
    <h1>Takvim Yönetimi</h1>
    <p style="color: var(--text-muted); margin-bottom:25px;">Giriş yapmak için bilgilerinizi girin.</p>
    
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Kullanıcı Adı</label>
            <input type="text" name="username" required autofocus value="admin">
        </div>
        <div class="form-group">
            <label>Şifre</label>
            <input type="password" name="password" required value="password">
        </div>
        <button type="submit" class="btn">Giriş Yap</button>
    </form>
    
    <div style="margin-top:20px; text-align:left; color:var(--text-muted); font-size:0.8rem; background:rgba(255,255,255,0.05); padding:10px; border-radius:6px;">
        <p style="margin:0 0 5px 0"><b>Demo Bilgileri:</b></p>
        <p style="margin:0">Kullanıcı: <code>admin</code><br>Şifre: <code>password</code></p>
    </div>
</div>

</body>
</html>
