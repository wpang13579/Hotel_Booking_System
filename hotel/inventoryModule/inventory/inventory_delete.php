<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);
$id = $_GET['id'];
$query = "DELETE FROM inventory_management WHERE id=$id";
$result = mysqli_query($con, $query) or die(mysqli_error($con));
header("Location: inventory_view.php");
exit();
