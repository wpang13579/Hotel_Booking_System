<?php
session_start();
require('../database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_email = $_POST['email']; // Directly using the input without sanitization
    $staff_password = $_POST['password']; // Directly using the input without sanitization

    // Query to check user credentials
    $query = "SELECT * FROM `staff` WHERE staff_email='$staff_email' AND staff_password='$staff_password'";
    $result = mysqli_query($con, $query) or die(mysqli_error($con));
    $rows = mysqli_num_rows($result);

    if ($rows == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['staff_email'] = $user['staff_email'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['staff_firstname'] = $user['staff_firstname'];

        // Redirect based on user role
        if ($user['role_id'] == 1) {
            header("Location: staffDashboard/hr_dashboard.php"); //HR
            exit();
        } elseif ($user['role_id'] == 2) {
            header("Location: staffDashboard/admin_dashboard.php"); //Admin
            exit();
        } elseif ($user['role_id'] == 3) {
            header("Location: ../guestModule/guest_dashboard.php"); // Guest Manager
            exit();
        } elseif ($user['role_id'] == 4) {
            header("Location:../R&M modules/dashboard.php"); // Room Manager
            exit();
        } elseif ($user['role_id'] == 5) {
            header("Location:../inventoryModule/inventory_management.php"); //Inventory Manager
            exit();
        } elseif ($user['role_id'] == 6) {
            header("Location: staffDashboard/nstaff_dashboard.php"); //Normal Staff
            exit();
        } else {
            echo "<h3 style='color: red;'>Email or password is incorrect.</h3>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            border: none;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .form p {
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }

        .form p a {
            color: #007bff;
            text-decoration: none;
        }

        .form p a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <div class="form">
        <h1>Staff Log In</h1>
        <form action="" method="post" name="login">
            <input type="email" name="email" placeholder="Email" required><br>
            <input type="password" name="password" placeholder="Password" required><br>
            <input name="submit" type="submit" value="Login">
        </form>
    </div>

</body>

</html>