<?php
session_start();
include 'db.php';

if(!isset($_SESSION['admin_user'])) { header("Location: login.php"); exit(); }

// --- LOGIC: DELETE PRODUCT ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $conn->query("DELETE FROM products WHERE id = $id");
    header("Location: products_list.php");
    exit();
}

// --- LOGIC: QUICK UPDATE STOCK ---
if (isset($_POST['update_stock'])) {
    $id = intval($_POST['p_id']);
    $new_stock = intval($_POST['new_stock']);
    $conn->query("UPDATE products SET stock = $new_stock WHERE id = $id");
    header("Location: products_list.php");
    exit();
}

$res = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Inventory | FreshMart</title>
    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f0f8ff; color: #333; }
        
        /* SIDEBAR (Consistent sa Dashboard) */
        .sidebar { width: 260px; background: #03a9f4; height: 100vh; position: fixed; top: 0; left: 0; padding-top: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar h2 { color: white; text-align: center; margin-bottom: 30px; font-size: 1.5rem; }
        .sidebar a { display: block; color: #e1f5fe; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar a:hover, .sidebar a.active { background: #0288d1; color: white; border-left: 4px solid white; }

        .content { margin-left: 260px; padding: 40px; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* PRODUCT GRID (No Tables) */
        .inventory-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        
        .item-card { background: white; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); border: 1px solid #eef2f5; }
        .item-img { width: 70px; height: 70px; object-fit: contain; background: #f9f9f9; border-radius: 8px; }
        
        .item-info { flex-grow: 1; }
        .item-info h4 { margin: 0 0 5px; font-size: 16px; color: #333; }
        .item-info .category { font-size: 11px; color: #03a9f4; font-weight: bold; text-transform: uppercase; }
        .item-info .price { color: #2e7d32; font-weight: bold; font-size: 14px; }

        /* STOCK CONTROLS */
        .stock-tag { font-size: 12px; padding: 3px 8px; border-radius: 4px; font-weight: bold; }
        .in-stock { background: #e8f5e9; color: #2e7d32; }
        .low-stock { background: #fff3e0; color: #ef6c00; }
        .out-stock { background: #ffebee; color: #c62828; }

        .action-area { display: flex; flex-direction: column; gap: 8px; border-left: 1px solid #eee; padding-left: 15px; }
        .btn-edit { color: #03a9f4; text-decoration: none; font-size: 13px; font-weight: bold; }
        .btn-delete { color: #f44336; text-decoration: none; font-size: 13px; font-weight: bold; }

        /* QUICK UPDATE FORM */
        .stock-form { margin-top: 10px; display: flex; gap: 5px; }
        .stock-input { width: 50px; padding: 5px; border: 1px solid #ddd; border-radius: 4px; font-size: 12px; }
        .btn-update { background: #03a9f4; color: white; border: none; padding: 5px 8px; border-radius: 4px; cursor: pointer; font-size: 10px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>JustPick</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="products_list.php" class="active">📋 Products List</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="reports.php">📈 Sales Reports</a>
        <div style="margin-top: 30px; padding: 0 25px; font-size: 11px; color: #e1f5fe; opacity: 0.6;">EXTERNAL</div>
        <a href="store.php" target="_blank">🌐 View Live Store</a>
        
        <a href="logout.php" onclick="return confirm('Logout?')" style="margin-top: 50px; color: red; font-weight: bold;">🚪 Logout</a>
    </div>

    <div class="content">
        <div class="header-flex">
            <h2 style="margin:0; color: #0288d1;">Product Management</h2>
            <a href="add_product.php" style="background: #03a9f4; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 14px;">+ New Product</a>
        </div>

        <div class="inventory-grid">
            <?php while($row = $res->fetch_assoc()): 
                // Determine Stock Label
                $stock = $row['stock'];
                $class = "in-stock";
                if($stock <= 0) $class = "out-stock";
                elseif($stock <= 5) $class = "low-stock";
            ?>
                <div class="item-card">
                    <img src="<?php echo $row['image']; ?>" class="item-img">
                    
                    <div class="item-info">
                        <span class="category"><?php echo $row['category']; ?></span>
                        <h4><?php echo $row['product_name']; ?></h4>
                        <div class="price">₱<?php echo number_format($row['price'], 2); ?></div>
                        
                        <form class="stock-form" method="POST">
                            <input type="hidden" name="p_id" value="<?php echo $row['id']; ?>">
                            <span class="stock-tag <?php echo $class; ?>">
                                <?php echo ($stock <= 0) ? "Out" : $stock; ?>
                            </span>
                        </form>
                    </div>

                    <div class="action-area">
                        <a href="add_product.php?edit_id=<?php echo $row['id']; ?>" class="btn-edit">Edit</a>
                        <a href="?delete_id=<?php echo $row['id']; ?>" class="btn-delete" onclick="return confirm('Delete product?')">Delete</a>
                        <small style="font-size: 10px; color: #999; margin-top: 5px;">Sold: <?php echo $row['sold']; ?></small>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

</body>
</html>