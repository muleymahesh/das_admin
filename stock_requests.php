
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Check if user is logged in   
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
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
            color:rgb(249, 250, 252) !important;
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
                <a class="nav-link" href="products.php">Inventory </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php">Users</a>
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
    $userId = $_SESSION['user_id'];
    $sql = "SELECT m.id, m.date, d.name, d.user_id, `pending_with`, `status` FROM stock_request_master m, user_master d WHERE pending_with = '$userId' AND m.raised_by = d.user_id ORDER BY m.date DESC;";
    $result = $conn->query($sql);

    $requests = [];

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $detailSql = "SELECT d.id, d.stock_request_id, d.product_id, d.quantity, p.name FROM `stock_request_detail` d , products p WHERE stock_request_id =" . $row['id'] ." and p.id = d.product_id;";
            $detailResult = $conn->query($detailSql);
            $details = [];
            if ($detailResult->num_rows > 0) {
    
                while ($row1 = $detailResult->fetch_assoc()) {
                     $details[] = $row1;
                }
                $row['details'] = $details;

            }
            $requests[] = $row;
        }
    }




    ?>

    <div class="container my-5">
        <div class="card">
        <div class="card-header">
           <h2> Stock requests</h2>
        </div>
        <div class="card-body"></div>
        <table id="usersTable" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Date</th>
                    <th>Raised By</th>
                    <th>Pending With</th>
                    <th>Status</th>
                    <th>Details</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($requests)) {
                    foreach ($requests as $request) {
                        echo "<tr>";
                        echo "<td>" . $request["id"] . "</td>";
                        echo "<td>" . $request["date"] . "</td>";
                        echo "<td>" . $request["name"] . "</td>";
                        echo "<td>Admin</td>";
                        echo "<td>" . $request["status"] . "</td>";
                        echo "<td>";
                        if (!empty($request["details"])) {
                            echo "<ul>";
                            foreach ($request["details"] as $detail) {
                                echo "<li>Product: " . $detail["name"] . ", Quantity: " . $detail["quantity"] . "</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "No details available";
                        }
                        echo "</td>";
                        echo "<td>";

                        if ($request["status"] === "Pending") {
                            echo "<form method='post' action='handleStockRequest.php' style='display:inline;'>";
                            echo "<input type='hidden' name='id' value='" . $request["id"] . "'>";
                            echo "<input type='hidden' name='method' value='accept'>";
                            echo "<button type='submit' class='btn btn-success btn-sm'>Accept</button>";
                            echo "</form>";
                            echo "<form method='post' action='handleStockRequest.php' style='display:inline;'>";
                            echo "<input type='hidden' name='id' value='" . $request["id"] . "'>";
                            echo "<input type='hidden' name='method' value='reject'>";
                            echo "<button type='submit' class='btn btn-danger btn-sm'>Reject</button>";
                            echo "</form>";
                        
                        } else if ($request["status"] === "Approved") {
                            echo "<button class='btn btn-success btn-sm' disabled>Approved</button>";
                        } else if ($request["status"] === "Rejected") {
                            echo "<button class='btn btn-danger btn-sm' disabled>Rejected</button>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No stock requests found</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <script>
            function handleAction(requestId, action) {
                if (confirm(`Are you sure you want to ${action} this request?`)) {
                    // Perform AJAX request to handle accept/reject
                    $.post(__DIR__.'handleStockRequest.php', { id: requestId, method: action }, function(response) {
                        alert(response.message);
                        location.reload();
                    }, 'json').fail(function() {
                        alert('An error occurred. Please try again.');
                    });
                }
            }
        </script>

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
                "order": [[0, "desc"]], "searching": true
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