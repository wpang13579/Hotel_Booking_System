<?php
// Require the database connection
require('../../database.php');

$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
$inventory_id = isset($_GET['inventory_id']) ? $_GET['inventory_id'] : null;
$quantity = isset($_GET['quantity']) ? $_GET['quantity'] : null;

if (!$order_id || !$inventory_id || !$quantity) {
    die("Missing required parameters.");
}

// Add record to order_inventory table
$insert_order_inventory_query = "INSERT INTO order_inventory (order_id, inventory_id, contributed_quantity) 
                                 VALUES ($order_id, $inventory_id, $quantity)";

if (mysqli_query($con, $insert_order_inventory_query)) {
    header("Location: ../inventory/inventory_select.php?status=success&message=Inventory added to order successfully.");
} else {
    header("Location: ../inventory/inventory_select.php?status=error&message=Error adding inventory to order.");
}
