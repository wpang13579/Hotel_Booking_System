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
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Guest Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-left: 260px;
            padding-top: 20px;
        }

        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .form-inline .form-control {
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .container {
                margin-left: 0;
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
        <div class="table-container">
            <h2 class="mb-4">View Guest Profile</h2>

            <!-- Search and Filter Form -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by Guest ID or Email" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                </div>
                <div class="col-md-3">
                    <select name="tier" class="form-select">
                        <option value="">All Tiers</option>
                        <option value="bronze" <?php echo (isset($_GET['tier']) && $_GET['tier'] == 'bronze') ? 'selected' : ''; ?>>Bronze</option>
                        <option value="silver" <?php echo (isset($_GET['tier']) && $_GET['tier'] == 'silver') ? 'selected' : ''; ?>>Silver</option>
                        <option value="gold" <?php echo (isset($_GET['tier']) && $_GET['tier'] == 'gold') ? 'selected' : ''; ?>>Gold</option>
                        <option value="platinum" <?php echo (isset($_GET['tier']) && $_GET['tier'] == 'platinum') ? 'selected' : ''; ?>>Platinum</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="start_date" class="form-control" placeholder="Start Date" value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : ''; ?>">
                </div>
                <div class="col-md-2">
                    <input type="date" name="end_date" class="form-control" placeholder="End Date" value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : ''; ?>">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="col-md-12">
                    <a href="view_guest_profile.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Date of Birth</th>
                            <th scope="col">Phone Number</th>
                            <th scope="col">Address</th>
                            <th scope="col">Tier</th>
                            <th scope="col">Register Date</th>
                            <th scope="col">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Initialize variables for search and filters
                        $search = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
                        $tier = isset($_GET['tier']) ? mysqli_real_escape_string($con, $_GET['tier']) : '';
                        $start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($con, $_GET['start_date']) : '';
                        $end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($con, $_GET['end_date']) : '';

                        // Base query with join to loyalty_program
                        $base_query = "SELECT guest.*, loyalty_program.tier_level 
                                       FROM guest 
                                       LEFT JOIN loyalty_program ON guest.guest_id = loyalty_program.guest_id 
                                       WHERE 1=1";

                        // Apply search filter
                        if (!empty($search)) {
                            $base_query .= " AND (guest.guest_id LIKE '%$search%' OR guest.guest_email LIKE '%$search%')";
                        }

                        // Apply tier filter
                        if (!empty($tier)) {
                            $base_query .= " AND loyalty_program.tier_level = '$tier'";
                        }

                        // Apply date range filter
                        if (!empty($start_date) && !empty($end_date)) {
                            $base_query .= " AND DATE(guest.record_date) BETWEEN '$start_date' AND '$end_date'";
                        } elseif (!empty($start_date)) {
                            $base_query .= " AND DATE(guest.record_date) >= '$start_date'";
                        } elseif (!empty($end_date)) {
                            $base_query .= " AND DATE(guest.record_date) <= '$end_date'";
                        }

                        $base_query .= " ORDER BY guest.guest_id ASC;";

                        $result = mysqli_query($con, $base_query);

                        if (mysqli_num_rows($result) > 0) {
                            $count = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $count++ . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_email']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_dob']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_phone']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['guest_address']) . "</td>";
                                echo "<td>" . ucfirst(htmlspecialchars($row['tier_level'] ?? 'N/A')) . "</td>";
                                echo "<td>" . htmlspecialchars($row['record_date']) . "</td>";
                                echo "<td><a href='../guest/guest_profile_edit.php?guest_id=" . $row['guest_id'] . "' class='btn btn-sm btn-warning'>Edit</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9' class='text-center'>No guests found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>