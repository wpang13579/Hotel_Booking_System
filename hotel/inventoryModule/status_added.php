<?php
// Require the database connection
require('../database.php');

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
$inventory_id = isset($_GET['inventory_id']) ? $_GET['inventory_id'] : null;
$quantity = isset($_GET['quantity']) ? $_GET['quantity'] : null;

if (!$order_id || !$inventory_id || !$quantity) {
    die("Missing required parameters.");
}

// Update order status
$update_status_query = "UPDATE order_management 
                        SET o_status = 'added' 
                        WHERE id = $order_id";

if (mysqli_query($con, $update_status_query)) {
    // Redirect to order_inventory.php
    header("Location: order_inventory/order_inventory.php?order_id=$order_id&inventory_id=$inventory_id&quantity=$quantity");
    exit();
} else {
    echo "<p style='color: red;'>Failed to update order status.</p>";
    echo "<a href='http://localhost/mini_update/inventoryModule/'>Go Back</a>";
}
