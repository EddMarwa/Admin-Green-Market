<?php
session_start(); // Start the session to store cart data

// Include the functions
include 'init.php'; // Assuming 'init.php' includes the functions we defined above

// If the form is submitted (i.e., Add to Cart button is pressed)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get the POST data
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    // Add the item to the cart
    addToCart($item_id, $item_name, $item_price);
}

// Display the cart content
displayCart();
?>
