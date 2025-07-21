<?php
session_start();
require('../../database.php');

// Check if the user is logged in using email
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    header("Location: login.php");
    exit();
}

// Retrieve staff details using the email
$staff_email = $_SESSION['staff_email'];
$query = "SELECT staff_id, staff_firstname, staff_lastname FROM staff WHERE staff_email = '$staff_email'";
$result = mysqli_query($con, $query);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $staff_id = $row['staff_id']; // Fetch staff_id
    $staff_name = $row['staff_firstname'] . " " . $row['staff_lastname'];
} else {
    die("<p style='color: red;'>Error: Staff details not found. Please log in again.</p>");
}

// Check if the logged-in user is a manager or admin
$role_id = $_SESSION['role_id'];
$roles = [
    2 => 'Admin',
    3 => 'Guest Manager',
    6 => 'Normal Staff',
];

// Get the role name
$role_name = isset($roles[$role_id]) ? $roles[$role_id] : 'Unknown Role';

// Define role-based visibility flags
$is_manager_or_admin = ($role_id == 2 || $role_id == 3);  // Admin (2) or Guest Manager (3)
// Initialize status message
$status = "";

// Function to sanitize inputs
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Fetch room_id from GET parameters
if (isset($_GET['room_id'])) {
    $room_id = intval($_GET['room_id']);
} else {
    // Handle the error if room_id is not set
    echo "<script>
            alert('Room ID is missing.');
            window.location.href = 'view_booking_room.php';
          </script>";
    exit();
}

// Fetch stay information
$stay_query = "SELECT * FROM stay WHERE room_id = ?";
if ($stmt = $con->prepare($stay_query)) {
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $stay_result = $stmt->get_result();
    $stay_info = $stay_result->fetch_assoc();
    $stmt->close();

    if (!$stay_info) {
        echo "<script>
                alert('Stay information not found.');
                window.location.href = 'view_booking_room.php';
              </script>";
        exit();
    }
} else {
    // Handle query preparation error
    $status = "<p style='color: red;'>Database error: " . htmlspecialchars($con->error) . "</p>";
}

// Fetch room information
$room_query = "SELECT * FROM room WHERE room_id = ?";
if ($stmt = $con->prepare($room_query)) {
    $stmt->bind_param("i", $room_id);
    $stmt->execute();
    $room_result = $stmt->get_result();
    $room_info = $room_result->fetch_assoc();
    $stmt->close();

    if (!$room_info) {
        echo "<script>
                alert('Room information not found.');
                window.location.href = 'view_booking_room.php';
              </script>";
        exit();
    }
} else {
    $status = "<p style='color: red;'>Database error: " . htmlspecialchars($con->error) . "</p>";
}

