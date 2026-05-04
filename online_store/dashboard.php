<?php
session_start();
include 'db.php';

if(!isset($_SESSION['admin_user'])) { header("Location: login.php"); exit(); }

// --- ANALYTICS QUERIES ---
// 1. Total Products
$prod_count = $conn->query("SELECT COUNT(*) as t FROM products")->fetch_assoc()['t'];

// 2. Revenue Today
$sales_today = $conn->query("SELECT SUM(amount) as t FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['t'] ?? 0;

// 3. Total Orders Today
$orders_today = $conn->query("SELECT COUNT(*) as t FROM sales WHERE DATE(sale_date) = CURDATE()")->fetch_assoc()['t'];

// 4. Low Stock Alert (Products with 5 or less stock)
$low_stock = $conn->query("SELECT COUNT(*) as t FROM products WHERE stock <= 5")->fetch_assoc()['t'];

// 5. Recent Transactions List
$recent_sales = $conn->query("SELECT * FROM sales ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Dashboard | FreshMart Admin</title>
    <style>
        :root {
            --primary-blue: #03a9f4;
            --dark-blue: #0288d1;
            --bg-color: #f0f8ff;
            --text-main: #333;
            --white: #ffffff;
        }

        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg-color); color: var(--text-main); }
        
        /* SIDEBAR */
        .sidebar { width: 260px; background: var(--primary-blue); height: 100vh; position: fixed; top: 0; left: 0; padding-top: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar h2 { color: white; text-align: center; margin-bottom: 30px; font-size: 1.5rem; font-weight: 700; }
        .sidebar a { display: block; color: #e1f5fe; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-size: 14px; border-left: 4px solid transparent; }
         .sidebar a:hover, .sidebar a.active { background: #0288d1; color: white; border-left: 5px solid white; padding-left: 30px; }
        
        /* HEADER */
        .header { position: fixed; left: 260px; width: calc(100% - 260px); top: 0; background: var(--white); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); z-index: 1000; box-sizing: border-box; }
        .header h3 { margin: 0; color: var(--dark-blue); font-weight: 600; }
        
        /* MAIN CONTENT */
        .content { margin-left: 260px; padding: 90px 30px 30px; }
        
        /* ANALYTICS GRID */
        .analytics-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); display: flex; flex-direction: column; position: relative; overflow: hidden; }
        .stat-card::after { content: ""; position: absolute; bottom: 0; left: 0; width: 100%; height: 4px; background: var(--primary-blue); }
        .stat-card.alert::after { background: #f44336; } /* Red for low stock */
        
        .stat-label { font-size: 12px; color: black; text-transform: uppercase; font-weight: bold; margin-bottom: 8px; }
        .stat-value { font-size: 1.8rem; font-weight: 700; color: var(--dark-blue); }
        .stat-icon { position: absolute; top: 20px; right: 20px; font-size: 1.5rem; opacity: 0.2; }

        /* RECENT ACTIVITY SECTION */
        .main-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; }
        .table-container { background: var(--white); padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; }
        table th { text-align: left; padding: 12px; border-bottom: 2px solid #f0f0f0; color: black; font-size: 14px; }
        table td { padding: 12px; border-bottom: 1px solid #f9f9f9; font-size: 14px; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; background: #e3f2fd; color: #03a9f4; }

        .quick-actions { background: var(--white); padding: 25px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .btn-action { display: block; width: 100%; padding: 12px; background: #f8f9fa; color: var(--dark-blue); text-decoration: none; border-radius: 6px; margin-bottom: 10px; text-align: center; font-weight: 600; font-size: 14px; transition: 0.2s; }
        .btn-action:hover { background: var(--primary-blue); color: white; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>JustPick</h2>
        <a href="dashboard.php" class="active">📊 Dashboard</a>
        <a href="products_list.php">📋 Products List</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="reports.php">📈 Sales Reports</a>
        
        <div style="margin-top: 30px; padding: 0 25px; font-size: 11px; color: #e1f5fe; opacity: 0.6;">EXTERNAL</div>
        <a href="store.php" target="_blank">🌐 View Live Store</a>
        
        <a href="logout.php" onclick="return confirm('Logout?')" style="margin-top: 50px; color: red; font-weight: bold;">🚪 Logout</a>
    </div>

    <div class="header">
        <h3>Overview</h3>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="text-align: right;">
                <div style="font-weight: bold; color: #333; font-size: 14px;"><?php echo $_SESSION['admin_user']; ?></div>
                <div style="font-size: 11px; color: #888;">System Administrator</div>
            </div>
            <div style="width: 40px; height: 40px; background: #ddd; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; background: var(--dark-blue);">A</div>
        </div>
    </div>

    <div class="content">
        
        <div class="analytics-grid">
            <div class="stat-card">
                <span class="stat-label">Total Inventory</span>
                <span class="stat-value"><?php echo $prod_count; ?></span>
                <span class="stat-icon">📦</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Revenue Today</span>
                <span class="stat-value">₱<?php echo number_format($sales_today, 2); ?></span>
                <span class="stat-icon">💰</span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Orders Today</span>
                <span class="stat-value"><?php echo $orders_today; ?></span>
                <span class="stat-icon">🛒</span>
            </div>
            <div class="stat-card <?php echo ($low_stock > 0) ? 'alert' : ''; ?>">
                <span class="stat-label">Low Stock Items</span>
                <span class="stat-value"><?php echo $low_stock; ?></span>
                <span class="stat-icon">⚠️</span>
            </div>
        </div>

        <div class="main-grid">
            <div class="table-container">
                <div class="table-header">
                    <h4 style="margin:0;">Recent Transactions</h4>
                    <a href="reports.php" style="color: var(--primary-blue); text-decoration: none; font-size: 13px; font-weight: bold;">View All</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($recent_sales->num_rows > 0): ?>
                            <?php while($row = $recent_sales->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo $row['product_name']; ?></strong></td>
                                <td style="color: #2e7d32; font-weight: bold;">₱<?php echo number_format($row['amount'], 2); ?></td>
                                <td style="color: #666;"><?php echo date('M d, Y h:i A', strtotime($row['sale_date'])); ?></td>
                                <td><span class="badge">Completed</span></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding: 20px; color: #999;">No transactions yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="quick-actions">
                <h4 style="margin: 0 0 20px;">Quick Actions</h4>
                <a href="add_product.php" class="btn-action">➕ Add New Product</a>
                <a href="products_list.php" class="btn-action">📦 Manage Inventory</a>
                <a href="reports.php" class="btn-action">📑 Export Sales Report</a>
                
                <div style="margin-top: 30px; padding: 15px; background: #03a9f4; border-radius: 8px; border-left: 4px solid skyblue;">
                    <small style="font-weight: bold; color: white;">System Note:</small><br>
                    <small style="color: black;">Check "Low Stock Items" immediately to maintain store supply.</small>
                </div>
            </div>
        </div>
    </div>

</body>
</html>