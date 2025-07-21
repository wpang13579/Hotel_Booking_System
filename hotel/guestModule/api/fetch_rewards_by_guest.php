<?php
require('../../database.php');

// Get guest tier and points from GET parameters
$guest_tier = $_GET['tier'];
$guest_points = intval($_GET['points']);

// Define a simple hierarchy for tiers
$tiers = ['bronze', 'silver', 'gold', 'platinium'];

// Find the index of the guest's tier
$guest_tier_index = array_search($guest_tier, $tiers);

// If tier not found, no rewards
if ($guest_tier_index === false) {
    echo json_encode([]);
    exit;
}

$query = "
    SELECT * FROM reward
    WHERE points_required <= $guest_points
";

$result = mysqli_query($con, $query);

$rewards = [];
while ($row = mysqli_fetch_assoc($result)) {
    $reward_tier = $row['tier_required'];
    $reward_tier_index = array_search($reward_tier, $tiers);
    if ($reward_tier_index !== false && $reward_tier_index <= $guest_tier_index) {
        $rewards[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($rewards);
