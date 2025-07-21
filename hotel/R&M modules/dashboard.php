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

// Check if the logged-in user is a manager
$role_id = $_SESSION['role_id'];
$roles = [
    2 => 'Admin',
    4 => 'Room Manager',
    6 => 'Normal Staff',

];

// Get the role name
$role_name = isset($roles[$role_id]) ? $roles[$role_id] : 'Unknown Role';

// Query to check for emergency maintenance requests
$emergency_query = "SELECT COUNT(*) AS emergency_count 
                    FROM maintenance_request 
                    WHERE req_desc LIKE 'Emergency:%' AND req_status = 'Pending'";
$emergency_result = mysqli_query($con, $emergency_query);
$emergency_count = 0;

if ($emergency_result) {
    $emergency_row = mysqli_fetch_assoc($emergency_result);
    $emergency_count = $emergency_row['emergency_count'];
}

// Query to check if the staff is assigned to any emergency task
$assigned_emergency_query = "SELECT COUNT(*) AS assigned_emergency_count 
                             FROM maintenance_task t
                             JOIN maintenance_request r ON t.req_id = r.req_id
                             WHERE t.staff_id = $staff_id AND r.req_desc LIKE 'Emergency:%' AND t.task_status = 'in progress'";
$assigned_emergency_result = mysqli_query($con, $assigned_emergency_query);
$assigned_emergency_count = 0;

if ($assigned_emergency_result) {
    $assigned_emergency_row = mysqli_fetch_assoc($assigned_emergency_result);
    $assigned_emergency_count = $assigned_emergency_row['assigned_emergency_count'];
}

// Track if the emergency task prompt has been shown
if (!isset($_SESSION['emergency_prompt_shown']) && $assigned_emergency_count > 0) {
    $_SESSION['emergency_prompt_shown'] = true;
    $show_prompt = true;
} else {
    $show_prompt = false;
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Room And Maintenance Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .sidebar {
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 250px;
            background-color: #343a40;
            color: white;
            padding-top: 20px;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            display: block;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .content {
            margin-left: 260px;
            /* Matches sidebar width */
            padding: 20px;
        }

        .section-header {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 5px 5px 0 0;
        }

        .section-content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #ced4da;
            border-radius: 0 0 5px 5px;
        }
    </style>


    <script>
        // Prompt action for emergency maintenance requests (for managers)
        <?php if ($role_id == 4 && $emergency_count > 0): ?>
            window.onload = function() {
                const handleEmergency = confirm("You have <?php echo $emergency_count; ?> pending emergency maintenance request(s). Do you want to handle them now?");
                if (handleEmergency) {
                    window.location.href = "maintenance request/viewEmergencyRequest.php";
                }
            };
        <?php endif; ?>

        // Prompt action for assigned emergency tasks (for staff)
        <?php if ($role_id == 6 && $assigned_emergency_count > 0): ?>
            window.onload = function() {
                const handleAssignedTasks = confirm("You have <?php echo $assigned_emergency_count; ?> emergency maintenance task(s) assigned to you. Do you want to handle them now?");
                if (handleAssignedTasks) {
                    window.location.href = "maintenance task/viewEmergencyTask.php";
                }
            };
        <?php endif; ?>
    </script>

</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <h4 class="text-center">Room Maintenance </h4>
        <a href="../staffModule/staffShift/staffshift_view.php">View Shift</a>
        <a href="room management/viewRoom.php">Room</a>
        <a href="maintenance request/viewMainRequest.php">Maintenance Requests</a>
        <a href="maintenance task/viewMainTask.php">Maintenance Task</a>
        <a href="viewMainReport.php">Maintenance Report</a>
        <a href="maintenance request/viewEmergencyRequest.php">Emergency Request</a>
        <a href="maintenance task/viewEmergencyTask.php">Emergency Task</a>

        <?php if ($role_id == 4): ?>
                    <button class="btn" style="color: white;" onclick="window.location.href='dashboard.php'">Back to Dashboard</button>
        <?php endif; ?>

        <?php if ($role_id != 4): ?>
                    <button class="btn" style="color: white;" onclick="window.location.href='../staffModule/staffDashboard/nstaff_dashboard.php'">Back to Staff Dashboard</button>
        <?php endif; ?>

        <a href="../staffModule/staff_logout.php" style="color: red;">Logout</a>
    </div>

    <!-- Content Area -->
    <div class="content">
        <div class="container">
            <h1>Room and Maintenance Dashboard</h1>
            <p><strong>Welcome:</strong> <?php echo htmlspecialchars($staff_name); ?> (<?php echo htmlspecialchars($role_name); ?>)</p>

            <!-- Room and Maintenance Dashboard -->
            <div class="mb-4">
                <div class="section-header">
                    <h5>Room and Maintenance</h5>
                </div>
                <div class="section-content">
                    <ul class="list-group">
                        <li class="list-group-item"><a href="room management/viewRoom.php">View Rooms</a></li>
                        <li class="list-group-item"><a href="maintenance request/viewMainRequest.php">View Maintenance Requests</a></li>
                        <li class="list-group-item"><a href="maintenance task/viewMainTask.php">View Maintenance Task</a></li>
                    </ul>
                </div>
            </div>

            <!-- Emergency Dashboard -->
            <div class="mb-4">
                <div class="section-header">
                    <h5>Emergency Maintenance</h5>
                </div>
                <div class="section-content">
                    <ul class="list-group">
                        <li class="list-group-item"><a href="maintenance request/viewEmergencyRequest.php">View Emergency Request</a></li>
                        <li class="list-group-item"><a href="maintenance task/viewEmergencyTask.php">View Emergency Task</a></li>
                    </ul>
                </div>
            </div>

            <!-- Maintenance Report Section -->
            <div class="mb-4">
                <div class="section-header">
                    <h5>Maintenance Report</h5>
                </div>
                <div class="section-content">
                    <ul class="list-group">
                        <li class="list-group-item"><a href="viewMainReport.php">View Maintenance Report</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>