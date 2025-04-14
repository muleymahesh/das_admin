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


    <!-- Content Section -->

    <?php
    require_once 'server.php';

    // Fetch data from user_master
    $sql = "
    SELECT `um`.`user_id` AS `user_id`, `um`.`name` AS `name`, `um`.`username` AS `username`, `um`.`mobile` AS `mobile`, `r`.`role_name` AS `role`, `rg`.`region_name` AS `region`, `s`.`name` AS `supervisor_name`, `um`.`collection` AS `collection`, `um`.`commission` AS `commission` FROM (((`user_master` `um` join `roles` `r` on((`um`.`role_id` = `r`.`role_id`))) join `regions` `rg` on((`um`.`region_id` = `rg`.`region_id`))) left join `user_master` `s` on((`um`.`supervisor_id` = `s`.`user_id`)));
    ";
        $result = $conn->query($sql);
    ?>

    <div class="container my-5">
        <div class="card">
            <div class="card-header">
                <h2>User List</h2>
                <a href="add_user.php" class="btn btn-danger">Add User</a>
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
                            <th>Supervisor</th>
                            <th>Collection</th>
                            <th>Commission</th>
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
                                    <td>" . $row["supervisor_name"] . "</td>
                                    <td>" . $row["collection"] . "</td>
                                    <td>" . $row["commission"] . "</td>
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