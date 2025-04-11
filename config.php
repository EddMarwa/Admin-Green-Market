<?php
#DB Connection file
$conn = mysqli_connect("localhost", "root", "", "shop");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

ini_set('log_errors', 1);
ini_set('error_log', 'errors.log');





?>

