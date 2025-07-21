<?php
session_start();
require('../../database.php');
$status = "";

// Check if the user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_GET['room_num'])) {
    die("<p style='color: red;'>Access denied. </p>");
}

// Retrieve staff email from session
$staff_email = $_SESSION['staff_email'];

// Fetch staff details using staff_email
$staff_query = "SELECT staff_id, staff_firstname, staff_lastname FROM staff WHERE staff_email = '$staff_email'";
$staff_result = mysqli_query($con, $staff_query);

if (mysqli_num_rows($staff_result) > 0) {
    $staff_row = mysqli_fetch_assoc($staff_result);
    $staff_id = $staff_row['staff_id']; // Get the staff_id
    $staff_name = $staff_row['staff_firstname'] . " " . $staff_row['staff_lastname'];
} else {
    die("<p style='color: red;'>Error: Staff not found in the database.</p>");
}

// Fetch room num
$room_num = $_GET['room_num'];
$room_query = "SELECT room_id, room_status FROM room WHERE room_num = '$room_num'";
$room_result = mysqli_query($con, $room_query);

if (mysqli_num_rows($room_result) > 0) {
    $room_row = mysqli_fetch_assoc($room_result);
    $room_id = $room_row['room_id'];
    $room_status = $room_row['room_status'];


}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $room_id = $_POST['room_id'];
    $req_desc = $_POST['req_desc'];
    $priority_level = $_POST['priority_level'];
    $req_date = $_POST['req_date'];
    $req_status = 'Pending';

    // Validate room_id
    $validate_room_query = "SELECT room_id FROM room WHERE room_id = '$room_id'";
    $validate_room_result = mysqli_query($con, $validate_room_query);

    if (mysqli_num_rows($validate_room_result) > 0) {
        // Insert the maintenance request
        $query = "INSERT INTO `maintenance_request` (staff_id, room_id, req_desc, priority_level, req_date, req_status)
                  VALUES ('$staff_id', '$room_id', '$req_desc', '$priority_level', '$req_date', '$req_status')";
        $result = mysqli_query($con, $query);

        if ($result) {
            echo "<script>
            alert('Maintenance request submitted successfully!');
            window.location.href = 'viewMainRequest.php';
            </script>";
        } else {
            $status = "<p style='color: red;'>Failed to submit the request: " . mysqli_error($con) . "</p>";
        }
    } else {
        $status = "<p style='color: red;'>Invalid Room ID. Please select a valid room.</p>";
    }
}
?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Maintenance Request</title>
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

        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .form-label {
            font-weight: bold;
        }

        .btn-submit {
            background-color: #0d6efd;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #0b5ed7;
        }

        .status-message {
            text-align: center;
            font-size: 14px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Create Maintenance Request</h1>

        <!-- Display Staff Name -->
        <p><strong>Staff Name:</strong> <?php echo htmlspecialchars($staff_name); ?></p>

        <form action="" method="POST">
            <!-- Room Details -->
            <div class="mb-3">
                <label for="room_num" class="form-label">Room Number:</label>
                <input type="text" name="room_num" id="room_num" class="form-control" 
                       value="<?php echo htmlspecialchars($room_num); ?>" readonly>
                <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_id); ?>">
            </div>

            <!-- Date and Time -->
            <div class="mb-3">
                <label for="req_date" class="form-label">Date and Time:</label>
                <input type="datetime-local" name="req_date" id="req_date" class="form-control" required>
            </div>

            <!-- Issue Description -->
            <div class="mb-3">
                <label for="req_desc" class="form-label">Issue Description:</label>
                <textarea name="req_desc" id="req_desc" class="form-control" rows="5" 
                          placeholder="Describe the issue..." required></textarea>
            </div>

            <!-- Priority Level -->
            <div class="mb-3">
                <label for="priority_level" class="form-label">Priority Level:</label>
                <select name="priority_level" id="priority_level" class="form-select" required>
                    <option value="Low">Low</option>
                    <option value="Medium" selected>Medium</option>
                    <option value="High">High</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" name="submit" class="btn-submit">Submit</button>
        </form>

        <!-- Status Message -->
        <p class="status-message"><?php echo $status; ?></p>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>


