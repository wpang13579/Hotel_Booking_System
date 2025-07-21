<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

// Get all categories for the dropdown
$category_query = "SELECT DISTINCT inv_category FROM inventory_management";
$category_result = mysqli_query($con, $category_query) or die(mysqli_error($con));

$categories = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row['inv_category'];
}

// Handle form submission for filtering by category
if (isset($_POST['filter_by_category'])) {
    $selected_category = $_POST['category'];
    if ($selected_category == "") {
        // If "All Categories" is selected, fetch all records
        $sel_query = "SELECT * FROM inventory_management ORDER BY id ASC;";
    } else {
        // If a specific category is selected, filter accordingly
        $sel_query = "SELECT * FROM inventory_management WHERE inv_category = '$selected_category' ORDER BY id ASC;";
    }
} else {
    // Default query to show all inventory
    $sel_query = "SELECT * FROM inventory_management ORDER BY id ASC;";
}


$result = mysqli_query($con, $sel_query);
$currencySymbol = "RM";

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>View inventory Records</title>
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
                <h2>View - Inventory Records</h2>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="category" class="form-label">Filter by Category:</label>
                        <select class="form-select" name="category" id="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category; ?>" <?php if (isset($selected_category) && $selected_category == $category) echo 'selected'; ?>><?php echo $category; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" name="filter_by_category" class="btn btn-primary">Filter</button>
                </form>

                <table class="table table-bordered table-hover text-center">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Inventory Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Category</th>
                            <th>Alert Level</th>
                            <?php if ($role_id == 5): ?>
                                <th>Edit</th>
                                <th>Delete</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $count = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                        ?>
                            <tr>
                                <td><?php echo $count; ?></td>
                                <td><?php echo htmlspecialchars($row["inv_name"]); ?></td>
                                <td><?php echo htmlspecialchars($currencySymbol . $row["inv_price"]); ?></td>
                                <td><?php echo htmlspecialchars($row["inv_quantity"]); ?></td>
                                <td><?php echo htmlspecialchars($row["inv_category"]); ?></td>
                                <td><?php echo htmlspecialchars($row["alert_level"]); ?></td>
                                <?php if ($role_id == 5): ?>
                                    <td><a href="inventory_update.php?id=<?php echo $row["id"]; ?>" class="btn btn-warning btn-sm">Update</a></td>
                                    <td><a href="inventory_delete.php?id=<?php echo $row["id"]; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this inventory record?')">Delete</a></td>
                                <?php endif; ?>
                            </tr>
                        <?php $count++;
                        } ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="../inventoryDash.php" class="btn btn-secondary">Back</a>
            </div>
        </div>
    </div>


</body>

</html>