<?php
require('../../database.php');

// Ensure performance_id is passed and valid
if (!isset($_GET['performance_id'])) {
    die("Error: Performance ID is missing.");
}

$performance_id = intval($_GET['performance_id']); // Sanitize input

// Fetch the role_id and staff_id associated with the performance record
$query = "
    SELECT s.staff_id, s.role_id
    FROM staff_performance sp
    JOIN staff s ON sp.staff_id = s.staff_id
    WHERE sp.performance_id = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $performance_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $staff_id = intval($row['staff_id']); // Get the associated staff_id
    $role_id = intval($row['role_id']); // Get the associated role_id

    // Find another performance_id for the same staff, excluding the one being deleted
    $new_performance_id_query = "
        SELECT performance_id
        FROM staff_performance
        WHERE staff_id = ? AND performance_id <> ?
        ORDER BY eval_date DESC, eval_time DESC
        LIMIT 1";
    $stmt = $con->prepare($new_performance_id_query);
    $stmt->bind_param("ii", $staff_id, $performance_id);
    $stmt->execute();
    $new_performance_result = $stmt->get_result();
    $new_performance_id = null;
    if ($new_performance_result->num_rows > 0) {
        $new_performance_row = $new_performance_result->fetch_assoc();
        $new_performance_id = $new_performance_row['performance_id'];
    }
    $stmt->close();

    // Update the staff table if a new performance_id is found
    if ($new_performance_id) {
        $update_staff_query = "UPDATE staff SET performance_id = ? WHERE staff_id = ?";
        $stmt = $con->prepare($update_staff_query);
        $stmt->bind_param("ii", $new_performance_id, $staff_id);
        $stmt->execute();
        $stmt->close();
    } else {
        // If no other performance record exists, set performance_id to NULL
        $update_staff_query = "UPDATE staff SET performance_id = NULL WHERE staff_id = ?";
        $stmt = $con->prepare($update_staff_query);
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $stmt->close();
    }

    // Delete the performance record
    $delete_query = "DELETE FROM staff_performance WHERE performance_id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $performance_id);

    if ($stmt->execute()) {
        echo "<script>alert('Performance record deleted successfully.');</script>";
        // Redirect based on role_id
        if ($role_id === 1 || $role_id === 2) {
            header("Location: viewrating_hastaff.php");
        } elseif ($role_id === 3 || $role_id === 4 || $role_id === 5) {
            header("Location: viewrating_mstaff.php");
        } elseif ($role_id === 6) {
            header("Location: viewrating_nstaff.php");
        }
        exit();
    } else {
        die("Error deleting performance record: " . $stmt->error);
    }
} else {
    echo "<script>alert('Error: Performance record not found.');</script>";
    header("Location: ../../staffModule/staff_login.php");
    exit();
}
?>
