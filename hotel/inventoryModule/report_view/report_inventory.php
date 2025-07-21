<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

// Initialize filter conditions
$date_filter = "";
$selected_date = date('Y-m-d'); // Default to current date

// Handle filtering based on the selected date range
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['filter_date'])) {
    $filter_type = $_POST['filter_date'];
    $selected_date = $_POST['selected_date'] ?? date('Y-m-d');

    if ($filter_type === 'day') {
        $date_filter = "WHERE DATE(stay.check_in_date) = '$selected_date'";
    } elseif ($filter_type === 'week') {
        $date_filter = "WHERE YEARWEEK(DATE(stay.check_in_date), 1) = YEARWEEK('$selected_date', 1)";
    } elseif ($filter_type === 'month') {
        $date_filter = "WHERE YEAR(DATE(stay.check_in_date)) = YEAR('$selected_date') 
                        AND MONTH(DATE(stay.check_in_date)) = MONTH('$selected_date')";
    }
}

// Fetch filtered or all inventory usage reports
$inventory_used_query = "
    SELECT 
        stay.stay_id,
        stay.check_in_date,
        stay.check_out_date,
        room.room_num,
        room_type.type AS room_type,
        inventory_management.inv_name,
        inventory_assign.quantity AS quantity_used
    FROM 
        stay
    INNER JOIN room ON stay.room_id = room.room_id
    INNER JOIN room_type ON room.room_type = room_type.id
    INNER JOIN inventory_assign ON inventory_assign.room_type_id = room.room_type
    INNER JOIN inventory_management ON inventory_assign.inventory_id = inventory_management.id
    $date_filter
    ORDER BY stay.check_in_date DESC
";
$inventory_used_result = mysqli_query($con, $inventory_used_query);

// Group data by stay ID and prepare chart data
$grouped_data = [];
$chart_data = [];
while ($row = mysqli_fetch_assoc($inventory_used_result)) {
    $stay_id = $row['stay_id'];
    if (!isset($grouped_data[$stay_id])) {
        $grouped_data[$stay_id] = [
            'stay_details' => [
                'stay_id' => $row['stay_id'],
                'check_in_date' => $row['check_in_date'],
                'check_out_date' => $row['check_out_date'],
                'room_num' => $row['room_num'],
                'room_type' => $row['room_type']
            ],
            'inventory' => []
        ];
    }
    $grouped_data[$stay_id]['inventory'][] = [
        'inv_name' => $row['inv_name'],
        'quantity_used' => $row['quantity_used']
    ];

    // Prepare chart data
    if (isset($chart_data[$row['inv_name']])) {
        $chart_data[$row['inv_name']] += $row['quantity_used'];
    } else {
        $chart_data[$row['inv_name']] = $row['quantity_used'];
    }
}

// Prepare chart data for JavaScript
$chart_labels = json_encode(array_keys($chart_data));
$chart_values = json_encode(array_values($chart_data));
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Group 3 Hotel Management System- Guest Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Arial', sans-serif;
        }

        .form-container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .form-header {
            text-align: center;
            margin-bottom: 20px;
            color: #05106F;
        }

        .btn-submit {
            background-color: #05106F;
            color: white;
        }

        .btn-submit:hover {
            background-color: #0d2760;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 100px;
            margin-top: 30px;
        }

        .button-container .btn {
            flex: 0 0 150px;
            /* Fixed width of 150px */
            height: 150px;
            /* Fixed height of 150px */
            width: 200px;
            background-color: #05106F;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 5px;
            font-size: 16px;
            text-transform: uppercase;
        }

        .button-container .btn:hover {
            background-color: #0d2760;
            color: white;
        }
    </style>
</head>

