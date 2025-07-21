<?php
session_start();

// Ensure the role_id is set in the session
if (!isset($_SESSION['role_id'])) {
    // Handle cases where the role_id is missing (optional)
    header('Location: ../login.php');
    exit();
}

// Determine the redirection URL based on the role_id
$role_id = $_SESSION['role_id'];
$back_url = "";

if ($role_id == 1) {
    $back_url = "../staffDashboard/hr_dashboard.php";
} elseif ($role_id == 2) {
    $back_url = "../staffDashboard/admin_dashboard.php";
} else {
    // Default fallback (optional, in case of unexpected role_id)
    $back_url = "../staffDashboard/";
}

$staff_firstname = $_SESSION['staff_firstname']; // Get the staff firstname
?>

<!DOCTYPE html>
<html lang="en">

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management Dashboard</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f9fc;
            color: #333;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Container */
        .container {
            width: 90%;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        header {
            background-color: #007bff;
            color: white;
            padding: 50px 0;
            text-align: center;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 36px;
            margin-bottom: 10px;
        }

        header p {
            font-size: 20px;
            font-weight: 300;
        }

        /* Dashboard Links */
        .dashboard-links {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Two items per row */
            gap: 20px;
            margin-top: 30px;
        }

        .dashboard-item {
            background-color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border: 1px solid #e2e2e2;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 250px; /* Fixed height for consistent item size */
            box-sizing: border-box;
        }

        .dashboard-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .dashboard-item h2 {
            font-size: 22px;
            color: #333;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .dashboard-item p {
            font-size: 16px;
            color: #555;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .dashboard-item a {
            padding: 12px 25px;
            background-color: #28a745;
            color: white;
            border-radius: 6px;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.3s;
            display: inline-block;
        }

        .dashboard-item a:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        /* Footer */
        footer {
            text-align: center;
            padding: 20px 0;
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
            padding: 10px 25px;
            background-color: #dc3545;
            border-radius: 6px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        footer a:hover {
            background-color: #c82333;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header h1 {
                font-size: 32px;
            }

            header p {
                font-size: 18px;
            }

            .dashboard-item h2 {
                font-size: 20px;
            }

            .dashboard-item p {
                font-size: 14px;
            }

            .dashboard-item a {
                font-size: 14px;
            }

            .dashboard-links {
                grid-template-columns: 1fr; /* Stack items in a single column for smaller screens */
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <header>
            <h1>Staff Management Dashboard</h1>
            <p>Welcome back, <?php echo isset($staff_firstname) ? htmlspecialchars($staff_firstname) : 'User'; ?>!</p>
        </header>

        <div class="dashboard-links">
            <div class="dashboard-item">
                <h2>Create Staff Record</h2>
                <p>Create a new staff record for your Hotel.</p>
                <a href="../staffManagement/staff_create.php">Create Staff Record</a>
            </div>

            <div class="dashboard-item">
                <h2>View HR and Admin Staff</h2>
                <p>Manage and view HR and Admin staff records for your Hotel.</p>
                <a href="../staffManagement/view_hastaff.php">View HR and Admin Staff</a>
            </div>

            <div class="dashboard-item">
                <h2>View Manager Staff</h2>
                <p>Review and manage records Managerial staff for your Hotel.</p>
                <a href="../staffManagement/view_mstaff.php">View Manager Staff</a>
            </div>

            <div class="dashboard-item">
                <h2>View Normal Staff</h2>
                <p>Review and manage Normal staff records for your Hotel.</p>
                <a href="../staffManagement/view_nstaff.php">View Normal Staff</a>
            </div>
        </div>

        <footer>
            <a href="<?= htmlspecialchars($back_url); ?>">Back</a>
        </footer>
    </div>

</body>
</html>

