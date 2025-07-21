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

// Fetch room data
$room_status_query = "SELECT room_status, COUNT(*) as count FROM room GROUP BY room_status";
$room_status_result = mysqli_query($con, $room_status_query);

$occupied_count = 0;
$available_count = 0;
while ($row = mysqli_fetch_assoc($room_status_result)) {
    if ($row['room_status'] == 'occupied') {
        $occupied_count = $row['count'];
    } elseif ($row['room_status'] == 'available') {
        $available_count = $row['count'];
    }
}

// Revenue calculations
// Daily
$daily_revenue_query = "SELECT IFNULL(SUM(amount),0) as daily_total FROM guest_revenue WHERE DATE(occur_date) = CURDATE()";
$daily_revenue_result = mysqli_query($con, $daily_revenue_query);
$daily_revenue = mysqli_fetch_assoc($daily_revenue_result)['daily_total'];

// Weekly (last 7 days)
$weekly_revenue_query = "SELECT IFNULL(SUM(amount),0) as weekly_total FROM guest_revenue WHERE occur_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
$weekly_revenue_result = mysqli_query($con, $weekly_revenue_query);
$weekly_revenue = mysqli_fetch_assoc($weekly_revenue_result)['weekly_total'];

// Monthly (last 30 days)
$monthly_revenue_query = "SELECT IFNULL(SUM(amount),0) as monthly_total FROM guest_revenue WHERE occur_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
$monthly_revenue_result = mysqli_query($con, $monthly_revenue_query);
$monthly_revenue = mysqli_fetch_assoc($monthly_revenue_result)['monthly_total'];

// 7-day revenue flow (line chart)
$seven_day_query = "
    SELECT DATE(occur_date) as dt, IFNULL(SUM(amount),0) as total 
    FROM guest_revenue 
    WHERE occur_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
    GROUP BY DATE(occur_date)
    ORDER BY dt ASC";
$seven_day_result = mysqli_query($con, $seven_day_query);
$seven_day_labels = [];
$seven_day_data = [];
while ($row = mysqli_fetch_assoc($seven_day_result)) {
    $seven_day_labels[] = $row['dt'];
    $seven_day_data[] = $row['total'];
}

// 4-week revenue flow (line chart by week)
$four_week_query = "
    SELECT YEARWEEK(occur_date,1) as yw, IFNULL(SUM(amount),0) as total 
    FROM guest_revenue 
    WHERE occur_date >= DATE_SUB(CURDATE(), INTERVAL 28 DAY)
    GROUP BY YEARWEEK(occur_date,1)
    ORDER BY yw ASC";
$four_week_result = mysqli_query($con, $four_week_query);
$four_week_labels = [];
$four_week_data = [];

// We'll generate labels as 'Week 1', 'Week 2', etc.
$week_count = 1;
while ($row = mysqli_fetch_assoc($four_week_result)) {
    $four_week_labels[] = "Week " . $week_count;
    $four_week_data[] = $row['total'];
    $week_count++;
}

// Newly joined guests (last 10 by record_date descending)
$new_guests_query = "SELECT guest_id, guest_name, guest_email, record_date FROM guest ORDER BY record_date DESC LIMIT 10";
$new_guests_result = mysqli_query($con, $new_guests_query);

// Top 10 loyalty
$top_loyalty_query = "
    SELECT l.loyalty_id, l.points, l.tier_level, g.guest_name 
    FROM loyalty_program l
    JOIN guest g ON l.guest_id = g.guest_id
    ORDER BY l.points DESC
    LIMIT 10";
$top_loyalty_result = mysqli_query($con, $top_loyalty_query);

// Tier distribution
$tier_query = "SELECT tier_level, COUNT(*) as cnt FROM loyalty_program GROUP BY tier_level";
$tier_result = mysqli_query($con, $tier_query);

