<?php
header('Content-Type: application/json');
require('../../database.php');

if (isset($_GET['guest_id'])) {
    $guest_id = intval($_GET['guest_id']);

    // Fetch loyalty program data for the guest
    $loyalty_query = "SELECT points, tier_level FROM loyalty_program WHERE guest_id = $guest_id";
    $loyalty_result = mysqli_query($con, $loyalty_query);

    if ($loyalty_result && mysqli_num_rows($loyalty_result) > 0) {
        $loyalty = mysqli_fetch_assoc($loyalty_result);
        echo json_encode([
            'status' => 'success',
            'points' => $loyalty['points'],
            'tier_level' => ucfirst($loyalty['tier_level'])
        ]);
    } else {
        // If no loyalty record exists
        echo json_encode([
            'status' => 'no_record',
            'message' => 'No loyalty program data found for this guest.'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Guest ID not provided.'
    ]);
}
