<?php
session_start();
require('../../database.php');

// Check if the user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    die("<p style='color: red;'>Access denied. Please log in.</p>");
}

$role = $_SESSION['role_id']; 
$staff_email = $_SESSION['staff_email']; 

// Only managers can create maintenance tasks
if ($role != 4) {
    die("<p style='color: red;'>Access denied. Only managers can create emergency maintenance tasks.</p>");
}

// Get emergency request ID 
$req_id = isset($_GET['req_id']) ? $_GET['req_id'] : null;

if (!$req_id) {
    die("<p style='color: red;'>Invalid emergency maintenance request ID. Please ensure the req_id is passed in the URL.</p>");
}

// Fetch emergency maintenance request details
$fetch_request_query = "SELECT m.req_desc, r.room_num, m.req_date,m.req_status 
                        FROM maintenance_request m 
                        JOIN room r ON m.room_id = r.room_id 
                        WHERE m.req_id = $req_id AND m.req_desc LIKE 'Emergency:%'";

$request_result = mysqli_query($con, $fetch_request_query);

if ($request_result && mysqli_num_rows($request_result) > 0) {
    $request = mysqli_fetch_assoc($request_result);
    $req_desc = $request['req_desc'];
    $room_number = $request['room_num'];
    $req_date = $request['req_date'];
    $req_status = $request['req_status'];

    // Restriction: Check if the request has already been approved
    if ($req_status === 'approved') {
        echo "<script>
            alert('This emergency maintenance request has already been approved. You cannot create another task for it.');
            window.location.href = '../maintenance request/viewEmergencyRequest.php';
        </script>";
        exit();
    }
} else {
    die("<p style='color: red;'>Emergency maintenance request not found.</p>");
}

// Fetch all staff for assignment
// Fetch all staff for assignment
$staff_query = "SELECT staff_id, staff_firstname, staff_lastname 
                FROM staff 
                WHERE role_id IN ( 6)";

$staff_result = mysqli_query($con, $staff_query);
$staff_list = [];
if ($staff_result) {
    while ($row = mysqli_fetch_assoc($staff_result)) {
        $staff_list[] = $row;
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $task_status = $_POST['task_status'];
    $main_type = $_POST['main_type'];
    $task_desc = "Emergency: " . $_POST['task_desc']; // Prepend "Emergency:" to the task description
    $staff_id = $_POST['staff_id'];

    // Insert the task
    $insert_task_query = "INSERT INTO maintenance_task (req_id, task_status, main_type, task_desc, staff_id) 
                          VALUES ('$req_id', '$task_status', '$main_type', '$task_desc', '$staff_id')";

    if (mysqli_query($con, $insert_task_query)) {
        // Update the request status to "Approved"
        $update_request_query = "UPDATE maintenance_request SET req_status = 'Approved' WHERE req_id = $req_id";
        if (mysqli_query($con, $update_request_query)) {
            echo "<script>
                alert('Emergency task created successfully, and the request status has been updated to Approved.');
                window.location.href = 'viewEmergencyTask.php';
            </script>";
            exit();
        } else {
            echo "<p style='color: red;'>Emergency task created, but failed to update the request status: " . mysqli_error($con) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Error creating emergency maintenance task: " . mysqli_error($con) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Emergency Maintenance Task</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
            max-width: 700px;
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
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #b02a37;
        }

        .btn-back-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .form-label {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Create Emergency Maintenance Task</h2>

        <!-- Display Maintenance Request Details -->
        <div class="mb-3">
            <label class="form-label">Request ID:</label>
            <p class="form-control-plaintext"><?php echo $req_id; ?></p>
        </div>

        <div class="mb-3">
            <label class="form-label">Room Number:</label>
            <p class="form-control-plaintext"><?php echo $room_number; ?></p>
        </div>

        <div class="mb-3">
            <label class="form-label">Request Date:</label>
            <p class="form-control-plaintext"><?php echo $req_date; ?></p>
        </div>

        <div class="mb-3">
            <label class="form-label">Request Description:</label>
            <p class="form-control-plaintext"><?php echo htmlspecialchars($req_desc); ?></p>
        </div>

        <!-- Emergency Task Creation Form -->
        <form method="post">
            <input type="hidden" id="req_desc" name="req_desc" value="<?php echo htmlspecialchars($req_desc); ?>">

            <div class="mb-3">
                <label for="main_type" class="form-label">Maintenance Type:</label>
                <select id="main_type" name="main_type" class="form-select" required>
                    <option value="AC repair">Air-Con</option>
                    <option value="plumbing">Plumbing</option>
                    <option value="lighting">Electrical</option>
                    <option value="furniture">Furniture</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="task_desc" class="form-label">Task Description:</label>
                <textarea id="task_desc" name="task_desc" class="form-control" rows="4" placeholder="Enter task description" required></textarea>
            </div>

            <div class="mb-3">
                <label for="staff_id" class="form-label">Assign To (Staff):</label>
                <select id="staff_id" name="staff_id" class="form-select" required>
                    <option value="">-- Select Staff --</option>
                    <?php foreach ($staff_list as $staff) { ?>
                        <option value="<?php echo $staff['staff_id']; ?>">
                            <?php echo $staff['staff_firstname'] . " " . $staff['staff_lastname']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="task_status" class="form-label">Task Status:</label>
                <select id="task_status" name="task_status" class="form-select" required>
                    <option value="in progress" selected>In Progress</option>
                </select>
            </div>

            <button type="submit" class="btn-submit">Create Emergency Task</button>
        </form>

        <!-- Centered Back to View Button -->
        <div class="btn-back-container">
            <a href="../maintenance request/viewEmergencyRequest.php" class="btn-back">Back </a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

