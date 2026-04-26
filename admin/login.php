<?php
session_start();

if(isset($_SESSION['admin_id'])){
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/../config/db.php'; 

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim(htmlspecialchars($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if(empty($username) || empty($password)){
        $error = 'Please enter both username and password.';
    }else{
        $stmt = $pdo->prepare('SELECT id, username, password FROM admins WHERE username = :username LIMIT 1');
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['username'];
            header('Location:dashboard.php');
            exit;
        }else{
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login – Faculty Events</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --black:     #0a0a0a;
            --ink:       #1a1a1a;
            --ink-soft:  #525252;
            --ink-muted: #737373;
            --ink-faint: #a3a3a3;
            --border:    #e5e5e5;
            --bg:        #fafafa;
            --surface:   #ffffff;
            --font:      'Plus Jakarta Sans', sans-serif;
        }

        body {
            font-family: var(--font);
            background: var(--bg);
            color: var(--ink);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.25rem;
            -webkit-font-smoothing: antialiased;
        }

        /* Subtle grid bg */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(var(--border) 1px, transparent 1px),
                linear-gradient(90deg, var(--border) 1px, transparent 1px);
            background-size: 40px 40px;
            opacity: 0.4;
            pointer-events: none;
        }

        .wrapper {
            position: relative;
            width: 100%;
            max-width: 380px;
        }

        .eyebrow {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo-mark {
            width: 40px;
            height: 40px;
            background: var(--black);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            margin: 0 auto 1rem;
        }

        .eyebrow h1 {
            font-size: 1.375rem;
            font-weight: 800;
            color: var(--black);
            letter-spacing: -0.025em;
            margin-bottom: 0.3rem;
        }

        .eyebrow p {
            font-size: 0.83rem;
            color: var(--ink-muted);
        }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            animation: slideUp 0.35s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .card-body {
            padding: 1.75rem 1.75rem 0;
        }

        .error-box {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 0.75rem 0.875rem;
            margin-bottom: 1.25rem;
            color: #991b1b;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .field {
            margin-bottom: 1rem;
        }

        .field label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 0.375rem;
        }

        .field input {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 0.875rem;
            font-family: var(--font);
            color: var(--ink);
            background: var(--surface);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .field input:focus {
            border-color: var(--black);
            box-shadow: 0 0 0 2px rgba(10,10,10,0.08);
        }

        .field input::placeholder { color: var(--ink-faint); }

        .card-footer {
            padding: 1.25rem 1.75rem;
            border-top: 1px solid #f0f0f0;
            background: #fafafa;
            margin-top: 1.75rem;
        }

        .submit-btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--black);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-family: var(--font);
            font-weight: 700;
            cursor: pointer;
            transition: opacity 0.15s;
        }

        .submit-btn:hover { opacity: 0.82; }

        .footer-note {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.72rem;
            color: var(--ink-faint);
        }

        @media (max-width: 420px) {
            .card-body { padding: 1.25rem 1.25rem 0; }
            .card-footer { padding: 1rem 1.25rem; }
        }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="eyebrow">
        <div class="logo-mark">🎓</div>
        <h1>Faculty Events</h1>
        <p>Administrator portal</p>
    </div>

    <div class="card">
        <div class="card-body">
            <?php if ($error): ?>
                <div class="error-box">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="field">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username"
                           value="<?= htmlspecialchars($username ?? '') ?>"
                           placeholder="Enter your username"
                           required autofocus>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="••••••••"
                           required>
                </div>
        </div>

        <div class="card-footer">
                <button type="submit" class="submit-btn">Sign in →</button>
            </form>
        </div>
    </div>

    <p class="footer-note">Faculty Events &copy; <?= date('Y') ?></p>
</div>

</body>
</html>