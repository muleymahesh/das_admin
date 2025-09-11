<?php
// DB connection
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'server.php';
// Fetch regions for dropdown
$regions = $conn->query("SELECT region_id, region_name FROM regions ORDER BY region_name");

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['area_name'], $_POST['region_id'])) {
    $area_name = trim($_POST['area_name']);
    $region_id = (int) $_POST['region_id'];

    if ($area_name !== "" && $region_id > 0) {
        $stmt = $conn->prepare("INSERT INTO areas (name, region_id) VALUES (?, ?)");
        $stmt->bind_param("si", $area_name, $region_id);
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Area added successfully!</p>";
        } else {
            echo "<p style='color:red;'>Error: " . $conn->error . "</p>";
        }
        $stmt->close();
    }
}
include 'header.php';
?>
<div class="container mt-4">
    <div class="card">
        <div class="card-header">
            <h2>Add Area</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <label>Area Name:</label>
                <input type="text" name="area_name" required>

                <label>Region:</label>
                <select name="region_id" required>
                    <option value="">-- Select Region --</option>
                    <?php while ($row = $regions->fetch_assoc()) { ?>
                        <option value="<?= $row['region_id'] ?>"><?= htmlspecialchars($row['region_name']) ?></option>
                    <?php } ?>
                </select>

                <button type="submit">Add Area</button>
            </form>
        </div>
    </div>
    <!-- Add empty space to push footer down -->
    <div style="height:50vh;"></div>
</div>
<?php include 'footer.php'; ?>