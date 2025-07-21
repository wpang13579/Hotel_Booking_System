<?php
require('../../database.php');

session_start(); // Start the session at the beginning

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reward_name = $_POST['reward_name'];
    $description  = $_POST['description'];
    $points_required  = $_POST['points_required'];
    $reward_type  = $_POST['reward_type'];
    $tier_required  = $_POST['tier_required'];
    $discount_rate = $_POST['discount_rate'];

    $query = "INSERT INTO reward (reward_name, description, points_required, reward_type, tier_required, discount_rate)
                VALUES ('$reward_name','$description', '$points_required', '$reward_type', '$tier_required', '$discount_rate')";
    $result = mysqli_query($con, $query);
    if ($result) {
        $status = "<p style='color: green;'>Reward data inserted successfully.</p>";
    } else {
        $status = "<p style='color: red;'>Reward data failed to be inserted.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reward</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .container {
            margin-left: 270px;
        }

        .card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #05106F;
            color: #fff;
            font-size: 1.25rem;
            font-weight: 600;
            border-radius: 10px 10px 0 0;
        }

        .card-body {
            background-color: #fff;
        }

        .form-control,
        .form-select {
            border-radius: 5px;
        }

        label {
            font-weight: 600;
        }

        .btn-primary {
            background-color: #05106F;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0d2760;
        }

        table thead th {
            background-color: #05106F;
            color: #fff;
            font-weight: 600;
        }

        table tbody tr:hover {
            background-color: #f1f3f5;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h4 class="text-center">Guest Management</h4>
        <ul class="list-unstyled">
            <li><a href="../guest_dashboard.php">Dashboard</a></li>
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

    <div class="container pb-5">
        <br>
        <!-- Add New Reward Form Card -->
        <div class="card mb-5">
            <div class="card-header">
                Add New Reward
            </div>
            <div class="card-body">
                <!-- Status Message -->
                <?php if (isset($status)) echo $status; ?>

                <!-- Reward Form -->
                <form method="POST" action="">
                    <div class="form-row">
                        <!-- Reward Name -->
                        <div class="form-group col-md-4">
                            <label for="reward_name">Reward Name</label>
                            <input type="text" class="form-control" id="reward_name" name="reward_name" required placeholder="Enter reward name">
                        </div>

                        <!-- Points Required -->
                        <div class="form-group col-md-2">
                            <label for="points_required">Points Required</label>
                            <input type="number" class="form-control" id="points_required" name="points_required" required placeholder="e.g. 100">
                        </div>

                        <!-- Tier Required -->
                        <div class="form-group col-md-2">
                            <label for="tier_required">Tier Required</label>
                            <select class="form-control" id="tier_required" name="tier_required" required>
                                <option value="bronze">Bronze</option>
                                <option value="silver">Silver</option>
                                <option value="gold">Gold</option>
                                <option value="platinum">Platinum</option>
                            </select>
                        </div>

                        <!-- Reward Type -->
                        <div class="form-group col-md-2">
                            <label for="reward_type">Reward Type</label>
                            <select class="form-control" id="reward_type" name="reward_type" required onchange="toggleDiscountRate()">
                                <option value="upgrade_room">Room Upgrade</option>
                                <option value="discount">Discount</option>
                            </select>
                        </div>

                        <!-- Discount Rate -->
                        <div class="form-group col-md-2" id="discount_rate_field">
                            <label for="discount_rate">Discount Rate (%)</label>
                            <input type="number" class="form-control" id="discount_rate" name="discount_rate" step="0.01" required placeholder="e.g. 10">
                        </div>
                    </div>

                    <!-- Description Row -->
                    <div class="form-group mt-3">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required placeholder="Describe the reward..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Add Reward</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reward List Card -->
        <div class="card">
            <div class="card-header">
                Reward List
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Description</th>
                            <th>Points Required</th>
                            <th>Reward Type</th>
                            <th>Tier Required</th>
                            <th>Discount Rate</th>
                            <th colspan="2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require('../../database.php');
                        $sel_query = "SELECT * FROM reward ORDER BY reward_id ASC";
                        $result = mysqli_query($con, $sel_query);
                        while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td><?php echo $row['reward_id']; ?></td>
                                <td><?php echo $row['description']; ?></td>
                                <td><?php echo $row['points_required']; ?></td>
                                <td><?php echo $row['reward_type']; ?></td>
                                <td><?php echo $row['tier_required']; ?></td>
                                <td><?php echo $row['discount_rate']; ?></td>
                                <td><a href="edit_reward.php?id=<?php echo $row['reward_id']; ?>" class="btn btn-sm btn-warning">Edit</a></td>
                                <td><a href="delete_reward.php?id=<?php echo $row['reward_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this reward?')">Delete</a></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleDiscountRate() {
            var rewardType = document.getElementById("reward_type").value;
            var discountRateField = document.getElementById("discount_rate_field");

            if (rewardType === "discount") {
                discountRateField.style.display = "block";
                document.getElementById("discount_rate").required = true;
            } else {
                discountRateField.style.display = "none";
                document.getElementById("discount_rate").required = false;
                document.getElementById("discount_rate").value = "";
            }
        }

        window.onload = function() {
            toggleDiscountRate();
        };
    </script>

    <!-- JS for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>