<?php
require('../../database.php');
$status = "";

$id = $_GET['id'];
$query = "SELECT * FROM reward WHERE reward_id='$id'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $description = $_POST['description'];
    $points_required = $_POST['points_required'];
    $reward_type = $_POST['reward_type'];
    $tier_required = $_POST['tier_required'];
    $discount_rate = $_POST['discount_rate'];
    $update_query = "UPDATE reward SET 
                        description='$description', 
                        points_required='$points_required', 
                        reward_type='$reward_type', 
                        tier_required='$tier_required', 
                        discount_rate='$discount_rate' 
                        WHERE reward_id='$id'";

    if (mysqli_query($con, $update_query)) {
        $status = "<p style='color: green;'>Reward data updated successfully.</p>";
    } else {
        $status = "<p style='color: red;'>Reward data failed to be updated.</p>";
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Edit Reward</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4">Edit Reward</h2>

        <!-- Status Message -->
        <?php if (isset($status)) echo $status; ?>

        <!-- Reward Form -->
        <form method="POST" action="">
            <p><input type="hidden" name="reward_id" id="reward_id" value="<?php echo $row['reward_id']; ?>"></p>
            <div class="form-row">
                <!-- Points Required -->
                <div class="form-group col-md-3">
                    <label for="points_required">Points Required</label>
                    <input type="number" class="form-control" id="points_required" name="points_required" value="<?php echo $row['points_required']; ?>" required>
                </div>

                <!-- Tier Required -->
                <div class="form-group col-md-3">
                    <label for="tier_required">Tier Required</label>
                    <select class="form-control" id="tier_required" name="tier_required" required>
                        <option value="bronze" <?php if ($row['tier_required'] == 'bronze') echo "selected"; ?>>Bronze</option>
                        <option value="silver" <?php if ($row['tier_required'] == 'silver') echo "selected"; ?>>Silver</option>
                        <option value="gold" <?php if ($row['tier_required'] == 'gold') echo "selected"; ?>>Gold</option>
                        <option value="platinum" <?php if ($row['tier_required'] == 'platinum') echo "selected"; ?>>Platinum</option>
                    </select>
                </div>

                <!-- Reward Type -->
                <div class="form-group col-md-3">
                    <label for="reward_type">Reward Type</label>
                    <select class="form-control" id="reward_type" name="reward_type" required onchange="toggleDiscountRate()">
                        <option value="upgrade_room" <?php if ($row['reward_type'] == 'upgrade_room') echo "selected"; ?>>Room Upgrade</option>
                        <option value="discount" <?php if ($row['reward_type'] == 'discount') echo "selected"; ?>>Discount</option>
                    </select>
                </div>

                <!-- Discount Rate -->
                <div class="form-group col-md-3" id="discount_rate_field">
                    <label for="discount_rate">Discount Rate</label>
                    <input type="number" class="form-control" id="discount_rate" name="discount_rate" value="<?php echo $row['discount_rate']; ?>" step="0.01" required>
                </div>
            </div>

            <!-- Description Row -->
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" required> <?php echo $row['description']; ?></textarea>
            </div>

            <div>
                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">Edit</button>
                <a href="reward_management.php" id="cancel" class="btn btn-secondary">Back</a>
                <p style="color: red;"><?php echo $status; ?></p>
            </div>
        </form>
    </div>

    <!-- JS for Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>