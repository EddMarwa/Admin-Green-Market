<form action="checkout.php" method="GET">
    <input type="hidden" name="item_id" value="<?php echo $_GET['item_id']; ?>">
    <input type="hidden" name="item_name" value="<?php echo $_GET['item_name']; ?>">
    <input type="hidden" name="item_price" value="<?php echo $_GET['item_price']; ?>">
    <input type="hidden" name="lease" value="yes"> <!-- Indicate this is a lease -->
    
    <label for="lease_duration">Select Lease Duration (Days):</label>
    <select name="lease_duration" id="lease_duration">
        <option value="7">7 Days</option>
        <option value="14">14 Days</option>
        <option value="30">30 Days</option>
    </select>

    <button type="submit" class="btn btn-primary">Proceed to Lease Checkout</button>
</form>
