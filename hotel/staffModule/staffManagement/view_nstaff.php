<?php
require('../../database.php');
$count = 1;
$normalStaffRoleName = "normal_staff"; // The role name for normal staff

// Query with JOIN to get the role name from staff_role table
$sel_query = "
    SELECT s.*, sr.role_name 
    FROM staff s
    JOIN staff_role sr ON s.role_id = sr.role_id
    WHERE sr.role_name = '$normalStaffRoleName'
    ORDER BY s.staff_id DESC;
";
$result = mysqli_query($con, $sel_query);
$currencySymbol = "RM";

// Function to fetch records by role
function fetchRecordsByRole($con, $roleName) {
    $query = "
        SELECT s.*, sr.role_name 
        FROM staff s
        JOIN staff_role sr ON s.role_id = sr.role_id
        WHERE sr.role_name = '$roleName'
        ORDER BY s.staff_id DESC;
    ";
    return mysqli_query($con, $query);
}

// Define roles and their titles
$roles = [
    'normal_staff' => 'Normal Staff'
];

// Fetch records for each role
$records = [];
foreach ($roles as $role_key => $role_title) {
    $records[$role_key] = fetchRecordsByRole($con, $role_key);
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Staff Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        header {
            width: 100%;
            background-color: #0056b3;
            color: #fff;
            padding: 20px 10px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 24px;
        }

        h2 {
            margin-top: 30px;
            color: #0056b3;
            font-size: 20px;
            text-align: center;
        }

        table {
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        a {
            color: #0056b3;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .button-container {
            text-align: center;
            margin: 20px;
        }

        .button-container button {
            background-color: #0056b3;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .button-container button:hover {
            background-color: #003d80;
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            h2 {
                font-size: 18px;
            }

            table {
                width: 100%;
            }

            th, td {
                padding: 8px;
            }

            .button-container button {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <header>
        <h1>View Staff Records</h1>
    </header>

    <?php foreach ($roles as $role_key => $role_title): ?>
        <h2><?php echo $role_title; ?></h2>
        <table>
            <thead>
                <tr>
                    <th>Staff No.</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Date of Birth</th>
                    <th>Contact Number</th>
                    <th>Hire Date</th>
                    <th>Gender</th>
                    <th>Email</th>
                    <th>Password</th>
                    <th>Salary</th>
                    <th>Role</th>
                    <th>Update</th>
                    <th>Delete</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $records[$role_key];
                $count = 1;
                while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo $count; ?></td>
                        <td><?php echo $row["staff_firstname"]; ?></td>
                        <td><?php echo $row["staff_lastname"]; ?></td>
                        <td><?php echo $row["staff_dob"]; ?></td>
                        <td><?php echo $row["staff_contactnum"]; ?></td>
                        <td><?php echo date('d M Y', strtotime($row["staff_hiredate"])); ?></td>
                        <td><?php echo ucfirst($row["staff_gender"]); ?></td>
                        <td><?php echo $row["staff_email"]; ?></td>
                        <td><?php echo $row["staff_password"]; ?></td>
                        <td><?php echo $currencySymbol . number_format($row["staff_salary"], 2); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $row["role_name"])); ?></td>
                        <td><a href="staff_update.php?staff_id=<?php echo $row["staff_id"]; ?>">Update</a></td>
                        <td><a href="staff_delete.php?staff_id=<?php echo $row["staff_id"]; ?>" onclick="return confirm('Are you sure you want to delete this staff record?')">Delete</a></td>
                    </tr>
                <?php $count++; endwhile; ?>
            </tbody>
        </table>
    <?php endforeach; ?>

    <div class="button-container">
        <button onclick="location.href='../staffDashboard/staffinfo_dashboard.php'">Back</button>
    </div>
</body>
</html>

