<?php
require('../database.php');
require('../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

// Fetch low inventory items
$low_inventory_query = "SELECT inv_name, inv_quantity, alert_level 
                        FROM inventory_management 
                        WHERE inv_quantity <= alert_level";
$low_inventory_result = mysqli_query($con, $low_inventory_query);

// Initialize an array to store low inventory names
$low_inventory_items = [];
while ($row = mysqli_fetch_assoc($low_inventory_result)) {
    $low_inventory_items[] = $row['inv_name'] . " (Quantity: " . $row['inv_quantity'] . ")";
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Group 3 Hotel Management System- Guest Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
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
        <h4 class="text-center text-white"><a href="inventory_management.php">Inventory Management</a></h4>
        <ul class="list-unstyled">
            <li>
                <a href="inventoryDash.php">Inventory</a>
                <ul class="submenu list-unstyled">
                    <?php if ($role_id == 5): ?>
                        <li><a href="inventory/inventory_add.php">Inventory Add</a></li>
                    <?php endif; ?>
                    <li><a href="inventory/inventory_view.php">Inventory View</a></li>
                    <?php if ($role_id == 5): ?>
                        <li><a href="report_view/report_inventory.php">Inventory Report</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php if ($role_id == 5): ?>
                <li>
                    <a href="orderDash.php">Order</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="order/order_add.php">Order Add</a></li>
                        <li><a href="inventory/inventory_select.php">Order To Inventory</a></li>
                        <li><a href="order/order_view.php">Order View</a></li>
                        <li><a href="order_inventory/o_i_view.php">Order Contribution Report</a></li>
                    </ul>
                </li>
            <?php endif; ?>
            <li>
                <a href="assignDash.php">Room Inventory</a>
                <ul class="submenu list-unstyled">
                    <?php if ($role_id == 5): ?>
                        <li><a href="assign_inventory/assign_inventory.php">Assign new</a></li>
                    <?php endif; ?>
                    <li><a href="assign_inventory/assign_view.php">Assign View</a></li>
                </ul>
            </li>
        </ul>
        <ul class="list-unstyled">
            <li>
                <a href="../staffModule/staffShift/staffshift_view.php">View Shift</a>
            </li>
        </ul>
        <?php if ($role_id != 5): ?>
        <ul class="list-unstyled">
            <li>
                <a href=../staffModule/staffDashboard/nstaff_dashboard.php>Back To Staff Dashboard</a>
            </li>
        </ul>
        <?php endif; ?>
        <ul class="list-unstyled">
            <li>
                <a href="../staffModule/staff_logout.php">Logout</a>
            </li>
        </ul>
    </div>

    <br><br><br><br><br><br>
    <div class="container">
        <h2 class="form-header">Inventory Management</h2>
    </div>

    <div class="container">
        <!-- Alert for Low Inventory -->
        <?php if (!empty($low_inventory_items)): ?>
            <div class="alert alert-warning text-center mt-4">
                <strong>Low Inventory Alert:</strong>
                <ul>
                    <?php foreach ($low_inventory_items as $item): ?>
                        <li><?php echo htmlspecialchars($item); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>

    <br><br><br><br>
    <div class="container">
        <div class="form-container">
            <div class="button-container">
                <button class="btn" onclick="window.location.href='inventoryDash.php'">Inventory</button>
                <button class="btn" onclick="window.location.href='orderDash.php'">Order</button>
                <button class="btn" onclick="window.location.href='assignDash.php'">Assign inventory</button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>