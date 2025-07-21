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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $guest_name = $_POST['guest_name'];
    $guest_email = $_POST['guest_email'];
    $guest_dob = $_POST['guest_dob'];
    $guest_phone = $_POST['guest_phone'];
    $guest_address = $_POST['guest_address'];
    $gender = $_POST['gender'];
    $capture_photo = $_POST['capture_photo']; // Base64 image from the form

    // Check if email already exists
    $checkQuery = "SELECT * FROM guest WHERE guest_email = ?";
    if ($stmt = mysqli_prepare($con, $checkQuery)) {
        mysqli_stmt_bind_param($stmt, "s", $guest_email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $errorMsg = "The email already exists. Please use a different email.";
            $_SESSION['error_message'] = $errorMsg;
        }
        mysqli_stmt_close($stmt);
    }

    // Insert into the guest table if no error
    if (!isset($_SESSION['error_message']) || empty($_SESSION['error_message'])) {
        $record_date = date('Y-m-d H:i:s');
        $query = "INSERT INTO `guest` (guest_name, guest_email, guest_dob, guest_phone, guest_address, record_date, gender)
                  VALUES ('$guest_name', '$guest_email', '$guest_dob', '$guest_phone', '$guest_address','$record_date','$gender')";
        $result = mysqli_query($con, $query);
        if ($result) {
            $points = 0;
            $tier_level = 'bronze';
            $total_point_redeem = 0;
            $guest_id = mysqli_insert_id($con);
            $loyalty_query = "INSERT INTO `loyalty_program` (points, tier_level, total_point_redeem, guest_id) 
                              VALUES ('$points','$tier_level','$total_point_redeem','$guest_id')";
            $loyalty_result = mysqli_query($con, $loyalty_query);

            // If there's a captured photo, decode and store it locally
            if (!empty($capture_photo)) {
                // Extract the base64 data
                $image_parts = explode(";base64,", $capture_photo);
                if (count($image_parts) == 2) {
                    $image_base64 = base64_decode($image_parts[1]);
                    $filename = 'guest_' . $guest_id . '.png';
                    $file = __DIR__ . '/uploads/' . $filename;

                    // Ensure the uploads directory exists and is writable
                    if (!file_exists(__DIR__ . '/uploads')) {
                        mkdir(__DIR__ . '/uploads', 0777, true);
                    }

                    file_put_contents($file, $image_base64);
                }
            }

            $successMsg = "Registration successful!";
            echo '<script type="text/javascript">alert("Info: ' . $successMsg . '");</script>';
            $_SESSION['error_message'] = '';
        } else {
            echo "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Group 3 Hotel Management System - Guest Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to bottom right, #f4f6f9, #e9ecf3);
            font-family: 'Arial', sans-serif;
        }

        .navbar {
            background-color: #05106F;
        }

        .navbar-brand {
            color: #ffffff !important;
            font-weight: bold;
        }

        .form-container {
            max-width: 650px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #05106F;
            border-radius: 15px 15px 0 0;
            color: #fff;
            padding: 1.5rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .form-label {
            font-weight: 600;
            color: #333;
        }

        .btn-submit {
            background-color: #05106F;
            color: white;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-submit:hover {
            background-color: #0d2760;
        }

        .error-message {
            color: red;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .text-danger {
            font-size: 0.9rem;
        }

        /* Styles for camera capture */
        .camera-container {
            margin-bottom: 20px;
            text-align: center;
        }

        #videoElement {
            width: 100%;
            max-width: 320px;
            border: 2px solid #05106F;
            border-radius: 8px;
        }

        #captureBtn,
        #takePhotoBtn {
            margin-top: 10px;
            background-color: #05106F;
            color: #fff;
        }

        #capturedImage {
            margin-top: 10px;
            max-width: 320px;
            border: 2px solid #ccc;
            border-radius: 8px;
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

    <div class="container form-container">
        <div class="card">
            <br>
            <div class="card-header">
                Guest Registration Form
            </div>
            <div class="card-body p-4">
                <form action="" method="POST">
                    <!-- Guest Name -->
                    <div class="mb-3">
                        <label for="guest_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="guest_name" name="guest_name" required>
                        <div id="nameError" class="text-danger" style="display: none;">Name cannot be empty!</div>
                    </div>

                    <!-- Guest Email -->
                    <div class="mb-3">
                        <label for="guest_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="guest_email" name="guest_email" required>
                        <div id="emailError" class="text-danger" style="display: none;">Please enter a valid email!</div>
                    </div>

                    <?php if (isset($_SESSION['error_message']) && !empty($_SESSION['error_message'])): ?>
                        <div class="error-message"><?php echo $_SESSION['error_message']; ?></div>
                    <?php endif; ?>

                    <!-- Date of Birth -->
                    <div class="mb-3">
                        <label for="guest_dob" class="form-label">Date of Birth</label>
                        <input type="date" class="form-control" id="guest_dob" name="guest_dob" required>
                        <div id="dobError" class="text-danger" style="display: none;">Please enter a valid date of birth!</div>
                    </div>

                    <!-- Guest Phone -->
                    <div class="mb-3">
                        <label for="guest_phone" class="form-label">Phone Number</label>
                        <input type="text" class="form-control" id="guest_phone" name="guest_phone" required>
                        <div id="phoneError" class="text-danger" style="display: none;">Phone number must be numeric!</div>
                    </div>

                    <!-- Guest Address -->
                    <div class="mb-3">
                        <label for="guest_address" class="form-label">Address</label>
                        <textarea class="form-control" id="guest_address" name="guest_address" rows="3" required></textarea>
                        <div id="addressError" class="text-danger" style="display: none;">Address cannot be empty!</div>
                    </div>

                    <!-- Gender -->
                    <div class="mb-3">
                        <label for="gender" class="form-label">Gender</label>
                        <select class="form-select" id="gender" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        <div id="genderError" class="text-danger" style="display: none;">Please select a gender!</div>
                    </div>

                    <!-- Camera Capture Section -->
                    <div class="camera-container">
                        <button type="button" class="btn btn-primary" id="captureBtn">Capture Photo</button>
                        <div id="cameraArea" style="display:none; margin-top:20px;">
                            <video id="videoElement" autoplay playsinline></video><br>
                            <button type="button" class="btn btn-primary" id="takePhotoBtn">Take Snapshot</button>
                            <canvas id="canvas" style="display:none;"></canvas>
                            <img id="capturedImage" alt="Captured Image" style="display:none;">
                        </div>
                    </div>

                    <!-- Hidden input for the captured image -->
                    <input type="hidden" id="capture_photo" name="capture_photo" value="">

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-submit">Register</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector("form");
            const nameInput = document.getElementById("guest_name");
            const emailInput = document.getElementById("guest_email");
            const dobInput = document.getElementById("guest_dob");
            const phoneInput = document.getElementById("guest_phone");
            const addressInput = document.getElementById("guest_address");
            const genderSelect = document.getElementById("gender");

            form.addEventListener("submit", function(event) {
                let isValid = true;

                // Full Name Validation
                if (nameInput.value.trim() === "") {
                    document.getElementById("nameError").style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById("nameError").style.display = "none";
                }

                // Email Validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailInput.value)) {
                    document.getElementById("emailError").style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById("emailError").style.display = "none";
                }

                // Date of Birth Validation
                const currentDate = new Date();
                const dobDate = new Date(dobInput.value);
                const ageLimit = 150;
                const minAgeDate = new Date(currentDate);
                minAgeDate.setDate(currentDate.getDate() - 7);

                if (dobInput.value === "" || dobDate > currentDate || dobDate > minAgeDate ||
                    dobDate < new Date(currentDate.getFullYear() - ageLimit, currentDate.getMonth(), currentDate.getDate())
                ) {
                    document.getElementById("dobError").style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById("dobError").style.display = "none";
                }

                // Phone Number Validation
                const phoneRegex = /^[0-9]+$/;
                if (!phoneRegex.test(phoneInput.value) || phoneInput.value.trim().length < 10) {
                    document.getElementById("phoneError").style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById("phoneError").style.display = "none";
                }

                // Address Validation
                if (addressInput.value.trim() === "") {
                    document.getElementById("addressError").style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById("addressError").style.display = "none";
                }

                // Gender Validation
                if (genderSelect.value === "") {
                    document.getElementById("genderError").style.display = "block";
                    isValid = false;
                } else {
                    document.getElementById("genderError").style.display = "none";
                }

                if (!isValid) {
                    event.preventDefault();
                }
            });


            // Camera Capture Logic
            const captureBtn = document.getElementById('captureBtn');
            const cameraArea = document.getElementById('cameraArea');
            const video = document.getElementById('videoElement');
            const takePhotoBtn = document.getElementById('takePhotoBtn');
            const canvas = document.getElementById('canvas');
            const capturedImage = document.getElementById('capturedImage');
            const hiddenInput = document.getElementById('capture_photo');

            let stream;

            captureBtn.addEventListener('click', async () => {
                cameraArea.style.display = 'block';
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: false
                    });
                    video.srcObject = stream;
                } catch (err) {
                    alert('Failed to access camera: ' + err);
                }
            });

            takePhotoBtn.addEventListener('click', () => {
                const context = canvas.getContext('2d');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                // Stop the video stream
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                }

                const dataURL = canvas.toDataURL('image/png');
                hiddenInput.value = dataURL;

                capturedImage.src = dataURL;
                capturedImage.style.display = 'block';
                video.style.display = 'none';
                takePhotoBtn.style.display = 'none';
            });
        });
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
unset($_SESSION['error_message']);
?>