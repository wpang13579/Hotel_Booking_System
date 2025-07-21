<?php
session_start();
require('../../database.php');

// Check if the user is logged in using email
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve staff details using the email
$staff_email = $_SESSION['staff_email'];
$query = "SELECT staff_id, staff_firstname, staff_lastname FROM staff WHERE staff_email = '$staff_email'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $staff_id = $row['staff_id']; // Fetch staff_id
    $staff_name = $row['staff_firstname'] . " " . $row['staff_lastname'];
} else {
    die("<p style='color: red;'>Error: Staff details not found. Please log in again.</p>");
}

// Check if the logged-in user is a manager or admin
$role_id = $_SESSION['role_id'];
$roles = [
    2 => 'Admin',
    3 => 'Guest Manager',
    6 => 'Normal Staff',
];

// Get the role name
$role_name = isset($roles[$role_id]) ? $roles[$role_id] : 'Unknown Role';

// Define role-based visibility flags
$is_manager_or_admin = ($role_id == 2 || $role_id == 3);  // Admin (2) or Guest Manager (3)

// Get list of guests
$guest_query = "SELECT * FROM guest";
$guest_result = mysqli_query($con, $guest_query);

$room_type_query = "SELECT * FROM room_type";
$room_type_result = mysqli_query($con, $room_type_query);

