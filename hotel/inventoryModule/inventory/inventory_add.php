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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $inv_name = mysqli_real_escape_string($con, $_POST['inv_name']);
    $inv_price = (float)$_POST['inv_price'];
    $inv_quantity = (int)$_POST['inv_quantity'];
    $inv_category = mysqli_real_escape_string($con, $_POST['inv_category']);
    $alert_level = (int)$_POST['alert_level'];

    // Validate required fields
    if (empty($inv_name) || empty($inv_price) || empty($inv_quantity) || empty($inv_category) || empty($alert_level)) {
        $status = "All fields are required.";
    } elseif ($inv_price <= 0) {
        $status = "Price must be greater than 0.";
    } elseif ($inv_quantity < 0) {
        $status = "Quantity cannot be negative.";
    } elseif ($alert_level < 0) {
        $status = "Alert level cannot be negative.";
    } else {
        try {
            // Insert new inventory record into the database
            $query = "INSERT INTO inventory_management (inv_name, inv_price, inv_quantity, inv_category, alert_level) 
                      VALUES ('$inv_name', '$inv_price', '$inv_quantity', '$inv_category', '$alert_level')";

            if (!mysqli_query($con, $query)) {
                throw new Exception("Database insertion error: " . mysqli_error($con));
            }
            $status = "Inventory item added successfully.";
        } catch (Exception $e) {
            $status = "Error adding inventory: " . $e->getMessage();
        }
    }
}
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
                        <li><a href="inventory_add.php">Inventory Add</a></li>
                    <?php endif; ?>
                    <li><a href="inventory_view.php">Inventory View</a></li>
                    <?php if ($role_id == 5): ?>
                        <li><a href="../report_view/report_inventory.php">Inventory Report</a></li>
                    <?php endif; ?>
                </ul>
            </li>
            <?php if ($role_id == 5): ?>
                <li>
                    <a href="../orderDash.php">Order</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="../order/order_add.php">Order Add</a></li>
                        <li><a href="inventory_select.php">Order To Inventory</a></li>
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
        <div class="card shadow">
            <div class="card-header bg-primary text-white text-center">
                <h2>Add Inventory</h2>
            </div>
            <div class="card-body">
                <form name="form" method="post" action="">
                    <!-- Inventory Name -->
                    <div class="mb-3">
                        <label for="inv_name" class="form-label">Inventory Name:</label>
                        <input type="text" class="form-control" id="inv_name" name="inv_name" required>
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                        <label for="inv_price" class="form-label">Price:</label>
                        <input type="number" step="0.01" class="form-control" id="inv_price" name="inv_price" required>
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label for="inv_quantity" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" id="inv_quantity" name="inv_quantity" required>
                    </div>

                    <!-- Category -->
                    <div class="mb-3">
                        <label for="category" class="form-label">Category:</label>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="linens" name="inv_category" value="linens">
                            <label class="form-check-label" for="linens">Linens</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="toiletries" name="inv_category" value="toiletries">
                            <label class="form-check-label" for="toiletries">Toiletries</label>
                        </div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="complimentary" name="inv_category" value="complimentary items">
                            <label class="form-check-label" for="complimentary">Complimentary Items</label>
                        </div>
                    </div>


                    <!-- Alert Level -->
                    <div class="mb-3">
                        <label for="alert_level" class="form-label">Alert Level:</label>
                        <input type="number" class="form-control" id="alert_level" name="alert_level" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <input name="submit" type="submit" value="Add Inventory" class="btn btn-success">
                    </div>
                </form>

                <!-- Status Message -->
                <?php if (!empty($status)): ?>
                    <div class="alert alert-info mt-3 text-center">
                        <?php echo $status; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Back Links -->
            <div class="card-footer text-center">
                <a href="../inventoryDash.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>

</body>

</html>