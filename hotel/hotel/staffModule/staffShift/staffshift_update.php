<?php
session_start();
require('../../database.php');

// Check if logged in
if (!isset($_SESSION['staff_email'])) {
    header("Location: login.php");
    exit();
}

// Ensure required parameters are present
if (!isset($_GET['target_email']) || !isset($_GET['shift_type'])) {
    die("Error: Required parameters missing.");
}

$requesting_email = $_SESSION['staff_email']; // Email of the requesting staff
$target_email = $_GET['target_email']; // Email of the target staff
$target_shift_type = $_GET['shift_type']; // Target shift type

// Fetch requesting staff details
$requesting_query = "
    SELECT s.staff_id, s.staff_firstname, s.staff_lastname, s.staff_gender, sr.role_name, ss.shift_id, ss.shift_type, ss.start_time, ss.end_time
    FROM staff s
    JOIN staff_role sr ON s.role_id = sr.role_id
    JOIN staff_shift ss ON s.staff_id = ss.staff_id
    WHERE s.staff_email = ?
";
$stmt = $con->prepare($requesting_query);
$stmt->bind_param("s", $requesting_email);
$stmt->execute();
$requesting_staff = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$requesting_staff) {
    die("Error: Requesting staff details not found.");
}

// Fetch target staff details
$target_query = "
    SELECT s.staff_id, s.staff_firstname, s.staff_lastname, s.staff_gender, sr.role_name, ss.shift_id, ss.shift_type, ss.start_time, ss.end_time
    FROM staff s
    JOIN staff_role sr ON s.role_id = sr.role_id
    JOIN staff_shift ss ON s.staff_id = ss.staff_id
    WHERE s.staff_email = ? AND ss.shift_type = ?
";
$stmt = $con->prepare($target_query);
$stmt->bind_param("ss", $target_email, $target_shift_type);
$stmt->execute();
$target_staff = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$target_staff) {
    die("Error: Target staff details not found.");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Insert swap request into staff_shift_swap table
    $insert_query = "
    INSERT INTO staff_shift_swap (request_date,request_status,request_staff_id, target_staff_id, request_shift_id, target_shift_id)
    VALUES (CURDATE(),'Pending',?, ?, ?, ?)
    ";

    $stmt = $con->prepare($insert_query);
    $stmt->bind_param(
        "iiii",
        $requesting_staff['staff_id'],
        $target_staff['staff_id'],
        $requesting_staff['shift_id'],
        $target_staff['shift_id']
    );

    if ($stmt->execute()) {
        echo "<script>
                alert('Shift swap request submitted successfully.');
                window.location.href = 'staffshift_view.php';
              </script>";
        exit(); // Ensure no further code is executed
    } else {
        die("Error submitting shift swap request: " . $stmt->error);
    }
    
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Shift Change</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }

        h1 {
            text-align: center;
            background-color: #4CAF50;
            color: white;
            padding: 20px 0;
            margin: 0;
        }

        h2 {
            text-align: left;
            color: #4CAF50;
            margin-left: 20px;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow-x: auto;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
            color: #333;
        }

        td {
            background-color: #fff;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        input[type="submit"],
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            margin: 10px 5px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #45a049;
        }

        button {
            background-color: #f44336;
        }

        button:hover {
            background-color: #d32f2f;
        }

        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>

    <script>
        function goBack() {
            // Redirect to staffshift_view.php
            window.location.href = 'staffshift_view.php';
        }
    </script>
</head>

<body>
    <h1>Request Shift Change</h1>
    <div class="container">
        <!-- Requesting Staff Information -->
        <h2>Your Information</h2>
        <table>
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>Role</th>
                    <th>Shift Type</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($requesting_staff['staff_firstname']); ?></td>
                    <td><?= htmlspecialchars($requesting_staff['staff_lastname']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($requesting_staff['staff_gender'])); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $requesting_staff['role_name']))); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $requesting_staff['shift_type']))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($requesting_staff['start_time']))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($requesting_staff['end_time']))); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Target Staff Information -->
        <h2>Target Staff Information</h2>
        <table>
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Gender</th>
                    <th>Role</th>
                    <th>Shift Type</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($target_staff['staff_firstname']); ?></td>
                    <td><?= htmlspecialchars($target_staff['staff_lastname']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($target_staff['staff_gender'])); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace('_', ' ', $target_staff['role_name']))); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $target_staff['shift_type']))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($target_staff['start_time']))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($target_staff['end_time']))); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Submit Request Form -->
        <form method="post">
            <input type="submit" value="Submit Shift Change Request">
        </form>

        <!-- Back Button -->
        <button onclick="goBack()">Back</button>
    </div>
</body>

</html>
