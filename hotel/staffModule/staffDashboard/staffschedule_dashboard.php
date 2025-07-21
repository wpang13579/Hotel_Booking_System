
<?php
session_start();

// Ensure the role_id is set in the session
if (!isset($_SESSION['role_id'])) {
    // Handle cases where the role_id is missing
    header('Location: ../staff_login.php');
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
    $back_url = "../staff_login.php";
}

$staff_firstname = $_SESSION['staff_firstname']; // Get the staff firstname
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Schedule Dashboard</title>
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
            padding: 40px 20px;
            text-align: center;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }

        header p {
            font-size: 16px;
            margin: 0;
        }

        /* Dashboard Links */
        .dashboard-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
            height: 200px;
        }

        .dashboard-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
        }

        .dashboard-item h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #007bff;
        }

        .dashboard-item p {
            font-size: 14px;
            color: #555;
            margin: 10px 0;
        }

        .dashboard-item a {
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            border-radius: 6px;
            font-size: 14px;
            transition: background-color 0.3s, transform 0.3s;
            display: inline-block;
        }

        .dashboard-item a:hover {
            background-color: #218838;
            transform: scale(1.05);
        }

        /* Footer */
        .button-container {
            text-align: center;
            margin-top: 30px;
        }

        .back-button {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: #5a6268;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            header h1 {
                font-size: 24px;
            }

            header p {
                font-size: 14px;
            }

            .dashboard-item h2 {
                font-size: 16px;
            }

            .dashboard-item p {
                font-size: 12px;
            }

            .dashboard-item a {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <header>
            <h1>Staff Schedule Dashboard</h1>
            <p>Welcome back, <?php echo isset($staff_firstname) ? htmlspecialchars($staff_firstname) : 'User'; ?>!</p>
        </header>

        <div class="dashboard-links">
            <div class="dashboard-item">
                <h2>Create Staff Schedule</h2>
                <p>Create a new schedule for staff management.</p>
                <a href="../staffShift/staffshift_create.php">Create Schedule</a>
            </div>

            <div class="dashboard-item">
                <h2>View Staff Schedule</h2>
                <p>View and manage existing staff schedules.</p>
                <a href="../staffShift/staffshift_view.php">View Schedule</a>
            </div>

            <div class="dashboard-item">
                <h2>Delete Staff Schedule</h2>
                <p>Remove the staff schedules.</p>
                <a href="../staffShift/staffshift_delete.php">Delete Schedule</a>
            </div>
        </div>

        <div class="button-container">
            <button class="back-button" onclick="location.href='<?= htmlspecialchars($back_url); ?>'">Back</button>
        </div>
    </div>

</body>

</html>




