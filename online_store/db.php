<?php
$conn = new mysqli("localhost", "root", "", "online_store_db");
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }
?>