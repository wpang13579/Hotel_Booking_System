<?php
require('../../database.php');

$guest_id = intval($_GET['guest_id']);

// Query to get guest details and loyalty info
$query = "
    SELECT g.guest_name, g.guest_email, g.guest_dob, g.guest_phone, g.guest_address, 
           l.points, l.tier_level
    FROM guest g
    LEFT JOIN loyalty_program l ON g.guest_id = l.guest_id
    WHERE g.guest_id = $guest_id
";

$result = mysqli_query($con, $query);

$guest_info = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
echo json_encode($guest_info);