$tier_counts = ['bronze' => 0, 'sliver' => 0, 'gold' => 0, 'platinium' => 0];
while ($row = mysqli_fetch_assoc($tier_result)) {
    $level = strtolower($row['tier_level']);
    if (isset($tier_counts[$level])) {
        $tier_counts[$level] = $row['cnt'];
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../style.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
        }

        .container {
            margin-left: 250px;
            padding: 30px;
        }

        h5 {
            font-weight: 600;
            margin-bottom: 20px;
        }

        hr {
            margin: 40px 0;
        }

        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .card {
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .card-body h6 {
            font-size: 14px;
            font-weight: 600;
        }

        .card-body p {
            font-size: 18px;
            margin: 0;
            font-weight: 500;
        }

        table.table {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }

        table.table th {
            font-weight: 600;
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
        <div class="row">
            <!-- Pie Chart for Occupation -->
            <div class="col-md-4">
                <h5>Room Occupation</h5>
                <div class="chart-container">
                    <canvas id="roomStatusChart"></canvas>
                </div>
            </div>

            <div class="col-md-4">
                <h5>Tier Distribution</h5>
                <div class="chart-container">
                    <canvas id="tierChart"></canvas>
                </div>
            </div>
            <!-- Revenue Cards -->
            <div class="col-md-4">
                <h5>Revenue Summary</h5>
                <div class="card mb-2 bg-white shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Daily Revenue</h6>
                        <p class="card-text">$<?php echo number_format($daily_revenue, 2); ?></p>
                    </div>
                </div>
                <div class="card mb-2 bg-white shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Weekly Revenue</h6>
                        <p class="card-text">$<?php echo number_format($weekly_revenue, 2); ?></p>
                    </div>
                </div>
                <div class="card mb-2 bg-white shadow-sm">
                    <div class="card-body">
                        <h6 class="card-title">Monthly Revenue</h6>
                        <p class="card-text">$<?php echo number_format($monthly_revenue, 2); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <hr>

        <!-- Line Charts -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h5>Weekly Revenue Flow</h5>
                <div class="chart-container">
                    <canvas id="sevenDayChart"></canvas>
                </div>
            </div>
            <div class="col-md-6">
                <h5>Monthly Revenue Flow</h5>
                <div class="chart-container">
                    <canvas id="fourWeekChart"></canvas>
                </div>
            </div>
        </div>

        <hr>

        <!-- Tables and Tier Pie Chart -->
        <div class="row mt-4">
            <div class="col-md-12">
                <h5>Newly Joined Guests (Latest 10)</h5>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Guest ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Record Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($new_guests_result)): ?>
                            <tr>
                                <td><?php echo $row['guest_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['guest_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['guest_email']); ?></td>
                                <td><?php echo $row['record_date']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <h5 class="mt-4">Top 10 Loyalty Points</h5>
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Loyalty ID</th>
                            <th>Guest Name</th>
                            <th>Points</th>
                            <th>Tier</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($top_loyalty_result)): ?>
                            <tr>
                                <td><?php echo $row['loyalty_id']; ?></td>
                                <td><?php echo htmlspecialchars($row['guest_name']); ?></td>
                                <td><?php echo $row['points']; ?></td>
                                <td><?php echo $row['tier_level']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="text-center my-4">
            <button type="submit" class="btn btn-primary" onclick="window.print()">Print as PDF</button>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Helper function to format currency in Chart.js axes
        const currencyFormatter = (value) => {
            return '$' + value.toLocaleString();
        };

        // Room Status Pie Chart
        const ctxRoom = document.getElementById('roomStatusChart').getContext('2d');
        const roomStatusChart = new Chart(ctxRoom, {
            type: 'pie',
            data: {
                labels: ['Occupied', 'Available'],
                datasets: [{
                    data: [<?php echo $occupied_count; ?>, <?php echo $available_count; ?>],
                    backgroundColor: ['#FF6384', '#36A2EB']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Room Status Distribution',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // 7-Day Line Chart
        const ctx7Day = document.getElementById('sevenDayChart').getContext('2d');
        const sevenDayChart = new Chart(ctx7Day, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($seven_day_labels); ?>,
                datasets: [{
                    label: 'Daily Revenue',
                    data: <?php echo json_encode($seven_day_data); ?>,
                    borderColor: '#36A2EB',
                    fill: false,
                    tension: 0.3
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Last 7 Days Revenue',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return currencyFormatter(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true
                    },
                    y: {
                        display: true,
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return currencyFormatter(value);
                            }
                        },
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        // 4-Week Line Chart
        const ctx4Week = document.getElementById('fourWeekChart').getContext('2d');
        const fourWeekChart = new Chart(ctx4Week, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($four_week_labels); ?>,
                datasets: [{
                    label: 'Weekly Revenue',
                    data: <?php echo json_encode($four_week_data); ?>,
                    borderColor: '#FF6384',
                    fill: false,
                    tension: 0.3
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Last 4 Weeks Revenue',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return currencyFormatter(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        display: true
                    },
                    y: {
                        display: true,
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return currencyFormatter(value);
                            }
                        },
                        grid: {
                            borderDash: [5, 5]
                        }
                    }
                }
            }
        });

        // Tier Distribution Pie Chart
        const ctxTier = document.getElementById('tierChart').getContext('2d');
        const tierChart = new Chart(ctxTier, {
            type: 'pie',
            data: {
                labels: ['Bronze', 'Sliver', 'Gold', 'Platinium'],
                datasets: [{
                    data: [
                        <?php echo $tier_counts['bronze']; ?>,
                        <?php echo $tier_counts['sliver']; ?>,
                        <?php echo $tier_counts['gold']; ?>,
                        <?php echo $tier_counts['platinium']; ?>
                    ],
                    backgroundColor: ['#CD7F32', '#C0C0C0', '#FFD700', '#E5E4E2']
                }]
            },
            options: {
                plugins: {
                    title: {
                        display: true,
                        text: 'Loyalty Tier Distribution',
                        font: {
                            size: 16,
                            weight: 'bold'
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>

</html>