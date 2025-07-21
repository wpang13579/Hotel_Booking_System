<?php
require('../../staffModule/staff_authentication.php');

// Check if the user is logged in and if their role_id is not 6
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 6) {
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
    <title>Staff Dashboard</title>
    <style>
        /* Reset and Base Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
            color: #333;
        }

        h1 {
            color: #007bff;
        }

        a {
            text-decoration: none;
            color: white;
        }

        /* Dashboard Container */
        .dashboard {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        /* Buttons/Links */
        .dashboard a {
            display: inline-block;
            margin: 10px 0;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-size: 16px;
        }

        .dashboard a:hover {
            background-color: #0056b3;
        }

        /* Footer Styling */
        footer {
            text-align: center;
            margin-top: 30px;
            font-size: 14px;
            color: #777;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .dashboard {
                margin: 20px;
                padding: 15px;
            }

            .dashboard a {
                font-size: 14px;
                padding: 8px 15px;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard">
        <h1>Welcome, <?php echo $staff_firstname; ?>!</h1>
        <p>Explore your staff dashboard below:</p>
        <a href="../../guestModule/guest_dashboard.php">Guest Management</a><br> <!-- Guest Module-->
        <a href="../../inventoryModule/inventory_management.php">Inventory Management</a><br> <!-- Inventory Module-->
        <a href="../../R&M modules/dashboard.php">Room and Maintenance</a><br> <!-- Room and Maintenance Module-->
        <a href="../staff_logout.php">Logout</a>
        <a href="../staffShift/staffshift_view.php">View Staff Schedule</a><br><!-- Staff Schedule-->
    </div>

    <footer>
        &copy; 2024 Company Name. All rights reserved.
    </footer>
</body>

</html>