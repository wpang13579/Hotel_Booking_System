<?php
session_start();
require('../../database.php');

// Ensure user is logged in
if (!isset($_SESSION['staff_email']) || !isset($_SESSION['role_id'])) {
    header("Location: ../../staffDashboard/staff_login.php");
    exit();
}

$staff_email = $_SESSION['staff_email'];
$role_id = $_SESSION['role_id'];

// Fetch specific staff's shift record
function fetchSpecificStaffShiftRecord($con, $staff_email)
{
    $query = "
        SELECT s.staff_firstname, s.staff_lastname, s.staff_gender,sr.role_name, ss.shift_type, ss.start_time, ss.end_time
        FROM staff s
        JOIN staff_role sr ON s.role_id = sr.role_id
        JOIN staff_shift ss ON s.staff_id = ss.staff_id
        WHERE s.staff_email = ?
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $staff_email);
    $stmt->execute();
    return $stmt->get_result();
}

// Fetch other staff shift records with the same role but different shift type
function fetchNormalStaffShiftRecords($con, $role_id, $current_shift_type, $staff_email)
{
    $query = "
        SELECT s.staff_firstname, s.staff_lastname, s.staff_gender, s.staff_email, ss.shift_type, ss.start_time, ss.end_time
        FROM staff s
        JOIN staff_role sr ON s.role_id = sr.role_id
        JOIN staff_shift ss ON s.staff_id = ss.staff_id
        WHERE sr.role_id = ? AND ss.shift_type <> ? AND s.staff_email <> ?
        ORDER BY s.staff_id ASC;
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("iss", $role_id, $current_shift_type, $staff_email);
    $stmt->execute();
    return $stmt->get_result();
}

// Fetch requests made by the logged-in user
function fetchUserShiftRequests($con, $staff_email)
{
    $query = "
        SELECT 
            ts.staff_firstname AS target_staff_name,
            ts.staff_lastname AS target_staff_lastname,
            ts.staff_email AS target_staff_email,
            ss.request_date,
            ss.request_status,
            ss.reject_comment
        FROM staff_shift_swap ss
        JOIN staff ts ON ss.target_staff_id = ts.staff_id
        JOIN staff rs ON ss.request_staff_id = rs.staff_id
        WHERE rs.staff_email = ?
        ORDER BY ss.request_date DESC;
    ";
    $stmt = $con->prepare($query);
    $stmt->bind_param("s", $staff_email);
    $stmt->execute();
    return $stmt->get_result();
}

// Fetch requests for the logged-in user
$user_shift_requests_result = fetchUserShiftRequests($con, $staff_email);


// Fetch shift requests for Admin or HR roles
function fetchShiftRequests($con, $role_id)
{
    if ($role_id == 1 || $role_id == 2) { // Assuming 1 = Admin, 2 = HR
        $query = "
            SELECT 
                rs.staff_firstname AS request_staff_name,
                rsr.role_name AS request_staff_role,
                rss.shift_type AS request_staff_shift_type,
                rss.start_time AS request_staff_start_time,
                rss.end_time AS request_staff_end_time,
                ts.staff_firstname AS target_staff_name,
                tsr.role_name AS target_staff_role,
                tss.shift_type AS target_staff_shift_type,
                tss.start_time AS target_staff_start_time,
                tss.end_time AS target_staff_end_time,
                ss.request_date,
                ss.request_status,
                ss.request_id
            FROM staff_shift_swap ss
            JOIN staff rs ON ss.request_staff_id = rs.staff_id
            JOIN staff_role rsr ON rs.role_id = rsr.role_id
            JOIN staff_shift rss ON rs.staff_id = rss.staff_id
            JOIN staff ts ON ss.target_staff_id = ts.staff_id
            JOIN staff_role tsr ON ts.role_id = tsr.role_id
            JOIN staff_shift tss ON ts.staff_id = tss.staff_id
            ORDER BY ss.request_date DESC;
        ";
        return $con->query($query);
    }
    return null;
}

// Fetch logged-in staff's data
$specific_staff_result = fetchSpecificStaffShiftRecord($con, $staff_email);
$specific_staff_record = $specific_staff_result->fetch_assoc();

if ($specific_staff_record) {
    $shift_records_result = fetchNormalStaffShiftRecords($con, $role_id, $specific_staff_record['shift_type'], $staff_email);
} else {
    $shift_records_result = null;
}

// Fetch shift requests (only for Admin/HR)
$shift_requests_result = fetchShiftRequests($con, $role_id);
?>

<?php
$role_id = isset($_SESSION['role_id']) ? $_SESSION['role_id'] : 0;

