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

$guest_id = isset($_GET['guest_id']) ? (int)$_GET['guest_id'] : 0;

// Fetch guest info
$guest_query = "SELECT * FROM guest WHERE guest_id = ?";
$stmt = mysqli_prepare($con, $guest_query);
mysqli_stmt_bind_param($stmt, 'i', $guest_id);
mysqli_stmt_execute($stmt);
$guest_result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($guest_result) == 0) {
    echo "Guest not found.";
    exit;
}
$guest = mysqli_fetch_assoc($guest_result);
mysqli_stmt_close($stmt);

// Fetch loyalty program info
$loyalty_query = "SELECT * FROM loyalty_program WHERE guest_id = ?";
$stmt = mysqli_prepare($con, $loyalty_query);
mysqli_stmt_bind_param($stmt, 'i', $guest_id);
mysqli_stmt_execute($stmt);
$loyalty_result = mysqli_stmt_get_result($stmt);
$loyalty = mysqli_fetch_assoc($loyalty_result);
mysqli_stmt_close($stmt);

// Fetch redemption records
$redemption_query = "SELECT * FROM redemption_record WHERE guest_id = ? ORDER BY redemption_id DESC";
$stmt = mysqli_prepare($con, $redemption_query);
mysqli_stmt_bind_param($stmt, 'i', $guest_id);
mysqli_stmt_execute($stmt);
$redemption_result = mysqli_stmt_get_result($stmt);
$redemptions = mysqli_fetch_all($redemption_result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Fetch stay records
$stay_query = "SELECT * FROM stay WHERE guest_id = ? ORDER BY stay_id DESC";
$stmt = mysqli_prepare($con, $stay_query);
mysqli_stmt_bind_param($stmt, 'i', $guest_id);
mysqli_stmt_execute($stmt);
$stay_result = mysqli_stmt_get_result($stmt);
$stays = mysqli_fetch_all($stay_result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Handle form submission for guest info update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $guest_name = $_POST['guest_name'];
    $guest_email = $_POST['guest_email'];
    $guest_dob = $_POST['guest_dob'];
    $guest_phone = $_POST['guest_phone'];
    $guest_address = $_POST['guest_address'];
    $gender = $_POST['gender'];

    $update_query = "UPDATE guest SET guest_name=?, guest_email=?, guest_dob=?, guest_phone=?, guest_address=?, gender=? WHERE guest_id=?";
    $stmt = mysqli_prepare($con, $update_query);
    mysqli_stmt_bind_param($stmt, 'ssssssi', $guest_name, $guest_email, $guest_dob, $guest_phone, $guest_address, $gender, $guest_id);
    $result = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if ($result) {
        echo "<script>alert('Guest info updated successfully!'); window.location.href='guest_profile_edit.php?guest_id=$guest_id';</script>";
    } else {
        echo "<script>alert('Failed to update guest info.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Guest Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../style.css" rel="stylesheet">
    <style>
        /* Body Styling */
        body {
            background-color: #f8f9fa;
        }

        /* Main Container */
        .main-container {
            margin-left: 240px;
            padding: 20px;
        }

        /* Profile Image */
        .profile-image {
            width: 150px;
            height: 150px;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin-bottom: 20px;
        }

        /* Section Titles */
        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }

        /* Tables */
        table {
            width: 100%;
            margin-bottom: 30px;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background-color: #e9ecef;
        }

        /* Buttons */
        .btn-custom {
            background-color: #343a40;
            color: #fff;
        }

        .btn-custom:hover {
            background-color: #495057;
            color: #fff;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-container {
                margin-left: 0;
            }

            .profile-image {
                width: 100px;
                height: 100px;
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
    <!-- Main Content Container -->
    <div class="main-container">
        <h2>Edit Guest Profile</h2>

        <?php
        // Check if photo exists
        $photo_path = __DIR__ . "/uploads/guest_" . $guest_id . ".png";
        $photo_url = file_exists($photo_path) ? "uploads/guest_" . $guest_id . ".png" : "uploads/placeholder.png";
        ?>

        <div class="d-flex align-items-center mb-4">
            <img src="<?php echo $photo_url; ?>" alt="Guest Photo" class="profile-image me-4">
            <div>
                <h4><?php echo htmlspecialchars($guest['guest_name']); ?></h4>
                <p class="mb-0"><strong>Guest ID:</strong> <?php echo htmlspecialchars($guest['guest_id']); ?></p>
            </div>
        </div>

        <!-- Guest Info Update Form -->
        <form method="POST" class="mb-5">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name:</label>
                    <input type="text" name="guest_name" class="form-control" value="<?php echo htmlspecialchars($guest['guest_name']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email:</label>
                    <input type="email" name="guest_email" class="form-control" value="<?php echo htmlspecialchars($guest['guest_email']); ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date of Birth:</label>
                    <input type="date" name="guest_dob" class="form-control" value="<?php echo htmlspecialchars(date('Y-m-d', strtotime($guest['guest_dob']))); ?>" readonly>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone Number:</label>
                    <input type="text" name="guest_phone" class="form-control" value="<?php echo htmlspecialchars($guest['guest_phone']); ?>" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label">Address:</label>
                    <textarea name="guest_address" class="form-control" rows="3" required><?php echo htmlspecialchars($guest['guest_address']); ?></textarea>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Gender:</label>
                    <select name="gender" class="form-select" required>
                        <option value="male" <?php if ($guest['gender'] == 'male') echo 'selected'; ?>>Male</option>
                        <option value="female" <?php if ($guest['gender'] == 'female') echo 'selected'; ?>>Female</option>
                    </select>
                </div>

                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-custom">Update Guest Info</button>
                </div>
            </div>
        </form>

        <!-- Navigation Links for Sections -->
        <nav class="mb-4">
            <ul class="nav nav-pills">
                <li class="nav-item">
                    <a href="#loyalty" class="nav-link active" data-bs-toggle="pill">Loyalty Program Info</a>
                </li>
                <li class="nav-item">
                    <a href="#redemptions" class="nav-link" data-bs-toggle="pill">Redemption Records</a>
                </li>
                <li class="nav-item">
                    <a href="#stays" class="nav-link" data-bs-toggle="pill">Stay Records</a>
                </li>
            </ul>
        </nav>

        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Loyalty Program Info -->
            <div class="tab-pane fade show active" id="loyalty">
                <h3 class="section-title">Loyalty Program Info</h3>
                <?php if ($loyalty) { ?>
                    <table class="table table-striped table-hover">
                        <tbody>
                            <tr>
                                <th scope="row">Loyalty ID</th>
                                <td><?php echo htmlspecialchars($loyalty['loyalty_id']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Points</th>
                                <td><?php echo htmlspecialchars($loyalty['points']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Tier Level</th>
                                <td><?php echo htmlspecialchars(ucfirst($loyalty['tier_level'])); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Total Points Redeemed</th>
                                <td><?php echo htmlspecialchars($loyalty['total_point_redeem']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">Total Book Days</th>
                                <td><?php echo htmlspecialchars($loyalty['total_book_days']); ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="alert alert-warning" role="alert">
                        No loyalty program info found for this guest.
                    </div>
                <?php } ?>
            </div>

            <!-- Redemption Records -->
            <div class="tab-pane fade" id="redemptions">
                <h3 class="section-title">Redemption Records</h3>
                <?php if (!empty($redemptions)) { ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Redemption ID</th>
                                    <th>Redeem Date</th>
                                    <th>Points Used</th>
                                    <th>Tier</th>
                                    <th>Reward ID</th>
                                    <th>Reward Name</th>
                                    <th>Reward Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($redemptions as $r) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['redemption_id']); ?></td>
                                        <td><?php echo htmlspecialchars($r['redeemp_date']); ?></td>
                                        <td><?php echo htmlspecialchars($r['point_used']); ?></td>
                                        <td><?php echo htmlspecialchars(ucfirst($r['tier'])); ?></td>
                                        <td><?php echo htmlspecialchars($r['reward_id']); ?></td>
                                        <td><?php echo htmlspecialchars($r['reward_name']); ?></td>
                                        <td><?php echo htmlspecialchars($r['reward_type']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info" role="alert">
                        No redemption records found for this guest.
                    </div>
                <?php } ?>
            </div>

            <!-- Stay Records -->
            <div class="tab-pane fade" id="stays">
                <h3 class="section-title">Stay Records</h3>
                <?php if (!empty($stays)) { ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Stay ID</th>
                                    <th>Check-In Date</th>
                                    <th>Check-Out Date</th>
                                    <th>Room ID</th>
                                    <th>Staff ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stays as $s) { ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($s['stay_id']); ?></td>
                                        <td><?php echo htmlspecialchars($s['check_in_date']); ?></td>
                                        <td><?php echo htmlspecialchars($s['check_out_date']); ?></td>
                                        <td><?php echo htmlspecialchars($s['room_id']); ?></td>
                                        <td><?php echo htmlspecialchars($s['staff_id']); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-info" role="alert">
                        No stay records found for this guest.
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-4">
            <a href="../guest/view_guest_profile.php" class="btn btn-secondary">Back to Guest Profiles</a>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>