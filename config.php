<?php
$conn = mysqli_connect("localhost", "root", "", "your_database_name");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>
