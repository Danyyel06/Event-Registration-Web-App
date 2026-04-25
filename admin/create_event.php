<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/db.php';


$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $title   = trim(htmlspecialchars($_POST['title']   ?? ''));
    $description   = trim(htmlspecialchars($_POST['description']   ?? ''));
    $date   = trim(($_POST['date']   ?? ''));
    $venue   = trim(htmlspecialchars($_POST['venue']   ?? ''));
    $capacity   = (int)(($_POST['capacity']   ?? 0));

    $errors = [];
    if(empty($title)) $errors[] = 'Event title is required.';
    if(empty($date)) $errors[] = 'Event date is required.';
    if(empty($venue)) $errors[] = 'Venue is required.';
    if($capacity < 1) $errors[] = 'Capacity must be at least 1.';

    if(!empty($date) && strtotime($date) < strtotime('today')){
        $errors[] = 'Event date cannot be in the past';
    }

    if(!empty($errors)){
        $error = implode('', $errors);
    }else{
        $stmt = $pdo->prepare('
        INSERT INTO events (title, description, date, venue, capacity)
        VALUES (:title, :description, :date, :venue, :capacity)
        ');
    

    $stmt->execute([
        ':title'  => $title,
        ':description' => $description,
        ':date' => $date,
        ':venue' => $venue,
        ':capacity' => $capacity
    ]);

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
    <title>Create Event</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; margin: 0; }
        nav { background: #1B3A5C; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        nav a { color: #93C5FD; text-decoration: none; margin-left: 1.5rem; }
        .wrap { max-width: 600px; margin: 2rem auto; background: white; padding: 2rem; border-radius: 8px; }
        h2 { color: #1B3A5C; margin-top: 0; }
        label { display: block; margin: .8rem 0 .3rem; font-weight: bold; font-size: .9rem; color: #374151; }
        input, textarea, select { width: 100%; padding: .6rem .8rem; border: 1px solid #D1D5DB; border-radius: 5px; font-size: 1rem; box-sizing: border-box; }
        textarea { height: 100px; resize: vertical; }
        button { margin-top: 1.2rem; width: 100%; padding: .75rem; background: #2563EB; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; }
        .success { background: #DCFCE7; color: #166534; padding: .8rem 1rem; border-radius: 5px; margin-bottom: 1rem; }
        .error { background: #FEE2E2; color: #991B1B; padding: .8rem 1rem; border-radius: 5px; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <nav>
        <strong>Faculty Events – Admin</strong>
        <div>
            <a href="dashboard.php">Dashboard</a>
            <a href="events.php">All Events</a>
            <a href="logout.php">Logout</a>
        </div>
    </nav>
    <div class="wrap">
        <h2>Create New Event</h2>
        <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <form method="post" action="">
            <label>Event Title *</label>
            <input type="text" name="title" value="<?= htmlspecialchars($title ?? '') ?>" required>

            <label>Description</label>
            <textarea name="description"><?= htmlspecialchars($description ?? '') ?></textarea>

            <label>Date *</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date ?? '') ?>" required>

            <label>Venue *</label>
            <input type="text" name="venue" value="<?= htmlspecialchars($venue ?? '') ?>" required>

            <label>Capacity (max attendees) *</label>
            <input type="number" name="capacity" min="1" max="5000" value="<?= isset($capacity) ? (int)$capacity : 50 ?>" required>

            <button type="submit">Create Event</button>
        </form>
    </div>
</body>
</html>