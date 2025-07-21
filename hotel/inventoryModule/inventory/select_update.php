<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Get order ID and name from the URL parameters
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;
$order_name = isset($_GET['order_name']) ? $_GET['order_name'] : null;

// Redirect back if ID or name is missing
if (!$order_id || !$order_name) {
    header("Location: ../inventory_select.php?error=Missing order ID or name");
    exit();
}

try {
    $order_query = "SELECT * FROM order_management WHERE id = $order_id";
    $order_result = mysqli_query($con, $order_query);

    if (!$order_result) {
        throw new Exception("Failed to fetch order details: " . mysqli_error($con));
    }

    $order = mysqli_fetch_assoc($order_result);

    if (!$order) {
        throw new Exception("Order not found.");
    }

    $inventory_query = "SELECT * FROM inventory_management WHERE inv_name = '$order_name'";
    $inventory_result = mysqli_query($con, $inventory_query);

    if (!$inventory_result) {
        throw new Exception("Failed to fetch inventory details: " . mysqli_error($con));
    }

    $inventory = mysqli_fetch_assoc($inventory_result);

    if (!$inventory) {
        throw new Exception("Inventory not found.");
    }
} catch (Exception $e) {
    die("<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>");
}


// Fetch inventory details
$inventory_query = "SELECT * FROM inventory_management WHERE inv_name = '$order_name'";
$inventory_result = mysqli_query($con, $inventory_query);
$inventory = mysqli_fetch_assoc($inventory_result);

if (!$inventory) {
    die("Inventory not found.");
}

$status = "";


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate input
    $price = isset($_POST['price']) ? (float)$_POST['price'] : null;
    $alert_level = isset($_POST['alert_level']) ? (int)$_POST['alert_level'] : null;

    if ($price <= 0) {
        $status = "<p style='color: red;'>Error: Price must be greater than 0.</p>";
    } elseif ($alert_level < 0) {
        $status = "<p style='color: red;'>Error: Alert level cannot be negative.</p>";
    } else {
        // Update inventory
        try {
            $new_quantity = $inventory['inv_quantity'] + $order['o_quantity'];
            $update_query = "UPDATE inventory_management 
                             SET inv_quantity = $new_quantity, inv_price = $price, alert_level = $alert_level
                             WHERE id = {$inventory['id']}";

            if (!mysqli_query($con, $update_query)) {
                throw new Exception("Failed to update inventory: " . mysqli_error($con));
            }

            $status = "<p style='color: green;'>Inventory updated successfully.</p>";
            header("Location: ../status_added.php?order_id=$order_id&inventory_id={$inventory['id']}&quantity={$order['o_quantity']}");
            exit();
        } catch (Exception $e) {
            $status = "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
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
                <h1>Update Inventory for Order: <?php echo htmlspecialchars($order_name); ?></h1>
            </div>
            <div class="card-body">
                <form name="form" method="post" action="">
                    <div class="mb-3">
                        <h5><strong>Previous Inventory Details:</strong></h5>
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Previous Quantity:</strong> <?php echo htmlspecialchars($inventory['inv_quantity']); ?></li>
                            <li class="list-group-item"><strong>Previous Price:</strong> <?php echo htmlspecialchars($inventory['inv_price']); ?></li>
                            <li class="list-group-item"><strong>Previous Alert Level:</strong> <?php echo htmlspecialchars($inventory['alert_level']); ?></li>
                        </ul>
                    </div>

                    <div class="mb-3">
                        <h5><strong>Order Details:</strong></h5>
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Quantity to Add:</strong> <?php echo htmlspecialchars($order['o_quantity']); ?></li>
                        </ul>
                    </div>

                    <!-- Editable Fields -->
                    <div class="mb-3">
                        <label for="price" class="form-label">New Price:</label>
                        <input type="text" class="form-control" name="price" value="<?php echo htmlspecialchars($inventory['inv_price']); ?>" required />
                    </div>
                    <div class="mb-3">
                        <label for="alert_level" class="form-label">New Alert Level:</label>
                        <input type="number" class="form-control" name="alert_level" value="<?php echo htmlspecialchars($inventory['alert_level']); ?>" required />
                    </div>

                    <div class="text-center">
                        <button type="submit" name="submit" class="btn btn-primary">Update Inventory</button>
                    </div>
                </form>
            </div>

            <?php if (!empty($status)): ?>
                <div class="card-footer text-center">
                    <p class="text-success"><?php echo $status; ?></p>
                </div>
            <?php endif; ?>

            <div class="card-footer text-center">
                <a href="inventory_select.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>

</body>

</html>