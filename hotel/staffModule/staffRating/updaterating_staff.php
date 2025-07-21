<?php
require('../../database.php');
$status = "";

// Check if performance_id is set
if (isset($_GET['performance_id'])) {
    $performance_id = intval($_GET['performance_id']);

    // Fetch the performance record details
    $query = "
        SELECT sp.*, s.staff_id, s.staff_firstname, s.staff_lastname, s.staff_gender, sr.role_id, sr.role_name
        FROM staff_performance sp
        JOIN staff s ON sp.staff_id = s.staff_id
        JOIN staff_role sr ON s.role_id = sr.role_id
        WHERE sp.performance_id = ?";
    $stmt = $con->prepare($query);
    $stmt->bind_param("i", $performance_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $performance = $result->fetch_assoc();
    $stmt->close();
} else {
    header("Location: view_performance.php");
    exit();
}

// Process form submission for updating the performance record
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['update_performance'])) {
        $perf_rating = $_POST['perf_rating'];
        $perf_comment = $_POST['perf_comment'];
        $eval_date = $_POST['eval_date'];
        $eval_time = $_POST['eval_time'];

        // Update the performance record
        $update_query = "
            UPDATE staff_performance
            SET perf_rating = ?, perf_comment = ?, eval_date = ?, eval_time = ?
            WHERE performance_id = ?";
        $stmt = $con->prepare($update_query);
        $stmt->bind_param("dsssi", $perf_rating, $perf_comment, $eval_date, $eval_time, $performance_id);
        $result = $stmt->execute();

        if ($result) {
            // Redirect with a success alert
            echo "<script>
                alert('Update staff rating successfully.');
                ";
            if ($performance['role_id'] == 1 || $performance['role_id'] == 2) {
                echo "window.location.href = 'viewrating_hastaff.php';";
            } elseif ($performance['role_id'] == 3 || $performance['role_id'] == 4 || $performance['role_id'] == 5) {
                echo "window.location.href = 'viewrating_mstaff.php';";
            } elseif ($performance['role_id'] == 6) {
                echo "window.location.href = 'viewrating_nstaff.php';";
            } else {
                echo "window.location.href = '../../staffModule/staff_login.php';";
            }
            echo "</script>";
        } else {
            // Alert for error and stay on the same page
            echo "<script>alert('Error updating staff rating. Please try again.');</script>";
        }
    } elseif (isset($_POST['back'])) {
        // Handle back button redirection based on role_id
        if ($performance['role_id'] == 1 || $performance['role_id'] == 2) {
            header("Location: viewrating_hastaff.php");
        } elseif ($performance['role_id'] == 3 || $performance['role_id'] == 4 || $performance['role_id'] == 5) {
            header("Location: viewrating_mstaff.php");
        } elseif ($performance['role_id'] == 6) {
            header("Location: viewrating_nstaff.php");
        } else {
            header("Location:../../staffModule/staff_login.php");
        }
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Performance Record</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #4CAF50;
            margin-top: 50px;
            font-size: 28px;
        }

        .container {
            width: 80%;
            max-width: 900px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-size: 16px;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"],
        input[type="time"],
        textarea {
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        input[type="submit"] {
            padding: 12px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .back-button {
            text-align: center;
            margin-top: 20px;
        }

        .back-button input {
            background-color: #f44336;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .back-button input:hover {
            background-color: #e53935;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 95%;
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Update Performance Record</h1>
        <?= $status; ?>
        <form action="" method="post">
            <!-- Display Current Performance Record Details -->
            <p>
                <label for="staff_name">Staff Name:</label>
                <input type="text" id="staff_name" name="staff_name" value="<?= htmlspecialchars($performance['staff_firstname'] . ' ' . $performance['staff_lastname']); ?>" readonly />
            </p>
            <p>
                <label for="staff_gender">Gender:</label>
                <input type="text" id="staff_gender" name="staff_gender" value="<?= ucfirst(htmlspecialchars($performance['staff_gender'])); ?>" readonly />
            </p>
            <p>
                <label for="staff_role">Role:</label>
                <input type="text" id="staff_role" name="staff_role" value="<?= htmlspecialchars($performance['role_name']); ?>" readonly />
            </p>

            <!-- Performance Input Fields -->
            <p>
                <label for="perf_rating">Rating (0-5):</label>
                <input type="number" id="perf_rating" name="perf_rating" min="0" max="5" step="0.1" value="<?= htmlspecialchars($performance['perf_rating']); ?>" required />
            </p>
            <p>
                <label for="perf_comment">Performance Comment:</label>
                <textarea id="perf_comment" name="perf_comment" rows="4" cols="50" required><?= htmlspecialchars($performance['perf_comment']); ?></textarea>
            </p>

            <!-- Date and Time -->
            <p>
                <label for="eval_date">Evaluation Date:</label>
                <input type="date" id="eval_date" name="eval_date" value="<?= htmlspecialchars($performance['eval_date']); ?>" required />
            </p>
            <p>
                <label for="eval_time">Evaluation Time:</label>
                <input type="time" id="eval_time" name="eval_time" value="<?= htmlspecialchars($performance['eval_time']); ?>" required />
            </p>

            <!-- Submit Button for Performance Update -->
            <input type="submit" name="update_performance" value="Update">
            <div class="back-button">
                <!-- Back Button -->
                <input type="submit" name="back" value="Back">
            </div>
        </form>
    </div>
</body>

</html>
