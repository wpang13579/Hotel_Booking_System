<?php
require('../../database.php'); // Database connection
session_start();


// Initialize filter conditions
$role_filter = "";
$selected_role = "";
$total_requests = 0; // Default value

// Ensure the role_id is set in the session
if (!isset($_SESSION['role_id'])) {
    // Handle cases where the role_id is missing (optional)
    header('Location: ../login.php');
    exit();
}

// Determine the redirection URL based on the role_id
$role_id = $_SESSION['role_id'];
$back_url = "";

if ($role_id == 1) {
    $back_url = "../staffDashboard/hr_dashboard.php";
} elseif ($role_id == 2) {
    $back_url = "../staffDashboard/admin_dashboard.php";
} else {
    // Default fallback (optional, in case of unexpected role_id)
    $back_url = "../staffDashboard/";
}

// Handle filtering based on selected role
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role_id'])) {
    $selected_role = $_POST['role_id']; // Retrieve selected role_id from the form
    if (!empty($selected_role)) {
        $role_filter = "WHERE sr.role_id = '$selected_role'"; // Apply filter condition
    }
}


// Fetch Role-Based Performance Report
function fetchRolePerformance($con, $role_filter) {
    $query = "
        SELECT sr.role_name, s.staff_id, CONCAT(s.staff_firstname, ' ', s.staff_lastname) AS staff_name, 
               AVG(sp.perf_rating) AS avg_rating, COUNT(sp.performance_id) AS total_ratings
        FROM staff_performance sp
        JOIN staff s ON sp.staff_id = s.staff_id
        JOIN staff_role sr ON s.role_id = sr.role_id
        $role_filter
        GROUP BY sr.role_name, s.staff_id
        ORDER BY avg_rating DESC;
    ";
    return $con->query($query);
}

// Ensure role_id is set, default to 0 if not selected
$role_id = isset($_POST['role_id']) ? $_POST['role_id'] : 0; 

// Fetch Role Performance with applied filter
$role_performance_result = fetchRolePerformance($con, $role_filter);

// Fetch all roles for dropdown
$roles_query = "SELECT * FROM staff_role";
$roles_result = $con->query($roles_query);

// Fetch counts for daily, weekly, and monthly performance
$daily_query = "SELECT staff_id, AVG(perf_rating) AS daily_avg 
                FROM staff_performance 
                WHERE DATE(eval_date) = CURDATE()
                GROUP BY staff_id";

$weekly_query = "SELECT staff_id, AVG(perf_rating) AS weekly_avg 
                FROM staff_performance 
                WHERE YEARWEEK(DATE(eval_date), 1) = YEARWEEK(CURDATE(), 1)
                GROUP BY staff_id";
                
$monthly_query = "SELECT staff_id, AVG(perf_rating) AS monthly_avg 
                  FROM staff_performance 
                  WHERE YEAR(DATE(eval_date)) = YEAR(CURDATE()) 
                  AND MONTH(DATE(eval_date)) = MONTH(CURDATE())
                  GROUP BY staff_id";

$daily_result = $con->query($daily_query);
$weekly_result = $con->query($weekly_query);
$monthly_result = $con->query($monthly_query);

$daily_data = [];
$weekly_data = [];
$monthly_data = [];

while ($row = $daily_result->fetch_assoc()) {
    $daily_data[$row['staff_id']] = $row['daily_avg'];
}

while ($row = $weekly_result->fetch_assoc()) {
    $weekly_data[$row['staff_id']] = $row['weekly_avg'];
}

while ($row = $monthly_result->fetch_assoc()) {
    $monthly_data[$row['staff_id']] = $row['monthly_avg'];
}

$staff_names = [];
$average_ratings = [];
$daily_averages = [];
$weekly_averages = [];
$monthly_averages = [];
$total_ratings_array = [];
$role_names = [];

