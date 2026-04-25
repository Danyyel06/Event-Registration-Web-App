<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';

$id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM events WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$event = $stmt->fetch();

if (!$event) {
    header('Location: events.php');
    exit;
}

$success = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim(htmlspecialchars($_POST['title']       ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $date        = trim($_POST['date']                         ?? '');
    $venue       = trim(htmlspecialchars($_POST['venue']       ?? ''));
    $capacity    = (int)($_POST['capacity']                    ?? 0);

    if (empty($title) || empty($date) || empty($venue) || $capacity < 1) {
        $error = 'All required fields must be filled in.';
    } else {
        $stmt = $pdo->prepare('
            UPDATE events 
            SET title = :title, description = :description, 
                date = :date, venue = :venue, capacity = :capacity 
            WHERE id = :id
        ');
        
        $stmt->execute([
            ':title'       => $title,
            ':description' => $description,
            ':date'        => $date,
            ':venue'       => $venue,
            ':capacity'    => $capacity,
            ':id'          => $id
        ]);

        $event = array_merge($event, compact('title', 'description', 'date', 'venue', 'capacity'));
        $success = 'Event updated successfully!';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        nav { background: #1B3A5C; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #93C5FD; text-decoration: none; margin-left: 1.5rem; }
        .wrap { max-width: 600px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; }
        h2 { color: #1B3A5C; margin-top: 0; }
        label { display: block; margin: .8rem 0 .3rem; font-weight: bold; font-size: .9rem; color: #374151; }
        input, textarea { width: 100%; padding: .6rem .8rem; border: 1px solid #D1D5DB; border-radius: 5px; font-size: 1rem; box-sizing: border-box; }
        textarea { height: 100px; resize: vertical; }
        button { margin-top: 1.2rem; padding: .75rem 2rem; background: #2563EB; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; }
        .success { background: #DCFCE7; color: #166534; padding: .8rem 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .error { background: #FEE2E2; color: #991B1B; padding: .8rem 1rem; border-radius: 5px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav>
        <strong>Faculty Events – Admin</strong>
        <div><a href="events.php">All Events</a><a href="logout.php">Logout</a></div>
    </nav>
    <div class="wrap">
        <h2>Edit Event</h2>
        <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <form method="post" action="">
            <label>Event Title *</label>
            <input type="text" name="title" value="<?= htmlspecialchars($event['title']) ?>" required>

            <label>Description</label>
            <textarea name="description"><?= htmlspecialchars($event['description']) ?></textarea>

            <label>Date *</label>
            <input type="date" name="date" value="<?= htmlspecialchars($event['date']) ?>" required>

            <label>Venue *</label>
            <input type="text" name="venue" value="<?= htmlspecialchars($event['venue']) ?>" required>

            <label>Capacity *</label>
            <input type="number" name="capacity" min="1" value="<?= (int)$event['capacity'] ?>" required>

            <button type="submit">Save Changes</button>
            <a href="events.php" style="margin-left: 1rem; color: #6B7280;">Cancel</a>
        </form>
    </div>
</body>
</html>