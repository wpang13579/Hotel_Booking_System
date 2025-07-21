<?php
session_start();
require('../../database.php');

// Check if the user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    die("<p style='color: red;'>Access denied. Please log in.</p>");
}

$role = $_SESSION['role_id']; 
$staff_email = $_SESSION['staff_email']; 
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>View Emergency Maintenance Requests</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .page-header {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #dc3545;
            color: white;
            border-radius: 8px;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-dashboard {
            margin-top: 40px;
            background-color: #dc3545;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .btn-dashboard:hover {
            background-color: #a71d2a;
            color: white;
        }

        .text-center-btn {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="page-header text-center">
            <h2>Emergency Maintenance Requests</h2>
            <p><strong>Logged in as:</strong>
                <?php 
                echo $role == 4 ? 'Room Manager' : ($role == 6 ? 'Normal Staff' : 'Unknown Role'); 
                ?>
            </p>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead class="table-danger">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Request By</th>
                        <th scope="col">Request Date</th>
                        <th scope="col">Room Number</th>
                        <th scope="col">Priority Level</th>
                        <th scope="col">Request Status</th>
                        <th scope="col">Request Description</th>
                        <th scope="col">Create Task</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;

                    // Fetch only emergency maintenance requests
                    $sel_query = "SELECT m.req_id, m.req_date, m.priority_level, m.req_status, m.req_desc, r.room_num, 
                                  s.staff_firstname, s.staff_lastname 
                                  FROM maintenance_request m 
                                  JOIN room r ON m.room_id = r.room_id 
                                  JOIN staff s ON m.staff_id = s.staff_id 
                                  WHERE m.req_desc LIKE 'Emergency:%'
                                  ORDER BY m.req_id";

                    $result = mysqli_query($con, $sel_query);
                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                            <tr>
                                <th scope="row"><?php echo $count; ?></th>
                                <td><?php echo $row["staff_firstname"] . " " . $row["staff_lastname"]; ?></td>
                                <td><?php echo $row["req_date"]; ?></td>
                                <td><?php echo $row["room_num"]; ?></td>
                                <td><?php echo $row["priority_level"]; ?></td>
                                <td><?php echo $row["req_status"]; ?></td>
                                <td><?php echo $row["req_desc"]; ?></td>
                                <td class="text-center-btn">
                                    <?php if ($role == 4) { ?>
                                        <a class="btn btn-danger btn-sm" href="../maintenance task/createEmergencyTask.php?req_id=<?php echo $row['req_id']; ?>">Create</a>
                                    <?php } else { ?>
                                        <span class="text-muted">Not Allowed</span>
                                    <?php } ?>
                                </td>
                            </tr>
                    <?php
                            $count++;
                        }
                    } else {
                        echo '<tr><td colspan="8" align="center">No records found</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        <br><br>

        <!-- Dashboard Button -->
        <div class="text-center">
            <a class="btn-dashboard" href="../dashboard.php">Go to Dashboard</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>


