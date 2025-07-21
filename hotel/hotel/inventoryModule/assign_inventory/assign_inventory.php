<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session
$status = "";

// Fetch all room types
$room_types_query = "SELECT * FROM room_type";
$room_types_result = mysqli_query($con, $room_types_query);

// Fetch room types and inventory items
try {
    $room_types_query = "SELECT * FROM room_type";
    $room_types_result = mysqli_query($con, $room_types_query);

    if (!$room_types_result) {
        throw new Exception("Failed to fetch room types: " . mysqli_error($con));
    }

    $inventory_query = "SELECT * FROM inventory_management";
    $inventory_result = mysqli_query($con, $inventory_query);

    if (!$inventory_result) {
        throw new Exception("Failed to fetch inventory items: " . mysqli_error($con));
    }
} catch (Exception $e) {
    die("<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and validate input
    $room_type_id = isset($_POST['room_type_id']) ? (int)$_POST['room_type_id'] : null;
    $inventory_id = isset($_POST['inventory_id']) ? (int)$_POST['inventory_id'] : null;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : null;

    if (empty($room_type_id) || empty($inventory_id) || empty($quantity)) {
        $status = "<p style='color: red;'>All fields are required.</p>";
    } elseif ($quantity <= 0) {
        $status = "<p style='color: red;'>Quantity must be greater than 0.</p>";
    } else {
        try {
            // Check if the inventory item is already assigned to the room type
            $check_query = "SELECT * FROM inventory_assign 
                            WHERE room_type_id = $room_type_id 
                              AND inventory_id = $inventory_id";
            $check_result = mysqli_query($con, $check_query);

            if (!$check_result) {
                throw new Exception("Failed to check existing assignment: " . mysqli_error($con));
            }

            if (mysqli_num_rows($check_result) > 0) {
                $status = "<p style='color: red;'>This inventory item is already assigned to the selected room type.</p>";
            } else {
                // Insert into `inventory_assign` table
                $insert_query = "INSERT INTO inventory_assign (room_type_id, inventory_id, quantity)
                                 VALUES ($room_type_id, $inventory_id, $quantity)";
                $insert_result = mysqli_query($con, $insert_query);

                if (!$insert_result) {
                    throw new Exception("Failed to assign inventory: " . mysqli_error($con));
                }

                $status = "<p style='color: green;'>Inventory assigned to room type successfully.</p>";
            }
        } catch (Exception $e) {
            $status = "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Inventory to Room Type</title>
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
                        <li><a href="assign_inventory.php">Assign new</a></li>
                    <?php endif; ?>
                    <li><a href="assign_view.php">Assign View</a></li>
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
                <h2>Assign Inventory to Room Type</h2>
            </div>
            <div class="card-body">
                <!-- Display Status -->
                <?php if (isset($status)): ?>
                    <div class="alert alert-info text-center">
                        <?php echo $status; ?>
                    </div>
                <?php endif; ?>

                <!-- Form for assigning inventory -->
                <form method="post" action="">
                    <!-- Room Type Selection -->
                    <div class="mb-3">
                        <label for="room_type_id" class="form-label">Select Room Type:</label>
                        <select name="room_type_id" id="room_type_id" class="form-select" required>
                            <option value="" disabled selected>-- Select Room Type --</option>
                            <?php while ($room_type = mysqli_fetch_assoc($room_types_result)) { ?>
                                <option value="<?php echo $room_type['id']; ?>">
                                    <?php echo $room_type['type']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Inventory Selection -->
                    <div class="mb-3">
                        <label for="inventory_id" class="form-label">Select Inventory Item:</label>
                        <select name="inventory_id" id="inventory_id" class="form-select" required>
                            <option value="" disabled selected>-- Select Inventory Item --</option>
                            <?php while ($inventory = mysqli_fetch_assoc($inventory_result)) { ?>
                                <option value="<?php echo $inventory['id']; ?>">
                                    <?php echo $inventory['inv_name']; ?> (Available: <?php echo $inventory['inv_quantity']; ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- Quantity Input -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Enter Quantity:</label>
                        <input type="number" name="quantity" id="quantity" class="form-control" min="1" required>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Assign Inventory</button>
                    </div>
                </form>
            </div>

            <!-- Back Links -->
            <div class="card-footer text-center">
                <a href="../assignDash.php" class="btn btn-secondary me-2">Back</a>
            </div>
        </div>
    </div>

</body>

</html>