<?php
// DB connection
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'server.php';

// Fetch regions for dropdown
$regions = $conn->query("SELECT region_id, region_name FROM regions ORDER BY region_name");
// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['region_name'])) {
    $region_name = trim($_POST['region_name']);
    if ($region_name !== "") {
        $stmt = $conn->prepare("INSERT INTO regions (name) VALUES (?)");
        $stmt->bind_param("s", $region_name);
        if ($stmt->execute()) {
            echo "<p style='color:green;'>Region added successfully!</p>";
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
            <h2>Add Region</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <label>Region Name:</label>
                <input type="text" name="region_name" required>
                 <button class="btn btn-success btn-sm action-btn" type="submit">Add Region</button>
            </form>
        </div>
    </div>
    <div class="card">
        <div class="card-header">
        <h3>Existing Regions</h3>
        </div>
        <div class="card-body"></div>
        <table id="customersTable" class="table table-striped table-bordered table-hover align-middle">
            <tr></tr>
            <th>ID</th>
            <th>Region ID</th>
            <th>Name</th>
            <th>Action</th>
            </tr>
            <?php
            $i = 1;
            if ($regions->num_rows > 0) {
                while ($row = $regions->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><?= $row['region_id'] ?></td>
                        <td><?= htmlspecialchars($row['region_name']) ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm action-btn"
                                onclick="editRegion(<?= $row['region_id'] ?>, '<?= htmlspecialchars($row['region_name']) ?>')">Edit</button>
                        </td>
                    </tr>
                <?php }
            } else { ?>
                <tr>
                    <td colspan="3" class="text-center">No regions found.</td>
                </tr>
            <?php } ?>
        </table>

        <script>
            function editRegion(id, name) {
                document.getElementById('region_id').value = id;
                document.getElementById('region_name').value = name;
            }
        </script>
        <script>
            $(document).ready(function () {
                $('#customersTable').DataTable({
                    "order": [[0, "asc"]], // Default sorting by region name
                    "pageLength": 25 // Show 25 records per page
                });
            });
        </script>
    </div>
</div>
</div>
<div style="height:50vh;"></div>

<?php include 'footer.php'; ?>