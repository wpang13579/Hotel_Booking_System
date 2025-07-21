<?php
session_start();
require('../../database.php');

// Ensure user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    header("Location: login.php");
    exit();
}

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id'];

// Ensure required parameter is present
if (!isset($_GET['request_id'])) {
    die("Error: Required parameter missing.");
}

$request_id = intval($_GET['request_id']);

// Fetch the staff ID of the logged-in user
$approval_staff_query = "
    SELECT staff_id 
    FROM staff 
    WHERE staff_email = ?
";
$stmt = $con->prepare($approval_staff_query);
$stmt->bind_param("s", $staff_email);
$stmt->execute();
$result = $stmt->get_result();
$approval_staff = $result->fetch_assoc();
$stmt->close();

if (!$approval_staff) {
    die("Error: Approving staff details not found.");
}

$approval_staff_id = $approval_staff['staff_id'];

// Fetch request details
$request_query = "
    SELECT 
        rs.staff_firstname AS request_staff_firstname, 
        rs.staff_lastname AS request_staff_lastname, 
        rs.staff_gender AS request_staff_gender, 
        rsr.role_name AS request_staff_role, 
        rss.shift_type AS request_staff_shift_type, 
        rss.start_time AS request_staff_start_time, 
        rss.end_time AS request_staff_end_time,
        ts.staff_firstname AS target_staff_firstname, 
        ts.staff_lastname AS target_staff_lastname, 
        ts.staff_gender AS target_staff_gender, 
        tsr.role_name AS target_staff_role, 
        tss.shift_type AS target_staff_shift_type, 
        tss.start_time AS target_staff_start_time, 
        tss.end_time AS target_staff_end_time,
        ss.request_date, 
        ss.request_status, 
        ss.reject_comment
    FROM staff_shift_swap ss
    JOIN staff rs ON ss.request_staff_id = rs.staff_id
    JOIN staff_role rsr ON rs.role_id = rsr.role_id
    JOIN staff_shift rss ON rs.staff_id = rss.staff_id
    JOIN staff ts ON ss.target_staff_id = ts.staff_id
    JOIN staff_role tsr ON ts.role_id = tsr.role_id
    JOIN staff_shift tss ON ts.staff_id = tss.staff_id
    WHERE ss.request_id = ?
