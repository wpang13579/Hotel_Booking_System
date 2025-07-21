<?php
session_start();
require('../../database.php');

// Check if the user is logged in and has the manager role
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id']) || $_SESSION['role_id'] != 4) {
    die("<p style='color: red;'>Access denied. Only managers can delete maintenance requests.</p>");
}

// Check if a request ID is provided
if (!isset($_GET['req_id'])) {
    die("<p style='color: red;'>Invalid request ID.</p>");
}

$req_id = $_GET['req_id'];

// Fetch the maintenance request to ensure it exists
$fetch_query = "SELECT * FROM maintenance_request WHERE req_id = $req_id";
$result = mysqli_query($con, $fetch_query);

if (!$result || mysqli_num_rows($result) == 0) {
    die("<p style='color: red;'>Maintenance request not found.</p>");
}

// Fetch the room ID associated with the maintenance request
$request = mysqli_fetch_assoc($result);
$room_id = $request['room_id'];
$req_status = $request['req_status'];


// Prevent deletion if the status is 'Approved'
if (strtolower($req_status) === 'approved') {
    echo "<script>
        alert('Cannot delete a maintenance request with status \"Approved\".');
        window.location.href = 'viewMainRequest.php';
    </script>";
    exit();
}
// If the delete request is confirmed
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Update the room status to 'available'
    $update_room_query = "UPDATE room SET room_status = 'available' WHERE room_id = $room_id";
    if (!mysqli_query($con, $update_room_query)) {
        echo "<p style='color: red;'>Error updating room status: " . mysqli_error($con) . "</p>";
        exit();
    }

    // Delete the maintenance request
    $delete_query = "DELETE FROM maintenance_request WHERE req_id = $req_id";
    if (mysqli_query($con, $delete_query)) {
        header("Location: viewMainRequest.php?status=deleted");
        exit();
    } else {
        echo "<p style='color: red;'>Error deleting the maintenance request: " . mysqli_error($con) . "</p>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Maintenance Request</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
        }

        h2 {
            color: #dc3545;
            margin-bottom: 20px;
        }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-delete:hover {
            background-color: #bb2d3b;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Delete Maintenance Request</h2>
        <p>Are you sure you want to delete this maintenance request?</p>

        <form method="post">
            <button type="submit" class="btn-delete">Delete</button>
            <button type="button" class="btn-cancel" onclick="window.location.href='viewMainRequest.php';">Cancel</button>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

