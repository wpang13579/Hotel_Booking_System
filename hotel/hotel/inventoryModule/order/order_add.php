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

// php superglobal
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $oName = $_POST['oName'];
    $oQuantity = $_POST['quantity'];
    $oStatus = 1;
    $oDate = $_POST['oDate'];
    $sName = $_POST['sName'];
    $sContact = $_POST['sContact'];

    $query =    "   INSERT into `order_management` (o_name, o_quantity, o_status, o_date, supplier_name, supplier_contact) 
                    VALUES ('$oName', '$oQuantity', '$oStatus','$oDate', '$sName','$sContact')
                ";

    $result = mysqli_query($con, $query);

    if ($result) {
        $status = "New order add successfully";
    } else {
        $status = "Order add failed";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Insert New Inventory</title>
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
    <div>
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
                    <h1>Create Order</h1>
                </div>
                <div class="card-body">
                    <form action="" method="post">
                        <!-- Order Details Section -->
                        <h2 class="text-primary mb-4">Order Details</h2>
                        <div class="mb-3">
                            <label for="oName" class="form-label">Ordered Inventory Name:</label>
                            <input type="text" name="oName" class="form-control" placeholder="Order name" required />
                        </div>
                        <div class="mb-3">
                            <label for="oQuantity" class="form-label">Quantity Ordered:</label>
                            <input type="number" name="quantity" class="form-control" placeholder="Order quantity" required />
                        </div>
                        <div class="mb-3">
                            <label for="oDate" class="form-label">Date Ordered:</label>
                            <input type="date" name="oDate" class="form-control" placeholder="Order Date" required />
                        </div>

                        <!-- Supplier Details Section -->
                        <h2 class="text-primary mb-4">Supplier Details</h2>
                        <div class="mb-3">
                            <label for="sName" class="form-label">Supplier Name:</label>
                            <input type="text" name="sName" class="form-control" placeholder="Supplier name" required />
                        </div>
                        <div class="mb-3">
                            <label for="sContact" class="form-label">Supplier Contact:</label>
                            <input type="text" name="sContact" class="form-control" placeholder="Supplier contact number" required />
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" name="submit" class="btn btn-primary">Create Order</button>
                        </div>
                    </form>
                </div>

                <div>
                    <p><?php echo $status; ?></p>
                </div>

                <div class="card-footer text-center">
                    <a href="../orderDash.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>

    </div>
</body>

</html>