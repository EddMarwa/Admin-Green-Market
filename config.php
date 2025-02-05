<?php
$conn = mysqli_connect("localhost", "root", "", "shop");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