// Determine the back URL based on user role ID
$back_url = '';
switch ($role_id) {
    case 1:
        $back_url = '../staffDashboard/staffschedule_dashboard.php';
        break;
    case 2:
        $back_url = '../staffDashboard/staffschedule_dashboard.php';
        break;
    case 3:
        $back_url = '../../guestModule/guest_dashboard.php';
        break;
    case 4:
        $back_url = '../../R&M modules/dashboard.php';
        break;
    case 5:
        $back_url = '../../inventoryModule/inventory_management.php';
        break;
    case 6:
        $back_url = '../staffDashboard/nstaff_dashboard.php';
        break;
    default:
        $back_url = '../staff_login.php';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Staff Shift Records</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f9f9f9;
            color: #333;
        }

        h1,
        h2 {
            color: #004d99;
            text-align: center;
        }

        h1 {
            font-size: 2.5em;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 1.8em;
            margin-top: 30px;
        }

        table {
            width: 100%;
            margin-bottom: 30px;
            border-collapse: collapse;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: #004d99;
            color: white;
        }

        td {
            background-color: #ffffff;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:hover {
            background-color: #e6f7ff;
        }

        .btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px;
        }

        .button-container {
            text-align: center;
            margin: 20px;
        }

        .btn:hover {
            background-color: #218838;
        }

        .btn-back {
            background-color: #dc3545;
        }

        .btn-back:hover {
            background-color: #c82333;
        }

        .request-link {
            color: #0069d9;
            text-decoration: none;
        }

        .request-link:hover {
            text-decoration: underline;
        }

        .no-records {
            text-align: center;
            font-size: 1.2em;
            color: #888;
        }
    </style>
</head>