<body>
    <!-- sidde bar -->
    <div class="sidebar">
        <h4 class="text-center text-white"><a href="../inventory_management.php">Inventory Management</a></h4>
        <ul class="list-unstyled">
            <li>
                <a href="../inventoryDash.php">Inventory</a>
                <ul class="submenu list-unstyled">
                    <?php if ($role_id == 5): ?>
                        <li><a href="../inventory/inventory_add.php">Inventory Add</a></li>
                    <?php endif; ?>
                    <li><a href="../inventory/inventory_view.php">Inventory View</a></li>
                    <?php if ($role_id == 5): ?>
                        <li><a href="report_inventory.php">Inventory Report</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php if ($role_id == 5): ?>
                <li>
                    <a href="../orderDash.php">Order</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="../order/order_add.php">Order Add</a></li>
                        <li><a href="../inventory/inventory_select.php">Order To Inventory</a></li>
                        <li><a href="../order/order_view.php">Order View</a></li>
                        <li><a href="../order_inventory/o_i_view.php">Order Contribution Report</a></li>
                    </ul>
                </li>
            <?php endif; ?>
            <li>
                <a href="../assignDash.php">Room Inventory</a>
                <ul class="submenu list-unstyled">
                    <?php if ($role_id == 5): ?>
                        <li><a href="../assign_inventory/assign_inventory.php">Assign new</a></li>
                    <?php endif; ?>
                    <li><a href="../assign_inventory/assign_view.php">Assign View</a></li>
                </ul>
            </li>
        </ul>
        <ul class="list-unstyled">
            <li>
                <a href="../../staffModule/staff_logout.php">Logout</a>
            </li>
        </ul>
    </div>

    <div class="container mt-5">
        <h2 class="text-primary text-center mb-4">Inventory Usage Report</h2>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Filter Inventory Usage</div>
            <div class="card-body">
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="selected_date" class="form-label">Select Date:</label>
                            <input type="date" name="selected_date" id="selected_date" class="form-control" value="<?php echo htmlspecialchars($selected_date); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="filter_date" class="form-label">Filter by:</label>
                            <select name="filter_date" id="filter_date" class="form-select" required>
                                <option value="day" <?php echo (isset($_POST['filter_date']) && $_POST['filter_date'] === 'day') ? 'selected' : ''; ?>>Specific Day</option>
                                <option value="week" <?php echo (isset($_POST['filter_date']) && $_POST['filter_date'] === 'week') ? 'selected' : ''; ?>>Specific Week</option>
                                <option value="month" <?php echo (isset($_POST['filter_date']) && $_POST['filter_date'] === 'month') ? 'selected' : ''; ?>>Specific Month</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-center mt-3">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped">
                <thead class="table-primary">
                    <tr>
                        <th>Report Date</th>
                        <th>Room Number</th>
                        <th>Room Type</th>
                        <th>Inventory Used</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($grouped_data)) : ?>
                        <?php foreach ($grouped_data as $stay_id => $data) : ?>
                            <tr>
                                <td rowspan="<?php echo count($data['inventory']); ?>">
                                    <?php echo htmlspecialchars(date('Y-m-d', strtotime($data['stay_details']['check_in_date']))); ?>
                                </td>
                                <td rowspan="<?php echo count($data['inventory']); ?>">
                                    <?php echo htmlspecialchars($data['stay_details']['room_num']); ?>
                                </td>
                                <td rowspan="<?php echo count($data['inventory']); ?>">
                                    <?php echo htmlspecialchars($data['stay_details']['room_type']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($data['inventory'][0]['inv_name'] . " - " . $data['inventory'][0]['quantity_used']); ?></td>
                            </tr>
                            <?php for ($i = 1; $i < count($data['inventory']); $i++) : ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($data['inventory'][$i]['inv_name'] . " - " . $data['inventory'][$i]['quantity_used']); ?></td>
                                </tr>
                            <?php endfor; ?>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="4" class="text-center">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Bar Chart Section -->
        <h3 class="text-primary text-center mb-3">Inventory Usage Distribution</h3>
        <div class="card mx-auto" style="width: 1000px;"> <!-- Centered and smaller width -->
            <div class="card-body">
                <canvas id="inventoryBarChart" style="width: 400%; height: 300px;"></canvas> <!-- Small height -->
            </div>
        </div>

        <!-- Scripts for Chart -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const barCtx = document.getElementById('inventoryBarChart').getContext('2d');
            const inventoryBarChart = new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo $chart_labels; ?>,
                    datasets: [{
                        label: 'Quantity Used',
                        data: <?php echo $chart_values; ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.6)',
                            'rgba(54, 162, 235, 0.6)',
                            'rgba(255, 206, 86, 0.6)',
                            'rgba(75, 192, 192, 0.6)',
                            'rgba(153, 102, 255, 0.6)',
                            'rgba(255, 159, 64, 0.6)',
                            'rgba(201, 203, 207, 0.6)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)',
                            'rgba(201, 203, 207, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false // Hide the legend
                        },
                        title: {
                            display: true,
                            text: 'Inventory Usage'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Quantity'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Inventory Items'
                            }
                        }
                    }
                }
            });
        </script>

        <br><br><br><br>
        <div class="table-responsive mb-4" align="center">
            <a href="../inventoryDash.php" class="btn btn-secondary">Back</a>
        </div>



</body>

</html>