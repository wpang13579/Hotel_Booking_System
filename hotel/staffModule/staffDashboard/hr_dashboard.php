<?php
require('../../staffModule/staff_authentication.php');

// Check if the user is logged in and if their role_id is not 1
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
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
    <title>Human Resource Dashboard</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Container */
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px;
        }

        /* Header Styles */
        header {
            background-color: #0066cc;
            color: white;
            text-align: center;
            padding: 50px 0;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        header h1 {
            font-size: 48px;
            margin-bottom: 10px;
        }

        header p {
            font-size: 30px; /* Increased font size */
            font-weight: 300;
            margin-top: 10px; /* Added some margin to separate from the heading */
        }

        /* Dashboard Content */
        .dashboard-content {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .dashboard-item {
            background-color: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .dashboard-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
        }

        .dashboard-item h2 {
            font-size: 22px;
            margin-bottom: 15px;
            color: #333;
        }

        .dashboard-item p {
            font-size: 16px;
            margin-bottom: 20px;
            color: #666;
        }

        .dashboard-item a {
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .dashboard-item a:hover {
            background-color: #218838;
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 15px 0;
            background-color: #343a40;
            color: white;
            border-radius: 8px;
            margin-top: 30px;
        }

        footer a {
            color: white;
            font-weight: bold;
            font-size: 16px;
            text-transform: uppercase;
            padding: 12px 24px;
            background-color: #dc3545;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        footer a:hover {
            background-color: #c82333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header h1 {
                font-size: 36px;
            }

            header p {
                font-size: 24px; /* Adjusted font size for smaller screens */
            }

            .dashboard-item {
                padding: 20px;
            }

            .dashboard-item h2 {
                font-size: 18px;
            }

            .dashboard-item p {
                font-size: 14px;
            }

            footer a {
                padding: 10px 20px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <header>
            <h1>Human Resource Dashboard</h1>
            <p>Welcome back, <?php echo htmlspecialchars($staff_firstname); ?>! Manage your HR tasks here.</p>
        </header>

        <div class="dashboard-content">
            <div class="dashboard-item">
                <h2>Staff Management</h2>
                <p>Manage all staff-related information and updates.</p>
                <a href="../staffDashboard/staffinfo_dashboard.php">Go to Staff Management</a>
            </div>

            <div class="dashboard-item">
                <h2>Staff Schedule</h2>
                <p>View and manage staff schedules and shifts.</p>
                <a href="../staffDashboard/staffschedule_dashboard.php">Go to Staff Schedule</a>
            </div>

            <div class="dashboard-item">
                <h2>Staff Rating</h2>
                <p>Rate and evaluate staff performance.</p>
                <a href="../staffDashboard/staffrating_dashboard.php">Go to Staff Rating</a>
            </div>

            <div class="dashboard-item">
                <h2>Staff Performance Report</h2>
                <p>Generate detailed performance reports for staff.</p>
                <a href="../staffReport/staff_report.php">View Performance Report</a>
            </div>
        </div>

        <footer>
            <a href="../staff_logout.php">Logout</a>
        </footer>
    </div>

</body>

</html>
