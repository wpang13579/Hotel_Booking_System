<?php
require('../../database.php');

// Get room_type_id from the request
$room_type_id = intval($_GET['room_type_id']);

// Query to get available rooms of the selected type
$room_query = "SELECT * FROM room WHERE room_type = $room_type_id AND room_status = 'available' ORDER BY room_num";
$room_result = mysqli_query($con, $room_query);

$rooms = array();
while ($room = mysqli_fetch_assoc($room_result)) {
    $rooms[] = $room;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($rooms);
