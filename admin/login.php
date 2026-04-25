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
        $error = 'Please enter both username and passowrd.';
    }else{
        $stmt = $pdo ->prepare('SELECT id, username, password FROM admins WHERE username = :username LIMIT 1');
        $stmt -> execute([':username' => $username]);
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
    <style>
        body { font-family: Arial, sans-serif; background: #f3f4f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,.1); width: 100%; max-width: 380px; }
        h2 { margin: 0 0 1.5rem; color: #1B3A5C; text-align: center; }
        label { display: block; font-size: .85rem; color: #374151; margin-bottom: .3rem; font-weight: bold; }
        input { width: 100%; padding: .6rem .8rem; border: 1px solid #D1D5DB; border-radius: 5px; font-size: 1rem; box-sizing: border-box; margin-bottom: 1rem; }
        button { width: 100%; padding: .75rem; background: #1B3A5C; color: white; border: none; border-radius: 5px; font-size: 1rem; cursor: pointer; }
        button:hover { background: #2563EB; }
        .error { background: #FEE2E2; color: #991B1B; padding: .7rem 1rem; border-radius: 5px; margin-bottom: 1rem; font-size: .9rem; }
    </style>
</head>
<body>
<div class="card">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post" action="">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required autofocus>
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Log In</button>
    </form>
</div>
</body>
</html>