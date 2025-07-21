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

// Initialize staff_members and selected_staff arrays
$staff_members = [];
$selected_staff = [];

// Check if form is submitted for role selection
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['find_staff'])) {
    if (isset($_POST['role_id'])) {
        $role_id = intval($_POST['role_id']);
        // Fetch staff members based on role
        $staff_query = "SELECT staff_id, CONCAT(staff_firstname, ' ', staff_lastname) AS full_name FROM staff WHERE role_id = ?";
        $stmt = $con->prepare($staff_query);
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff_members = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

// Check if form is submitted for staff selection and evaluation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_staff'])) {
    if (isset($_POST['staff_id']) && $_POST['staff_id'] !== "") {
        $staff_id = intval($_POST['staff_id']);
        // Fetch selected staff details
        $staff_detail_query = "SELECT staff_firstname, staff_lastname, staff_gender, staff_role.role_name FROM staff INNER JOIN staff_role ON staff.role_id = staff_role.role_id WHERE staff.staff_id = ?";
        $stmt = $con->prepare($staff_detail_query);
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $selected_staff = $result->fetch_assoc();
        $stmt->close();
    }
}

// Process the form submission for performance evaluation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    if (isset($_POST['staff_id']) && isset($_POST['perf_rating']) && isset($_POST['perf_comment']) && isset($_POST['eval_date']) && isset($_POST['eval_time'])) {
        $staff_id = intval($_POST['staff_id']);
        $perf_rating = $_POST['perf_rating'];
        $perf_comment = $_POST['perf_comment'];
        $eval_date = $_POST['eval_date'];
        $eval_time = $_POST['eval_time'];

        // Use prepared statements to insert data
        $insert_query = "INSERT INTO staff_performance (staff_id, perf_rating, eval_date, eval_time, perf_comment) VALUES (?, ?, ?, ?, ?)";
        $stmt = $con->prepare($insert_query);
        $stmt->bind_param("idsss", $staff_id, $perf_rating, $eval_date, $eval_time, $perf_comment);
        $result = $stmt->execute();

        if ($result) {
            $performance_id = $stmt->insert_id; // Get the last inserted id

            // Update the staff table to set the performance_id
            $update_query = "UPDATE staff SET performance_id = ? WHERE staff_id = ?";
            $stmt = $con->prepare($update_query);
            $stmt->bind_param("ii", $performance_id, $staff_id);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                echo "<script>
                    alert('Staff rating data inserted and updated successfully.');
                    window.location.href = '../staffDashboard/staffrating_dashboard.php';
                </script>";
            } else {
                echo "<script>alert('Failed to update staff performance ID.');</script>";
            }
        } else {
            echo "<script>alert('Staff rating data failed to be inserted: " . addslashes($stmt->error) . "');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Please fill in all required fields.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Performance Evaluation</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 80%;
            max-width: 1000px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        label {
            font-size: 16px;
            color: #444;
        }

        select,
        input[type="text"],
        input[type="number"],
        input[type="date"],
        textarea {
            padding: 10px;
            font-size: 14px;
            border-radius: 4px;
            border: 1px solid #ddd;
            width: 100%;
        }

        input[type="number"]:focus,
        input[type="text"]:focus,
        select:focus,
        textarea:focus {
            border-color: #4CAF50;
            outline: none;
        }

        input[type="submit"],
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #45a049;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        textarea {
            resize: vertical;
        }

        .back-button {
            text-align: center;
            margin-top: 20px;
        }

        .status {
            text-align: center;
            margin: 20px 0;
            font-size: 16px;
            font-weight: bold;
            color: #e74c3c;
        }

        .status.success {
            color: #2ecc71;
        }

        .status.error {
            color: #e74c3c;
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Staff Performance Evaluation</h1>

        <div class="status"><?= isset($status) ? htmlspecialchars($status) : ''; ?></div>

        <form action="" method="post">
            <!-- Role Dropdown -->
            <div class="form-group">
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
            </div>

            <!-- Find Role Button -->
            <div class="form-group">
                <input type="submit" name="find_staff" value="Find Staff">
            </div>
        </form>

        <?php if (!empty($staff_members)): ?>
            <form action="" method="post">
                <!-- Staff Dropdown (Based on Role) -->
                <div class="form-group">
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
                </div>

                <!-- Display Selected Staff Details -->
                <?php if (!empty($selected_staff)): ?>
                    <div class="form-group">
                        <label for="staff_fullname">Full Name:</label>
                        <input type="text" id="staff_fullname" name="staff_fullname"
                            value="<?= htmlspecialchars($selected_staff['staff_firstname'] . ' ' . $selected_staff['staff_lastname']); ?>"
                            readonly />
                    </div>

                    <div class="form-group">
                        <label for="staff_gender">Gender:</label>
                        <input type="text" id="staff_gender" name="staff_gender"
                            value="<?= ucfirst(htmlspecialchars($selected_staff['staff_gender'])); ?>" readonly />
                    </div>

                    <div class="form-group">
                        <label for="staff_role">Role:</label>
                        <input type="text" id="staff_role" name="staff_role"
                            value="<?= htmlspecialchars($selected_staff['role_name']); ?>" readonly />
                    </div>
                <?php endif; ?>

                <!-- Performance Input Fields -->
                <div class="form-group">
                    <label for="perf_rating">Rating (0-5):</label>
                    <input type="number" id="perf_rating" name="perf_rating" min="0" max="5" step="0.1" required />
                </div>

                <div class="form-group">
                    <label for="perf_comment">Performance Comment:</label>
                    <textarea id="perf_comment" name="perf_comment" rows="4" cols="50" required></textarea>
                </div>

                <!-- Date and Time -->
                <div class="form-group">
                    <label for="eval_date">Evaluation Date:</label>
                    <input type="date" id="eval_date" name="eval_date" value="<?= date('Y-m-d'); ?>" required />
                </div>

                <div class="form-group">
                    <label for="eval_time">Evaluation Time:</label>
                    <input type="text" id="eval_time" name="eval_time" value="<?= date('H:i:s'); ?>" readonly />
                </div>

                <!-- Submit Button for Performance Evaluation -->
                <div class="form-group">
                    <input type="submit" name="submit" value="Submit">
                </div>
            </form>
        <?php endif; ?>

        <div class="back-button">
            <button onclick="location.href='../staffDashboard/staffrating_dashboard.php'">Back</button>
        </div>
    </div>
</body>

</html>
