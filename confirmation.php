<?php
session_start();

if (!isset($_SESSION['reg_code'])) {
    header('Location: register.php');
    exit;
}

$reg_code    = $_SESSION['reg_code'];
$event_title = $_SESSION['event_title'];
$student_name = $_SESSION['student_name'];

unset($_SESSION['reg_code'], $_SESSION['event_title'], $_SESSION['student_name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Confirmed!</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 2.5rem; border-radius: 12px; max-width: 500px; width: 100%; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,.08); }
        .tick { font-size: 3.5rem; color: #16A34A; margin-bottom: .5rem; }
        h2 { color: #166534; margin: 0 0 .5rem; }
        .name { color: #6B7280; margin-bottom: 1.5rem; }
        .code-box { background: #EDE9FE; border: 2px dashed #7C3AED; border-radius: 8px; padding: 1.2rem; margin: 1.2rem 0; }
        .code-label { font-size: .85rem; color: #5B21B6; margin-bottom: .5rem; }
        .code { font-size: 2rem; font-weight: bold; letter-spacing: .2em; color: #4C1D95; font-family: monospace; }
        .event-name { font-size: 1rem; color: #374151; margin-bottom: 1rem; }
        .instructions { background: #DBEAFE; border-radius: 8px; padding: 1rem; text-align: left; font-size: .88rem; color: #1D4ED8; margin-top: 1rem; }
        .instructions li { margin-bottom: .4rem; }
        .btn { display: inline-block; margin-top: 1.5rem; padding: .75rem 2rem; background: #2563EB; color: white; border-radius: 6px; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <div class="tick">✓</div>
        <h2>You are registered!</h2>
        <p class="name">
            Hello, <strong><?= htmlspecialchars($student_name) ?></strong>
        </p>
        <p class="event-name">
            You have successfully registered for:<br>
            <strong><?= htmlspecialchars($event_title) ?></strong>
        </p>
        <div class="code-box">
            <div class="code-label">Your Registration Code</div>
            <div class="code"><?= htmlspecialchars($reg_code) ?></div>
        </div>
        <ul class="instructions">
            <li>Screenshot or write down your code – you will need it at the event.</li>
            <li>Present this code at the entrance for verification.</li>
            <li>Your code is unique to you. Do not share it.</li>
        </ul>
        <a href="events_list.php" class="btn">Back to Events</a>
    </div>
</body>
</html>