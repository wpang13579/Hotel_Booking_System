<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

$inventory_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$inventory_id) {
    die("<p style='color: red;'>Error: Missing inventory ID.</p>");
}

// Fetch inventory details to pre-fill the form
try {
    $inventory_query = "SELECT * FROM inventory_management WHERE id = $inventory_id";
    $inventory_result = mysqli_query($con, $inventory_query);

    if (!$inventory_result) {
        throw new Exception("Database query failed: " . mysqli_error($con));
    }

    $inventory = mysqli_fetch_assoc($inventory_result);

    if (!$inventory) {
        throw new Exception("Inventory item not found.");
    }
} catch (Exception $e) {
    die("<p style='color: red;'>Error: " . $e->getMessage() . "</p>");
}


$status = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $new_name = mysqli_real_escape_string($con, $_POST['name'] ?? '');
    $new_price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $new_quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $new_alert_level = isset($_POST['alert_level']) ? (int)$_POST['alert_level'] : 0;

    // error handdling
    if (empty($new_name)) {
        $status = "<p style='color: red;'>Error: Inventory name is required.</p>";
    } elseif ($new_price <= 0) {
        $status = "<p style='color: red;'>Error: Price must be greater than 0.</p>";
    } elseif ($new_quantity < 0) {
        $status = "<p style='color: red;'>Error: Quantity cannot be negative.</p>";
    } elseif ($new_alert_level < 0) {
        $status = "<p style='color: red;'>Error: Alert level cannot be negative.</p>";
    } else {
        // Update the inventory details
        try {
            $update_query = "UPDATE inventory_management 
                             SET inv_name = '$new_name', inv_price = $new_price, 
                                 inv_quantity = $new_quantity, alert_level = $new_alert_level
                             WHERE id = $inventory_id";

            if (!mysqli_query($con, $update_query)) {
                throw new Exception("Update query failed: " . mysqli_error($con));
            }

            $status = "<p style='color: green;'>Inventory updated successfully.</p>";
        } catch (Exception $e) {
            $status = "<p style='color: red;'>Error updating inventory: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Update Inventory</title>
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
                <h1>Update Inventory: <?php echo htmlspecialchars($inventory['inv_name']); ?></h1>
            </div>
            <div class="card-body">
                <form name="form" method="post" action="">
                    <!-- Inventory Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">Inventory Name:</label>
                        <input type="text" name="name" id="name" class="form-control"
                            value="<?php echo htmlspecialchars($inventory['inv_name']); ?>" required />
                    </div>

                    <!-- Price -->
                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="text" name="price" id="price" class="form-control"
                            value="<?php echo htmlspecialchars($inventory['inv_price']); ?>" required />
                    </div>

                    <!-- Quantity -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" class="form-control"
                            value="<?php echo htmlspecialchars($inventory['inv_quantity']); ?>" required />
                    </div>

                    <!-- Alert Level -->
                    <div class="mb-3">
                        <label for="alert_level" class="form-label">Alert Level:</label>
                        <input type="number" name="alert_level" id="alert_level" class="form-control"
                            value="<?php echo htmlspecialchars($inventory['alert_level']); ?>" required />
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <input name="submit" type="submit" value="Update Inventory" class="btn btn-success">
                    </div>
                </form>
            </div>

            <!-- Status Message -->
            <?php if (isset($status) && !empty($status)): ?>
                <div class="card-footer text-center">
                    <p class="text-info"><?php echo $status; ?></p>
                </div>
            <?php endif; ?>

            <!-- Navigation Links -->
            <div class="card-footer text-center">
                <a href="inventory_view.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>

</body>

</html>