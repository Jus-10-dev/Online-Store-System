<?php
include 'db.php';
$name = mysqli_real_escape_string($conn, $_GET['name']);
$q = $conn->query("SELECT * FROM purchase_history WHERE product_name = '$name' AND rating > 0 ORDER BY id DESC");
if($q->num_rows > 0) {
    while($r = $q->fetch_assoc()) {
        echo "<div style='border-bottom:1px solid #eee; padding:10px 0;'>";
        echo "<div style='color:#ffc107; font-size:12px;'>" . str_repeat("★", $r['rating']) . "</div>";
        echo "<p style='margin:5px 0; font-size:13px;'>" . $r['comment'] . "</p>";
        echo "</div>";
    }
} else { echo "No reviews yet."; }
?>