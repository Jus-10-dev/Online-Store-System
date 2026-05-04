<?php
session_start();
include 'db.php';
if(!isset($_SESSION['admin_user'])) { header("Location: login.php"); exit(); }

// Logic para sa Filtering
$where = "";
if(isset($_POST['filter'])) {
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    if(!empty($start) && !empty($end)) {
        $where = " WHERE sale_date BETWEEN '$start' AND '$end' ";
    }
}

$res = $conn->query("SELECT * FROM sales $where ORDER BY sale_date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Online Store</title>
    <style>
        :root {
            --primary-blue: #03a9f4;
            --dark-blue: #0288d1;
            --bg-color: #f0f8ff;
            --text-main: #333;
            --white: #ffffff;
        }

        body { margin: 0; font-family: 'Poppins', sans-serif; background: var(--bg-color); color: var(--text-main); }
        
        /* SIDEBAR - EXACT DASHBOARD STYLE */
        .sidebar { width: 260px; background: var(--primary-blue); height: 100vh; position: fixed; top: 0; left: 0; padding-top: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar h2 { color: white; text-align: center; margin-bottom: 30px; font-size: 1.5rem; font-weight: 700; }
        .sidebar a { display: block; color: #e1f5fe; padding: 15px 25px; text-decoration: none; transition: 0.3s; font-size: 14px; border-left: 4px solid transparent; }
        .sidebar a:hover, .sidebar a.active { background: var(--dark-blue); color: white; border-left: 4px solid white; }
        
        /* HEADER */
        .header { position: fixed; left: 260px; width: calc(100% - 260px); top: 0; background: var(--white); padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); z-index: 1000; box-sizing: border-box; }
        .header h3 { margin: 0; color: var(--dark-blue); font-weight: 600; }
        
        /* CONTENT AREA */
        .content { margin-left: 260px; padding: 90px 30px 30px; }
        
        /* FILTER SECTION */
        .filter-card { background: white; padding: 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .filter-card input { padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; }
        .btn-filter { background: var(--primary-blue); color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        .btn-filter:hover { background: var(--dark-blue); }
        .btn-print { background: #4caf50; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; margin-left: auto; text-decoration: none; transition: 0.3s; }
        .btn-print:hover { background: #388e3c; }

        /* REPORT TABLE CARD */
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); position: relative; overflow: hidden; }
        .card::after { content: ""; position: absolute; top: 0; left: 0; width: 100%; height: 5px; background: var(--primary-blue); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 15px; border-bottom: 1px solid #f0f0f0; text-align: left; }
        th { background: #f8f9fa; color: #666; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        td { font-size: 14px; }
        .total-row { background: #f0f8ff; font-weight: bold; color: #2e7d32; font-size: 1.1rem; }

        /* PRINT MODE */
        @media print {
            .sidebar, .filter-card, .header, .btn-print, .btn-reset { display: none !important; }
            .content { margin-left: 0; padding: 0; }
            .card { box-shadow: none; border: none; width: 100%; }
            .card::after { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>JustPick</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="products_list.php">📋 Products List</a>
        <a href="add_product.php">➕ Add Product</a>
        <a href="reports.php" class="active">📈 Sales Reports</a>
        
        <div style="margin-top: 30px; padding: 0 25px; font-size: 11px; color: #e1f5fe; opacity: 0.6;">EXTERNAL</div>
        <a href="store.php" target="_blank">🌐 View Live Store</a>
        
        <a href="logout.php" onclick="return confirm('Log out?')" style="margin-top: 50px; color: red; font-weight: bold;">🚪 Logout</a>
    </div>

    <div class="header">
        <h3>Sales Reports</h3>
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="text-align: right;">
                <div style="font-weight: bold; color: #333; font-size: 14px;"><?php echo $_SESSION['admin_user']; ?></div>
                <div style="font-size: 11px; color: #888;">System Administrator</div>
            </div>
            <div style="width: 40px; height: 40px; background: var(--dark-blue); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: white;">A</div>
        </div>
    </div>

    <div class="content">
        <div class="filter-card">
            <form method="POST" style="display: flex; align-items: center; gap: 10px;">
                <label style="font-size: 13px; font-weight: bold; color: #666;">From:</label>
                <input type="date" name="start_date" value="<?php echo $_POST['start_date'] ?? ''; ?>">
                <label style="font-size: 13px; font-weight: bold; color: #666;">To:</label>
                <input type="date" name="end_date" value="<?php echo $_POST['end_date'] ?? ''; ?>">
                <button type="submit" name="filter" class="btn-filter">Generate Report</button>
                <a href="reports.php" class="btn-reset" style="background: red; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 12px; margin-left: auto; text-decoration: none; transition: 0.3s; ">Reset</a>
            </form>
            
            <button onclick="window.print()" class="btn-print">🖨️ Print Report / Save PDF</button>
        </div>

        <div class="card">
            <div id="report-header" style="text-align: center; margin-bottom: 30px;">
                <h2 style="margin: 0; color: var(--dark-blue); letter-spacing: 1px;">SALES SUMMARY REPORT</h2>
                <p style="color: #888; margin-top: 5px;">Generated on: <?php echo date("F d, Y h:i A"); ?></p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Sale Date</th>
                        <th>Product Name</th>
                        <th>Amount Paid</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_revenue = 0;
                    if($res->num_rows > 0): 
                        while($r = $res->fetch_assoc()): 
                        $total_revenue += $r['amount'];
                    ?>
                    <tr>
                        <td><span style="color: #666;"><?php echo date("M d, Y", strtotime($r['sale_date'])); ?></span></td>
                        <td><strong><?php echo $row['product_name'] ?? $r['product_name']; ?></strong></td>
                        <td style="font-weight: bold; color: #2e7d32;">₱<?php echo number_format($r['amount'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <tr class="total-row">
                        <td colspan="2" style="text-align: right; padding-right: 30px;">TOTAL REVENUE:</td>
                        <td>₱<?php echo number_format($total_revenue, 2); ?></td>
                    </tr>
                    <?php else: ?>
                    <tr><td colspan="3" style="text-align:center; padding: 50px; color: #999;">No sales records found for this period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>