// Handle form submission for booking a room
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check and sanitize guest_id
    $guest_id = isset($_POST['guest_id']) ? intval($_POST['guest_id']) : 0;

    // Try both room_id and original_room_id in case room_id is empty
    $room_id = isset($_POST['room_id']) && !empty($_POST['room_id']) ? intval($_POST['room_id']) : 0;
    if ($room_id == 0) {
        $room_id = isset($_POST['original_room_id']) && !empty($_POST['original_room_id']) ? intval($_POST['original_room_id']) : 0;
    }

    if ($room_id == 0) {
        $error = "Please select a room.";
    }

    $check_in_date = isset($_POST['check_in_date']) ? mysqli_real_escape_string($con, $_POST['check_in_date']) : '';
    $check_out_date = isset($_POST['check_out_date']) ? mysqli_real_escape_string($con, $_POST['check_out_date']) : '';
    $reward_id = isset($_POST['reward_id']) && !empty($_POST['reward_id']) ? intval($_POST['reward_id']) : null;
    $upgrade_room_id = isset($_POST['upgrade_room_id']) && !empty($_POST['upgrade_room_id']) ? intval($_POST['upgrade_room_id']) : null;

    // Only proceed if no immediate errors
    if (!isset($error)) {
        if (strtotime($check_in_date) >= strtotime($check_out_date)) {
            $error = "Check-out date must be later than check-in date.";
        } else {
            // If upgrade room is chosen, $final_room_id = $upgrade_room_id else $final_room_id = $room_id
            $final_room_id = $upgrade_room_id ? $upgrade_room_id : $room_id;

            // Check if final room is available
            $check_room_status_query = "SELECT room_status FROM room WHERE room_id = $final_room_id";
            $check_room_status_result = mysqli_query($con, $check_room_status_query);
            if ($check_room_status_result) {
                $room_status_data = mysqli_fetch_assoc($check_room_status_result);
                $room_status = $room_status_data ? $room_status_data["room_status"] : null;

                if ($room_status == 'available') {
                    // Update the chosen final room to 'occupied'
                    $update_room_query = "UPDATE room SET room_status = 'occupied' WHERE room_id = $final_room_id";
                    if (mysqli_query($con, $update_room_query)) {
                        // Insert stay record into 'stay' table
                        $insert_stay_query = "INSERT INTO stay (check_in_date, check_out_date, room_id, staff_id, guest_id) 
                                              VALUES ('$check_in_date', '$check_out_date', $final_room_id, $staff_id, $guest_id)";
                        if (mysqli_query($con, $insert_stay_query)) {
                            $success = "Room booking successful and room status updated to 'occupied'.";

                            $stay_id = mysqli_insert_id($con);

                            // Compute the number of booked days
                            $booked_days = (int)((strtotime($check_out_date) - strtotime($check_in_date)) / (60 * 60 * 24));
                            if ($booked_days < 1) {
                                $booked_days = 1; // Ensure at least 1 day if same-day or invalid calculation
                            }

                            // Fetch the price of the final chosen room
                            $fetch_price_query = "SELECT room_price, room_type FROM room WHERE room_id = $final_room_id";
                            $fetch_price_result = mysqli_query($con, $fetch_price_query);
                            if ($fetch_price_result && mysqli_num_rows($fetch_price_result) > 0) {
                                $room_data = mysqli_fetch_assoc($fetch_price_result);
                                $base_room_price = (float)$room_data['room_price'];
                                $base_room_type = (int)$room_data['room_type']; // 1=superior, 2=deluxe, 3=luxury

                                // Original price is based on the final chosen room
                                $original_price = $base_room_price * $booked_days;
                                $final_price = $original_price;

                                // Define a helper function to determine next tier room type
                                function getNextRoomTypeId($currentTypeId)
                                {
                                    // Assuming tier mapping: 1=superior, 2=deluxe, 3=luxury
                                    if ($currentTypeId === 1) return 2;
                                    if ($currentTypeId === 2) return 3;
                                    return null;
                                }

                                if (!empty($reward_id)) {
                                    // Fetch reward details
                                    $reward_query = "SELECT * FROM reward WHERE reward_id = $reward_id";
                                    $reward_res = mysqli_query($con, $reward_query);
                                    if ($reward_res && mysqli_num_rows($reward_res) > 0) {
                                        $reward_data = mysqli_fetch_assoc($reward_res);
                                        $reward_type = $reward_data['reward_type'];
                                        $points_required = (int)$reward_data['points_required'];

                                        if ($reward_type === 'upgrade_room') {
                                            // Check if next tier is available
                                            $nextTierId = getNextRoomTypeId($base_room_type);
                                            if (is_null($nextTierId)) {
                                                // Already top-tier (no higher tier available)
                                                // Apply a 50% discount to the original price
                                                $final_price = $original_price * 0.5;
                                            } else {
                                                // Next tier is available, so must have chosen an upgrade room from that tier
                                                if (!empty($upgrade_room_id)) {
                                                    // Fetch the upgrade room price and recalculate final_price based on the upgrade room's price
                                                    $fetch_upgrade_price_query = "SELECT room_price FROM room WHERE room_id = $upgrade_room_id";
                                                    $fetch_upgrade_price_result = mysqli_query($con, $fetch_upgrade_price_query);
                                                    if ($fetch_upgrade_price_result && mysqli_num_rows($fetch_upgrade_price_result) > 0) {
                                                        $upgrade_room_data = mysqli_fetch_assoc($fetch_upgrade_price_result);
                                                        $upgrade_room_price = (float)$upgrade_room_data['room_price'];
                                                        $final_price = $upgrade_room_price * $booked_days;
                                                    } else {
                                                        // If cannot fetch the upgrade room price, revert changes and throw an error
                                                        mysqli_query($con, "UPDATE room SET room_status = 'available' WHERE room_id = $final_room_id");
                                                        die("Error fetching upgrade room price: " . mysqli_error($con));
                                                    }
                                                } else {
                                                    // If no upgrade room was selected, revert and throw error since upgrade reward requires an upgrade room
                                                    mysqli_query($con, "UPDATE room SET room_status = 'available' WHERE room_id = $final_room_id");
                                                    die("Upgrade reward chosen but no upgrade room selected.");
                                                }
                                            }
                                        } elseif ($reward_type === 'discount') {
                                            // Apply discount
                                            $discount_rate = (float)$reward_data['discount_rate']; // percentage discount
                                            $final_price = $original_price * ((100 - $discount_rate) / 100);
                                        }

                                        // Deduct points from guest since reward was applied
                                        $deduct_points_query = "UPDATE loyalty_program SET points = points - $points_required WHERE guest_id = $guest_id AND points >= $points_required";
                                        mysqli_query($con, $deduct_points_query);
                                        // If points not enough, query won't update. In real scenario, add checks.
                                    } else {
                                        // If reward_id provided but reward not found
                                        mysqli_query($con, "UPDATE room SET room_status = 'available' WHERE room_id = $final_room_id");
                                        die("Error: Reward not found for reward_id = $reward_id");
                                    }
                                }

                                // Format dates for post_booking_handler
                                $check_in_dt_formatted = date('Y-m-d H:i:s', strtotime($check_in_date));
                                $check_out_dt_formatted = date('Y-m-d H:i:s', strtotime($check_out_date));

                                // Now include the post_booking_handler with final_price defined
                                include('post_booking_handler.php');
                            } else {
                                // If can't fetch the final room price, revert and show error
                                mysqli_query($con, "UPDATE room SET room_status = 'available' WHERE room_id = $final_room_id");
                                $error = "Error fetching room price after stay insertion.";
                            }
                        } else {
                            // revert room status if error in stay insertion
                            mysqli_query($con, "UPDATE room SET room_status = 'available' WHERE room_id = $final_room_id");
                            $error = "Error inserting stay record: " . mysqli_error($con);
                        }
                    } else {
                        $error = "Error updating room status: " . mysqli_error($con);
                    }
                } else {
                    $error = "Selected final room is not available. Current status: $room_status.";
                }
            } else {
                $error = "Error checking room status: " . mysqli_error($con);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-left: 270px;
        }

        h2 {
            font-weight: 600;
            margin-bottom: 30px;
        }

        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
        }

        .card-header {
            background-color: #05106F;
            color: #fff;
            border-radius: 12px 12px 0 0;
            font-weight: 600;
            font-size: 1.25rem;
            padding: 15px;
        }

        .guest-info,
        .price-info {
            background-color: #e9ecef;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .guest-info h4,
        .price-info h4 {
            font-weight: 600;
            margin-bottom: 15px;
        }

        .guest-info p,
        .price-info p {
            margin: 0 0 5px;
            font-size: 0.95rem;
        }

        .guest-info {
            display: none;
        }

        .price-info {
            display: none;
        }

        #upgrade_room_wrapper {
            display: none;
            margin-top: 10px;
        }

        .readonly-select {
            background-color: #e9ecef;
            pointer-events: none;
            opacity: 1;
        }

        @media(max-width: 768px) {
            .row-section {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h4 class="text-center">Guest Management</h4>
        <ul class="list-unstyled">
            <li>
                <a href="../guest_dashboard.php">Dashboard</a>
            </li>
            <li>
                <a href="#">Guest</a>
                <ul class="submenu list-unstyled">
                    <li><a href="../guest/guest_registration.php">Guest Registration</a></li>
                    <li><a href="../guest/view_guest_profile.php">Guest Profile Management</a></li>
                </ul>
            </li>
            <?php if ($is_manager_or_admin): ?>
                <li>
                    <a href="#">Reward</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="../reward/reward_management.php">Reward Management</a></li>
                    </ul>
                </li>
            <?php endif; ?>
            <li>
                <a href="#">Booking</a>
                <ul class="submenu list-unstyled">
                    <li><a href="../room/booking.php">New Booking</a></li>
                    <li><a href="../room/view_booking_room.php">Booking Management</a></li>
                </ul>
            </li>
            <?php if ($is_manager_or_admin): ?>
                <li>
                    <a href="#">Report</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="../report/guest_report.php">Report</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <a href="../../staffModule/staff_logout.php" style="color: red;">Logout</a>
        </ul>
    </div>

    <div class="container">
        <br>
        <h2>Room Booking</h2>

        <?php if (isset($success)) : ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">
                Booking Details
            </div>
            <div class="card-body">
                <form method="POST" action="" class="row g-3 needs-validation" novalidate>
                    <div class="col-md-6">
                        <label for="guest_id" class="form-label"><strong>Guest:</strong></label>
                        <select name="guest_id" id="guest_id" class="form-select" required>
                            <option value="">Select Guest</option>
                            <?php
                            mysqli_data_seek($guest_result, 0);
                            while ($guest = mysqli_fetch_assoc($guest_result)) {
                                echo "<option value='" . htmlspecialchars($guest['guest_id']) . "'>" . htmlspecialchars($guest['guest_name']) . "</option>";
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Please select a guest.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="room_type" class="form-label"><strong>Room Type:</strong></label>
                        <select name="room_type" id="room_type" class="form-select" required>
                            <option value="">Select Room Type</option>
                            <?php
                            mysqli_data_seek($room_type_result, 0);
                            while ($room_type = mysqli_fetch_assoc($room_type_result)) {
                                echo "<option value='" . htmlspecialchars($room_type['id']) . "'>" . htmlspecialchars($room_type['type']) . "</option>";
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Please select a room type.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="room_id" class="form-label"><strong>Room Number:</strong></label>
                        <select name="room_id" id="room_id" class="form-select" required>
                            <option value="">Select Room</option>
                        </select>
                        <div class="invalid-feedback">Please select a room.</div>
                    </div>

                    <!-- Hidden field to store the original room ID -->
                    <input type="hidden" name="original_room_id" id="original_room_id" value="">

                    <div class="col-md-6" id="upgrade_room_wrapper">
                        <label for="upgrade_room_id" class="form-label"><strong>Upgrade Room:</strong></label>
                        <select name="upgrade_room_id" id="upgrade_room_id" class="form-select">
                            <option value="">Select Upgrade Room</option>
                        </select>
                        <div class="invalid-feedback">Please select an upgrade room if upgrade reward is chosen.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="check_in_date" class="form-label"><strong>Check-in Date:</strong></label>
                        <input type="datetime-local" name="check_in_date" id="check_in_date" class="form-control" required>
                        <div class="invalid-feedback">Please provide a valid check-in date.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="check_out_date" class="form-label"><strong>Check-out Date:</strong></label>
                        <input type="datetime-local" name="check_out_date" id="check_out_date" class="form-control" required>
                        <div class="invalid-feedback">Please provide a valid check-out date.</div>
                    </div>

                    <div class="col-md-6">
                        <label for="reward_id" class="form-label"><strong>Reward:</strong></label>
                        <select name="reward_id" id="reward_id" class="form-select">
                            <option value="">Select Reward (Optional)</option>
                        </select>
                    </div>

                    <div class="col-12 mt-4">
                        <button type="submit" class="btn btn-primary">Book Room</button>
                    </div>

                    <div class="col-12 mt-5">
                        <div class="row row-section">
                            <div class="col-md-6 guest-info" id="guestInfo">
                                <h4>Guest Information</h4>
                                <p><strong>Guest Name:</strong> <span id="g_name"></span></p>
                                <p><strong>Guest Email:</strong> <span id="g_email"></span></p>
                                <p><strong>Guest Phone:</strong> <span id="g_phone"></span></p>
                                <p><strong>Guest DOB:</strong> <span id="g_dob"></span></p>
                                <p><strong>Tier:</strong> <span id="g_tier"></span></p>
                                <p><strong>Points:</strong> <span id="g_points"></span></p>
                            </div>

                            <div class="col-md-6 price-info" id="priceInfo">
                                <h4>Price Information</h4>
                                <p><strong>Room Num:</strong> <span id="p_room_num"></span></p>
                                <p><strong>Type:</strong> <span id="p_room_type"></span></p>
                                <p><strong>Price per day:</strong> RM<span id="p_price"></span></p>
                                <p><strong>Total days:</strong> <span id="p_days"></span></p>
                                <hr>
                                <p><strong>Total Price:</strong> RM<span id="p_total"></span></p>
                                <p id="upgrade_info" style="margin-top:10px; font-style: italic;"></p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms)
                .forEach(function(form) {
                    form.addEventListener('submit', function(event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        form.classList.add('was-validated')
                    }, false)
                })
        })();

        let selectedRoom = {
            room_num: '',
            room_type: '',
            room_price: 0,
            room_type_id: 0
        };

        let selectedReward = null;
        let rewardDataCache = [];
        let upgradeScenarioNoNextTier = false;

        // Helper: get next room type ID
        function getNextRoomTypeId(currentTypeId) {
            // 1=superior, 2=deluxe, 3=luxury
            if (currentTypeId === 1) return 2;
            if (currentTypeId === 2) return 3;
            return null;
        }

        function updatePriceInfo() {
            const checkIn = document.getElementById('check_in_date').value;
            const checkOut = document.getElementById('check_out_date').value;
            const priceInfoDiv = document.getElementById('priceInfo');
            const upgradeInfo = document.getElementById('upgrade_info');
            upgradeInfo.textContent = '';

            if (selectedRoom.room_price && checkIn && checkOut) {
                const checkInDate = new Date(checkIn);
                const checkOutDate = new Date(checkOut);
                if (checkOutDate > checkInDate) {
                    const diffTime = Math.abs(checkOutDate - checkInDate);
                    const totalDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                    const originalPrice = selectedRoom.room_price * totalDays;
                    let finalPrice = originalPrice;

                    if (selectedReward && selectedReward.reward_type === 'upgrade_room') {
                        if (upgradeScenarioNoNextTier) {
                            // Already top tier, apply 50% discount
                            finalPrice = originalPrice * 0.5;
                            upgradeInfo.textContent = `No higher tier available. 50% discount applied.`;
                        } else {
                            // If upgrade is chosen and next tier is available
                            upgradeInfo.textContent = `Upgrade selected. Price will recalculate with chosen upgrade room.`;
                        }
                    } else if (selectedReward && selectedReward.reward_type === 'discount') {
                        let discountRate = parseFloat(selectedReward.discount_rate);
                        if (isNaN(discountRate)) discountRate = 0;
                        finalPrice = originalPrice * ((100 - discountRate) / 100);
                        upgradeInfo.textContent = `Applied ${discountRate}% discount.`;
                    }

                    document.getElementById('p_room_num').textContent = selectedRoom.room_num;
                    document.getElementById('p_room_type').textContent = selectedRoom.room_type;
                    document.getElementById('p_price').textContent = selectedRoom.room_price.toFixed(2);
                    document.getElementById('p_days').textContent = totalDays;
                    document.getElementById('p_total').textContent = finalPrice.toFixed(2);
                    priceInfoDiv.style.display = 'block';
                } else {
                    priceInfoDiv.style.display = 'none';
                }
            } else {
                priceInfoDiv.style.display = 'none';
            }
        }

        document.getElementById('guest_id').addEventListener('change', function() {
            var guestId = this.value;
            var guestInfoDiv = document.getElementById('guestInfo');
            var rewardSelect = document.getElementById('reward_id');

            if (guestId) {
                fetch('../api/fetch_guest_info.php?guest_id=' + guestId)
                    .then(response => response.json())
                    .then(data => {
                        if (data) {
                            document.getElementById('g_name').textContent = data.guest_name || '';
                            document.getElementById('g_email').textContent = data.guest_email || '';
                            document.getElementById('g_phone').textContent = data.guest_phone || '';
                            document.getElementById('g_dob').textContent = data.guest_dob || '';
                            document.getElementById('g_tier').textContent = data.tier_level || 'N/A';
                            document.getElementById('g_points').textContent = data.points || '0';

                            guestInfoDiv.style.display = 'block';

                            const guestTier = data.tier_level || 'bronze';
                            const guestPoints = data.points || 0;

                            fetch('../api/fetch_rewards_by_guest.php?tier=' + guestTier + '&points=' + guestPoints)
                                .then(resp => resp.json())
                                .then(rewards => {
                                    rewardSelect.innerHTML = '<option value="">Select Reward (Optional)</option>';
                                    rewardDataCache = rewards;
                                    if (rewards.length > 0) {
                                        rewards.forEach(rw => {
                                            var opt = document.createElement('option');
                                            opt.value = rw.reward_id;
                                            opt.textContent = rw.reward_name + ' - (' + rw.description + ') ' + 'Requires: ' + rw.points_required + 'pts';
                                            rewardSelect.appendChild(opt);
                                        });
                                    }
                                })
                                .catch(err => console.error('Error fetching rewards:', err));

                        } else {
                            guestInfoDiv.style.display = 'none';
                            rewardSelect.innerHTML = '<option value="">Select Reward (Optional)</option>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching guest info:', error);
                        guestInfoDiv.style.display = 'none';
                    });
            } else {
                guestInfoDiv.style.display = 'none';
            }
        });

        document.getElementById('reward_id').addEventListener('change', function() {
            const rid = this.value;
            const upgradeRoomWrapper = document.getElementById('upgrade_room_wrapper');
            const roomTypeSelect = document.getElementById('room_type');
            const roomIdSelect = document.getElementById('room_id');

            if (rid) {
                const r = rewardDataCache.find(rew => rew.reward_id == rid);
                selectedReward = r || null;
            } else {
                selectedReward = null;
            }

            if (selectedReward && selectedReward.reward_type === 'upgrade_room') {
                let nextTierId = getNextRoomTypeId(selectedRoom.room_type_id);
                if (nextTierId) {
                    upgradeScenarioNoNextTier = false;
                    fetch('../api/fetch_rooms_by_type.php?room_type_id=' + nextTierId)
                        .then(response => response.json())
                        .then(data => {
                            const upgradeSelect = document.getElementById('upgrade_room_id');
                            upgradeSelect.innerHTML = '<option value="">Select Upgrade Room</option>';

                            if (data.length > 0) {
                                data.forEach(room => {
                                    var option = document.createElement('option');
                                    option.value = room.room_id;
                                    option.textContent = 'Room ' + room.room_num + ' (RM' + room.room_price + ')';
                                    upgradeSelect.appendChild(option);
                                });
                                upgradeRoomWrapper.style.display = 'block';

                                // Make them look read-only by adding a class
                                roomTypeSelect.classList.add('readonly-select');
                                roomIdSelect.classList.add('readonly-select');

                            } else {
                                upgradeSelect.innerHTML = '<option value="">No Upgrade Rooms Available</option>';
                                upgradeRoomWrapper.style.display = 'block';
                                roomTypeSelect.classList.add('readonly-select');
                                roomIdSelect.classList.add('readonly-select');
                            }

                            updatePriceInfo();
                        })
                        .catch(err => {
                            console.error('Error fetching upgrade rooms:', err);
                            upgradeRoomWrapper.style.display = 'none';
                        });
                } else {
                    // Already top tier (luxury)
                    upgradeScenarioNoNextTier = true;
                    upgradeRoomWrapper.style.display = 'none';
                    roomTypeSelect.classList.remove('readonly-select');
                    roomIdSelect.classList.remove('readonly-select');
                    updatePriceInfo();
                }
            } else {
                // Not an upgrade reward
                upgradeScenarioNoNextTier = false;
                upgradeRoomWrapper.style.display = 'none';
                roomTypeSelect.classList.remove('readonly-select');
                roomIdSelect.classList.remove('readonly-select');
                updatePriceInfo();
            }
        });

        document.getElementById('upgrade_room_id').addEventListener('change', function() {
            updatePriceInfo();
        });

        document.getElementById('room_type').addEventListener('change', function() {
            var roomTypeId = this.value;
            var roomSelect = document.getElementById('room_id');

            roomSelect.innerHTML = '<option value="">Select Room</option>';

            if (roomTypeId) {
                fetch('../api/fetch_rooms_by_type.php?room_type_id=' + roomTypeId)
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(room => {
                            var option = document.createElement('option');
                            option.value = room.room_id;
                            option.dataset.roomNum = room.room_num;
                            option.dataset.roomPrice = room.room_price;
                            option.dataset.roomType = room.type;
                            option.dataset.roomTypeId = room.room_type;
                            option.textContent = 'Room ' + room.room_num + ' (RM' + room.room_price + ')';
                            roomSelect.appendChild(option);
                        });
                    })
                    .catch(error => console.error('Error fetching rooms:', error));
            }
        });

        document.getElementById('room_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (selectedOption.value) {
                selectedRoom.room_num = selectedOption.dataset.roomNum;
                selectedRoom.room_price = parseFloat(selectedOption.dataset.roomPrice);
                selectedRoom.room_type = selectedOption.dataset.roomType;
                selectedRoom.room_type_id = parseInt(selectedOption.dataset.roomTypeId);

                // Store the selected room ID in the hidden input
                document.getElementById('original_room_id').value = selectedOption.value;

                updatePriceInfo();
            } else {
                selectedRoom = {
                    room_num: '',
                    room_type: '',
                    room_price: 0,
                    room_type_id: 0
                };
                document.getElementById('original_room_id').value = '';
                document.getElementById('priceInfo').style.display = 'none';
            }
        });

        document.getElementById('check_in_date').addEventListener('change', updatePriceInfo);
        document.getElementById('check_out_date').addEventListener('change', updatePriceInfo);
    </script>
</body>

</html>

<?php
unset($_SESSION['error_message']);
?>