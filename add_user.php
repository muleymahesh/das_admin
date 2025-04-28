<?php
// Database connection
require_once 'server.php';

// Handle AJAX request to get areas
if (isset($_POST['ajax']) && $_POST['ajax'] == 'get_areas' && isset($_POST['region_id'])) {
    $region_id = $_POST['region_id'];

    $stmt = $conn->prepare("SELECT area_id, area_name FROM area_master WHERE region_id = ?");
    $stmt->bind_param("i", $region_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<option value=''>--Select Area--</option>";
    while ($row = $result->fetch_assoc()) {
        echo "<option value='{$row['area_id']}'>{$row['area_name']}</option>";
    }

    $stmt->close();
    $conn->close();
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['ajax'])) {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $mobile = $_POST['mobile'];
    $role_id = $_POST['role_id'];
    $region_id = $_POST['region_id'];
    $area_id = $_POST['area_id'];
    $supervisor_id = $_POST['supervisor_id'];
    $collection = $_POST['collection'] ?? 0.00;
    $commission = $_POST['commission'] ?? 0.00;

    $sql = "INSERT INTO user_master (name, username, password, mobile, role_id, region_id, area_id, supervisor_id, collection, commission)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssiiiidd", $name, $username, $password, $mobile, $role_id, $region_id, $area_id, $supervisor_id, $collection, $commission);

    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ User added successfully!</p>";
    } else {
        echo "<p style='color: red;'>❌ Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bootstrap Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional Bootstrap Theme -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap-theme.min.css" rel="stylesheet">
    <style>
        a {
            color: rgb(249, 250, 252) !important;
        }
    </style>
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-danger">
        <a class="navbar-brand" href="#">DAS Group</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto"></ul>
            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="products.php">Inventory</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">Users <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h4>Add User</h4>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <div class="form-group">
                        <label>Name:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Username:</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Password:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Mobile:</label>
                        <input type="text" name="mobile" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Role :</label>
                        <select name="role_id" class="form-control" required>
                            <option value="">--Select Role--</option>
                            <?php
                            $roleQuery = "SELECT role_id, role_name FROM roles ORDER BY role_name";
                            $result = $conn->query($roleQuery);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['role_id']}'>{$row['role_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Region:</label>
                        <select name="region_id" id="region_id" class="form-control" required>
                            <option value="">--Select Region--</option>
                            <?php
                            $regionQuery = "SELECT * FROM regions order by region_name";
                            $result = $conn->query($regionQuery);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['region_id']}'>{$row['region_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Area:</label>
                        <select name="area_id" id="area_id" class="form-control" required>
                            <option value="">--Select Area--</option>
                            <?php
                            $areaQuery = "SELECT id, name FROM areas order by name";
                            $result = $conn->query($areaQuery);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Supervisor ID:</label>
                        <select name="supervisor_id" id="supervisor_id" class="form-control" required>
                            <option value="">--Select Supervisor--</option>
                            <?php
                            $supervisorQuery = "SELECT user_id, name FROM user_master WHERE role_id IN (2, 3) order by name";
                            $result = $conn->query($supervisorQuery);
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['user_id']}'>{$row['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Collection:</label>
                        <input type="number" value="0" name="collection" class="form-control">
                    </div>

                    <div class="form-group">
                        <label>Commission:</label>
                        <input type="number" value="0" name="commission" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-success">Add User</button>
                </form>
            </div>
        </div>
    </div>

    
</body>
</html>

<?php $conn->close(); ?>
