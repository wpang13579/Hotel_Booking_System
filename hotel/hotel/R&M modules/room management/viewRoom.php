<?php
session_start();
require('../../database.php');

// Check if the user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    die("<p style='color: red;'>Access denied. Please log in.</p>");
}

$role = $_SESSION['role_id']; // Get the role (staff or manager)
$staff_id = $_SESSION['staff_email']; // Get the staff email

// Initialize filter
$status_filter = isset($_GET['status_filter']) ? $_GET['status_filter'] : '';

// Query to fetch rooms with optional filter
$filter_condition = $status_filter ? "WHERE room_status = '$status_filter'" : '';
$sel_query = "SELECT * FROM room $filter_condition ORDER BY room_id;";
$result = mysqli_query($con, $sel_query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>View Room</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .page-header {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #007bff;
            color: white;
            border-radius: 8px;
        }

        .btn-dashboard {
            margin-top: 20px;
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: 0.3s;
        }

        .btn-dashboard:hover {
            background-color: #0056b3;
            color: white;
        }

        .update-link {
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .update-link:hover {
            text-decoration: underline;
            color: #0056b3;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <!-- Page Header -->
        <div class="page-header text-center">
            <h2>Room Status Management</h2>
            <p><strong>Logged in as:</strong>
                <?php
                echo $role == 4 ? 'Room Manager' : ($role == 6 ? 'Normal Staff' : 'Unknown Role');
                ?>
            </p>
        </div>

        <!-- Filter Form -->
        <div class="mb-4">
            <form method="get" class="d-flex justify-content-center align-items-center">
                <label for="status_filter" class="form-label me-2">Filter by Status:</label>
                <select name="status_filter" id="status_filter" class="form-select me-2" style="width: 200px;">
                    <option value="">All</option>
                    <option value="Available" <?php echo $status_filter == 'Available' ? 'selected' : ''; ?>>Available</option>
                    <option value="Under Maintenance" <?php echo $status_filter == 'Under Maintenance' ? 'selected' : ''; ?>>Under Maintenance</option>
                    <option value="Housekeeping" <?php echo $status_filter == 'Housekeeping' ? 'selected' : ''; ?>>Housekeeping</option>
                    <option value="Occupied" <?php echo $status_filter == 'Occupied' ? 'selected' : ''; ?>>Occupied</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <!-- Room Table -->
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">Room Number</th>
                        <th scope="col">Room Type</th>
                        <th scope="col">Room Price</th>
                        <th scope="col">Room Status</th>
                        <th scope="col">Room Update</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $currencySymbol = "RM";
                    while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr>
                            <td align="center"><?php echo htmlspecialchars($row["room_num"]); ?></td>
                            <td align="center">
                                <?php
                                switch ($row["room_type"]) {
                                    case 1:
                                        echo "Superior";
                                        break;
                                    case 2:
                                        echo "Deluxe";
                                        break;
                                    case 3:
                                        echo "Luxury";
                                        break;
                                    default:
                                        echo "Unknown";
                                        break;
                                }
                                ?>
                            </td>
                            <td align="center"><?php echo $currencySymbol . htmlspecialchars($row["room_price"]); ?></td>
                            <td align="center"><?php echo htmlspecialchars($row["room_status"]); ?></td>
                            <td align="center">
                                <a class="update-link" href="updateRoomStatus.php?room_id=<?php echo $row["room_id"]; ?>">Update</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Dashboard Button -->
        <div class="text-center">
            <a class="btn-dashboard" href="../dashboard.php">Go to Dashboard</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
