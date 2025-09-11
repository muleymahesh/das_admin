<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'server.php'; // DB connection

// Handle date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// Fetch all products
$productQuery = $conn->query("SELECT id, SKU AS name FROM products ORDER BY id");
$products = [];
while ($p = $productQuery->fetch_assoc()) {
    $products[$p['id']] = $p['name'];
}

// Fetch all regions
$regionQuery = $conn->query("SELECT region_id, region_name FROM regions ORDER BY region_name");

include 'header.php';
?>
<div class="container my-4">
    <div class="d-flex justify-content-end mb-3">
        <a href="customers.php" class="btn btn-success me-2">View Customers List</a>
        <!-- Add more buttons here if needed -->
    </div>
</div>
<div class="container my-4">
    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-md-3">
            <label>Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        </div>
        <div class="col-md-3">
            <label>End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
    <button onclick="window.print()">Print report</button>

</div>

<div class="container my-4">
    <?php
    while ($region = $regionQuery->fetch_assoc()) {
        echo '<div class="mb-5">';
        $regionId = $region['region_id'];

        // Fetch all areas in this region
        $areaQuery = $conn->prepare("SELECT id, name FROM areas WHERE region_id = ?");
        $areaQuery->bind_param("i", $regionId);
        $areaQuery->execute();
        $areaResult = $areaQuery->get_result();

        if ($areaResult->num_rows == 0) {
            echo '<div class="alert alert-warning">No areas found for this region.</div></div>';
            continue;
        }

        echo '<div class="table-responsive">';
        echo '<div class="card">';
        echo '<div class="card-header text-center"><h4 class="mb-0">Region: ' . htmlspecialchars($region['region_name']) . '</h4></div>';
        echo '<div class="card-body p-0">';
        echo '<table class="table table-bordered table-striped align-middle mb-0 region-table">';
        echo '<thead class="table-light"><tr><th>Area Name</th>';
        foreach ($products as $prodName) {
            echo '<th>' . htmlspecialchars($prodName) . '</th>';
        }
        echo '<th>Total Devices Sold</th><th>Total Sales (₹)</th></tr></thead><tbody>';

        // Totals for region
        $regionProductTotals = array_fill_keys(array_keys($products), 0);
        $regionTotalDevices = 0;
        $regionTotalSales = 0;

        while ($area = $areaResult->fetch_assoc()) {
            $areaId = $area['id'];
            $productSales = array_fill_keys(array_keys($products), 0);
            $totalDevices = 0;
            $totalSales = 0;

            // Fetch sales for this area in date range
            $salesQuery = $conn->prepare("
            SELECT sm.product_id, COUNT(sm.product_id) AS qty, SUM(p.off_cash) AS sales_amount
            FROM sales_master sm
            JOIN products p ON sm.product_id = p.id
            JOIN user_master u ON sm.user_id = u.user_id
            WHERE u.area_id = ?
              AND sm.sales_date BETWEEN ? AND ?
            GROUP BY sm.product_id
        ");
            $salesQuery->bind_param("iss", $areaId, $start_date, $end_date);
            $salesQuery->execute();
            $salesResult = $salesQuery->get_result();

            while ($row = $salesResult->fetch_assoc()) {
                $pid = $row['product_id'];
                $productSales[$pid] = $row['qty'];
                $totalDevices += $row['qty'];
                $totalSales += $row['sales_amount'];
                $regionProductTotals[$pid] += $row['qty'];
            }

            $regionTotalDevices += $totalDevices;
            $regionTotalSales += $totalSales;

            // Output area row
            echo '<tr><td>' . htmlspecialchars($area['name']) . '</td>';
            foreach ($products as $pid => $pname) {
                echo '<td class="text-center">' . $productSales[$pid] . '</td>';
            }
            echo '<td class="text-center">' . $totalDevices . '</td>';
            echo '<td class="text-center">' . number_format($totalSales, 2) . '</td>';
            echo '</tr>';
        }

        // Region totals row
        echo '<tr class="table-secondary fw-bold"><td>Total</td>';
        foreach ($products as $pid => $pname) {
            echo '<td class="text-center">' . $regionProductTotals[$pid] . '</td>';
        }
        echo '<td class="text-center">' . $regionTotalDevices . '</td>';
        echo '<td class="text-center">' . number_format($regionTotalSales, 2) . '</td>';
        echo '</tr>';

        echo '</tbody></table></div></div></div></div>';
    }
    ?>
</div>

<!-- Include DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function () {
        // Apply DataTables to all region tables

    });
</script>

<?php include 'footer.php'; ?>

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
                        'y' => (float) $row['total_sales'],
                        'indexLabel' => (string) $row['total_sales'] // Display number at the top of the bar
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