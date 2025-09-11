<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'header.php'; // Include header for consistent styling
?>


    <!-- Content Section -->

    <?php
    require_once 'server.php';

    // Fetch data from user_master
    $sql = "
    SELECT 
        um.user_id AS user_id, 
        um.name AS name, 
        um.username AS username, 
        um.mobile AS mobile, 
        r.role_name AS role, 
        rg.region_name AS region, 
        a.name AS area, 
        s.name AS supervisor_name, 
        um.collection AS collection, 
        um.commission AS commission 
    FROM user_master um 
        JOIN roles r ON um.role_id = r.role_id
        JOIN regions rg ON um.region_id = rg.region_id
        LEFT JOIN areas a ON um.area_id = a.id
        LEFT JOIN user_master s ON um.supervisor_id = s.user_id;
    ";
        $result = $conn->query($sql);
    ?>

    <div class="container my-5">
        <div class="card">
            <div class="card-header">
                <h2>User List</h2>
                <a href="add_user.php" class="btn btn-danger">Add User</a>
                <a href="add_area.php" class="btn btn-danger">Add new Area</a>
                <a href="add_region.php" class="btn btn-danger">Add new Region</a>
            </div>
            <div class="card-body">
                <table id="usersTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Mobile</th>
                            <th>Role</th>
                            <th>Region</th>
                            <th>Area</th>
                            <th>Supervisor</th>
                            <th>Collection</th>
                            <th>Commission</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>" . $row["user_id"] . "</td>
                                    <td>" . $row["name"] . "</td>
                                    <td>" . $row["username"] . "</td>
                                    <td>" . $row["mobile"] . "</td>
                                    <td>" . $row["role"] . "</td>
                                    <td>" . $row["region"] . "</td>
                                    <td>" . $row["area"] . "</td>
                                    <td>" . $row["supervisor_name"] . "</td>
                                    <td>" . $row["collection"] . "</td>
                                    <td>" . $row["commission"] . "</td>
                                    <td>
                                        <a href=\"edit_user.php?id=".$row['user_id']."\" class=\"btn btn-sm btn-primary\">
                                            <i class=\"fas fa-edit\"></i> Edit
                                        </a>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <?php
    $conn->close();
    ?>

    <!-- DataTables JS and CSS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>

    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                "paging": true,
                "searching": true
            });
        });
    </script>

    <!-- Footer -->
    <footer class="bg-danger text-center py-4">
        <p>&copy; 2025 My Site. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS and dependencies (jQuery and Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>