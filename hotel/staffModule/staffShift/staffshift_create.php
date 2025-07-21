<?php
require('../../database.php');
date_default_timezone_set('Asia/Kuala_Lumpur'); // Set timezone to Malaysia (UTC+8)
$status = "";


// Fetch roles for the dropdown
$roles = [];
$role_query = "SELECT * FROM staff_role";
$role_result = mysqli_query($con, $role_query);
if ($role_result) {
    $roles = mysqli_fetch_all($role_result, MYSQLI_ASSOC);
}

// Initialize staff_members array
$staff_members = [];

// Check if form is submitted for role selection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['find_staff'])) {
    if (isset($_POST['role_id'])) {
        $role_id = intval($_POST['role_id']);
        // Fetch staff members based on role excluding those already assigned a shift
        $staff_query = "SELECT staff_id, CONCAT(staff_firstname, ' ', staff_lastname) AS full_name 
                        FROM staff 
                        WHERE role_id = ? AND staff_id NOT IN (SELECT staff_id FROM staff_shift)";
        $stmt = $con->prepare($staff_query);
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff_members = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Process the form submission for assigning shift
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['assign_shift'])) {
    if (isset($_POST['staff_id']) && isset($_POST['shift_type']) && isset($_POST['start_time']) && isset($_POST['end_time'])) {
        $staff_id = intval($_POST['staff_id']);
        $shift_type = $_POST['shift_type'];
        $start_time = date("H:i:s", strtotime($_POST['start_time'])); // Convert to 24-hour format
        $end_time = date("H:i:s", strtotime($_POST['end_time'])); // Convert to 24-hour format

        // Insert shift details into staff_shift table
        $insert_query = "INSERT INTO staff_shift (staff_id, shift_type, start_time, end_time) VALUES (?, ?, ?, ?)";
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param("isss", $staff_id, $shift_type, $start_time, $end_time);
        $result = $stmt->execute();

        if ($result) {
            $status = "<p style='color: green;'>Shift assigned successfully.</p>";
        } else {
            $status = "<p style='color: red;'>Failed to assign shift: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        $status = "<p style='color: red;'>Please fill in all required fields.</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Staff Shift</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f7f9fc;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
            margin-bottom: 5px;
        }

        select, input[type="text"], input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        button {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #5a6268;
        }

        .no-staff {
            text-align: center;
            color: #dc3545;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .back-button-container {
            text-align: center;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .container {
                margin: 20px;
                padding: 15px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
    <script>
        function updateShiftTimes() {
            const shiftType = document.getElementById('shift_type').value;
            const startTimeField = document.getElementById('start_time');
            const endTimeField = document.getElementById('end_time');

            if (shiftType === 'morning_shift') {
                startTimeField.value = '08:00 AM';
                endTimeField.value = '12:00 AM';
            } else if (shiftType === 'night_shift') {
                startTimeField.value = '12:00 AM';
                endTimeField.value = '08:00 AM';
            } else {
                startTimeField.value = '';
                endTimeField.value = '';
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <h1>Assign Staff Shift</h1>
        <?= $status; ?>

        <form action="" method="post">
            <label for="role_id">Role:</label>
            <select id="role_id" name="role_id" required>
                <option value="">--Select Role--</option>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= htmlspecialchars($role['role_id']); ?>"
                        <?php if (isset($_POST['role_id']) && $_POST['role_id'] == $role['role_id']) echo "selected"; ?>>
                        <?= htmlspecialchars($role['role_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="submit" name="find_staff" value="Find Staff">
        </form>

        <?php if (isset($_POST['find_staff']) && empty($staff_members)): ?>
            <p class="no-staff">No staff available or found for the selected role.</p>
        <?php elseif (!empty($staff_members)): ?>
            <form action="" method="post">
                <label for="staff_id">Staff (Full Name):</label>
                <select id="staff_id" name="staff_id" required>
                    <option value="">--Select Staff--</option>
                    <?php foreach ($staff_members as $staff): ?>
                        <option value="<?= htmlspecialchars($staff['staff_id']); ?>"
                            <?php if (isset($_POST['staff_id']) && $_POST['staff_id'] == $staff['staff_id']) echo "selected"; ?>>
                            <?= htmlspecialchars($staff['full_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="shift_type">Shift Type:</label>
                <select id="shift_type" name="shift_type" onchange="updateShiftTimes()" required>
                    <option value="">--Select Shift--</option>
                    <option value="morning_shift">Morning Shift</option>
                    <option value="night_shift">Night Shift</option>
                </select>

                <label for="start_time">Start Time:</label>
                <input type="text" id="start_time" name="start_time" readonly required>

                <label for="end_time">End Time:</label>
                <input type="text" id="end_time" name="end_time" readonly required>

                <input type="submit" name="assign_shift" value="Assign Shift">
            </form>
        <?php endif; ?>

        <div class="back-button-container">
            <button onclick="location.href='../staffDashboard/staffschedule_dashboard.php'">Back</button>
        </div>
    </div>
</body>

</html>

