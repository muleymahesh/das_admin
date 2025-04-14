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
    $query = "SELECT 
                um.name AS name,
                ar.name AS area,
                s.name AS supervisor_name,
                sm.sales_date,
                SUM(sm.total_amount) AS total_sales
              FROM 
                dasgroup_das_db.sales_master sm
              JOIN 
                dasgroup_das_db.user_master um 
              ON 
                sm.user_id = um.user_id
              JOIN 
                dasgroup_das_db.areas ar 
              ON 
                um.area_id = ar.id
              LEFT JOIN 
                dasgroup_das_db.user_master s 
              ON 
                um.supervisor_id = s.user_id
              WHERE 
                um.region_id = " . $_POST['id'] . "
              GROUP BY 
                um.user_id";
    $result = $conn->query($query);
    ?>

    <div class="container my-5">
        <div class="card">
            <div class="card-header">
                <h2> <?php echo htmlspecialchars($_POST['region_name']); ?> Region</h2>
                <p>Sales data for all areas in this region.</p>
            </div>
            <div class="card-body">
                <table id="usersTable" class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>FE Name</th>
                            <th>Area</th>
                            <th>Supervisor Name</th>
                            <th>Date</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (mysqli_num_rows($result) > 0) {
                            // Loop through each record and display in the table
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['name'] . "</td>";
                                echo "<td>" . $row['area'] . "</td>";
                                echo "<td>" . $row['supervisor_name'] . "</td>";
                                echo "<td>" . $row['sales_date'] . "</td>";
                                echo "<td>" . $row['total_sales'] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No sales found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
            <!-- Data Table Section for Region ID 1 -->
            <div class="container my-5">
                <div class="card">
                    <div class="card-header">
                        <h3>Area-wise Sales Chart</h3>
                    </div>
                    <div class="card-body">
                        <div id="areaChartContainer" style="height: 400px; width: 100%;"></div>
                    </div>
                </div>

                <?php
                // Fetch area data for region id 1
                $query = "SELECT areas.name, SUM(sales_master.total_amount) as total_sales FROM sales_master JOIN user_master ON sales_master.user_id = user_master.user_id JOIN areas ON areas.id = user_master.area_id WHERE user_master.region_id =" . $_POST['id'] . " GROUP BY areas.name;";
                $result = mysqli_query($conn, $query);

                $chartData = [];
                // Check if there are any records
                if (mysqli_num_rows($result) > 0) {
                    // Loop through each record and prepare data for the chart
                    while ($row = mysqli_fetch_assoc($result)) {
                        $chartData[] = [
                            'label' => $row['name'],
                            'y' => (float)$row['total_sales'],
                            'indexLabel' => (string)$row['total_sales'] // Display number at the top of the bar
                        ];
                    }
                }
                ?>

                <script>
                    document.addEventListener("DOMContentLoaded", function () {
                        var chartData = <?php echo json_encode($chartData); ?>;

                        var chart = new CanvasJS.Chart("areaChartContainer", {
                            animationEnabled: true,
                            theme: "light2",
                            title: {
                                text: ""
                            },
                            axisY: {
                                title: "Total Sales",
                                prefix: "Rs. "
                            },
                            data: [{
                                type: "column",
                                dataPoints: chartData
                            }]
                        });

                        chart.render();
                    });
                </script>

                <!-- Include CanvasJS Library -->
                <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
            </div>
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