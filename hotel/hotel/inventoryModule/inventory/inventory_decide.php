<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

// Check if POST request contains the required data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['order_id']) && isset($_POST['order_name'])) {
    $order_id = $_POST['order_id'];
    $order_name = $_POST['order_name'];

    // Check if the order name is already in the inventory
    $check_query = "SELECT * FROM inventory_management WHERE inv_name = '$order_name'";
    $check_result = mysqli_query($con, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        // Redirect to the update page with the order ID and name if inventory exists
        header("Location: select_update.php?order_id=$order_id&order_name=$order_name");
        exit();
    } else {
        // Redirect to the add page with the order ID and name if inventory does not exist
        header("Location: select_add.php?order_id=$order_id&order_name=$order_name");
        exit();
    }
} else {
    // If required data is not present, redirect back with an error
    header("Location: inventory_select.php?error=Missing order data");
    exit();
}
