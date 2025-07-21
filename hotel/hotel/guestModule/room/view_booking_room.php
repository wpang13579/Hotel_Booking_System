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

// Initialize filters
$search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($con, $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($con, $_GET['end_date']) : '';

// Base query
$query = "SELECT r.room_id, r.room_num, r.room_status, g.guest_name, s.check_in_date, s.check_out_date
          FROM room r
          JOIN stay s ON r.room_id = s.room_id
          JOIN guest g ON s.guest_id = g.guest_id
          WHERE r.room_status = 'occupied'";

// Apply search filter if provided
// Search by room_id or room_num
if (!empty($search)) {
    // If search is numeric, try match room_id or room_num
    if (is_numeric($search)) {
        $query .= " AND (r.room_id LIKE '%$search%' OR r.room_num LIKE '%$search%')";
    } else {
        // If search is not numeric, match room_num as string
        $query .= " AND r.room_num LIKE '%$search%'";
    }
}

// Apply date range filter if provided
// We'll assume that we want to find rooms that are occupied within the given date range.
// If both start_date and end_date are provided, we want stays that intersect with this range.
if (!empty($start_date) && !empty($end_date)) {
    // We'll consider a room occupied in the given range if the stay intersects the [start_date, end_date] interval.
    // Intersection condition: (check_in_date <= end_date) AND (check_out_date >= start_date)
    $query .= " AND (s.check_in_date <= '$end_date' AND s.check_out_date >= '$start_date')";
} elseif (!empty($start_date)) {
    // If only start_date is given, show rooms occupied on or after start_date
    $query .= " AND s.check_out_date >= '$start_date'";
} elseif (!empty($end_date)) {
    // If only end_date is given, show rooms occupied on or before end_date
    $query .= " AND s.check_in_date <= '$end_date'";
}

$result = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Occupied Rooms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
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
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #05106F;
            color: #fff;
            font-weight: 600;
            font-size: 1.25rem;
            border-radius: 10px 10px 0 0;
        }

        table thead th {
            background-color: #e9ecef;
            font-weight: 600;
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
            <!-- Only show if user is Admin or Guest Manager -->
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
            <!-- Only show if user is Admin or Guest Manager -->
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
        <h2 class="mb-4">Occupied Rooms</h2>

        <div class="card mb-4">
            <div class="card-header">
                Search & Filter
            </div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Search by Room ID or Room Number" value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="start_date" class="form-control" placeholder="Start Date" value="<?php echo htmlspecialchars($start_date); ?>">
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="end_date" class="form-control" placeholder="End Date" value="<?php echo htmlspecialchars($end_date); ?>">
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">Search</button>
                    </div>
                    <div class="col-12">
                        <a href="view_booking_room.php" class="btn btn-secondary">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                Occupied Rooms List
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Room Number</th>
                            <th>Room Status</th>
                            <th>Guest Name</th>
                            <th>Check-in Date</th>
                            <th>Check-out Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            // Output data for each row
                            while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['room_num']); ?></td>
                                    <td><?php echo htmlspecialchars($row['room_status']); ?></td>
                                    <td><?php echo htmlspecialchars($row['guest_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['check_in_date']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['check_out_date']))); ?></td>
                                    <td>
                                        <a href="check_out.php?room_id=<?php echo $row["room_id"]; ?>" class="btn btn-sm btn-primary">Update</a>
                                    </td>
                                </tr>
                        <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' class='text-center'>No occupied rooms found with the given criteria.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JS for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>