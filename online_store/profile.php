<?php
session_start();
include 'db.php';

// Rating & Status Logic
if(isset($_POST['submit_rating'])) {
    $id = intval($_POST['order_id']);
    $rate = intval($_POST['rating']);
    $msg = mysqli_real_escape_string($conn, $_POST['comment']);
    $conn->query("UPDATE purchase_history SET rating = $rate, comment = '$msg', status = 'Completed' WHERE id = $id");
    echo "<script>alert('Review submitted!'); window.location='profile.php';</script>";
}

$history = $conn->query("SELECT * FROM purchase_history WHERE user_id = 1 ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | JustPick</title>
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f0f8ff; color: #333; }
        /* CONSISTENT NAVBAR */
        .navbar { background: #03a9f4; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; color: white; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar .brand { font-size: 24px; font-weight: bold; text-decoration: none; color: white; }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: white; text-decoration: none; font-size: 14px; padding: 8px 12px; border-radius: 5px; }
        .nav-links .active { background: #ffeb3b !important; color: #333 !important; font-weight: bold; }

        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        .card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; border-left: 5px solid #03a9f4; box-shadow: 0 4px 15px rgba(0,0,0,0.05); position: relative; }
        .status-badge { position: absolute; top: 20px; right: 20px; font-size: 11px; font-weight: bold; padding: 5px 12px; border-radius: 20px; text-transform: uppercase; }
        .Pending { background: #fff3cd; color: #856404; }
        .Completed { background: #d4edda; color: #155724; }
        
        .rate-box { margin-top: 15px; background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px solid #eee; }
        .btn-rate { background: #03a9f4; color: white; border: none; padding: 8px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="store.php" class="brand">🛍️ JustPick</a>
        <div class="nav-links">
            <a href="store.php">🏠 Home</a>
           <a href="cart.php">🛒 Cart <span id="cartCount" style="background:#ffeb3b; color:#333; padding:2px 6px; border-radius:50%; font-size:10px;"><?php echo count($_SESSION['cart']); ?></span></a>
            <a href="profile.php" class="active">👤 Profile</a>
            <span style="color:rgba(255,255,255,0.3)">|</span>
            <a href="dashboard.php" style="background: rgba(0,0,0,0.1);">⚙️ Admin</a>
        </div>
    </div>

    <div class="container">
        <h2 style="color: #0288d1; margin-bottom: 25px;">Order History & Status</h2>
        
        <?php if($history->num_rows > 0): ?>
            <?php while($row = $history->fetch_assoc()): ?>
                <div class="card">
                    <span class="status-badge <?php echo $row['status']; ?>"><?php echo $row['status']; ?></span>
                    <h4 style="margin:0 0 5px 0;"><?php echo $row['product_name']; ?></h4>
                    <p style="color:#0288d1; font-weight:bold; margin:0;">₱<?php echo number_format($row['amount'], 2); ?></p>
                    
                    <?php if($row['status'] == 'Pending'): ?>
                        <div class="rate-box">
                            <form method="POST">
                                <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                <div style="display:flex; gap:10px; margin-bottom:10px;">
                                    <select name="rating" required style="padding:8px; border-radius:4px; border:1px solid #ddd;">
                                        <option value="5">★★★★★ (5)</option>
                                        <option value="4">★★★★☆ (4)</option>
                                        <option value="3">★★★☆☆ (3)</option>
                                        <option value="2">★★☆☆☆ (2)</option>
                                        <option value="1">★☆☆☆☆ (1)</option>
                                    </select>
                                    <input type="text" name="comment" placeholder="Share your experience..." style="flex-grow:1; padding:8px; border:1px solid #ddd; border-radius:4px;">
                                </div>
                                <button type="submit" name="submit_rating" class="btn-rate">Confirm Order & Rate</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div style="margin-top:15px; color:#ffc107;">
                            <?php echo str_repeat("★", $row['rating']) . str_repeat("☆", 5-$row['rating']); ?>
                            <span style="color:#999; font-size:12px; margin-left:10px;">You rated this item</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div style="text-align:center; padding:50px; color:#ccc;">No purchase history found.</div>
        <?php endif; ?>
    </div>

</body>
</html>