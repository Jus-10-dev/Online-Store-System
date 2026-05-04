<?php
session_start();
include 'db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    $result = $conn->query("SELECT * FROM users WHERE username='$user'");
    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['admin_user'] = $user;
            header("Location: dashboard.php");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | JustPick</title>
    <style>
        body { margin: 0; font-family: 'Segoe UI', sans-serif; background: #e3f2fd; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 350px; border-top: 5px solid #03a9f4; }
        h2 { text-align: center; color: #0288d1; margin-bottom: 25px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #bbdefb; border-radius: 8px; box-sizing: border-box; outline: none; }
        button { width: 100%; padding: 12px; background: #03a9f4; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; }
        button:hover { background: #0288d1; }
        .link { text-align: center; display: block; margin-top: 20px; color: #0288d1; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Admin Login</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <a href="signup.php" class="link">New admin? Sign up here</a>
        </form>
    </div>
</body>
</html>