<body>

    <h1>View - Staff Shift Records</h1>

    <!-- Table 1: Your Shift Record -->
    <h2>Your Shift Record</h2>
    <table>
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Gender</th>
                <th>Shift Type</th>
                <th>Start Time</th>
                <th>End Time</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($specific_staff_record): ?>
                <tr>
                    <td><?= htmlspecialchars($specific_staff_record["staff_firstname"]); ?></td>
                    <td><?= htmlspecialchars($specific_staff_record["staff_lastname"]); ?></td>
                    <td><?= ucfirst(htmlspecialchars($specific_staff_record["staff_gender"])); ?></td>
                    <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $specific_staff_record["shift_type"]))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($specific_staff_record["start_time"]))); ?></td>
                    <td><?= htmlspecialchars(date("g:i A", strtotime($specific_staff_record["end_time"]))); ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="no-records">No shift record found for you.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Table 2: Other Staff Shift Records -->
    <h2>Other Staff Records</h2>
    <table>
        <thead>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Gender</th>
                <th>Email</th>
                <th>Shift Type</th>
                <th>Start Time</th>
                <th>End Time</th>
                <?php if ($role_id == 1 || $role_id == 2): ?> <!-- Extra column for HR/Admin -->
                    <th>Role</th>
                <?php endif; ?>
                <th>Request Change</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Determine the SQL query based on role
            if ($role_id == 1 || $role_id == 2) {
                // HR/Admin: Fetch all staff excluding the logged-in user
                $query = "
                    SELECT s.staff_firstname, s.staff_lastname, s.staff_gender, s.staff_email, sr.role_name, ss.shift_type, ss.start_time, ss.end_time
                    FROM staff s
                    JOIN staff_role sr ON s.role_id = sr.role_id
                    JOIN staff_shift ss ON s.staff_id = ss.staff_id
                    WHERE s.staff_email <> ?
                    ORDER BY s.staff_id ASC;
                ";
                $stmt = $con->prepare($query);
                $stmt->bind_param("s", $staff_email);
            } else {
                // Other roles: Fetch only same-role records with different shift types
                $query = "
                    SELECT s.staff_firstname, s.staff_lastname, s.staff_gender, s.staff_email, sr.role_name, ss.shift_type, ss.start_time, ss.end_time
                    FROM staff s
                    JOIN staff_role sr ON s.role_id = sr.role_id
                    JOIN staff_shift ss ON s.staff_id = ss.staff_id
                    WHERE s.staff_email <> ? AND s.role_id = ? AND ss.shift_type <> ?
                    ORDER BY s.staff_id ASC;
                ";
                $stmt = $con->prepare($query);
                $stmt->bind_param("sis", $staff_email, $role_id, $specific_staff_record['shift_type']);
            }

            // Execute the query
            $stmt->execute();
            $result = $stmt->get_result();

            // Display the records
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()):
                    // Determine if "Request Change" should be available
                    $is_same_role = ($row['role_name'] === $specific_staff_record['role_name']);
                    $is_different_shift = ($row['shift_type'] !== $specific_staff_record['shift_type']);
                    $can_request_change = ($role_id == 1 || $role_id == 2)
                        ? $is_same_role && $is_different_shift // HR/Admin logic
                        : $is_same_role && $is_different_shift; // Other roles logic
            ?>
                    <tr>
                        <td><?= htmlspecialchars($row["staff_firstname"]); ?></td>
                        <td><?= htmlspecialchars($row["staff_lastname"]); ?></td>
                        <td><?= ucfirst(htmlspecialchars($row["staff_gender"])); ?></td>
                        <td><?= htmlspecialchars($row["staff_email"]); ?></td>
                        <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $row["shift_type"]))); ?></td>
                        <td><?= htmlspecialchars(date("g:i A", strtotime($row["start_time"]))); ?></td>
                        <td><?= htmlspecialchars(date("g:i A", strtotime($row["end_time"]))); ?></td>
                        <?php if ($role_id == 1 || $role_id == 2): ?>
                            <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $row["role_name"]))); ?></td>
                        <?php endif; ?>
                        <td>
                            <?php if ($can_request_change): ?>
                                <a href="staffshift_update.php?target_email=<?= htmlspecialchars($row['staff_email']); ?>&shift_type=<?= htmlspecialchars($row['shift_type']); ?>" class="request-link">
                                    Request Change
                                </a>
                            <?php else: ?>
                                <span>Not Applicable</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                endwhile;
            } else { ?>
                <tr>
                    <td colspan="<?= $role_id == 1 || $role_id == 2 ? '8' : '7'; ?>" class="no-records">No records found.</td>
                </tr>
            <?php } ?>
        </tbody>

    </table>


    <!-- Table3: Your Shift Change Requests -->
    <h2>Your Shift Change Requests</h2>
    <table>
        <thead>
            <tr>
                <th>Target Staff Name</th>
                <th>Target Staff Email</th>
                <th>Request Date</th>
                <th>Request Status</th>
                <th>Rejection Reason (If any)</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($user_shift_requests_result && $user_shift_requests_result->num_rows > 0): ?>
                <?php while ($row = $user_shift_requests_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row["target_staff_name"] . " " . $row["target_staff_lastname"]); ?></td>
                        <td><?= htmlspecialchars($row["target_staff_email"]); ?></td>
                        <td><?= htmlspecialchars(date("Y-m-d", strtotime($row["request_date"]))); ?></td>
                        <td><?= htmlspecialchars(ucfirst($row["request_status"])); ?></td>
                        <td><?= htmlspecialchars(ucfirst($row["reject_comment"])); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="no-records">You have not made any shift change requests.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Table 4: Shift Requests -->
    <?php if ($role_id == 1 || $role_id == 2): ?>
        <h2>Shift Requests</h2>
        <table>
            <thead>
                <tr>
                    <th>Request Staff Name</th>
                    <th>Request Staff Role</th>
                    <th>Request Staff Shift Type</th>
                    <th>Request Staff Start Time</th>
                    <th>Request Staff End Time</th>
                    <th>Target Staff Name</th>
                    <th>Target Staff Role</th>
                    <th>Target Staff Shift Type</th>
                    <th>Target Staff Start Time</th>
                    <th>Target Staff End Time</th>
                    <th>Request Date</th>
                    <th>Request Status</th>
                    <th>Update</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($shift_requests_result && $shift_requests_result->num_rows > 0): ?>
                    <?php while ($row = $shift_requests_result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row["request_staff_name"]); ?></td>
                            <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $row["request_staff_role"]))); ?></td>
                            <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $row["request_staff_shift_type"]))); ?></td>
                            <td><?= htmlspecialchars(date("g:i A", strtotime($row["request_staff_start_time"]))); ?></td>
                            <td><?= htmlspecialchars(date("g:i A", strtotime($row["request_staff_end_time"]))); ?></td>
                            <td><?= htmlspecialchars($row["target_staff_name"]); ?></td>
                            <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $row["target_staff_role"]))); ?></td>
                            <td><?= htmlspecialchars(ucwords(str_replace("_", " ", $row["target_staff_shift_type"]))); ?></td>
                            <td><?= htmlspecialchars(date("g:i A", strtotime($row["target_staff_start_time"]))); ?></td>
                            <td><?= htmlspecialchars(date("g:i A", strtotime($row["target_staff_end_time"]))); ?></td>
                            <td><?= htmlspecialchars(date("Y-m-d", strtotime($row["request_date"]))); ?></td>
                            <td><?= htmlspecialchars(ucfirst($row["request_status"])); ?></td>
                            <td>
                                <?php if (strtolower($row['request_status']) === 'approved'): ?>
                                    <span>Approved, Not Allow to Change the Status</span>
                                <?php else: ?>
                                    <a href="staffshift_status.php?request_id=<?= htmlspecialchars($row['request_id']); ?>&current_status=<?= htmlspecialchars($row['request_status']); ?>" class="request-link">
                                        Update
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" class="no-records">No shift requests found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="button-container"> <button class="btn btn-back" onclick="location.href='<?= $back_url ?>'">Back</button> </div>
</body>

</html>