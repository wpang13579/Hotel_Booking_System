<?php
session_start();
require('../../database.php');

// Ensure user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    header("Location: staff_login.php");
    exit();
}

// Handle role selection and fetch staff data
$role_id = isset($_POST['role_id']) ? $_POST['role_id'] : null;
$staff_shifts = [];

if ($role_id) {
    $query = "
        SELECT 
            ss.shift_id, s.staff_firstname, s.staff_lastname, s.staff_gender, sr.role_name, 
            ss.shift_type, ss.start_time, ss.end_time
        FROM staff_shift ss
        JOIN staff s ON ss.staff_id = s.staff_id
        JOIN staff_role sr ON s.role_id = sr.role_id
        WHERE sr.role_id = ?
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $staff_shifts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle deletion request
if (isset($_GET['delete_shift_id'])) {
    $delete_shift_id = $_GET['delete_shift_id'];

    // Delete the staff shift record
    $delete_query = "DELETE FROM staff_shift WHERE shift_id = ?";
    $stmt = $con->prepare($delete_query);
    $stmt->bind_param("i", $delete_shift_id);

    if ($stmt->execute()) {
        echo "<script>alert('Staff shift record deleted successfully.');</script>";
    } else {
        echo "<script>alert('Failed to delete the staff shift record.');</script>";
    }

    // Refresh the page after deletion
    echo "<script>window.location.href = 'staffshift_delete.php';</script>";
    exit();
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Delete Staff Shift Record</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f9f9f9;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #0056b3;
        }

        form {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
        }

        form label {
            margin-right: 10px;
            font-weight: bold;
        }

        form select {
            padding: 8px;
            font-size: 16px;
            margin-right: 10px;
        }

        form button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #0056b3;
            color: white;
            text-transform: uppercase;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e9ecef;
        }

        button.delete-button {
            padding: 8px 16px;
            font-size: 14px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button.delete-button:hover {
            background-color: #c82333;
        }

        button.back-button {
            display: block;
            margin: 20px auto;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button.back-button:hover {
            background-color: #5a6268;
        }

        p {
            text-align: center;
            font-size: 18px;
            color: #555;
        }
    </style>
    <script>
        function confirmDeletion(shiftId) {
            if (confirm('Are you sure you want to delete this staff shift record?')) {
                window.location.href = 'staffshift_delete.php?delete_shift_id=' + shiftId;
            }
        }
    </script>
</head>

<body>
    <h1>Delete Staff Shift Record</h1>

    <!-- Dropdown to select staff role -->
    <form method="POST" action="staffshift_delete.php">
        <label for="role_id">Select Role:</label>
        <select id="role_id" name="role_id" required>
            <option value="">--Select Role--</option>
            <?php
            $roles_query = "SELECT role_id, role_name FROM staff_role";
            $roles_result = $con->query($roles_query);
            while ($role = $roles_result->fetch_assoc()) {
                echo "<option value='{$role['role_id']}'" .
                    (($role_id == $role['role_id']) ? " selected" : "") .
                    ">{$role['role_name']}</option>";
            }
            ?>
        </select>
        <button type="submit">View Staff</button>
    </form>

    <!-- Display staff shift records -->
    <?php if ($staff_shifts): ?>
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
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($staff_shifts as $staff): ?>
                    <tr>
                        <td><?= htmlspecialchars($staff['staff_firstname']); ?></td>
                        <td><?= htmlspecialchars($staff['staff_lastname']); ?></td>
                        <td><?= htmlspecialchars(ucfirst($staff['staff_gender'])); ?></td>
                        <td><?= htmlspecialchars($staff['role_name']); ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $staff['shift_type']))); ?></td>
                        <td><?= htmlspecialchars(date("g:i A", strtotime($staff['start_time']))); ?></td>
                        <td><?= htmlspecialchars(date("g:i A", strtotime($staff['end_time']))); ?></td>
                        <td>
                            <button class="delete-button" onclick="confirmDeletion(<?= htmlspecialchars($staff['shift_id']); ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    <?php else: ?>
        <p>No staff shift records found for the selected role.</p>
    <?php endif; ?>

    <!-- Back Button -->
    <button class="back-button" onclick="location.href='../staffDashboard/staffschedule_dashboard.php'">Back</button>
</body>

</html>