<?php
require('../../database.php');

// Ensure staff_id is passed and valid
if (!isset($_GET['staff_id'])) {
    die("Error: Staff ID is missing.");
}

$staff_id = intval($_GET['staff_id']); // Sanitize input

// Check if the staff_id exists and get the role_id
$check_query = "SELECT role_id FROM staff WHERE staff_id = $staff_id";
$check_result = mysqli_query($con, $check_query);

if (mysqli_num_rows($check_result) > 0) {
    $row = mysqli_fetch_assoc($check_result);
    $role_id = intval($row['role_id']); // Fetch role_id

    // Create DELETE query
    $delete_query = "DELETE FROM staff WHERE staff_id = $staff_id";

    if (mysqli_query($con, $delete_query)) {
        echo "<script>alert('Record deleted successfully.');</script>";

        // Redirect based on role_id
        if ($role_id === 1 || $role_id === 2) {
            header("Location: view_hastaff.php");
        } elseif ($role_id === 3 || $role_id === 4 || $role_id === 5) {
            header("Location: view_mstaff.php");
        } elseif ($role_id === 6) {
            header("Location: view_nstaff.php");
        }
        
        exit();
    } else {
        die("Error deleting record: " . mysqli_error($con));
    }
} else {
    echo "<script>alert('Error: Record not found.');</script>";
    exit();
}
?>



