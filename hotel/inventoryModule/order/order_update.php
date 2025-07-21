<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);
$status = "";

// set condition to check before submittion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oName = trim($_POST['oName']);
    $oQuantity = (int)$_POST['oQuantity'];
    $oStatus = $_POST['oStatus'];
    $oDate = trim($_POST['oDate']);
    $sName = trim($_POST['sName']);
    $sContact = trim($_POST['sContact']);
    $order_id = (int)$_POST['id'];

    // Retrieve current order details
    $query = "SELECT * FROM order_management WHERE id = '$order_id'";
    $result = mysqli_query($con, $query);
    $current_row = mysqli_fetch_assoc($result);

    // compare current order details with updated details
    if (
        trim($oName) !== trim($current_row['o_name']) ||
        (int)$oQuantity !== (int)$current_row['o_quantity'] ||
        $oStatus !== $current_row['o_status'] ||
        trim($oDate) !== trim($current_row['o_date']) ||
        trim($sName) !== trim($current_row['supplier_name']) ||
        trim($sContact) !== trim($current_row['supplier_contact'])
    ) {

        $update_query = "   UPDATE order_management SET
                            o_name='$oName',
                            o_quantity='$oQuantity',
                            o_status='$oStatus',
                            o_date='$oDate',
                            supplier_name='$sName',
                            supplier_contact='$sContact'
                            WHERE id='$order_id'
                        ";

        if (mysqli_query($con, $update_query)) {
            $status = "<p style='color: green;'>Order updated successfully.</p>";
        } else {
            $status = "<p style='color: red;'>Order failed to be updated.</p>";
        }
    } else {
        $status = "<p style='color: red;'>No changes detected. Update not performed.</p>";
    }
}

// receive id from request site
$id = $_REQUEST['id'];
$query = "SELECT * FROM order_management WHERE id='$id'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Update Order</title>
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
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h1>Update Order</h1>
            </div>
            <div class="card-body">
                <form name="form" method="post" action="">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">

                    <!-- Order Details Section -->
                    <h2 class="text-primary">Order Details</h2>
                    <div class="mb-3">
                        <label for="oName" class="form-label">Ordered Inventory Name:</label>
                        <input type="text" class="form-control" name="oName" placeholder="Update Order Name" required value="<?php echo $row['o_name']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="oQuantity" class="form-label">Quantity Ordered:</label>
                        <input type="text" class="form-control" name="oQuantity" placeholder="Update Order Quantity" required value="<?php echo $row['o_quantity']; ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status:</label>
                        <div>
                            <input type="radio" name="oStatus" value="pending" <?php echo ($row['o_status'] == "pending") ? 'checked' : ''; ?>> Pending
                            <input type="radio" name="oStatus" value="confirmed" <?php echo ($row['o_status'] == "confirmed") ? 'checked' : ''; ?>> Confirmed
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="oDate" class="form-label">Date Ordered:</label>
                        <input type="date" class="form-control" name="oDate" placeholder="Order Date" required value="<?php echo $row['o_date']; ?>">
                    </div>

                    <!-- Supplier Details Section -->
                    <h2 class="text-primary">Supplier Details</h2>
                    <div class="mb-3">
                        <label for="sName" class="form-label">Supplier Name:</label>
                        <input type="text" class="form-control" name="sName" placeholder="Supplier name" required value="<?php echo $row['supplier_name']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="sContact" class="form-label">Supplier Contact:</label>
                        <input type="text" class="form-control" name="sContact" placeholder="Supplier contact number" required value="<?php echo $row['supplier_contact']; ?>">
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" name="submit" class="btn btn-success">Update Order</button>
                    </div>
                </form>

                <!-- Status Message -->
                <?php if (!empty($status)): ?>
                    <div class="alert alert-success mt-3">
                        <?php echo $status; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Footer Links -->
            <div class="card-footer text-center">
                <a href="order_view.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>

</body>

</html>