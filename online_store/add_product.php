<?php
session_start();
include 'db.php';

if(!isset($_SESSION['admin_user'])) { header("Location: login.php"); exit(); }

$msg = "";
$edit_mode = false;

// Variables para sa form values (Default: empty)
$p_id = "";
$p_name = "";
$p_price = "";
$p_stock = "";
$p_category = "";
$p_image = "";

// --- LOGIC: FETCH DATA KUNG EDIT MODE ---
if (isset($_GET['edit_id'])) {
    $edit_mode = true;
    $p_id = intval($_GET['edit_id']);
    $res_edit = $conn->query("SELECT * FROM products WHERE id = $p_id");
    if($res_edit->num_rows > 0) {
        $data = $res_edit->fetch_assoc();
        $p_name = $data['product_name'];
        $p_price = $data['price'];
        $p_stock = $data['stock'];
        $p_category = $data['category'];
        $p_image = $data['image'];
    }
}

// --- LOGIC: SAVE O UPDATE PRODUCT ---
if (isset($_POST['save_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category'];
    $final_image_path = $_POST['current_image'] ?? ""; // default sa luma kung meron

    // Handle Image Upload kung may bagong file
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
        
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $final_image_path = $target_file;
        }
    }

    if ($edit_mode) {
        // UPDATE QUERY
        $sql = "UPDATE products SET product_name='$name', price='$price', stock='$stock', category='$category', image='$final_image_path' WHERE id = $p_id";
        if ($conn->query($sql)) {
            echo "<script>alert('Product updated successfully!'); window.location='products_list.php';</script>";
        }
    } else {
        // INSERT QUERY
        $sql = "INSERT INTO products (product_name, price, stock, category, image) 
                VALUES ('$name', '$price', '$stock', '$category', '$final_image_path')";
        if ($conn->query($sql)) {
            $msg = "Product added successfully!";
        } else {
            $msg = "Error: " . $conn->error;
        }
    }
}

$recent_added = $conn->query("SELECT * FROM products ORDER BY id DESC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_mode ? "Edit Product" : "Add Product"; ?> | FreshMart Admin</title>
    <style>
        /* [PAREHONG CSS MO SA ITAAS - WALANG PAGBABAGO SA STYLE] */
        body { margin: 0; font-family: 'Poppins', sans-serif; background: #f0f8ff; color: #333; }
        .sidebar-main { width: 260px; background: #03a9f4; height: 100vh; position: fixed; top: 0; left: 0; padding-top: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar-main h2 { color: white; text-align: center; margin-bottom: 30px; font-size: 1.5rem; }
        .sidebar-main a { display: block; color: #e1f5fe; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-size: 14px; }
        .sidebar-main a:hover, .sidebar-main a.active { background: #0288d1; color: white; border-left: 4px solid white; }
        .content { margin-left: 260px; padding: 40px; display: flex; gap: 30px; }
        .form-container { flex: 1; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }
        .form-container h2 { margin-top: 0; color: #0288d1; border-bottom: 2px solid #f0f8ff; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: bold; margin-bottom: 5px; color: #666; }
        .form-group input, .form-group select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: inherit; font-size: 14px; }
        .btn-save { background: #03a9f4; color: white; border: none; padding: 15px; border-radius: 8px; cursor: pointer; font-weight: bold; width: 100%; transition: 0.3s; font-size: 15px; margin-top: 10px; }
        .btn-save:hover { background: #0288d1; }
        .added-sidebar { width: 320px; background: #fff; padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); height: fit-content; position: sticky; top: 40px; }
        .added-sidebar h4 { margin: 0 0 20px; color: #333; border-left: 4px solid #03a9f4; padding-left: 10px; font-size: 16px; }
        .vertical-item { display: flex; align-items: center; gap: 15px; padding: 12px 0; border-bottom: 1px solid #f9f9f9; }
        .vertical-item img { width: 50px; height: 50px; border-radius: 8px; object-fit: cover; background: #f8f9fa; }
        .item-details h5 { margin: 0; font-size: 14px; color: #444; }
        .item-details small { color: #03a9f4; font-weight: bold; font-size: 11px; display: block; margin-top: 3px; }
        .alert { padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px; text-align: center; font-weight: bold; }
        .success { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .preview-img { width: 100px; height: 100px; object-fit: cover; border-radius: 8px; border: 1px solid #eee; margin-bottom: 10px; }
    </style>
</head>
<body>

    <div class="sidebar-main">
        <h2>JustPick</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="products_list.php">📋 Products List</a>
        <a href="add_product.php" class="<?php echo !$edit_mode ? 'active' : ''; ?>">➕ Add Product</a>
        <a href="reports.php">📈 Sales Reports</a>
        <div style="margin-top: 30px; padding: 0 25px; font-size: 11px; color: #e1f5fe; opacity: 0.6;">EXTERNAL</div>
        <a href="store.php" target="_blank">🌐 View Live Store</a>
        <a href="logout.php" onclick="return confirm('Logout?')" style="margin-top: 50px; color: red; font-weight: bold;">🚪 Logout</a>
    </div>

    <div class="content">
        <div class="form-container">
            <h2><?php echo $edit_mode ? "Edit Product" : "Add New Product"; ?></h2>
            <?php if($msg != ""): ?>
                <div class="alert success"><?php echo $msg; ?></div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="current_image" value="<?php echo $p_image; ?>">

                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="name" value="<?php echo $p_name; ?>" required>
                </div>
                
                <div style="display: flex; gap: 15px;">
                    <div class="form-group" style="flex: 1;">
                        <label>Price (₱)</label>
                        <input type="number" step="0.01" name="price" value="<?php echo $p_price; ?>" required>
                    </div>
                    <div class="form-group" style="flex: 1;">
                        <label>Initial Stock</label>
                        <input type="number" name="stock" value="<?php echo $p_stock; ?>" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">Select Category</option>
                        <?php 
                        $cats = ["Electronics", "Fashion", "Home & Living", "Health & Personal Care", "Toys & Collectibles", "Sports & Outdoors", "Motors", "Others"];
                        foreach($cats as $cat) {
                            $selected = ($p_category == $cat) ? "selected" : "";
                            echo "<option value='$cat' $selected>$cat</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product Image</label>
                    <?php if($edit_mode && !empty($p_image)): ?>
                        <img src="<?php echo $p_image; ?>" class="preview-img"><br>
                        <small style="color:#888;">Leave blank to keep current image</small>
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" <?php echo $edit_mode ? "" : "required"; ?>>
                </div>

                <button type="submit" name="save_product" class="btn-save">
                    <?php echo $edit_mode ? "Update Product Details" : "💾 Save Product"; ?>
                </button>
                
                <?php if($edit_mode): ?>
                    <a href="products_list.php" style="display:block; text-align:center; margin-top:15px; color:blue; text-decoration:none; font-size:13px; font-weight: bold;">Cancel and Go Back</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="added-sidebar">
            <h4>Recently Added</h4>
            <?php if($recent_added->num_rows > 0): ?>
                <?php while($row = $recent_added->fetch_assoc()): ?>
                    <div class="vertical-item">
                        <img src="<?php echo $row['image']; ?>">
                        <div class="item-details">
                            <h5><?php echo $row['product_name']; ?></h5>
                            <small>₱<?php echo number_format($row['price'], 2); ?> • <?php echo $row['category']; ?></small>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
            <a href="products_list.php" style="display: block; text-align: center; margin-top: 15px; font-size: 12px; color: #03a9f4; text-decoration: none; font-weight: bold;">Manage All Items →</a>
        </div>
    </div>

</body>
</html>