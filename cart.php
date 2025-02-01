// cart.php
session_start();

// Include the addToCart function
include 'init.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and get POST data
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $item_price = $_POST['item_price'];

    // Call function to add item to the cart
    addToCart($item_id, $item_name, $item_price);
}

// Display cart
displayCart();
