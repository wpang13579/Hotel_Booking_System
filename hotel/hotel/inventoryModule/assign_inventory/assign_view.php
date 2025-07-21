<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

// Fetch assigned inventory grouped by room type
$query = "
    SELECT 
        inventory_assign.id AS assign_id,
        room_type.type AS room_type,
        inventory_management.inv_name AS inventory_name,
        inventory_assign.quantity AS assigned_quantity
    FROM 
        inventory_assign
    JOIN 
        room_type ON inventory_assign.room_type_id = room_type.id
    JOIN 
        inventory_management ON inventory_assign.inventory_id = inventory_management.id
    ORDER BY room_type.type ASC
";
$result = mysqli_query($con, $query);

// Group data by room type
$grouped_data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $room_type = $row['room_type'];
    if (!isset($grouped_data[$room_type])) {
        $grouped_data[$room_type] = [];
    }
    $grouped_data[$room_type][] = [
        'assign_id' => $row['assign_id'],
        'inventory_name' => $row['inventory_name'],
        'assigned_quantity' => $row['assigned_quantity']
    ];
}

// Session
$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assigned Inventory</title>
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
                <h2>Assigned Inventory</h2>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Room Type</th>
                            <th>Inventory Item</th>
                            <th>Assigned Quantity</th>
                            <?php if ($role_id == 5): ?>
                                <th>Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($grouped_data)): ?>
                            <?php foreach ($grouped_data as $room_type => $items): ?>
                                <tr>
                                    <td align="center" rowspan="<?php echo count($items); ?>" class="align-middle">
                                        <?php echo htmlspecialchars($room_type); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($items[0]['inventory_name']); ?>
                                    </td>
                                    <td align="center">
                                        <?php echo htmlspecialchars($items[0]['assigned_quantity']); ?>
                                    </td>
                                    <?php if ($role_id == 5): ?>
                                        <td align="center">
                                            <a href="assign_update.php?id=<?php echo urlencode($items[0]['assign_id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="assign_delete.php?id=<?php echo urlencode($items[0]['assign_id']); ?>"
                                                onclick="return confirm('Are you sure you want to delete this assignment?');"
                                                class="btn btn-sm btn-danger">Delete</a>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                                <?php for ($i = 1; $i < count($items); $i++): ?>
                                    <tr>
                                        <td>
                                            <?php echo htmlspecialchars($items[$i]['inventory_name']); ?>
                                        </td>
                                        <td align="center">
                                            <?php echo htmlspecialchars($items[$i]['assigned_quantity']); ?>
                                        </td>
                                        <?php if ($role_id == 5): ?>
                                            <td align="center">
                                                <a href="assign_update.php?id=<?php echo urlencode($items[$i]['assign_id']); ?>" class="btn btn-sm btn-warning">Edit</a>
                                                <a href="assign_delete.php?id=<?php echo urlencode($items[$i]['assign_id']); ?>"
                                                    onclick="return confirm('Are you sure you want to delete this assignment?');"
                                                    class="btn btn-sm btn-danger">Delete</a>
                                            </td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endfor; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center">No inventory assignments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-center">
                <a href="../assignDash.php" class="btn btn-secondary me-2">Back</a>
            </div>
        </div>
    </div>
</body>

</html>