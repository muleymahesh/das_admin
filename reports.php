<?php
include_once 'server.php'; // DB connection

// Handle date range filter
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date   = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

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
$(document).ready(function() {
    // Apply DataTables to all region tables
 
});
</script>

<?php include 'footer.php'; ?>
