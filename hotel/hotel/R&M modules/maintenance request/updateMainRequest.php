<?php
session_start();
require('../../database.php');
$status = "";

// Check if the user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    die("<p style='color: red;'>You must be logged in to update a maintenance request.</p>");
}

$staff_email = $_SESSION['staff_email'];
$role = $_SESSION['role_id']; // Staff (6) or Room Manager (4)

// Fetch request details if a request ID is provided
$req_id = isset($_GET['req_id']) ? $_GET['req_id'] : null;
$req_desc = "";
$priority_level = "";
$req_status = "Pending";
$room_number = "";
$req_date = "";
$request_read_only = false;
$request_staff_email = null;

if ($req_id) {
    $fetch_query = "SELECT m.req_desc, m.priority_level, m.req_status, m.req_date, r.room_num, 
                           s.staff_email, s.staff_firstname, s.staff_lastname 
                    FROM maintenance_request m 
                    JOIN room r ON m.room_id = r.room_id 
                    JOIN staff s ON m.staff_id = s.staff_id
                    WHERE m.req_id = $req_id";

    $result = mysqli_query($con, $fetch_query);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $req_desc = $row['req_desc'];
        $priority_level = $row['priority_level'];
        $req_status = $row['req_status'];
        $room_number = $row['room_num'];
        $req_date = $row['req_date'];
        $staff_name = $row['staff_firstname'] . " " . $row['staff_lastname'];
        $request_staff_email = $row['staff_email'];

        // Allow only staff who created the request or manager to update it
        if ($role != 4 && $request_staff_email != $staff_email) {
            echo "<script>
            alert('Cannot update other people maintenance request');
            window.location.href = 'viewMainRequest.php';
        </script>";
            exit();
        }

        // If status is already approved, make the fields read-only
        if ($req_status == 'Approved') {
            $request_read_only = true;
        }
    } else {
        die("<p style='color: red;'>Invalid Request ID.</p>");
        exit();
    }
}
// Prevent deletion if the status is 'Approved'
if (strtolower($req_status) === 'approved') {
    echo "<script>
        alert('Cannot update a maintenance request with status \"Approved\".');
        window.location.href = 'viewMainRequest.php';
    </script>";
}

// Update the request if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$request_read_only) {
    $req_desc = $_POST['req_desc'];
    $priority_level = $_POST['priority_level'];
    $req_status = $role == 4 ? $_POST['req_status'] : "Pending"; // Only Room Manager can change status

    // Update the maintenance_request table
    $update_query = "UPDATE maintenance_request 
                     SET req_desc = '$req_desc', 
                         priority_level = '$priority_level', 
                         req_status = '$req_status' 
                     WHERE req_id = $req_id";

    if (mysqli_query($con, $update_query)) {
        echo "<script>
            alert('Maintenance request updated successfully!');
            window.location.href = 'viewMainRequest.php';
        </script>";
    } else {
        echo "<p style='color: red;'>Error updating request: " . mysqli_error($con) . "</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Maintenance Request</title>
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

        .btn-update {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-update:hover {
            background-color: #0056b3;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }

        .form-label {
            font-weight: bold;
        }

        .readonly-field {
            background-color: #e9ecef;
            border: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Update Maintenance Request</h2>
        <form method="post">
            <!-- Request By -->
            <div class="mb-3">
                <label class="form-label"><strong>Request By:</strong></label>
                <p><?php echo htmlspecialchars($staff_name); ?></p>
            </div>

            <!-- Request Date and Time -->
            <div class="mb-3">
                <label for="req_date" class="form-label">Request Date and Time:</label>
                <input type="text" id="req_date" name="req_date" class="form-control readonly-field" 
                       value="<?php echo date('Y-m-d H:i', strtotime($req_date)); ?>" readonly>
            </div>

            <!-- Room Number -->
            <div class="mb-3">
                <label for="room_num" class="form-label">Room Number:</label>
                <input type="text" id="room_num" name="room_num" class="form-control readonly-field" 
                       value="<?php echo htmlspecialchars($room_number); ?>" readonly>
            </div>

            <!-- Request Description -->
            <div class="mb-3">
                <label for="req_desc" class="form-label">Request Description:</label>
                <textarea id="req_desc" name="req_desc" rows="4" class="form-control" 
                          <?php echo $request_read_only ? 'readonly' : ''; ?> required><?php echo htmlspecialchars($req_desc); ?></textarea>
            </div>

            <!-- Priority Level -->
            <div class="mb-3">
                <label for="priority_level" class="form-label">Priority Level:</label>
                <select id="priority_level" name="priority_level" class="form-select" 
                        <?php echo $request_read_only ? 'disabled' : ''; ?> required>
                    <option value="Low" <?php echo $priority_level == 'Low' ? 'selected' : ''; ?>>Low</option>
                    <option value="Medium" <?php echo $priority_level == 'Medium' ? 'selected' : ''; ?>>Medium</option>
                    <option value="High" <?php echo $priority_level == 'High' ? 'selected' : ''; ?>>High</option>
                </select>
            </div>

            <!-- Request Status -->
            <div class="mb-3">
                <label for="req_status" class="form-label">Request Status:</label>
                <?php if ($role == 4) { // Room Manager can change the status ?>
                    <select id="req_status" name="req_status" class="form-select" required>
                        <option value="Pending" <?php echo $req_status == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $req_status == 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    </select>
                <?php } else { // Staff can only see the status ?>
                    <p><strong>Status:</strong> <?php echo htmlspecialchars($req_status); ?></p>
                <?php } ?>
            </div>

            <!-- Buttons -->
            <?php if (!$request_read_only) { ?>
                <div class="text-center">
                    <button type="submit" class="btn btn-update">Update Request</button>
                </div>
            <?php } else { ?>
                <p class="text-success text-center"><strong>Request has been approved. No further changes allowed.</strong></p>
            <?php } ?>
        </form>
        <div class="text-center mt-4">
            <a href="viewMainRequest.php" class="btn-back">Back</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

