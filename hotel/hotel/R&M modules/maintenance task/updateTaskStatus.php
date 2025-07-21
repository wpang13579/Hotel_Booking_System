<?php
session_start();
require('../../database.php');

// Check if the user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    die("<p style='color: red;'>Access denied. Please log in.</p>");
}

$role = $_SESSION['role_id'];
$staff_id = $_SESSION['staff_email'];

if (isset($_REQUEST['task_id'])) {
    $task_id = $_REQUEST['task_id'];

    // Fetch current maintenance task details along with the room number
    $query = "SELECT t.task_id, t.main_type, t.task_status, t.completion_date, t.task_desc, t.staff_id, 
              m.req_desc, r.room_num, r.room_id
              FROM maintenance_task t
              JOIN maintenance_request m ON t.req_id = m.req_id
              JOIN room r ON m.room_id = r.room_id
              WHERE t.task_id = $task_id";

    $result = mysqli_query($con, $query);
    $task = mysqli_fetch_assoc($result);

    if (!$task) {
        die("<p style='color: red;'>Maintenance task not found.</p>");
    }

        // Restrict updates if the task status is 'completed'
        if (strtolower($task['task_status']) === 'completed') {
            echo "<script>
                alert('This maintenance task is already completed and cannot be updated.');
                window.location.href = 'viewMainTask.php';
            </script>";
            exit();
        }
    
    $room_number = $task['room_num'];
    $room_id = $task['room_id']; // Fetch room ID for updating status
    $main_type = $task['main_type'];
    $req_desc = $task['req_desc'];
} else {
    die("<p style='color: red;'>Invalid task ID.</p>");
}

// Fetch assigned staff's name
$assigned_staff_query = "SELECT staff_firstname, staff_lastname FROM staff WHERE staff_id = " . $task['staff_id'];
$assigned_staff_result = mysqli_query($con, $assigned_staff_query);
$assigned_staff = mysqli_fetch_assoc($assigned_staff_result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $task_status = $_POST['task_status'];
    $completion_date = $_POST['completion_date']; // Retrieve user input for completion_date

    // Validate and format the completion_date
    $completion_date = !empty($completion_date) ? date('Y-m-d H:i:s', strtotime($completion_date)) : null;

    // Update maintenance task
    $update_query = "UPDATE maintenance_task 
                     SET task_status = '$task_status', 
                         completion_date = " . ($completion_date ? "'$completion_date'" : "NULL") . "
                     WHERE task_id = $task_id";

    if (mysqli_query($con, $update_query)) {
        // If completion date is updated, change room status to "housekeeping"
        if (!empty($completion_date)) {
            $update_room_status_query = "UPDATE room 
                                         SET room_status = 'housekeeping' 
                                         WHERE room_id = $room_id";

            if (mysqli_query($con, $update_room_status_query)) {
                echo "<script>
                    alert('Task updated successfully and room status changed to housekeeping.');
                    window.location.href = 'viewMainTask.php';
                </script>";
                exit();
            } else {
                echo "<p style='color: red;'>Error updating room status: " . mysqli_error($con) . "</p>";
            }
        } else {
            echo "<script>
                alert('Task updated successfully.');
                window.location.href = 'viewMainTask.php';
            </script>";
            exit();
        }
    } else {
        echo "<p style='color: red;'>Error updating maintenance task: " . mysqli_error($con) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Maintenance Task</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
            max-width: 600px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .form-label {
            font-weight: bold;
        }

        .readonly {
            background-color: #e9ecef;
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Update Maintenance Task</h2>

        <!-- Display Task Details -->
        <div class="mb-3">
            <label class="form-label">Task ID:</label>
            <p class="readonly"><?php echo $task_id; ?></p>
        </div>

        <div class="mb-3">
            <label class="form-label">Room Number:</label>
            <p class="readonly"><?php echo $room_number; ?></p>
        </div>

        <div class="mb-3">
            <label class="form-label">Assigned To:</label>
            <p class="readonly"><?php echo $assigned_staff['staff_firstname'] . ' ' . $assigned_staff['staff_lastname']; ?></p>
        </div>

        <div class="mb-3">
            <label class="form-label">Maintenance Type:</label>
            <p class="readonly"><?php echo $main_type; ?></p>
        </div>

        <div class="mb-3">
            <label class="form-label">Task Description:</label>
            <p class="readonly"><?php echo htmlspecialchars($task['task_desc']); ?></p>
        </div>

        <!-- Update Task Form -->
        <form method="post">
            <div class="mb-3">
                <label for="completion_date" class="form-label">Completion Date/Time:</label>
                <input type="datetime-local" id="completion_date" name="completion_date" class="form-control"
                    value="<?php echo isset($task['completion_date']) ? date('Y-m-d\TH:i', strtotime($task['completion_date'])) : ''; ?>">
            </div>

            <div class="mb-3">
                <label for="task_status" class="form-label">Task Status:</label>
                <select id="task_status" name="task_status" class="form-select" required>
                    <option value="completed" <?php echo $task['task_status'] == 'completed' ? 'selected' : ''; ?>>
                        Completed</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Update Task</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

