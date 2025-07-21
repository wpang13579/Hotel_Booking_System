<?php
require('../../database.php');
$status = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_id = intval($_POST['staff_id']);
    $staff_firstname = $_POST['staff_firstname'];
    $staff_lastname = $_POST['staff_lastname'];
    $staff_dob = $_POST['staff_dob'];
    $staff_gender = $_POST['staff_gender'];
    $staff_contactnum = $_POST['staff_contactnum'];
    $staff_email = $_POST['staff_email'];
    $staff_hiredate = $_POST['staff_hiredate'];
    $staff_password = $_POST['staff_password'];
    $staff_salary = $_POST['staff_salary'];
    $role_id = intval($_POST['role_id']); // Corrected to use role_id

    // Check if the email exists for another staff member
    $email_check_query = "SELECT staff_id FROM staff WHERE staff_email = '$staff_email' AND staff_id != $staff_id";
    $email_check_result = mysqli_query($con, $email_check_query);

    if (mysqli_num_rows($email_check_result) > 0) {
        // Email already exists for another staff
        echo "<script>alert('The email address is already in use by another staff. Please use a different email.');</script>";
    } else {
        // Proceed with the update
        $update_query = "UPDATE staff SET 
            staff_firstname = '$staff_firstname', 
            staff_lastname = '$staff_lastname', 
            staff_dob = '$staff_dob', 
            staff_gender = '$staff_gender', 
            staff_contactnum = '$staff_contactnum', 
            staff_email = '$staff_email', 
            staff_hiredate = '$staff_hiredate', 
            staff_password = '$staff_password', 
            staff_salary = '$staff_salary', 
            role_id = $role_id
            WHERE staff_id = $staff_id";

        if (mysqli_query($con, $update_query)) {
            echo "<script>alert('Record updated successfully.');</script>";
            // Redirect to the appropriate page based on role_id
            if ($role_id === 1 || $role_id === 2) {
                header("Location: view_hastaff.php");
            } elseif ($role_id === 3 || $role_id === 4 || $role_id === 5) {
                header("Location: view_mstaff.php");
            } elseif ($role_id === 6) {
                header("Location: view_nstaff.php");
            }
            exit();
        } else {
            $status = "Error updating record: " . mysqli_error($con);
        }
    }
    if (isset($_POST['back'])) {
        // Redirect to the appropriate page based on role_id
        if ($role_id === 1 || $role_id === 2) {
            header("Location: view_hastaff.php");
        } elseif ($role_id === 3 || $role_id === 4 || $role_id === 5) {
            header("Location: view_mstaff.php");
        } elseif ($role_id === 6) {
            header("Location: view_nstaff.php");
        }
        exit();
    }
}

// Fetch data to populate the form
$staff_id = intval($_REQUEST['staff_id']); // Sanitize and cast staff_id
$query = "SELECT * FROM staff WHERE staff_id = $staff_id";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Staff Record</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            margin-top: 50px;
        }

        form {
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        p {
            margin-bottom: 20px;
        }

        label {
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="password"],
        input[type="number"],
        select {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"],
        button[type="submit"] {
            width: 100%;
            padding: 14px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover,
        button[type="submit"]:hover {
            background-color: #45a049;
        }

        .status-message {
            text-align: center;
            margin-top: 20px;
            font-size: 16px;
            color: #f44336;
        }

        .back-button {
            text-align: center;
            margin-top: 20px;
        }

        .back-button button {
            background-color: #ff9800;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .back-button button:hover {
            background-color: #f57c00;
        }

        @media (max-width: 768px) {
            form {
                width: 90%;
            }

            input[type="submit"],
            button[type="submit"] {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

    <h1>Update Staff Record</h1>

    <?php echo $status; ?>

    <form name="form" method="post" action="">
        <input type="hidden" name="staff_id" value="<?php echo $staff_id; ?>">

        <p>
            <label for="staff_firstname">First Name:</label>
            <input type="text" id="staff_firstname" name="staff_firstname" placeholder="Update First Name" required value="<?php echo $row['staff_firstname']; ?>">
        </p>

        <p>
            <label for="staff_lastname">Last Name:</label>
            <input type="text" id="staff_lastname" name="staff_lastname" placeholder="Update Last Name" required value="<?php echo $row['staff_lastname']; ?>">
        </p>

        <p>
            <label for="staff_dob">Date of Birth:</label>
            <input type="date" id="staff_dob" name="staff_dob" placeholder="Update Date of Birth" required value="<?php echo $row['staff_dob']; ?>">
        </p>

        <p>
            <label for="staff_gender">Gender:</label>
            <select id="staff_gender" name="staff_gender" required>
                <option value="1" <?php echo ($row['staff_gender'] == 1) ? 'selected' : ''; ?>>Male</option>
                <option value="2" <?php echo ($row['staff_gender'] == 2) ? 'selected' : ''; ?>>Female</option>
            </select>
        </p>

        <p>
            <label for="staff_contactnum">Contact Number:</label>
            <input type="text" id="staff_contactnum" name="staff_contactnum" placeholder="Update Contact Number" required value="<?php echo $row['staff_contactnum']; ?>">
        </p>

        <p>
            <label for="staff_email">Email:</label>
            <input type="email" id="staff_email" name="staff_email" placeholder="Update Email" required value="<?php echo $row['staff_email']; ?>">
        </p>

        <p>
            <label for="staff_hiredate">Hire Date:</label>
            <input type="date" id="staff_hiredate" name="staff_hiredate" value="<?php echo $row['staff_hiredate']; ?>" readonly>
        </p>

        <p>
            <label for="staff_password">Password:</label>
            <input type="password" id="staff_password" name="staff_password" placeholder="Update Password" required value="<?php echo $row['staff_password']; ?>">
        </p>

        <p>
            <label for="staff_salary">Salary:</label>
            <input type="number" id="staff_salary" name="staff_salary" placeholder="Update Salary" required value="<?php echo $row['staff_salary']; ?>">
        </p>

        <p>
            <label for="role_id">Role:</label>
            <select id="role_id" name="role_id" required>
                <option value="1" <?php echo ($row['role_id'] == 1) ? 'selected' : ''; ?>>HR</option>
                <option value="2" <?php echo ($row['role_id'] == 2) ? 'selected' : ''; ?>>Admin</option>
                <option value="3" <?php echo ($row['role_id'] == 3) ? 'selected' : ''; ?>>Guest Manager</option>
                <option value="4" <?php echo ($row['role_id'] == 4) ? 'selected' : ''; ?>>Room Manager</option>
                <option value="5" <?php echo ($row['role_id'] == 5) ? 'selected' : ''; ?>>Inventory Manager</option>
                <option value="6" <?php echo ($row['role_id'] == 6) ? 'selected' : ''; ?>>Normal Staff</option>
            </select>
        </p>

        <p>
            <input name="submit" type="submit" value="Update">
        </p>
    </form>

    <div class="back-button">
        <form method="post" action="">
            <input type="hidden" name="role_id" value="<?php echo $row['role_id']; ?>">
            <button type="submit" name="back">Back</button>
        </form>
    </div>

</body>

</html>
