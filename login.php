<?php
ini_set('display_errors', 1); error_reporting(E_ALL);

session_start();
require_once 'db.php';

// TEMPORARY: Auto-create the admin user if it doesn't exist
$checkUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($checkUsers == 0) {
    $hashedPassword = password_hash('password123', PASSWORD_BCRYPT);
    $insertAdmin = $pdo->prepare("INSERT INTO users (username, password) VALUES ('admin', :pass)");
    $insertAdmin->execute([':pass' => $hashedPassword]);
}

$error = '';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        // Check if user exists and verify password hash match
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Login</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: #fff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { margin-top: 0; color: #333; text-align: center; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #555; }
        input[type="text"], input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-login { background: #0056b3; color: white; border: none; padding: 12px; width: 100%; border-radius: 4px; cursor: pointer; font-size: 16px; font-weight: bold; }
        .btn-login:hover { background: #004494; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; text-align: center; font-weight: bold; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>System Login</h2>

    <?php if (!empty($error)): ?>
        <div class="error-msg"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" required autocomplete="off">
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn-login">Login</button>
    </form>
</div>

</body>
</html>