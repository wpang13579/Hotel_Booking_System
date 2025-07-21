<?php
session_start();
require('../database.php');

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
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Guest Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../guestModule/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom right, #f4f6f9, #e9ecf3);
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            background-color: #05106F;
        }

        .navbar-brand {
            color: #ffffff !important;
            font-weight: bold;
        }

        .container {
            margin-left: 250px;
            padding-top: 20px;
        }

        /* Adjust the layout of the card deck */
        .card-deck {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            /* Add space between the cards */
            justify-content: space-evenly;
            /* Distribute the cards evenly */
            padding: 0;
        }

        .card {
            width: 18rem;
            /* Fixed width for cards */
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            height: 250px;
            margin-bottom: 20px;
            /* Adjust vertical spacing between rows */
        }

        .card-header {
            background-color: #05106F;
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
            text-align: center;
            padding: 1.5rem;
        }

        .card-body {
            padding: 20px;
        }

        .btn-submit {
            background-color: #05106F;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #0d2760;
        }

        .form-group button {
            width: 100%;
            text-align: center;
            font-size: 1.1rem;
            padding: 10px;
            margin: 10px 0;
        }

        .error-message {
            color: red;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .text-danger {
            font-size: 0.9rem;
        }

        .card-body a {
            color: #05106F;
            text-decoration: none;
            font-weight: bold;
            display: block;
            text-align: center;
            margin-top: 15px;
        }

        .card-body a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h4 class="text-center">Guest Management</h4>
        <ul class="list-unstyled">
            <li>
                <a href="../guestModule/guest_dashboard.php">Dashboard</a>
            </li>
            <li>
                <a href="#">Guest</a>
                <ul class="submenu list-unstyled">
                    <li><a href="../guestModule/guest/guest_registration.php">Guest Registration</a></li>
                    <li><a href="../guestModule/guest/view_guest_profile.php">Guest Profile Management</a></li>
                </ul>
            </li>
            <!-- Only show if user is Admin or Guest Manager -->
            <?php if ($is_manager_or_admin): ?>
                <li>
                    <a href="#">Reward</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="../guestModule/reward/reward_management.php">Reward Management</a></li>
                    </ul>
                </li>
            <?php endif; ?>
            <li>
                <a href="#">Booking</a>
                <ul class="submenu list-unstyled">
                    <li><a href="../guestModule/room/booking.php">New Booking</a></li>
                    <li><a href="../guestModule/room/view_booking_room.php">Booking Management</a></li>
                </ul>
            </li>
            <!-- Only show if user is Admin or Guest Manager -->
            <?php if ($is_manager_or_admin): ?>
                <li>
                    <a href="#">Report</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="../guestModule/report/guest_report.php">Report</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <a href="../staffModule/staff_logout.php" style="color: red;">Logout</a>
        </ul>
    </div>

    <div class="container">
        <nav style="background-color: #05106F; padding: 20px;">
            <h1 style="color: white;">Guest Management Dashboard</h1>
        </nav>
        <br>
        <h3>Welcome, <?php echo htmlspecialchars($staff_name); ?></h3>
        <h4>Role: <?php echo htmlspecialchars($role_name); ?></h4>
        <br>
        <div class="card-deck">
            <div class="card">
                <div class="card-header">Guest Management</div>
                <div class="card-body">
                    <p>Manage guest profiles and registrations.</p>
                    <a href="../guestModule/guest/guest_registration.php" class="btn btn-submit" style="color: white;">Go to Guest Management</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Booking Management</div>
                <div class="card-body">
                    <p>Manage room bookings and availability.</p>
                    <a href="../guestModule/room/booking.php" class="btn btn-submit" style="color: white;">Go to Booking Management</a>
                </div>
            </div>
            <div class="card">
                <div class="card-header">Staff Shifting</div>
                <div class="card-body">
                    <p>Manage staff shifts and schedules.</p>
                    <a href="../staffModule/staffShift/staffshift_view.php" class="btn btn-submit" style="color: white;">Go to Staff Shifting</a>
                </div>
            </div>
            <div class="card-deck">
                <!-- Only show Reward and Report cards for Admin or Manager -->
                <?php if ($is_manager_or_admin): ?>
                    <div class="card">
                        <div class="card-header">Reward Management</div>
                        <div class="card-body">
                            <p>Manage rewards and loyalty programs.</p>
                            <a href="../guestModule/reward/reward_management.php" class="btn btn-submit" style="color: white;">Go to Reward Management</a>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">Report</div>
                        <div class="card-body">
                            <p>View & Generate guest-related reports.</p>
                            <a href="../guestModule/report/guest_report.php" class="btn btn-submit" style="color: white;">Go to Report</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>

<?php
unset($_SESSION['error_message']);
?>