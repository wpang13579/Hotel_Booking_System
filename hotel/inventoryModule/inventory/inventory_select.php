<?php
require('../../database.php');
require('../../staffModule/staff_authentication.php');

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id']; // Retrieve role_id from session

// Query to check user credentials
$query = "SELECT * FROM `staff` WHERE staff_email='$staff_email'";
$sresult = mysqli_query($con, $query) or die(mysqli_error($con));
$user = mysqli_fetch_assoc($sresult);

// Retrieve orders from the order_management table for the dropdown
$order_query = "SELECT * FROM order_management WHERE o_status = 'confirmed'";
$order_result = mysqli_query($con, $order_query);
$orders = [];
while ($order_row = mysqli_fetch_assoc($order_result)) {
    $orders[] = $order_row;
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Select An Order for Add or Update Inventory</title>
    <script>
        // Display order details and set order name
        function showOrderDetails() {
            const orders = <?php echo json_encode($orders); ?>; // PHP to JS data transfer
            const selectedOrderId = document.getElementById('order').value;
            const orderDetailsDiv = document.getElementById('orderDetails');
            const orderNameField = document.getElementById('order_name');

            // Clear previous details
            orderDetailsDiv.innerHTML = '';

            // Find the selected order's details
            const selectedOrder = orders.find(order => order.id == selectedOrderId);

            if (selectedOrder) {
                // Populate the details
                orderDetailsDiv.innerHTML = `
                    <p><strong>Order Name:</strong> ${selectedOrder.o_name}</p>
                    <p><strong>Quantity:</strong> ${selectedOrder.o_quantity}</p>
                    <p><strong>Date:</strong> ${selectedOrder.o_date}</p>
                    <p><strong>Status:</strong> Confirmed</p>
                    <p><strong>Supplier:</strong> ${selectedOrder.supplier_name}</p>
                    <p><strong>Contact:</strong> ${selectedOrder.supplier_contact}</p>
                `;

                // Set the order name in the hidden field
                orderNameField.value = selectedOrder.o_name;
            }
        }
    </script>
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
                    <h1>Check and Add Inventory</h1>
                </div>
                <div class="card-body">
                    <form action="inventory_decide.php" method="post">
                        <!-- Order Selection -->
                        <div class="mb-3">
                            <label for="order" class="form-label">Select Order:</label>
                            <select id="order" name="order_id" class="form-select" required onchange="setOrderName()">
                                <option value="" disabled selected>Select an order</option>
                                <?php foreach ($orders as $order_row) { ?>
                                    <option value="<?php echo $order_row['id']; ?>" data-order-name="<?php echo $order_row['o_name']; ?>">
                                        <?php echo "O_" . $order_row['id'] . " - " . $order_row['o_name']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <!-- Hidden field for order name -->
                        <input type="hidden" id="order_name" name="order_name" value="" />

                        <!-- Order Details -->
                        <div id="orderDetails" class="mt-3">
                            <!-- Order details will be dynamically displayed here -->
                        </div>

                        <div>
                            <?php
                            if (isset($_GET['status']) && isset($_GET['message'])) {
                                $status = $_GET['status'];
                                $message = $_GET['message'];

                                // Display the message based on the status
                                if ($status === 'success') {
                                    echo "<div class='alert alert-success'>$message</div>";
                                } elseif ($status === 'error') {
                                    echo "<div class='alert alert-danger'>$message</div>";
                                }
                            } ?>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center mt-4">
                            <input type="submit" name="check_order" value="Check and Add" class="btn btn-success">
                        </div>
                    </form>

                </div>

                <!-- Back Link -->
                <div class="card-footer text-center">
                    <a href="../orderDash.php" class="btn btn-secondary">Back</a>
                </div>
            </div>
        </div>

        <script>
            function showOrderDetails() {
                const select = document.getElementById('order');
                const orderDetailsDiv = document.getElementById('orderDetails');
                const selectedOption = select.options[select.selectedIndex];

                if (selectedOption.value) {
                    // Fetch and display order details dynamically
                    orderDetailsDiv.innerHTML = `
                <div class="alert alert-info">
                    <strong>Selected Order:</strong> ${selectedOption.text}
                </div>
            `;
                    document.getElementById('order_name').value = selectedOption.text;
                } else {
                    orderDetailsDiv.innerHTML = '';
                }
            }
        </script>

        <script>
            function setOrderName() {
                // Get the selected option from the dropdown
                const orderSelect = document.getElementById('order');
                const selectedOption = orderSelect.options[orderSelect.selectedIndex];

                // Extract the order name from the data attribute
                const orderName = selectedOption.getAttribute('data-order-name');

                // Set the hidden input's value
                document.getElementById('order_name').value = orderName;

                // Optionally display the order details (if needed)
                const orderDetailsDiv = document.getElementById('orderDetails');
                if (orderName) {
                    orderDetailsDiv.innerHTML = `<p><strong>Selected Order:</strong> ${orderName}</p>`;
                } else {
                    orderDetailsDiv.innerHTML = '';
                }
            }
        </script>


    </div>
</body>

</html>