if ($role_performance_result && $role_performance_result->num_rows > 0) {
    while ($row = $role_performance_result->fetch_assoc()) {
        $role_names[] = $row['role_name']; // Collect role names
        $staff_names[] = $row['staff_name'];
        $average_ratings[] = number_format($row['avg_rating'], 2);
        $total_ratings_array[] = $row['total_ratings'];
        $daily_averages[] = isset($daily_data[$row['staff_id']]) ? number_format($daily_data[$row['staff_id']], 2) : 'N/A';
        $weekly_averages[] = isset($weekly_data[$row['staff_id']]) ? number_format($weekly_data[$row['staff_id']], 2) : 'N/A';
        $monthly_averages[] = isset($monthly_data[$row['staff_id']]) ? number_format($monthly_data[$row['staff_id']], 2) : 'N/A';
    }
} 


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Performance Report</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
    <style>
        /* General styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        h2, h3 {
            text-align: center;
            color: #4CAF50;
            margin-top: 20px;
        }

        /* Form styling */
        form {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        label {
            font-size: 16px;
            margin-right: 10px;
        }

        select, button {
            padding: 8px 16px;
            font-size: 14px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            cursor: pointer;
            background-color: #4CAF50;
            color: white;
            border: none;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #45a049;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: #fff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }

        th {
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #ddd;
        }

        .form-container {
            margin-bottom: 20px;
        }

        /* Chart container styling */
        .chart-container {
            width: 100%;
            max-width: 1000px;
            margin: auto;
            height: 400px; /* Fixed height */
            padding-bottom: 50px;
        }

        /* Back button styling */
        .back-button {
            display: block;
            margin: 30px auto;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
        }

        .back-button:hover {
            background-color: #45a049;
        }

        /* Responsive design for mobile */
        @media (max-width: 768px) {
            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }

            h2, h3 {
                font-size: 20px;
            }

            button {
                font-size: 12px;
            }

            .chart-container {
                height: 300px; /* Adjust height on mobile */
            }
        }
    </style>
</head>

<body>

    <h2>Staff Performance Report</h2>

    <!-- HTML form for role selection -->
    <form method="POST">
        <label for="role_id">Select Role:</label>
        <select name="role_id" id="role_id">
            <option value="">All Roles</option> <!-- Option to show all roles -->
            <?php while ($role = $roles_result->fetch_assoc()): ?>
                <option value="<?php echo $role['role_id']; ?>" 
                    <?php echo ($role['role_id'] == $selected_role) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($role['role_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filter</button>
    </form>

    <!-- Displaying the Performance Table -->
    <table>
        <thead>
            <tr>
                <th>Role</th>
                <th>Staff Name</th>
                <th>Average Rating</th>
                <th>Total Ratings</th>
                <th>Daily Average</th>
                <th>Weekly Average</th>
                <th>Monthly Average</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Display the fetched data
            if (!empty($staff_names)) {
                foreach ($staff_names as $index => $staff_name) {
                    $row_class = ($index % 2 == 0) ? 'background-color: #f2f2f2;' : ''; // Alternating row colors
                    echo "<tr style='$row_class'>
                            <td>" . htmlspecialchars($role_names[$index]) . "</td>
                            <td>" . htmlspecialchars($staff_name) . "</td>
                            <td>" . $average_ratings[$index] . "</td>
                            <td>" . $total_ratings_array[$index] . "</td>
                            <td>" . $daily_averages[$index] . "</td>
                            <td>" . $weekly_averages[$index] . "</td>
                            <td>" . $monthly_averages[$index] . "</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align: center;'>No data available for the selected role.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="chart-container">
        <h3>Performance Trends</h3>
        <canvas id="performanceChart"></canvas>
    </div>

    <script>
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const performanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($staff_names); ?>,
                datasets: [
                    {
                        label: 'Daily Avg',
                        data: <?php echo json_encode($daily_averages); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Weekly Avg',
                        data: <?php echo json_encode($weekly_averages); ?>,
                        backgroundColor: 'rgba(255, 206, 86, 0.2)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Monthly Avg',
                        data: <?php echo json_encode($monthly_averages); ?>,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                    },
                    title: {
                        display: true,
                        text: 'Staff Performance Trends'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>

    <button class="back-button" onclick="location.href='<?= $back_url ?>'">Back</button>

</body>

</html>

