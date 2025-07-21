<?php
require('../../database.php');

// Define role for normal staff
$normal_staff_role = "normal_staff";

function fetchNormalStaffPerformanceRecords($con) {
    global $normal_staff_role;
    $query = "
        SELECT s.staff_id, s.staff_firstname, s.staff_lastname, s.staff_gender, sr.role_name, sp.eval_date, sp.eval_time, sp.perf_rating, sp.perf_comment, sp.performance_id
        FROM staff s
        JOIN staff_role sr ON s.role_id = sr.role_id
        JOIN staff_performance sp ON s.staff_id = sp.staff_id
        WHERE sr.role_name = ?
        ORDER BY s.staff_id ASC, sp.eval_date DESC, sp.eval_time DESC;
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $normal_staff_role);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Normal Staff Performance Records</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            margin-top: 40px;
            font-size: 28px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #4CAF50;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        td a {
            color: #2196F3;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        td a:hover {
            color: #0b7dda;
        }

        button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 30px;
            display: block;
            margin-left: auto;
            margin-right: auto;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            th,
            td {
                padding: 10px;
            }

            h1 {
                font-size: 24px;
            }

            button {
                font-size: 14px;
            }
        }
    </style>
</head>

<body>
    <h1>View - Normal Staff Performance Records</h1>

    <table>
        <thead>
            <tr>
                <th><strong>Staff No.</strong></th>
                <th><strong>First Name</strong></th>
                <th><strong>Last Name</strong></th>
                <th><strong>Gender</strong></th>
                <th><strong>Role</strong></th>
                <th><strong>Evaluation Date</strong></th>
                <th><strong>Evaluation Time</strong></th>
                <th><strong>Rating (0-5)</strong></th>
                <th><strong>Comment</strong></th>
                <th><strong>Update</strong></th>
                <th><strong>Delete</strong></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $result = fetchNormalStaffPerformanceRecords($con);

            if ($result->num_rows > 0) {
                $currentStaffId = null; // Track the current staff ID
                $rowspan = 0; // Rowspan count
                $staffCount = 1; // Counter for staff number

                while ($row = mysqli_fetch_assoc($result)) {
                    if ($currentStaffId !== $row['staff_id']) {
                        // Fetch rowspan for the current staff ID
                        $currentStaffId = $row['staff_id'];
                        $rowspan = mysqli_num_rows(mysqli_query($con, "SELECT * FROM staff_performance WHERE staff_id = " . $currentStaffId));
                        
                        // Start a new row with merged cells
                        echo "<tr>";
                        echo "<td rowspan='{$rowspan}'>" . $staffCount++ . "</td>";
                        echo "<td rowspan='{$rowspan}'>" . htmlspecialchars($row['staff_firstname']) . "</td>";
                        echo "<td rowspan='{$rowspan}'>" . htmlspecialchars($row['staff_lastname']) . "</td>";
                        echo "<td rowspan='{$rowspan}'>" . ucfirst(htmlspecialchars($row['staff_gender'])) . "</td>";
                        echo "<td rowspan='{$rowspan}'>" . ucfirst(str_replace('_', ' ', htmlspecialchars($row['role_name']))) . "</td>";
                    }

                    // Add individual performance records
                    echo "<td>" . htmlspecialchars($row['eval_date']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['eval_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['perf_rating']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['perf_comment']) . "</td>";
                    echo "<td><a href='updaterating_staff.php?performance_id=" . $row['performance_id'] . "'>Update</a></td>";
                    echo "<td><a href='deleterating_staff.php?performance_id=" . $row['performance_id'] . "' onclick=\"return confirm('Are you sure you want to delete this performance record?')\">Delete</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='11'>No records found for normal staff.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <br>
    <button onclick="location.href='../staffDashboard/staffrating_dashboard.php'">Back</button>
</body>

</html>

