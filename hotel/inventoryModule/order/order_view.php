<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>View Order Records</title>
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
                        <li><a href="../report_view/report_inventory.php">Inventory Report</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php if ($role_id == 5): ?>
                <li>
                    <a href="../orderDash.php">Order</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="order_add.php">Order Add</a></li>
                        <li><a href="../inventory/inventory_select.php">Order To Inventory</a></li>
                        <li><a href="order_view.php">Order View</a></li>
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
        <!-- Pending Orders -->
        <h2 class="text-primary mb-4">Pending Order Records</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-primary text-center">
                    <tr>
                        <th>No.</th>
                        <th>Order ID</th>
                        <th>Order's Inventory Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Supplier Name</th>
                        <th>Supplier Contact Number</th>
                        <?php if ($role_id == 5): ?>
                            <th>Edit</th>
                            <th>Cancel Order</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    $sel_query = "SELECT * FROM order_management WHERE o_status = 1 ORDER BY id ASC;";
                    $result = mysqli_query($con, $sel_query);
                    while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <tr class="text-center">
                            <td><?php echo $count; ?></td>
                            <td><?php echo "O_" . $row["id"]; ?></td>
                            <td><?php echo $row["o_name"]; ?></td>
                            <td><?php echo $row["o_quantity"]; ?></td>
                            <td><?php echo $row["o_status"]; ?></td>
                            <td><?php echo $row["o_date"]; ?></td>
                            <td><?php echo $row["supplier_name"]; ?></td>
                            <td><?php echo $row["supplier_contact"]; ?></td>
                            <?php if ($role_id == 5): ?>
                                <td>
                                    <a href="order_update.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                </td>
                                <td>
                                    <a href="order_cancel.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to cancel this order record?')">Cancel</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php $count++;
                    } ?>
                </tbody>
            </table>
        </div>

        <!-- Completed Orders -->
        <h2 class="text-primary mb-4">Completed Order Records</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-success text-center">
                    <tr>
                        <th>No.</th>
                        <th>Order ID</th>
                        <th>Order's Inventory Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Supplier Name</th>
                        <th>Supplier Contact Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    $sel_query_completed = "SELECT * FROM order_management WHERE o_status = 2 ORDER BY id ASC;";
                    $result_completed = mysqli_query($con, $sel_query_completed);
                    while ($row = mysqli_fetch_assoc($result_completed)) {
                    ?>
                        <tr class="text-center">
                            <td><?php echo $count; ?></td>
                            <td><?php echo "O_" . $row["id"]; ?></td>
                            <td><?php echo $row["o_name"]; ?></td>
                            <td><?php echo $row["o_quantity"]; ?></td>
                            <td><?php echo $row["o_status"]; ?></td>
                            <td><?php echo $row["o_date"]; ?></td>
                            <td><?php echo $row["supplier_name"]; ?></td>
                            <td><?php echo $row["supplier_contact"]; ?></td>
                        </tr>
                    <?php $count++;
                    } ?>
                </tbody>
            </table>
        </div>

        <!-- Cancelled Orders -->
        <h2 class="text-primary mb-4">Cancelled Order Records</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-danger text-center">
                    <tr>
                        <th>No.</th>
                        <th>Order ID</th>
                        <th>Order's Inventory Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Supplier Name</th>
                        <th>Supplier Contact Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    $sel_query_cancelled = "SELECT * FROM order_management WHERE o_status = 3 ORDER BY id ASC;";
                    $result_cancelled = mysqli_query($con, $sel_query_cancelled);
                    while ($row = mysqli_fetch_assoc($result_cancelled)) {
                    ?>
                        <tr class="text-center">
                            <td><?php echo $count; ?></td>
                            <td><?php echo "O_" . $row["id"]; ?></td>
                            <td><?php echo $row["o_name"]; ?></td>
                            <td><?php echo $row["o_quantity"]; ?></td>
                            <td><?php echo $row["o_status"]; ?></td>
                            <td><?php echo $row["o_date"]; ?></td>
                            <td><?php echo $row["supplier_name"]; ?></td>
                            <td><?php echo $row["supplier_contact"]; ?></td>
                        </tr>
                    <?php $count++;
                    } ?>
                </tbody>
            </table>
        </div>

        <!-- Added Orders -->
        <h2 class="text-primary mb-4">Added Order Records</h2>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-info text-center">
                    <tr>
                        <th>No.</th>
                        <th>Order ID</th>
                        <th>Order's Inventory Name</th>
                        <th>Quantity</th>
                        <th>Status</th>
                        <th>Order Date</th>
                        <th>Supplier Name</th>
                        <th>Supplier Contact Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $count = 1;
                    $sel_query_added = "SELECT * FROM order_management WHERE o_status = 4 ORDER BY id ASC;";
                    $result_added = mysqli_query($con, $sel_query_added);
                    while ($row = mysqli_fetch_assoc($result_added)) {
                    ?>
                        <tr class="text-center">
                            <td><?php echo $count; ?></td>
                            <td><?php echo "O_" . $row["id"]; ?></td>
                            <td><?php echo $row["o_name"]; ?></td>
                            <td><?php echo $row["o_quantity"]; ?></td>
                            <td><?php echo $row["o_status"]; ?></td>
                            <td><?php echo $row["o_date"]; ?></td>
                            <td><?php echo $row["supplier_name"]; ?></td>
                            <td><?php echo $row["supplier_contact"]; ?></td>
                        </tr>
                    <?php $count++;
                    } ?>
                </tbody>
            </table>
        </div>

        <!-- Back Button -->
        <div class="text-center mt-4">
            <a href="../orderDash.php" class="btn btn-secondary">Back</a>
        </div>
    </div>



</body>

</html>