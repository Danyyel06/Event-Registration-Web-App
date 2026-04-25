<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/db.php';

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare('DELETE FROM events WHERE id = :id');
    $stmt->execute([':id' => $id]);
    header('Location: events.php?deleted=1');
    exit;
}

$events = $pdo->query('
    SELECT e.*, 
           COUNT(r.id) AS reg_count
    FROM events e
    LEFT JOIN registrations r ON r.event_id = e.id
    GROUP BY e.id
    ORDER BY e.date ASC
')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>All Events – Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        nav { background: #1B3A5C; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #93C5FD; text-decoration: none; margin-left: 1.5rem; }
        .wrap { max-width: 1000px; margin: 2rem auto; padding: 0 1rem; }
        h1 { color: #1B3A5C; }
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
        th { background: #1B3A5C; color: white; padding: .8rem 1rem; text-align: left; font-size: .9rem; }
        td { padding: .8rem 1rem; border-bottom: 1px solid #E5E7EB; font-size: .9rem; }
        tr:last-child td { border-bottom: none; }
        .full { color: #DC2626; font-weight: bold; }
        .open { color: #16A34A; }
        .btn { padding: .35rem .7rem; border-radius: 4px; text-decoration: none; font-size: .85rem; }
        .edit { background: #DBEAFE; color: #1D4ED8; }
        .del { background: #FEE2E2; color: #DC2626; }
        .view { background: #DCFCE7; color: #16A34A; }
        .notice { background: #DCFCE7; color: #166534; padding: .8rem 1rem; border-radius: 5px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav>
        <strong>Faculty Events – Admin</strong>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="create_event.php">New Event</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    <div class="wrap">
        <h1>All Events</h1>
        <?php if (isset($_GET['deleted'])): ?>
            <div class="notice">Event deleted successfully.</div>
        <?php endif; ?>
        <table>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Venue</th>
                <th>Registrations</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($events as $event): ?>
            <?php 
                $spots_left = $event['capacity'] - $event['reg_count'];
                $is_full    = $spots_left <= 0;
            ?>
            <tr>
                <td><strong><?= htmlspecialchars($event['title']) ?></strong></td>
                <td><?= date('D, d M Y', strtotime($event['date'])) ?></td>
                <td><?= htmlspecialchars($event['venue']) ?></td>
                <td>
                    <span class="<?= $is_full ? 'full' : 'open' ?>">
                        <?= $event['reg_count'] ?> / <?= $event['capacity'] ?>
                        <?= $is_full ? '(FULL)' : '(' . $spots_left . ' left)' ?>
                    </span>
                </td>
                <td>
                    <a href="participants.php?event_id=<?= $event['id'] ?>" class="btn view">View List</a>
                    <a href="edit_event.php?id=<?= $event['id'] ?>" class="btn edit">Edit</a>
                    <a href="events.php?delete=<?= $event['id'] ?>" class="btn del" onclick="return confirm('Delete this event and all its registrations?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>