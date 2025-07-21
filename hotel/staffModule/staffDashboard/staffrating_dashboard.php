
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Rating Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f7fa;
            color: #333;
        }

        .container {
            width: 80%;
            max-width: 1200px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 20px;
        }

        p {
            font-size: 18px;
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }

        .dashboard-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card a {
            display: block;
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            margin-top: 20px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .card a:hover {
            background-color: #45a049;
        }

        .card h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 10px;
        }

        .card p {
            font-size: 14px;
            color: #666;
        }

        .back-link {
            text-align: center;
            margin-top: 40px;
            font-size: 16px;
        }

        .back-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }

        .back-link a:hover {
            color: #0056b3;
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 15px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Welcome back, <?php echo isset($staff_firstname) ? htmlspecialchars($staff_firstname) : 'User'; ?>!</h1>
        <p>Manage Staff Ratings through the following options:</p>

        <div class="dashboard-items">
            <div class="card">
                <h3>Create Staff Rating</h3>
                <p>Submit a new staff rating. Admins and managers can evaluate staff performance.</p>
                <a href="../staffRating/createrating_staff.php">Create Rating</a>
            </div>

            <div class="card">
                <h3>View Admin and HR Ratings</h3>
                <p>Access ratings provided by the Admin and HR departments.</p>
                <a href="../staffRating/viewrating_hastaff.php">View Ratings</a>
            </div>

            <div class="card">
                <h3>View Manager Ratings</h3>
                <p>Access ratings provided by Managers.</p>
                <a href="../staffRating/viewrating_mstaff.php">View Ratings</a>
            </div>

            <div class="card">
                <h3>View Normal Staff Ratings</h3>
                <p>Access ratings from other staff members.</p>
                <a href="../staffRating/viewrating_nstaff.php">View Ratings</a>
            </div>
        </div>

        <div class="back-link">
            <a href="<?= htmlspecialchars($back_url); ?>">Back</a>
        </div>
    </div>

</body>

</html>