// Fetch guest information
$guest_query = "SELECT * FROM guest WHERE guest_id = ?";
if ($stmt = $con->prepare($guest_query)) {
    $stmt->bind_param("i", $stay_info['guest_id']);
    $stmt->execute();
    $guest_result = $stmt->get_result();
    $guest_info = $guest_result->fetch_assoc();
    $stmt->close();

    if (!$guest_info) {
        echo "<script>
                alert('Guest information not found.');
                window.location.href = 'view_booking_room.php';
              </script>";
        exit();
    }
} else {
    $status = "<p style='color: red;'>Database error: " . htmlspecialchars($con->error) . "</p>";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate inputs
    $submitted_room_id = intval($_POST['room_id']);
    $priority_level = sanitize_input($_POST['priority_level']);
    $req_date = date("Y-m-d H:i:s");
    $req_status = 'Pending';
    $check_out_date_input = isset($_POST['check_out_date']) ? $_POST['check_out_date'] : '';

    // Validate checkout date format
    $date_format = 'Y-m-d\TH:i';
    $dt = DateTime::createFromFormat($date_format, $check_out_date_input);
    if (!$dt || $dt->format($date_format) !== $check_out_date_input) {
        $status = "<p style='color: red;'>Invalid checkout date format.</p>";
    } else {
        // Convert to standard datetime format
        $check_out_date = $dt->format('Y-m-d H:i:s');

        // Get the selected checkout option
        $checkout_option = isset($_POST['checkout_option']) ? sanitize_input($_POST['checkout_option']) : '';

        if ($checkout_option == 'emergency') {
            // Emergency Checkout logic
            if ($check_out_date < $stay_info['check_in_date']) {
                $status = "<p style='color: red;'>Error: Checkout date cannot be earlier than check-in date.</p>";
            } else {
                // Insert the emergency maintenance request using prepared statements
                $req_desc = "Emergency: " . sanitize_input($_POST['req_desc']);

                $insert_query = "INSERT INTO maintenance_request (staff_id, room_id, req_desc, priority_level, req_date, req_status)
                                 VALUES (?, ?, ?, ?, ?, ?)";

                if ($stmt = $con->prepare($insert_query)) {
                    $stmt->bind_param("iissss", $staff_id, $submitted_room_id, $req_desc, $priority_level, $req_date, $req_status);
                    if ($stmt->execute()) {
                        $stmt->close();

                        // Update the room status to "emergency_maintenance"
                        $update_room_query = "UPDATE room SET room_status = ? WHERE room_id = ?";
                        if ($stmt = $con->prepare($update_room_query)) {
                            $new_status = 'emergency maintenance';
                            $stmt->bind_param("si", $new_status, $submitted_room_id);
                            $stmt->execute();
                            $stmt->close();
                        } else {
                            $status = "<p style='color: red;'>Failed to prepare room status update: " . htmlspecialchars($con->error) . "</p>";
                        }

                        // Update the checkout date in the stay table
                        $update_stay_query = "UPDATE stay SET check_out_date = ? WHERE room_id = ?";
                        if ($stmt = $con->prepare($update_stay_query)) {
                            $stmt->bind_param("si", $check_out_date, $submitted_room_id);
                            $stmt->execute();
                            $stmt->close();
                        } else {
                            $status = "<p style='color: red;'>Failed to prepare stay update: " . htmlspecialchars($con->error) . "</p>";
                        }

                        echo "<script>
                            alert('Emergency maintenance request submitted successfully!');
                            window.location.href = 'view_booking_room.php';
                        </script>";
                        exit();
                    } else {
                        $status = "<p style='color: red;'>Failed to submit the emergency request: " . htmlspecialchars($stmt->error) . "</p>";
                        $stmt->close();
                    }
                } else {
                    $status = "<p style='color: red;'>Failed to prepare emergency request insertion: " . htmlspecialchars($con->error) . "</p>";
                }
            }
        } else if ($checkout_option == 'earlier') {
            // Checkout Earlier logic
            if ($check_out_date >= $stay_info['check_out_date']) {
                $status = "<p style='color: red;'>Error: Checkout date cannot be later or equal to the original checkout date.</p>";
            } else {
                // Update the room status to "housekeeping"
                $update_room_query = "UPDATE room SET room_status = ? WHERE room_id = ?";
                if ($stmt = $con->prepare($update_room_query)) {
                    $new_status = 'housekeeping';
                    $stmt->bind_param("si", $new_status, $submitted_room_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $status = "<p style='color: red;'>Failed to prepare room status update: " . htmlspecialchars($con->error) . "</p>";
                }

                // Update the checkout date in the stay table
                $update_stay_query = "UPDATE stay SET check_out_date = ? WHERE room_id = ?";
                if ($stmt = $con->prepare($update_stay_query)) {
                    $stmt->bind_param("si", $check_out_date, $submitted_room_id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $status = "<p style='color: red;'>Failed to prepare stay update: " . htmlspecialchars($con->error) . "</p>";
                }

                echo "<script>
                    alert('Checkout earlier request processed successfully!');
                    window.location.href = 'view_booking_room.php';
                </script>";
                exit();
            }
        } else {
            // If no checkout option is selected, update room status to "housekeeping" without changing checkout date
            $update_room_query = "UPDATE room SET room_status = ? WHERE room_id = ?";
            if ($stmt = $con->prepare($update_room_query)) {
                $new_status = 'housekeeping';
                $stmt->bind_param("si", $new_status, $submitted_room_id);
                if ($stmt->execute()) {
                    $stmt->close();
                    echo "<script>
                        alert('Room status updated to housekeeping!');
                        window.location.href = 'view_booking_room.php';
                    </script>";
                    exit();
                } else {
                    $status = "<p style='color: red;'>Failed to update room status: " . htmlspecialchars($stmt->error) . "</p>";
                    $stmt->close();
                }
            } else {
                $status = "<p style='color: red;'>Failed to prepare room status update: " . htmlspecialchars($con->error) . "</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        /* Optional: Add some custom styling */
        .required:after {
            content: "*";
            color: red;
            margin-left: 5px;
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h4 class="text-center">Guest Management</h4>
        <ul class="list-unstyled">
            <li>
                <a href="../guest_dashboard.php">Dashboard</a>
            </li>
            <li>
                <a href="#">Guest</a>
                <ul class="submenu list-unstyled">
                    <li><a href="../guest/guest_registration.php">Guest Registration</a></li>
                    <li><a href="../guest/view_guest_profile.php">Guest Profile Management</a></li>
                </ul>
            </li>
            <!-- Only show if user is Admin or Guest Manager -->
            <?php if ($is_manager_or_admin): ?>
                <li>
                    <a href="#">Reward</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="../reward/reward_management.php">Reward Management</a></li>
                    </ul>
                </li>
            <?php endif; ?>
            <li>
                <a href="#">Booking</a>
                <ul class="submenu list-unstyled">
                    <li><a href="../room/booking.php">New Booking</a></li>
                    <li><a href="../room/view_booking_room.php">Booking Management</a></li>
                </ul>
            </li>
            <!-- Only show if user is Admin or Guest Manager -->
            <?php if ($is_manager_or_admin): ?>
                <li>
                    <a href="#">Report</a>
                    <ul class="submenu list-unstyled">
                        <li><a href="../report/guest_report.php">Report</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <a href="../../staffModule/staff_logout.php" style="color: red;">Logout</a>
        </ul>
    </div>
    <div class="container">
        <br>
        <h1 class="mb-4">Checkout Page</h1>

        <?php if (!empty($status)) echo $status; ?>

        <form action="" method="POST">
            <!-- Hidden Inputs -->
            <input type="hidden" name="room_id" value="<?php echo htmlspecialchars($room_info['room_id']); ?>">
            <input type="hidden" name="priority_level" value="High">

            <!-- Room Information -->
            <div class="form-group">
                <label for="room_num">Room Number:</label>
                <input type="text" class="form-control" name="room_num" id="room_num" value="<?php echo htmlspecialchars($room_info['room_num']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="room_status">Room Status:</label>
                <input type="text" class="form-control" name="room_status" id="room_status" value="<?php echo htmlspecialchars($room_info['room_status']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="check_in_date">Check-in Date:</label>
                <input type="text" class="form-control" name="check_in_date" id="check_in_date" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($stay_info['check_in_date']))); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="check_out_date">Check-out Date:</label>
                <input type="datetime-local" class="form-control" name="check_out_date" id="check_out_date" value="<?php echo htmlspecialchars(date('Y-m-d\TH:i', strtotime($stay_info['check_out_date']))); ?>" readonly>
            </div>

            <!-- Guest Information -->
            <div class="form-group">
                <label for="guest_name">Guest Name:</label>
                <input type="text" class="form-control" name="guest_name" id="guest_name" value="<?php echo htmlspecialchars($guest_info['guest_name']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="guest_email">Guest Email:</label>
                <input type="email" class="form-control" name="guest_email" id="guest_email" value="<?php echo htmlspecialchars($guest_info['guest_email']); ?>" readonly>
            </div>

            <div class="form-group">
                <label for="guest_phone">Guest Phone Number:</label>
                <input type="text" class="form-control" name="guest_phone" id="guest_phone" value="<?php echo htmlspecialchars($guest_info['guest_phone']); ?>" readonly>
            </div>

            <!-- Checkout Options -->
            <div class="form-group">
                <label>Checkout Options:</label>
                <div class="form-check">
                    <input type="radio" class="form-check-input" name="checkout_option" id="emergency_check_out" value="emergency">
                    <label class="form-check-label" for="emergency_check_out">Emergency Checkout</label>
                </div>

                <div class="form-check">
                    <input type="radio" class="form-check-input" name="checkout_option" id="checkout_earlier" value="earlier">
                    <label class="form-check-label" for="checkout_earlier">Checkout Earlier</label>
                </div>
            </div>

            <!-- Issue Description (only if emergency checkout is selected) -->
            <div id="emergency_description" style="display: none;">
                <div class="form-group">
                    <label for="req_desc">Issue Description:</label>
                    <textarea class="form-control" name="req_desc" id="req_desc" rows="4" placeholder="Describe the issue..."></textarea>
                </div>
            </div>

            <!-- Form Buttons -->
            <button type="submit" class="btn btn-primary">Proceed</button>
            <a href="view_booking_room.php" id="cancel" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
        // Function to toggle the emergency description field and checkout date field
        function toggleFields() {
            var emergencyRadio = document.getElementById('emergency_check_out');
            var earlierRadio = document.getElementById('checkout_earlier');
            var emergencyDesc = document.getElementById('emergency_description');
            var reqDesc = document.getElementById('req_desc');
            var checkOutDateField = document.getElementById('check_out_date');

            if (emergencyRadio.checked) {
                emergencyDesc.style.display = 'block';
                reqDesc.setAttribute('required', 'required');
                // Enable the check-out date field
                checkOutDateField.removeAttribute('readonly');
            } else {
                emergencyDesc.style.display = 'none';
                reqDesc.removeAttribute('required');
            }

            if (earlierRadio.checked) {
                // Enable the check-out date field
                checkOutDateField.removeAttribute('readonly');
            } else {
                if (!emergencyRadio.checked) {
                    // Disable the check-out date field if neither option is selected
                    checkOutDateField.setAttribute('readonly', 'readonly');
                }
            }
        }

        // Event listeners for radio buttons
        document.getElementById('emergency_check_out').addEventListener('change', toggleFields);
        document.getElementById('checkout_earlier').addEventListener('change', toggleFields);

        // Initialize the form based on pre-selected options (if any)
        window.onload = function() {
            toggleFields();
        };
    </script>

    <!-- Bootstrap JS & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>