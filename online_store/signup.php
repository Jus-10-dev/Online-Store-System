<?php
include 'db.php';
$error_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = mysqli_real_escape_string($conn, $_POST['username']);
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // 1. I-check muna kung ang username ay may kapareho na sa database
    $check_user = $conn->query("SELECT * FROM users WHERE username = '$user'");
    
    if ($check_user->num_rows > 0) {
        $error_msg = "The username '$user' is already taken!";
    } else {
        // 2. I-insert ang data nang walang email column
        $sql = "INSERT INTO users (username, password) VALUES ('$user', '$pass')";
        
        if ($conn->query($sql)) {
            header("Location: login.php");
            exit();
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up | JustPick</title>
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #e3f2fd; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); width: 350px; border-top: 5px solid #03a9f4; }
        h2 { text-align: center; color: #0288d1; margin-bottom: 25px; font-weight: 700; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #bbdefb; border-radius: 8px; box-sizing: border-box; outline: none; transition: 0.3s; }
        input:focus { border-color: #03a9f4; box-shadow: 0 0 5px rgba(3, 169, 244, 0.2); }
        button { width: 100%; padding: 12px; background: #03a9f4; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 16px; margin-top: 10px; }
        button:hover { background: #0288d1; }
        .error { background: #ffebee; color: #c62828; padding: 10px; border-radius: 8px; font-size: 12px; text-align: center; margin-bottom: 15px; border: 1px solid #ffcdd2; }
        .link { text-align: center; display: block; margin-top: 20px; color: #0288d1; text-decoration: none; font-size: 13px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Create Admin</h2>
        
        <?php if($error_msg != ""): ?>
            <div class="error"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
            <a href="login.php" class="link">Already have an account? Login</a>
        </form>
    </div>
</body>
</html>