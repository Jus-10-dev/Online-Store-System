<?php
session_start();
include 'db.php';

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

// --- Logic: Plus / Minus / Remove ---
if (isset($_GET['action'])) {
    $p_id = intval($_GET['id']);
    $action = $_GET['action'];
    if ($action == 'plus') {
        $p_check = $conn->query("SELECT stock FROM products WHERE id = $p_id")->fetch_assoc();
        if ($p_check && $_SESSION['cart'][$p_id] < $p_check['stock']) { $_SESSION['cart'][$p_id]++; }
    } 
    elseif ($action == 'minus') {
        if (isset($_SESSION['cart'][$p_id]) && $_SESSION['cart'][$p_id] > 1) { $_SESSION['cart'][$p_id]--; } 
        else { unset($_SESSION['cart'][$p_id]); }
    } 
    elseif ($action == 'remove') { unset($_SESSION['cart'][$p_id]); }
    header("Location: cart.php"); exit();
}

// --- Logic: Checkout ---
if (isset($_POST['checkout_selected']) && !empty($_POST['selected_items'])) {
    foreach ($_POST['selected_items'] as $p_id) {
        $p_id = intval($p_id);
        $qty = $_SESSION['cart'][$p_id];
        $p = $conn->query("SELECT * FROM products WHERE id = $p_id")->fetch_assoc();
        if ($p && $p['stock'] >= $qty) {
            $total = $p['price'] * $qty;
            $conn->query("UPDATE products SET stock = stock - $qty, sold = sold + $qty WHERE id = $p_id");
            $conn->query("INSERT INTO purchase_history (user_id, product_name, amount, status) VALUES (1, '{$p['product_name']} x$qty', '$total', 'Pending')");
            unset($_SESSION['cart'][$p_id]);
        }
    }
    echo "<script>alert('Checkout Successful!'); window.location='profile.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart | JustPick</title>
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f0f8ff; color: #333; }
        /* CONSISTENT NAVBAR */
        .navbar { background: #03a9f4; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; color: white; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar .brand { font-size: 24px; font-weight: bold; text-decoration: none; color: white; }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: white; text-decoration: none; font-size: 14px; padding: 8px 12px; border-radius: 5px; }
        .nav-links .active { background: #ffeb3b !important; color: #333 !important; font-weight: bold; }
        
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        .cart-card { background: white; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden; border-top: 5px solid #03a9f4; }
        .cart-header { padding: 20px; background: #fff; border-bottom: 1px solid #eee; font-weight: bold; color: #0288d1; display: flex; justify-content: space-between; align-items: center; }
        
        .cart-item { display: flex; align-items: center; padding: 20px; border-bottom: 1px solid #f0f0f0; }
        .item-chk { width: 18px; height: 18px; cursor: pointer; margin-right: 15px; accent-color: #03a9f4; }
        .item-img { width: 70px; height: 70px; object-fit: contain; background: #f9f9f9; border-radius: 8px; }
        .item-info { flex-grow: 1; padding-left: 20px; }
        .qty-control { display: flex; align-items: center; gap: 12px; margin: 0 25px; }
        .btn-qty { background: #fff; border: 1px solid #ddd; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; text-decoration: none; color: #333; border-radius: 4px; font-size: 12px; }
        
        .total-section { padding: 25px; display: flex; justify-content: space-between; align-items: center; background: #fafafa; border-top: 1px solid #eee; }
        .btn-checkout { background: #03a9f4; color: white; padding: 12px 35px; border:none; border-radius: 6px; font-weight: bold; cursor:pointer; }
        .btn-checkout:disabled { background: #ccc; cursor: not-allowed; }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="store.php" class="brand">🛍️ JustPick</a>
        <div class="nav-links">
            <a href="store.php">🏠 Home</a>
            <a href="cart.php" class="active">🛒 Cart <span style="background:#ffeb3b; color:#333; padding:2px 6px; border-radius:50%; font-size:10px;"><?php echo count($_SESSION['cart']); ?></span></a>
            <a href="profile.php">👤 Profile</a>
            <span style="color:rgba(255,255,255,0.3)">|</span>
            <a href="dashboard.php" style="background: rgba(0,0,0,0.1);">⚙️ Admin</a>
        </div>
    </div>

    <div class="container">
        <form method="POST" id="cartForm">
            <div class="cart-card">
                <div class="cart-header">
                    <span>Shopping Cart</span>
                    <label style="font-size: 13px; font-weight: normal; color: #666; cursor: pointer;">
                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)" style="cursor:pointer;"> Select All
                    </label>
                </div>

                <?php if(empty($_SESSION['cart'])): ?>
                    <div style="padding:60px; text-align:center; color:#999;">Your cart is empty.</div>
                <?php else: ?>
                    <?php foreach($_SESSION['cart'] as $id => $qty): 
                        $p = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
                        $subtotal = $p['price'] * $qty;
                    ?>
                    <div class="cart-item">
                        <input type="checkbox" name="selected_items[]" value="<?php echo $id; ?>" class="item-chk" data-price="<?php echo $subtotal; ?>" onchange="updateUI()">
                        <img src="<?php echo $p['image']; ?>" class="item-img">
                        <div class="item-info">
                            <h4 style="margin:0; font-size:15px;"><?php echo $p['product_name']; ?></h4>
                            <small style="color:#0288d1; font-weight:bold;">₱<?php echo number_format($p['price'], 2); ?></small>
                        </div>
                        <div class="qty-control">
                            <a href="?action=minus&id=<?php echo $id; ?>" class="btn-qty">−</a>
                            <span style="font-size:14px; font-weight:bold;"><?php echo $qty; ?></span>
                            <a href="?action=plus&id=<?php echo $id; ?>" class="btn-qty">+</a>
                        </div>
                        <div style="width:100px; text-align:right; font-weight:bold;">₱<?php echo number_format($subtotal, 2); ?></div>
                        <a href="?action=remove&id=<?php echo $id; ?>" style="margin-left:15px; color:#ccc; text-decoration:none; font-size:18px;">×</a>
                    </div>
                    <?php endforeach; ?>

                    <div class="total-section">
                        <div>
                            <small style="color:#777;">Selected Total:</small><br>
                            <strong style="color:#0288d1; font-size:1.4rem;">₱<span id="totalDisplay">0.00</span></strong>
                        </div>
                        <button type="submit" name="checkout_selected" id="checkoutBtn" class="btn-checkout" disabled onclick="return confirm('Proceed to checkout?')">Check Out</button>
                    </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <script>
        function toggleAll(source) {
            const checkboxes = document.querySelectorAll('.item-chk');
            checkboxes.forEach(chk => chk.checked = source.checked);
            updateUI();
        }

        function updateUI() {
            const checkboxes = document.querySelectorAll('.item-chk');
            const selectAll = document.getElementById('selectAll');
            const btn = document.getElementById('checkoutBtn');
            let total = 0;
            let checkedCount = 0;

            checkboxes.forEach(chk => {
                if(chk.checked) {
                    total += parseFloat(chk.getAttribute('data-price'));
                    checkedCount++;
                }
            });

            document.getElementById('totalDisplay').innerText = total.toLocaleString(undefined, {minimumFractionDigits: 2});
            btn.disabled = checkedCount === 0;
            
            // Sync Select All checkbox
            if(selectAll) selectAll.checked = (checkedCount === checkboxes.length && checkboxes.length > 0);
        }
    </script>
</body>
</html>