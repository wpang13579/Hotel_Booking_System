<?php
require('../../staffModule/staff_authentication.php');
// Check if the user is logged in and if their role_id is not 2
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 2) {
    echo "<script>
        alert('Session expired. Please login again.');
        window.location.href = '../../staffModule/staff_login.php';
    </script>";
    exit();
}
$staff_firstname = $_SESSION['staff_firstname'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fc;
        }

        header {
            background-color: #004d99;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            box-shadow: 2px 0px 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            font-size: 16px;
            margin: 10px 0;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .sidebar a:hover {
            background-color: #3498db;
        }

        .main-content {
            flex: 1;
            padding: 20px;
        }

        .main-content h1 {
            color: #333;
        }

        .main-content p {
            color: #555;
            font-size: 1.2em;
        }

        .main-content a {
            display: inline-block;
            color: #3498db;
            text-decoration: none;
            font-size: 1.1em;
            margin: 10px 0;
            border: 2px solid #3498db;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .main-content a:hover {
            background-color: #3498db;
            color: white;
        }

        footer {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            text-align: center;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
    </style>
</head>

<body>

    <header>
        <h1>Welcome, <?php echo htmlspecialchars($staff_firstname); ?>!</h1>
    </header>

    <div class="container">
        <div class="sidebar">
            <h2>Admin Dashboard</h2>
            <a href="../staffDashboard/staffinfo_dashboard.php">Staff Management</a>
            <a href="../staffDashboard/staffschedule_dashboard.php">Staff Schedule</a>
            <a href="../staffDashboard/staffrating_dashboard.php">Staff Rating</a>
            <a href="../staffReport/staff_report.php">Staff Performance Report</a>
            <a href="../../guestModule/guest_dashboard.php">Guest Module</a><!-- Guest Module-->
            <a href="../../R&M modules/dashboard.php">Room and Maintenance Module</a><!-- Room and Maintenance Module-->
            <a href="../../inventoryModule/inventory_management.php">Inventory Module</a><!-- Inventory Module-->
            <a href="../staff_logout.php">Logout</a>
        </div>

        <div class="main-content">
            <h2>Admin Dashboard Overview</h2>
            <p>Use the navigation on the left to manage staff, view performance, and control other admin tasks.</p>

            <!-- links (could be dynamic content depending on your requirements) -->
            <a href="../staffDashboard/staffinfo_dashboard.php">Go to Staff Management</a>
            <a href="../staffDashboard/staffschedule_dashboard.php">Go to Staff Schedule</a>
            <a href="../staffReport/staff_report.php">View Performance Reports</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 Group 3. All rights reserved.</p>
    </footer>

</body>

</html>