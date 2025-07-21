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

$status = "";

// Fetch order details to pre-fill the form
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
} catch (Exception $e) {
    header("Location: ../inventory_select.php?error=" . urlencode($e->getMessage()));
    exit();
}

// Handle form submission
if (
    $_SERVER["REQUEST_METHOD"] == "POST"
) {
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $category = isset($_POST['category']) ? mysqli_real_escape_string($con, $_POST['category']) : '';
    $alert_level = isset($_POST['alert_level']) ? (int)$_POST['alert_level'] : 0;

    if ($price <= 0) {
        $status = "<p style='color: red;'>Error: Price must be greater than 0.</p>";
    } elseif ($quantity <= 0) {
        $status = "<p style='color: red;'>Error: Quantity must be greater than 0.</p>";
    } elseif (empty($category)) {
        $status = "<p style='color: red;'>Error: Category is required.</p>";
    } elseif ($alert_level < 0) {
        $status = "<p style='color: red;'>Error: Alert level cannot be negative.</p>";
    } else {
        // Insert new inventory item
        try {
            $query = "INSERT INTO inventory_management (inv_name, inv_price, inv_quantity, inv_category, alert_level) 
                      VALUES ('$order_name', $price, $quantity, '$category', $alert_level)";
            $result = mysqli_query($con, $query);

            if (!$result) {
                throw new Exception("Failed to add inventory: " . mysqli_error($con));
            }

            $inventory_id = mysqli_insert_id($con); // Get the new inventory ID

            // Redirect to `status_added.php` with order and inventory details
            header("Location: ../status_added.php?order_id=$order_id&inventory_id=$inventory_id&quantity=$quantity");
            exit();
        } catch (Exception $e) {
            $status = "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Add New Inventory</title>
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
                <h1>Add New Inventory for Order: <?php echo htmlspecialchars($order_name); ?></h1>
            </div>
            <div class="card-body">
                <form name="form" method="post" action="">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="text" class="form-control" name="quantity" placeholder="Order quantity"
                            value="<?php echo isset($order['o_quantity']) ? htmlspecialchars($order['o_quantity']) : ''; ?>" readonly />
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price:</label>
                        <input type="text" class="form-control" name="price" placeholder="Enter price" required />
                    </div>
                    <div class="mb-3">
                        <label for="alert_level" class="form-label">Alert Level:</label>
                        <input type="number" class="form-control" name="alert_level" placeholder="Enter alert level" required />
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category:</label><br />
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="category" value="linens" />
                            <label class="form-check-label">Linens</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="category" value="toiletries" />
                            <label class="form-check-label">Toiletries</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input type="radio" class="form-check-input" name="category" value="complimentary items" />
                            <label class="form-check-label">Complimentary items</label>
                        </div>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary">Add Inventory</button>
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