<?php
session_start();
include 'db.php';

if (!isset($_SESSION['cart'])) { $_SESSION['cart'] = []; }

// --- AJAX: Add to Cart ---
if (isset($_GET['ajax_add'])) {
    $p_id = intval($_GET['ajax_add']);
    $_SESSION['cart'][$p_id] = isset($_SESSION['cart'][$p_id]) ? $_SESSION['cart'][$p_id] + 1 : 1;
    echo count($_SESSION['cart']); exit(); 
}

// --- Buy Now Logic ---
if (isset($_GET['buy_id'])) {
    $p_id = intval($_GET['buy_id']);
    $p = $conn->query("SELECT * FROM products WHERE id = $p_id")->fetch_assoc();
    if ($p && $p['stock'] > 0) {
        $conn->query("UPDATE products SET stock = stock - 1, sold = sold + 1 WHERE id = $p_id");
        $conn->query("INSERT INTO purchase_history (user_id, product_name, amount, status) VALUES (1, '{$p['product_name']}', '{$p['price']}', 'Pending')");
        echo "<script>alert('Purchased Successfully!'); window.location='profile.php';</script>";
    }
}

// --- NEW: SEARCH & FILTER LOGIC (IBINALIK) ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$cat_filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : '';

$query = "SELECT * FROM products WHERE stock > 0";
if (!empty($search)) { $query .= " AND (product_name LIKE '%$search%' OR category LIKE '%$search%')"; }
if (!empty($cat_filter)) { $query .= " AND category = '$cat_filter'"; }
$query .= " ORDER BY id DESC";

$res = $conn->query($query);
$cat_list = $conn->query("SELECT DISTINCT category FROM products");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JustPick | Online Store</title>
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f0f8ff; }
        .navbar { background: #03a9f4; padding: 15px 50px; display: flex; justify-content: space-between; align-items: center; color: white; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .navbar .brand { font-size: 24px; font-weight: bold; text-decoration: none; color: white; }
        .nav-links { display: flex; align-items: center; gap: 20px; }
        .nav-links a { color: white; text-decoration: none; font-size: 14px; padding: 8px 12px; border-radius: 5px; }
        .nav-links .active { background: #ffeb3b !important; color: #333 !important; font-weight: bold; }
        
        /* TOOLBAR STYLE (IBINALIK) */
        .toolbar { background: white; padding: 20px 50px; display: flex; gap: 15px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .search-box { flex-grow: 1; display: flex; gap: 5px; }
        .search-box input, select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: inherit; }
        .btn-search { background: #03a9f4; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }

        .container { max-width: 1200px; margin: auto; padding: 20px; }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        .product-card { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); display: flex; flex-direction: column; border-bottom: 3px solid #03a9f4; transition: 0.3s; }
        .product-card:hover { transform: translateY(-5px); }
        .p-details { padding: 15px; flex-grow: 1; }
        .btn-group { display: flex; border-top: 1px solid #eee; }
        .btn-group button, .btn-group a { flex: 1; padding: 12px; cursor: pointer; font-size: 13px; font-weight: bold; text-decoration: none; text-align: center; border: none; font-family: inherit; }
        
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 2000; justify-content: center; align-items: center; }
        .modal-box { background: white; padding: 25px; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="store.php" class="brand">🛍️ JustPick</a>
        <div class="nav-links">
            <a href="store.php" class="active">🏠 Home</a>
            <a href="cart.php">🛒 Cart <span id="cartCount" style="background:#ffeb3b; color:#333; padding:2px 6px; border-radius:50%; font-size:10px;"><?php echo count($_SESSION['cart']); ?></span></a>
            <a href="profile.php">👤 Profile</a>
            <span style="color:rgba(255,255,255,0.3)">|</span>
            <a href="dashboard.php" style="background: rgba(0,0,0,0.1);">⚙️ Admin</a>
        </div>
    </div>

    <div class="toolbar">
        <form action="" method="GET" class="search-box">
            <input type="text" name="search" placeholder="Search for products..." value="<?php echo htmlspecialchars($search); ?>" style="flex-grow: 1;">
            <select name="category" onchange="this.form.submit()">
                <option value="">All Categories</option>
                <?php while($cat = $cat_list->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category']; ?>" <?php if($cat_filter == $cat['category']) echo 'selected'; ?>><?php echo $cat['category']; ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn-search">Search</button>
        </form>
    </div>

    <div class="container">
        <?php if($res->num_rows > 0): ?>
        <div class="product-grid">
            <?php while($r = $res->fetch_assoc()): ?>
            <div class="product-card">
                <div style="height:180px; padding:10px;"><img src="<?php echo $r['image']; ?>" style="width:100%; height:100%; object-fit:contain;"></div>
                <div class="p-details">
                    <small style="color:#999;"><?php echo $r['category']; ?></small>
                    <h3 style="font-size:15px; margin:5px 0;"><?php echo $r['product_name']; ?></h3>
                    
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                        <span style="color:#ffc107; font-size:13px;">
                            <?php 
                                $stars = round($r['rating'] ?? 5);
                                for($i=1; $i<=5; $i++) echo ($i <= $stars) ? "★" : "☆";
                            ?>
                        </span>
                        <a href="javascript:void(0)" onclick="openReviews('<?php echo addslashes($r['product_name']); ?>')" style="font-size:11px; color:#03a9f4; text-decoration:none; border-bottom: 1px dashed #03a9f4;">View Reviews</a>
                    </div>
                    <div style="color:#0288d1; font-weight:bold;">₱<?php echo number_format($r['price'], 2); ?></div>
                </div>
                <div class="btn-group">
                    <button style="background:#fff; color:#03a9f4; border-right:1px solid #eee;" onclick="addToCart(<?php echo $r['id']; ?>)">Cart</button>
                    <a href="?buy_id=<?php echo $r['id']; ?>" style="background:#03a9f4; color:white;" onclick="return confirm('Buy now?')">Buy Now</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
            <div style="text-align:center; padding:100px; color:#999;">
                <h2>No products found.</h2>
                <p>Try searching for something else or change the category.</p>
            </div>
        <?php endif; ?>
    </div>

    <div id="reviewsModal" class="modal-overlay">
        <div class="modal-box">
            <h3 id="revTitle" style="margin:0 0 15px 0; color:#03a9f4; border-bottom: 1px solid #eee; padding-bottom: 10px;">Product Reviews</h3>
            <div id="revList" style="max-height:300px; overflow-y:auto; margin-bottom:15px;">
                </div>
            <button onclick="closeReviews()" style="width:100%; padding:10px; border:none; background:#eee; border-radius:5px; cursor:pointer; font-weight:bold;">Close</button>
        </div>
    </div>

    <script>
        function addToCart(id) {
            fetch('store.php?ajax_add=' + id)
                .then(r => r.text())
                .then(count => {
                    document.getElementById('cartCount').innerText = count;
                    alert('Item added to cart!');
                });
        }
        function openReviews(name) {
            document.getElementById('revTitle').innerText = name + " Reviews";
            document.getElementById('reviewsModal').style.display = 'flex';
            fetch('get_reviews.php?name=' + encodeURIComponent(name))
                .then(r => r.text())
                .then(data => {
                    document.getElementById('revList').innerHTML = data;
                });
        }
        function closeReviews() {
            document.getElementById('reviewsModal').style.display = 'none';
        }
    </script>
</body>
</html>