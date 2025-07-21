<?php
session_start();
require('../database.php'); // Database connection

// Check if the user is logged in and retrieve role_id
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    header("Location: login.php");
    exit();
}

$role_id = $_SESSION['role_id'];

// Restrict normal staff (assuming role_id = 6 is for normal staff)
if ($role_id == 6) {
   echo "<script>alert('You do not have permission to access this page.');
                window.location.href = 'dashboard.php';</script>";
}

// Initialize filter conditions
$date_filter = "";

// Variables to hold total maintenance requests
$total_requests = 0;

// Handle filtering based on the selected date range
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_date'])) {
    $filter_type = $_POST['filter_date'];
    $selected_date = $_POST['selected_date'] ?? date('Y-m-d');

    if ($filter_type === 'day') {
        $date_filter = "AND DATE(req_date) = '$selected_date'";
    } elseif ($filter_type === 'week') {
        $date_filter = "AND YEARWEEK(DATE(req_date), 1) = YEARWEEK('$selected_date', 1)";
    } elseif ($filter_type === 'month') {
        $date_filter = "AND YEAR(DATE(req_date)) = YEAR('$selected_date') 
                        AND MONTH(DATE(req_date)) = MONTH('$selected_date')";
    }
}

// Count total approved maintenance requests based on the selected filter
$total_requests_query = "
    SELECT COUNT(*) AS total_requests 
    FROM maintenance_request
    WHERE req_status = 'Approved' 
    $date_filter
";
$total_requests_result = mysqli_query($con, $total_requests_query);

if ($total_requests_result) {
    $row = mysqli_fetch_assoc($total_requests_result);
    $total_requests = $row['total_requests'];
}

// Fetch approved maintenance requests based on the selected filter
$room_requests_query = "
    SELECT 
        req_id,
        req_date,
        req_desc,
        req_status,
        priority_level,
        room.room_num
    FROM 
        maintenance_request
    JOIN room ON maintenance_request.room_id = room.room_id
    WHERE req_status = 'Approved' 
    $date_filter
    ORDER BY req_date DESC
";
$room_requests_result = mysqli_query($con, $room_requests_query);

// Fetch counts for daily, weekly, and monthly approved maintenance requests
$daily_query = "SELECT COUNT(*) AS daily_count FROM maintenance_request WHERE req_status = 'Approved' AND DATE(req_date) = CURDATE()";
$weekly_query = "SELECT COUNT(*) AS weekly_count FROM maintenance_request WHERE req_status = 'Approved' AND YEARWEEK(DATE(req_date), 1) = YEARWEEK(CURDATE(), 1)";
$monthly_query = "SELECT COUNT(*) AS monthly_count FROM maintenance_request WHERE req_status = 'Approved' AND YEAR(DATE(req_date)) = YEAR(CURDATE()) AND MONTH(DATE(req_date)) = MONTH(CURDATE())";

$daily_result = mysqli_query($con, $daily_query);
$weekly_result = mysqli_query($con, $weekly_query);
$monthly_result = mysqli_query($con, $monthly_query);

$daily_count = ($daily_result) ? mysqli_fetch_assoc($daily_result)['daily_count'] : 0;
$weekly_count = ($weekly_result) ? mysqli_fetch_assoc($weekly_result)['weekly_count'] : 0;
$monthly_count = ($monthly_result) ? mysqli_fetch_assoc($monthly_result)['monthly_count'] : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Maintenance Request Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chart-container {
            width: 100%;
            height: 400px;
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <div class="text-center mb-4">
            <h2>Maintenance Request Report</h2>
        </div>

        <!-- Filter Form -->
        <div class="card p-3 mb-4">
            <form method="POST" action="">
                <div class="row">
                    <div class="col-md-4">
                        <label for="filter_date" class="form-label">Filter by:</label>
                        <select name="filter_date" id="filter_date" class="form-select" required>
                            <option value="day" <?php echo isset($_POST['filter_date']) && $_POST['filter_date'] === 'day' ? 'selected' : ''; ?>>Specific Day</option>
                            <option value="week" <?php echo isset($_POST['filter_date']) && $_POST['filter_date'] === 'week' ? 'selected' : ''; ?>>Specific Week</option>
                            <option value="month" <?php echo isset($_POST['filter_date']) && $_POST['filter_date'] === 'month' ? 'selected' : ''; ?>>Specific Month</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="selected_date" class="form-label">Select Date:</label>
                        <input type="date" name="selected_date" id="selected_date" class="form-control" value="<?php echo isset($_POST['selected_date']) ? $_POST['selected_date'] : ''; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Total Approved Requests -->
        <div class="alert alert-info text-center">
            <h4>Total Approved Maintenance Requests: <?php echo $total_requests; ?></h4>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Request Date</th>
                        <th>Room Number</th>
                        <th>Request Description</th>
                        <th>Priority Level</th>
                        <th>Request Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($room_requests_result && mysqli_num_rows($room_requests_result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($room_requests_result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['req_date']))); ?></td>
                                <td><?php echo htmlspecialchars($row['room_num']); ?></td>
                                <td><?php echo htmlspecialchars($row['req_desc']); ?></td>
                                <td><?php echo htmlspecialchars($row['priority_level']); ?></td>
                                <td><?php echo htmlspecialchars($row['req_status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Chart -->
        <div class="chart-container my-5">
            <canvas id="maintenanceChart"></canvas>
        </div>

        <!-- Back to Dashboard -->
        <div class="text-center">
            <a href="dashboard.php" class="btn btn-danger">Back to Dashboard</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('maintenanceChart').getContext('2d');
        const maintenanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Daily', 'Weekly', 'Monthly'],
                datasets: [{
                    label: 'Number of Approved Maintenance Requests',
                    data: [<?php echo $daily_count; ?>, <?php echo $weekly_count; ?>, <?php echo $monthly_count; ?>],
                    backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)'],
                    borderColor: ['rgba(75, 192, 192, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Approved Maintenance Requests Report'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
