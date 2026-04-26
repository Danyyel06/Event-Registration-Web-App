<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title       = trim(htmlspecialchars($_POST['title']       ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $date        = trim(($_POST['date']                        ?? ''));
    $venue       = trim(htmlspecialchars($_POST['venue']       ?? ''));
    $capacity    = (int)(($_POST['capacity']                   ?? 0));

    $errors = [];
    if(empty($title))    $errors[] = 'Event title is required.';
    if(empty($date))     $errors[] = 'Event date is required.';
    if(empty($venue))    $errors[] = 'Venue is required.';
    if($capacity < 1)   $errors[] = 'Capacity must be at least 1.';

    if(!empty($date) && strtotime($date) < strtotime('today')){
        $errors[] = 'Event date cannot be in the past.';
    }

    if(!empty($errors)){
        $error = implode(' ', $errors);
    }else{
        $stmt = $pdo->prepare('INSERT INTO events (title, description, date, venue, capacity) VALUES (:title, :description, :date, :venue, :capacity)');
        $stmt->execute([':title' => $title, ':description' => $description, ':date' => $date, ':venue' => $venue, ':capacity' => $capacity]);
        $success = "Event '$title' created successfully!";
        $title = $description = $date = $venue = '';
        $capacity = 50;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event – Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --black:        #0a0a0a;
            --ink:          #1a1a1a;
            --ink-soft:     #525252;
            --ink-muted:    #737373;
            --ink-faint:    #a3a3a3;
            --border:       #e5e5e5;
            --border-light: #f0f0f0;
            --bg:           #fafafa;
            --surface:      #ffffff;
            --hover:        #f5f5f5;
            --font:         'Plus Jakarta Sans', sans-serif;
        }

        body { font-family: var(--font); background: var(--bg); color: var(--ink); min-height: 100vh; -webkit-font-smoothing: antialiased; }

        nav { background: var(--surface); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 100; }
        .nav-inner { max-width: 1100px; margin: 0 auto; padding: 0 2rem; height: 56px; display: flex; align-items: center; justify-content: space-between; }
        .nav-brand { display: flex; align-items: center; gap: 8px; text-decoration: none; }
        .brand-mark { width: 28px; height: 28px; background: var(--black); border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .brand-name { font-size: .875rem; font-weight: 700; color: var(--ink); }
        .brand-tag  { font-size: .68rem; color: var(--ink-faint); margin-left: 2px; }
        .nav-links { display: flex; align-items: center; gap: .125rem; }
        .nav-links a { font-size: .8rem; color: var(--ink-muted); text-decoration: none; padding: 6px 10px; border-radius: 6px; font-weight: 500; transition: color .15s, background .15s; }
        .nav-links a:hover { color: var(--ink); background: var(--hover); }
        .nav-links a.logout { border: 1px solid var(--border); color: var(--ink); font-weight: 600; margin-left: .5rem; }

        .wrap { max-width: 560px; margin: 0 auto; padding: 2.5rem 2rem 5rem; }

        .page-header { margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border); }
        .page-eyebrow { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .12em; color: var(--ink-faint); margin-bottom: .4rem; }
        .page-title { font-size: 1.75rem; font-weight: 800; color: var(--black); letter-spacing: -.03em; }

        .alert { display: flex; align-items: center; gap: 8px; border-radius: 6px; padding: .75rem 1rem; margin-bottom: 1.5rem; font-size: .83rem; font-weight: 500; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .alert-error   { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; animation: fadeUp .3s ease both; }

        .form-body { padding: 1.75rem 1.75rem 0; }

        .field { margin-bottom: 1.25rem; }
        .field:last-child { margin-bottom: 0; }
        .field label { display: block; font-size: .75rem; font-weight: 600; color: var(--ink); margin-bottom: .4rem; }

        .field input,
        .field textarea {
            width: 100%;
            padding: .625rem .875rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: .875rem;
            font-family: var(--font);
            color: var(--ink);
            background: var(--surface);
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .field textarea { min-height: 90px; resize: vertical; }

        .field input:focus,
        .field textarea:focus { border-color: var(--black); box-shadow: 0 0 0 2px rgba(10,10,10,.08); }

        .field input::placeholder,
        .field textarea::placeholder { color: var(--ink-faint); }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

        .divider { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: var(--ink-faint); padding: .875rem 1.75rem; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light); background: #fafafa; margin: 1.5rem -1.75rem; }

        .form-footer { padding: 1.25rem 1.75rem; border-top: 1px solid var(--border-light); background: #fafafa; margin-top: 1.75rem; display: flex; gap: .75rem; }

        .submit-btn { flex: 1; padding: .75rem; background: var(--black); color: white; border: none; border-radius: 6px; font-size: .875rem; font-family: var(--font); font-weight: 700; cursor: pointer; transition: opacity .15s; }
        .submit-btn:hover { opacity: .82; }

        .cancel-btn { padding: .75rem 1.25rem; background: var(--surface); color: var(--ink-soft); border: 1px solid var(--border); border-radius: 6px; font-size: .83rem; font-family: var(--font); font-weight: 600; cursor: pointer; text-decoration: none; display: flex; align-items: center; transition: background .15s; }
        .cancel-btn:hover { background: var(--hover); color: var(--ink); }

        @media (max-width: 540px) {
            .form-row { grid-template-columns: 1fr; }
            .wrap { padding: 1.75rem 1.25rem 4rem; }
            .form-body { padding: 1.25rem 1.25rem 0; }
            .divider { margin: 1.25rem -1.25rem; padding: .75rem 1.25rem; }
            .form-footer { padding: 1rem 1.25rem; }
        }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<nav>
    <div class="nav-inner">
        <a href="dashboard.php" class="nav-brand">
            <div class="brand-mark">🎓</div>
            <span class="brand-name">Faculty Events</span>
            <span class="brand-tag">Admin</span>
        </a>
        <div class="nav-links">
            <a href="dashboard.php">Dashboard</a>
            <a href="events.php">Events</a>
            <a href="participants.php">Participants</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
</nav>

<div class="wrap">
    <div class="page-header">
        <p class="page-eyebrow">Admin Panel</p>
        <h1 class="page-title">Create New Event</h1>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            <?= $success ?>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-error">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <?= $error ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="post" action="">
            <div class="form-body">
                <div class="field">
                    <label for="title">Event Title *</label>
                    <input type="text" id="title" name="title"
                           value="<?= htmlspecialchars($title ?? '') ?>"
                           placeholder="e.g. Annual Faculty Symposium 2025"
                           required>
                </div>

                <div class="field">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Brief description of the event..."><?= htmlspecialchars($description ?? '') ?></textarea>
                </div>

                <div class="divider">Event Details</div>

                <div class="form-row">
                    <div class="field">
                        <label for="date">Date *</label>
                        <input type="date" id="date" name="date"
                               value="<?= htmlspecialchars($date ?? '') ?>" required>
                    </div>
                    <div class="field">
                        <label for="capacity">Capacity *</label>
                        <input type="number" id="capacity" name="capacity"
                               min="1" max="5000"
                               value="<?= isset($capacity) ? (int)$capacity : 50 ?>"
                               required>
                    </div>
                </div>

                <div class="field">
                    <label for="venue">Venue *</label>
                    <input type="text" id="venue" name="venue"
                           value="<?= htmlspecialchars($venue ?? '') ?>"
                           placeholder="e.g. Main Auditorium, Engineering Block"
                           required>
                </div>
            </div>

            <div class="form-footer">
                <button type="submit" class="submit-btn">Create Event →</button>
                <a href="events.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>