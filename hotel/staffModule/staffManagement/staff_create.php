<?php
require('../../database.php');
$status_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $staff_firstname = $_POST['staff_firstname'];
    $staff_lastname = $_POST['staff_lastname'];
    $staff_dob = $_POST['staff_dob'];
    $staff_gender = $_POST['staff_gender'];
    $staff_contactnum = $_POST['staff_contactnum'];
    $staff_email = $_POST['staff_email'];
    $staff_hiredate = $_POST['staff_hiredate'];
    $staff_password = $_POST['staff_password'];
    $staff_salary = $_POST['staff_salary'];
    $role_id = $_POST['role_id'];

    // Check if the email already exists in the staff table
    $check_email_query = "SELECT * FROM staff WHERE staff_email = '$staff_email'";
    $email_result = mysqli_query($con, $check_email_query);

    if (mysqli_num_rows($email_result) > 0) {
        // Email already exists
        $status_message = "Email already exists. Please use a different email address.";
    } else {
        // Role mapping for insertion into staff_role
        $roles = [
            1 => "HR",
            2 => "admin",
            3 => "guest_manager",
            4 => "room_manager",
            5 => "inventory_manager",
            6 => "normal_staff",
        ];

        // Check if the role exists in the staff_role table
        $role_name = $roles[$role_id];
        $check_role_query = "SELECT * FROM staff_role WHERE role_id = $role_id";
        $role_result = mysqli_query($con, $check_role_query);

        if (mysqli_num_rows($role_result) == 0) {
            // If the role does not exist, insert it
            $insert_role_query = "INSERT INTO staff_role (role_id, role_name) VALUES ($role_id, '$role_name')";
            mysqli_query($con, $insert_role_query);
        }

        // Insert staff data into the staff table
        $query = "INSERT INTO `staff` (staff_firstname, staff_lastname, staff_dob, staff_gender, staff_contactnum, staff_email, staff_hiredate, staff_password, staff_salary, role_id)
                  VALUES ('$staff_firstname', '$staff_lastname', '$staff_dob', '$staff_gender', '$staff_contactnum', '$staff_email', '$staff_hiredate', '$staff_password', '$staff_salary', '$role_id')";

        $result = mysqli_query($con, $query);

        if ($result) {
            $status_message = "Staff data inserted successfully.";
        } else {
            $status_message = "Staff data failed to be inserted.";
        }
    }

    // Output a JavaScript alert
    echo "<script>alert('$status_message');</script>";
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insert Staff Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        header {
            width: 100%;
            background-color: #0056b3;
            color: #fff;
            padding: 15px 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 22px;
        }

        .container {
            max-width: 600px;
            width: 90%;
            background: #fff;
            padding: 20px;
            margin: 20px auto;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h2 {
            text-align: center;
            background-color: #0056b3;
            color: #fff;
            font-size: 20px;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 4px;
        }

        form label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
            color: #444;
        }

        form input, form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        form input[type="submit"] {
            background-color: #0056b3;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form input[type="submit"]:hover {
            background-color: #003d80;
        }

        .back-button {
            text-align: center;
            margin-top: 10px;
        }

        .back-button button {
            background-color: #444;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
            font-size: 14px;
        }

        .back-button button:hover {
            background-color: #222;
        }
    </style>
</head>

<body>
    <header>
        <h1>Staff Management</h1>
    </header>
    <div class="container">
        <h2>Create Staff Data</h2>
        <form action="" method="post">
            <label for="staff_firstname">First Name:</label>
            <input type="text" id="staff_firstname" name="staff_firstname" placeholder="First Name" required />

            <label for="staff_lastname">Last Name:</label>
            <input type="text" id="staff_lastname" name="staff_lastname" placeholder="Last Name" required />

            <label for="staff_dob">Date of Birth:</label>
            <input type="date" id="staff_dob" name="staff_dob" required />

            <label for="staff_gender">Gender:</label>
            <select id="staff_gender" name="staff_gender" required>
                <option value="">--Select Gender--</option>
                <option value="1">Male</option>
                <option value="2">Female</option>
            </select>

            <label for="staff_contactnum">Contact Number:</label>
            <input type="text" id="staff_contactnum" name="staff_contactnum" placeholder="Exp: 012-3456789" pattern="^(\d{3}-\d{7}|\d{3}-\d{8})$" title="Enter a valid phone number (e.g. 012-3456789 or 012-34567890)" required />

            <label for="staff_email">Email:</label>
            <input type="email" id="staff_email" name="staff_email" placeholder="Email" required />

            <label for="staff_hiredate">Hire Date:</label>
            <input type="date" id="staff_hiredate" name="staff_hiredate" value="<?php echo date('Y-m-d'); ?>" required />

            <label for="staff_password">Password:</label>
            <input type="password" id="staff_password" name="staff_password" placeholder="Password" required />

            <label for="staff_salary">Salary:</label>
            <input type="number" id="staff_salary" name="staff_salary" placeholder="Exp: 100.00" step="0.01" required />

            <label for="role_id">Role:</label>
            <select id="role_id" name="role_id" required>
                <option value="">--Select Role--</option>
                <option value="1">HR</option>
                <option value="2">Admin</option>
                <option value="3">Guest_manager</option>
                <option value="4">Room_manager</option>
                <option value="5">Inventory_manager</option>
                <option value="6">Normal_staff</option>
            </select>

            <input type="submit" name="submit" value="Submit" />
        </form>
        <div class="back-button">
            <button onclick="location.href='../staffDashboard/staffinfo_dashboard.php'">Back</button>
        </div>
    </div>
</body>

</html>


