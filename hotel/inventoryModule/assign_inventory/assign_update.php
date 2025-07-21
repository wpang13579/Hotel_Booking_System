<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session
$status = "";

// Fetch the assignment details if `id` is provided
try {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $assign_id = (int)$_GET['id'];

        $fetch_query = "SELECT * FROM inventory_assign WHERE id = $assign_id";
        $assign_result = mysqli_query($con, $fetch_query);

        if (!$assign_result) {
            throw new Exception("Failed to fetch assignment details: " . mysqli_error($con));
        }

        if (
            mysqli_num_rows($assign_result) > 0
        ) {
            $assignment = mysqli_fetch_assoc($assign_result);
        } else {
            throw new Exception("Assignment not found.");
        }
    } else {
        throw new Exception("Invalid request: Assignment ID is missing or invalid.");
    }
} catch (Exception $e) {
    die("<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// Handle form submission
if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {
    try {
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

        if ($quantity <= 0) {
            throw new Exception("Quantity must be greater than 0.");
        }

        $update_query = "
            UPDATE inventory_assign 
            SET quantity = $quantity 
            WHERE id = $assign_id
        ";

        if (!mysqli_query($con, $update_query)) {
            throw new Exception("Failed to update quantity: " . mysqli_error($con));
        }

        $status = "<p style='color: green;'>Quantity updated successfully.</p>";
        header("Location: assign_view.php");
        exit();
    } catch (Exception $e) {
        $status = "<p style='color: red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Inventory Quantity</title>
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
                <h2>Update Inventory Quantity</h2>
            </div>
            <div class="card-body">
                <!-- Display Status -->
                <?php if (isset($status)): ?>
                    <div class="alert alert-info text-center">
                        <?php echo $status; ?>
                    </div>
                <?php endif; ?>

                <form method="post" action="">
                    <!-- Quantity Input -->
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Edit Quantity for Inventory Item:</label>
                        <input type="number" name="quantity" id="quantity" min="1"
                            class="form-control"
                            value="<?php echo htmlspecialchars($assignment['quantity']); ?>"
                            required>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-center">
                        <button type="submit" class="btn btn-success">Update Quantity</button>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center">
                <a href="assign_view.php" class="btn btn-secondary me-2">Back</a>
            </div>
        </div>
    </div>

</body>

</html>