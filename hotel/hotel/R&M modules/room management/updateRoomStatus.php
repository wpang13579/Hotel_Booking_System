<?php
session_start();
require('../../database.php');

// Check if the user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    die("<p style='color: red;'>Access denied. Please log in.</p>");
}

// Retrieve user role and staff email from session
$role = $_SESSION['role_id'];
$staff_id = $_SESSION['staff_email'];

// Validate room ID from request
if (!isset($_REQUEST['room_id'])) {
    die("<p style='color: red;'>Invalid room ID.</p>");
}

$room_id = $_REQUEST['room_id'];

// Fetch current room details
$query = "SELECT * FROM room WHERE room_id = $room_id";
$result = mysqli_query($con, $query);
$room = mysqli_fetch_assoc($result);

if (!$room) {
    die("<p style='color: red;'>Room not found.</p>");
}

$room_type_id = $room['room_type']; // Fetch the room type ID

// Define valid transitions based on role
$valid_transitions = [];

if ($role == 4) { // room Manager
    $valid_transitions = [
        'available' => ['housekeeping', 'under maintenance'],
        'housekeeping' => ['available', 'under maintenance'],
        'under maintenance' => ['housekeeping'],
    ];
} elseif ($role == 6) { // Staff
    $valid_transitions = [
        'available' => ['occupied', 'under maintenance', 'housekeeping'], // Staff can only change 'available' to 'occupied'
        'occupied' => ['emergency maintenance'],
        'housekeeping' => ['available', 'under maintenance'],
        'under maintenance' => ['under maintenance'], // Staff cannot make other changes
    ];
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $new_status = strtolower(trim($_POST['room_status']));

    // Prevent updating to the same status
    if ($room['room_status'] === $new_status) {
        echo "<script>
                alert('The room is already in the status \"$new_status\". Please select a different status.');
                window.location.href = 'viewRoom.php';
            </script>";
        exit();
    }

    // Validate status transition based on current room status
    if ($role == 4 && !array_key_exists($room['room_status'], $valid_transitions) || !in_array($new_status, $valid_transitions[$room['room_status']])) {
        die("<p style='color: red;'>Invalid status transition from '{$room['room_status']}' to '$new_status'.</p>");
    }


    // Validate status transition based on current room status
    if ($role == 6 && !array_key_exists($room['room_status'], $valid_transitions) || !in_array($new_status, $valid_transitions[$room['room_status']])) {
        die("<p style='color: red;'>Invalid status transition from '{$room['room_status']}' to '$new_status'.</p>");
    }


    // Update room status in the database
    $update_query = "UPDATE room SET room_status = '$new_status' WHERE room_id = $room_id";
    if (mysqli_query($con, $update_query)) {


        // Check if the transition is from 'occupied' to 'under maintenance' or 'emergency maintenance'
        if ($room['room_status'] === 'occupied' && ($new_status === 'under maintenance' || $new_status === 'emergency maintenance')) {
            if ($new_status === 'under maintenance') {
                echo "<script>
        alert('Room status updated to Under Maintenance. Redirecting to create a maintenance request.');
        window.location.href = '../maintenance request/createMainRequest.php?room_num={$room['room_num']}';
        </script>";
            } elseif ($new_status === 'emergency maintenance') {
                echo "<script>
        alert('Room is undergoing to Emergency Maintenance. Redirecting to Emergency Maintenance Request.');
        window.location.href = '../maintenance request/createEmergencyRequest.php?room_num={$room['room_num']}';
        </script>";
            }
            exit();
        }

        // Check if the new status is 'under maintenance' (not from 'occupied')
        if ($new_status === 'under maintenance') {
            echo "<script>
            setTimeout(function() {
                alert('Room status updated to Under Maintenance. Redirecting to create a maintenance request.');
                window.location.href = '../maintenance request/createMainRequest.php?room_num={$room['room_num']}';
            }, 500);
        </script>";
        } elseif ($new_status === 'emergency maintenance') {
            echo "<script>
            setTimeout(function() {
                alert('Room status updated to Emergency Maintenance. Redirecting to Emergency Maintenance Task.');
                window.location.href = '../maintenance request/createEmergencyRequest.php?room_num={$room['room_num']}';
            }, 500);
        </script>";
        } else {
            echo "<script>
            setTimeout(function() {
                alert('Room status updated successfully.');
                window.location.href = 'viewRoom.php';
            }, 500);
        </script>";
        }
    } else {
        echo "<p style='color: red;'>Error updating room status: " . mysqli_error($con) . "</p>";
    }


    //////////////////////////////// inventory management ///////////////////////////////////////////////////

    if ($new_status === 'available') {
        // Fetch required inventory from assign_inventory table
        $inventory_query = "SELECT inventory_id, quantity 
                            FROM inventory_assign 
                            WHERE room_type_id = $room_type_id";
        $inventory_result = mysqli_query($con, $inventory_query);

        $inventory_sufficient = true;
        $insufficient_items = []; // To store insufficient inventory items
        $low_inventory_items = []; // To store low inventory items

        while ($row = mysqli_fetch_assoc($inventory_result)) {
            $inventory_id = $row['inventory_id'];
            $required_quantity = $row['quantity'];

            // Fetch current inventory quantity and alert level
            $query = "SELECT inv_name, inv_quantity, alert_level 
                      FROM inventory_management 
                      WHERE id = $inventory_id";
            $result = mysqli_query($con, $query);
            $inventory = mysqli_fetch_assoc($result);

            if (!$inventory || $inventory['inv_quantity'] < $required_quantity) {
                $inventory_sufficient = false;
                $insufficient_items[] = [
                    'item_name' => $inventory['inv_name'] ?? 'Unknown',
                    'required_quantity' => $required_quantity,
                    'available_quantity' => $inventory['inv_quantity'] ?? 0,
                ];
            } elseif ($inventory['inv_quantity'] - $required_quantity <= $inventory['alert_level']) {
                $low_inventory_items[] = $inventory['inv_name'];
            }
        }

        // Deduct inventory if sufficient
        if ($inventory_sufficient) {
            mysqli_data_seek($inventory_result, 0); // Reset the result pointer for deduction
            while ($row = mysqli_fetch_assoc($inventory_result)) {
                $inventory_id = $row['inventory_id'];
                $required_quantity = $row['quantity'];

                // Deduct inventory
                $update_query = "UPDATE inventory_management 
                                 SET inv_quantity = inv_quantity - $required_quantity 
                                 WHERE id = $inventory_id";
                mysqli_query($con, $update_query);
            }

            // Handle low inventory alert
            if (!empty($low_inventory_items)) {
                $low_inventory_message = "Warning: The following items are low in inventory:\\n" . implode("\\n", $low_inventory_items);
                echo "<script>
                    alert('$low_inventory_message');
                    window.location.href = 'viewRoom.php';
                </script>";
                exit();
            }

            echo "<script>
                alert('Inventory deducted successfully for room type.');
                window.location.href = 'viewRoom.php';
            </script>";
            exit();
        } else {
            // Display insufficient inventory alert
            $insufficient_message = "Cannot update room status due to insufficient inventory:\\n";
            foreach ($insufficient_items as $item) {
                $insufficient_message .= "- {$item['item_name']}: Required: {$item['required_quantity']}, Available: {$item['available_quantity']}\\n";
            }
            echo "<script>
                alert('$insufficient_message');
            </script>";
            exit();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Room Status</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .container {
            margin-top: 50px;
            max-width: 600px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .btn-submit {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-submit:hover {
            background-color: #0056b3;
        }

        .btn-back {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Update Room Status</h2>
        <form method="post" onsubmit="handleFormSubmit(event)">
            <div class="mb-3">
                <label for="room_status" class="form-label">Room Status:</label>
                <select class="form-select" name="room_status" id="room_status" required>
                    <?php if (isset($valid_transitions[$room['room_status']])): ?>
                        <?php foreach ($valid_transitions[$room['room_status']] as $status): ?>
                            <option value="<?php echo $status; ?>" <?php echo ($room['room_status'] === $status) ? 'selected' : ''; ?>>
                                <?php echo ucfirst($status); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" selected>Not Allowed to Change Status</option>
                    <?php endif; ?>
                </select>
            </div>

            <div class="text-center">
                <button type="submit" class="btn-submit">Update Status</button>
            </div>
        </form>

        <div class="text-center mt-3">
            <form action="viewRoom.php" method="get">
                <button type="submit" class="btn-back">Back</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