";
$stmt = $con->prepare($request_query);
$stmt->bind_param("i", $request_id);
$stmt->execute();
$request = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$request) {
    die("Error: Request details not found.");
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ensure request_id is set and not null
    if (!isset($_POST['request_id']) || empty($_POST['request_id'])) {
        die("Error: Missing request ID.");
    }

    // Get the form inputs
    $request_id = $_POST['request_id'];  // Get the request_id from POST

    $request_status = $_POST['request_status'];
    $reject_comment = $_POST['reject_comment'] ?? null;

    if ($request_status === 'Approved') {
        // Step 1: Retrieve the current shift data for both shifts
        // Fetch shift IDs from the staff_shift_swap table
        $shift_ids_query = "
            SELECT request_shift_id, target_shift_id 
            FROM staff_shift_swap 
            WHERE request_id = ?
        ";
        $stmt = $con->prepare($shift_ids_query);
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->store_result();

        // Check if the shift IDs are found
        if ($stmt->num_rows === 0) {
            die("Error: No shift IDs found for the provided request ID.");
        }

        // Retrieve the shift IDs
        $stmt->bind_result($request_shift_id, $target_shift_id);
        $stmt->fetch();
        $stmt->close();

        // Check if the shift IDs are valid
        if (!$request_shift_id || !$target_shift_id) {
            die("Error: Invalid shift IDs.");
        }

        // Now, retrieve shift details using the shift IDs from the staff_shift table
        $temp_query = "
            SELECT shift_id, shift_type, start_time, end_time 
            FROM staff_shift 
            WHERE shift_id IN (?, ?)
        ";

        $stmt = $con->prepare($temp_query);
        $stmt->bind_param("ii", $request_shift_id, $target_shift_id);
        $stmt->execute();
        $stmt->store_result();

        // Check if shifts are found
        if ($stmt->num_rows === 0) {
            die("Error: No shift details found for the provided shift IDs.");
        }

        $shift_data = [];
        $stmt->bind_result($shift_id, $shift_type, $start_time, $end_time);

        // Fetch the shift details
        while ($stmt->fetch()) {
            $shift_data[$shift_id] = [
                'shift_type' => $shift_type,
                'start_time' => $start_time,
                'end_time' => $end_time
            ];
        }

        // Debugging: Output the shift data for inspection
        echo "<pre>";
        print_r($shift_data);
        echo "</pre>";

        $stmt->close();

        // Ensure that both shifts are found
        if (count($shift_data) === 2) {
            // Retrieve shift data
            $shift_type_1 = $shift_data[$request_shift_id]['shift_type'];
            $shift_type_2 = $shift_data[$target_shift_id]['shift_type'];

            // Determine new shift types and corresponding start/end times
            if ($shift_type_1 === "morning_shift") {
                $new_shift_type_1 = "night_shift";
                $new_start_time_1 = "00:00:00"; // New start time for night
                $new_end_time_1 = "08:00:00";   // New end time for night
            } else {
                $new_shift_type_1 = "morning_shift";
                $new_start_time_1 = "08:00:00"; // New start time for morning
                $new_end_time_1 = "00:00:00";   // New end time for morning
            }

            if ($shift_type_2 === "morning_shift") {
                $new_shift_type_2 = "night_shift";
                $new_start_time_2 = "00:00:00"; // New start time for night
                $new_end_time_2 = "08:00:00";   // New end time for night
            } else {
                $new_shift_type_2 = "morning_shift";
                $new_start_time_2 = "08:00:00"; // New start time for morning
                $new_end_time_2 = "00:00:00";   // New end time for morning
            }

            // Step 2: Update the request shift with the new shift type and times
            $update_query_1 = "
                UPDATE staff_shift 
                SET shift_type = ?, start_time = ?, end_time = ? 
                WHERE shift_id = ?
            ";
            $stmt1 = $con->prepare($update_query_1);
            $stmt1->bind_param("sssi", $new_shift_type_1, $new_start_time_1, $new_end_time_1, $request_shift_id);
            if (!$stmt1->execute()) {
                die("Error: Failed to update request shift. " . $stmt1->error);
            }
            $stmt1->close();

            // Step 3: Update the target shift with the new shift type and times
            $update_query_2 = "
                UPDATE staff_shift 
                SET shift_type = ?, start_time = ?, end_time = ? 
                WHERE shift_id = ?
            ";
            $stmt2 = $con->prepare($update_query_2);
            $stmt2->bind_param("sssi", $new_shift_type_2, $new_start_time_2, $new_end_time_2, $target_shift_id);
            if (!$stmt2->execute()) {
                die("Error: Failed to update target shift. " . $stmt2->error);
            }
            $stmt2->close();

            echo "Shift details exchanged successfully!";
        } else {
            echo "Request Shift ID: " . $request_shift_id . "<br>";
            echo "Target Shift ID: " . $target_shift_id . "<br>";

            die("Error: No data found for the provided shift IDs.");
        }
    }

    // Update the request status and details
    $update_query = "
        UPDATE staff_shift_swap
        SET request_status = ?, approval_date = NOW(), approval_staff_id = ?, reject_comment = ? 
        WHERE request_id = ?
    ";
    $stmt = $con->prepare($update_query);
    $stmt->bind_param("sisi", $request_status, $approval_staff_id, $reject_comment, $request_id);
    if ($stmt->execute()) {
        echo "<script>
                alert('Request status updated successfully.');
                window.location.href = 'staffshift_view.php';
              </script>";
        exit(); // Stop further script execution
    } else {
        die("Error updating request status: " . $stmt->error);
    }
    $stmt->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Shift Request Status</title>
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

        h1, h2 {
            color: #4CAF50;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #f1f1f1;
        }

        .error {
            background-color: #ffcccb;
            color: #e74c3c;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            background-color: #d4edda;
            color: #28a745;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            display: inline-block;
        }

        select, textarea, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        select:focus, textarea:focus, input[type="submit"]:focus {
            outline: none;
            border-color: #4CAF50;
        }

        input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .btn-back {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            border-radius: 5px;
            transition: background-color 0.3s;
            margin-top: 20px;
        }

        .btn-back:hover {
            background-color: #0056b3;
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
        <h1>Update Shift Request Status</h1>

        <!-- Display success or error messages -->
        <?php if (isset($error_message)): ?>
        <div class="error">
            <?= htmlspecialchars($error_message); ?>
        </div>
        <?php elseif (isset($success_message)): ?>
        <div class="success">
            <?= htmlspecialchars($success_message); ?>
        </div>
        <?php endif; ?>

        <!-- Request Staff Information -->
        <h2>Request Staff Information</h2>
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
                    <td><?= htmlspecialchars($request['request_staff_firstname']); ?></td>
                    <td><?= htmlspecialchars($request['request_staff_lastname']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($request['request_staff_gender'])); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $request['request_staff_role']))); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $request['request_staff_shift_type']))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($request['request_staff_start_time']))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($request['request_staff_end_time']))); ?></td>
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
                    <td><?= htmlspecialchars($request['target_staff_firstname']); ?></td>
                    <td><?= htmlspecialchars($request['target_staff_lastname']); ?></td>
                    <td><?= ucfirst(htmlspecialchars($request['target_staff_gender'])); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $request['target_staff_role']))); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $request['target_staff_shift_type']))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($request['target_staff_start_time']))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($request['target_staff_end_time']))); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Request Details -->
        <h2>Request Details</h2>
        <table>
            <thead>
                <tr>
                    <th>Request Date</th>
                    <th>Request Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars(date("Y-m-d", strtotime($request['request_date']))); ?></td>
                    <td><?= htmlspecialchars($request['request_status']); ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Form for Update -->
        <form method="post">
            <label for="request_status">Request Status:</label>
            <select name="request_status" id="request_status" required>
                <option value="Pending" <?= $request['request_status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Approved" <?= $request['request_status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="Rejected" <?= $request['request_status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
            
            <label for="reject_comment">Reject Comment (if any):</label>
            <textarea name="reject_comment" id="reject_comment"><?= htmlspecialchars($request['reject_comment']); ?></textarea>
            
            <input type="hidden" name="request_id" value="<?= htmlspecialchars($request_id); ?>">
            <input type="submit" value="Update">
        </form>

        <a href="staffshift_view.php" class="btn-back">Back to View Shift Records</a>
    </div>

</body>

</html>

