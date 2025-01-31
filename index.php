<?php
session_start();

$pageTitle = 'HomePage'; // Check eCommerce/includes/templates/header.php file AND eCommerce/includes/functions/functions.php file

include 'init.php';
?>

<div class="container">
    <div class="row">
        <?php
        // Fetch all approved items from the database
        $allItems = getAllFrom('*', '`items`', '`Item_ID`', 'WHERE `Approve` = 1', '');

        foreach ($allItems as $item) {
            echo '<div class="col-sm-6 col-md-3">';
                echo '<div class="thumbnail item-box">';
                    echo '<span class="price-tag">Ksh ' . $item['Price'] . '</span>';
                    echo '<img class="img-responsive" src="images/img.jpg' . $item['Image'] . '" alt="' . $item['Name'] . '">'; // Use the actual image path here
                    echo '<div class="caption">';
                        echo '<h3><a href="items.php?itemid=' . $item['Item_ID'] . '">' . $item['Name'] . '</a></h3>';
                        echo '<p>' . $item['Description'] . '</p>';
                        echo '<div class="date">' . $item['Add_Date'] . '</div>';

                        // Add to Cart button (form)
                        echo '<form action="#" method="POST">';
                            echo '<input type="hidden" name="item_id" value="' . $item['Item_ID'] . '">';
                            echo '<input type="hidden" name="item_name" value="' . $item['Name'] . '">';
                            echo '<input type="hidden" name="item_price" value="' . $item['Price'] . '">';
                            echo '<button type="submit" class="btn btn-success add-to-cart">Add to Cart</button>';
                        echo '</form>';
                    echo '</div>';
                echo '</div>';
            echo '</div>';
        }
        ?>
    </div>
</div>

<?php
// Footer
include $tpl . 'footer.php'; 
?>
