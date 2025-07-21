<?php
// Make sure $con, $guest_id, $stay_id, $final_price, $reward_id, $booked_days, $check_in_dt_formatted, $check_out_dt_formatted are defined
// Note: We are now using $check_in_dt_formatted as the properly formatted date-time.

if (!isset($final_price)) {
    die("Error: final_price is not defined. Ensure it is calculated before including post_booking_handler.php.");
}

// 1. Update loyalty info (unchanged)
$loyalty_query = "SELECT * FROM loyalty_program WHERE guest_id = $guest_id LIMIT 1";
$loyalty_result = mysqli_query($con, $loyalty_query);
if (!$loyalty_result) {
    die("Error fetching loyalty data: " . mysqli_error($con));
}

$loyalty_data = mysqli_fetch_assoc($loyalty_result);
if (!$loyalty_data) {
    $insert_loyalty = "INSERT INTO loyalty_program (points, tier_level, total_point_redeem, total_book_day, guest_id) 
                       VALUES (0, 'bronze', 0, 0, $guest_id)";
    if (!mysqli_query($con, $insert_loyalty)) {
        die("Error inserting new loyalty record: " . mysqli_error($con));
    }
    $loyalty_id = mysqli_insert_id($con);
    $loyalty_data = [
        'points' => 0,
        'tier_level' => 'bronze',
        'total_point_redeem' => 0,
        'total_book_day' => 0
    ];
} else {
    $loyalty_id = $loyalty_data['loyalty_id'];
    if (!isset($loyalty_data['points'])) $loyalty_data['points'] = 0;
    if (!isset($loyalty_data['tier_level'])) $loyalty_data['tier_level'] = 'bronze';
    if (!isset($loyalty_data['total_point_redeem'])) $loyalty_data['total_point_redeem'] = 0;
    if (!isset($loyalty_data['total_book_day'])) $loyalty_data['total_book_day'] = 0;
}

$new_points = $loyalty_data['points'] + ($booked_days * 10);
$new_total_book_day = $loyalty_data['total_book_day'] + $booked_days;

$new_tier = 'bronze';
if ($new_total_book_day > 365) {
    $new_tier = 'platinum';
} elseif ($new_total_book_day > 100) {
    $new_tier = 'gold';
} elseif ($new_total_book_day > 30) {
    $new_tier = 'silver';
}

$redemption_id = "NULL";
$new_total_point_redeem = $loyalty_data['total_point_redeem'];

if ($reward_id) {
    $reward_query = "SELECT * FROM reward WHERE reward_id = $reward_id";
    $reward_res = mysqli_query($con, $reward_query);
    $reward_data = mysqli_fetch_assoc($reward_res);

    if ($reward_data) {
        $reward_name = mysqli_real_escape_string($con, $reward_data['reward_name']);
        $reward_type = mysqli_real_escape_string($con, $reward_data['reward_type']);
        $points_required = (int)$reward_data['points_required'];

        $new_points = $new_points - $points_required;
        $new_total_point_redeem = $loyalty_data['total_point_redeem'] + $points_required;

        $redeem_date = $check_in_dt_formatted; // Already in proper YYYY-MM-DD HH:MM:SS format
        $insert_redemption = "INSERT INTO redemption_record (redeem_date, point_used, tier, reward_id, reward_name, reward_type, guest_id)
                              VALUES ('$redeem_date', $points_required, '$new_tier', $reward_id, '$reward_name', '$reward_type', $guest_id)";
        if (mysqli_query($con, $insert_redemption)) {
            $redemption_id = mysqli_insert_id($con);
        } else {
            die("Error inserting redemption record: " . mysqli_error($con));
        }
    }
}

$update_loyalty = "UPDATE loyalty_program SET 
                   points = $new_points, 
                   tier_level = '$new_tier', 
                   total_point_redeem = $new_total_point_redeem, 
                   total_book_days = $new_total_book_day
                   WHERE guest_id = $guest_id";
if (!mysqli_query($con, $update_loyalty)) {
    die("Error updating loyalty program: " . mysqli_error($con));
}

// Insert guest revenue
$occur_date = $check_in_dt_formatted; // already in proper format: YYYY-MM-DD HH:MM:SS
$insert_revenue = "INSERT INTO guest_revenue (amount, occur_date, stay_id, redemption_id)
                   VALUES ($final_price, '$occur_date', $stay_id, $redemption_id)";
if (!mysqli_query($con, $insert_revenue)) {
    die("Error inserting guest revenue: " . mysqli_error($con));
}
