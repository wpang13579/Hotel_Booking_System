<?php
require('../../database.php');
// delete func
$id = $_GET['id'];
$query = "DELETE FROM reward WHERE reward_id=$id";
$result = mysqli_query($con, $query) or die(mysqli_error($con));
header("Location: reward_management.php");
exit();
