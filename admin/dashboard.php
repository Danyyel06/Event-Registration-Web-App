<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$total_events = $pdo->query('SELECT COUNT(*) FROM events')->fetchColumn();
$total_regs   = $pdo->query('SELECT COUNT(*) FROM registrations')->fetchColumn();
$upcoming     = $pdo->query('SELECT COUNT(*) FROM events WHERE date >= CURDATE()')->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard – Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        nav { background: #1B3A5C; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #93C5FD; text-decoration: none; margin-left: 1.5rem; }
        nav a:hover { color: white; }
        .wrap { max-width: 900px; margin: 2rem auto; padding: 0 1rem; }
        h1 { color: #1B3A5C; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
        .stat { background: white; padding: 1.5rem; border-radius: 8px; text-align: center; border-left: 4px solid #2563EB; }
        .stat .num { font-size: 2.5rem; font-weight: bold; color: #1B3A5C; }
        .stat .lbl { color: #6B7280; font-size: .9rem; margin-top: .3rem; }
        .btn { display: inline-block; margin-top: 2rem; padding: .75rem 1.5rem; background: #2563EB; color: white; border-radius: 6px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <nav>
        <strong>Faculty Events – Admin</strong>
        <div>
            <a href="events.php">Events</a>
            <a href="participants.php">Participants</a>
            <span style="margin-left: 2rem; color: #CBD5E1">
                Hello, <?= htmlspecialchars($_SESSION['admin_name']) ?>
            </span>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    <div class="wrap">
        <h1>Dashboard</h1>
        <div class="stats">
            <div class="stat">
                <div class="num"><?= $total_events ?></div>
                <div class="lbl">Total Events</div>
            </div>
            <div class="stat">
                <div class="num"><?= $upcoming ?></div>
                <div class="lbl">Upcoming Events</div>
            </div>
            <div class="stat">
                <div class="num"><?= $total_regs ?></div>
                <div class="lbl">Total Registrations</div>
            </div>
        </div>
        <a href="create_event.php" class="btn">+ Create New Event</a>
        <a href="events.php" class="btn" style="margin-left: 1rem;">View All Events</a>
    </div>
</body>
</html>