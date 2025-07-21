<?php
require('../../database.php');

// Define roles
$roles = [
    "guest_manager" => "Guest Manager Records",
    "room_manager" => "Room Manager Records",
    "inventory_manager" => "Inventory Manager Records"
];

function fetchPerformanceRecordsByRole($role_name, $con)
{
    $query = "
        SELECT s.staff_id, s.staff_firstname, s.staff_lastname, s.staff_gender, sr.role_name, sp.eval_date, sp.eval_time, sp.perf_rating, sp.perf_comment, sp.performance_id
        FROM staff s
        JOIN staff_role sr ON s.role_id = sr.role_id
        JOIN staff_performance sp ON s.staff_id = sp.staff_id
        WHERE sr.role_name = ?
        ORDER BY s.staff_id ASC, sp.eval_date DESC, sp.eval_time DESC;
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $role_name);
    $stmt->execute();
    return $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Staff Performance Records</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            color: #4CAF50;
            text-align: center;
            padding: 20px;
            background-color: #fff;
            border-bottom: 2px solid #4CAF50;
        }

        h2 {
            color: #333;
            margin-top: 30px;
            font-size: 24px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
            font-size: 14px;
        }

        th {
            background-color: #f4f4f4;
            color: #4CAF50;
        }

        td a {
            color: #4CAF50;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        td a:hover {
            background-color: #4CAF50;
            color: white;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .back-button {
            text-align: center;
            margin-top: 30px;
        }

        .back-button button {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .back-button button:hover {
            background-color: #45a049;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 8px;
            }

            .container {
                width: 95%;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>View - Staff Performance Records</h1>

        <?php foreach ($roles as $role_key => $role_title): ?>
            <h2><?php echo $role_title; ?></h2>
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
                    $result = fetchPerformanceRecordsByRole($role_key, $con);
                    $current_staff_id = null;
                    $count = 1;
                    while ($row = mysqli_fetch_assoc($result)):
                        if ($current_staff_id !== $row["staff_id"]):
                            $current_staff_id = $row["staff_id"];
                            $rowspan = mysqli_num_rows(mysqli_query($con, "SELECT * FROM staff_performance WHERE staff_id = " . $row['staff_id']));
                    ?>
                            <tr>
                                <td rowspan="<?php echo $rowspan; ?>"><?php echo $count; ?></td>
                                <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($row["staff_firstname"]); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($row["staff_lastname"]); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>"><?php echo ucfirst(htmlspecialchars($row["staff_gender"])); ?></td>
                                <td rowspan="<?php echo $rowspan; ?>"><?php echo ucfirst(htmlspecialchars(str_replace('_', ' ', $row["role_name"]))); ?></td>
                                <td><?php echo htmlspecialchars($row["eval_date"]); ?></td>
                                <td><?php echo htmlspecialchars($row["eval_time"]); ?></td>
                                <td><?php echo htmlspecialchars($row["perf_rating"]); ?></td>
                                <td><?php echo htmlspecialchars($row["perf_comment"]); ?></td>
                                <td>
                                    <a href="updaterating_staff.php?performance_id=<?php echo $row["performance_id"]; ?>">Update</a>
                                </td>
                                <td>
                                    <a href="deleterating_staff.php?performance_id=<?php echo $row["performance_id"]; ?>"
                                        onclick="return confirm('Are you sure you want to delete this performance record?')">Delete</a>
                                </td>
                            </tr>
                        <?php
                            $count++; // Increment here for new staff
                        else: ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row["eval_date"]); ?></td>
                                <td><?php echo htmlspecialchars($row["eval_time"]); ?></td>
                                <td><?php echo htmlspecialchars($row["perf_rating"]); ?></td>
                                <td><?php echo htmlspecialchars($row["perf_comment"]); ?></td>
                                <td>
                                    <a href="updaterating_staff.php?performance_id=<?php echo $row["performance_id"]; ?>">Update</a>
                                </td>
                                <td>
                                    <a href="deleterating_staff.php?performance_id=<?php echo $row["performance_id"]; ?>"
                                        onclick="return confirm('Are you sure you want to delete this performance record?')">Delete</a>
                                </td>
                            </tr>
                    <?php
                        endif;
                    endwhile;
                    ?>
                </tbody>

            </table>
        <?php endforeach; ?>

        <div class="back-button">
            <button onclick="location.href='../staffDashboard/staffrating_dashboard.php'">Back</button>
        </div>
    </div>
</body>

</html>