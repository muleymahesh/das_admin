<?php
include 'server.php'; // Your DB connection

// Handle month/year filter
$month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// Get all products (for dynamic columns)
$productQuery = $conn->query("SELECT id, SKU as name FROM products ORDER BY id");
$products = [];
while ($p = $productQuery->fetch_assoc()) {
    $products[$p['id']] = $p['name'];
}

// Get all regions
$regionQuery = $conn->query("SELECT region_id, region_name FROM regions ORDER BY region_name");

// Filter form
?>
<?php include 'header.php'; // Include your header file ?>

<div class="container my-4">
    <form method="GET" class="row g-3 align-items-end mb-4">
        <div class="col-auto">
            <label for="month" class="form-label">Month:</label>
            <select name="month" id="month" class="form-select">
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m == $month ? 'selected' : '' ?>>
                        <?= date("F", mktime(0, 0, 0, $m, 1)) ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <label for="year" class="form-label">Year:</label>
            <select name="year" id="year" class="form-select">
                <?php for ($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
                    <option value="<?= $y ?>" <?= $y == $year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

<?php
// Loop through each region and create a table
while ($region = $regionQuery->fetch_assoc()) {
    echo '<div class="mb-5">';

    // Get all areas in this region
    $areaQuery = $conn->prepare("SELECT id, name FROM areas WHERE region_id = ?");
    $areaQuery->bind_param("i", $region['region_id']);
    $areaQuery->execute();
    $areaResult = $areaQuery->get_result();

    if ($areaResult->num_rows == 0) {
        echo '<div class="alert alert-warning">No areas found for this region.</div>';
        echo '</div>';
        continue;
    }

    echo '<div class="table-responsive">';
    echo '<div class="card">';
    echo '<div class="card-header text-center"><h4 class="mb-0">Region: ' . htmlspecialchars($region['region_name']) . '</h4></div>';
    echo '<div class="card-body p-0">';
    echo '<table class="table table-bordered table-striped align-middle mb-0">';
    echo '<thead class="table-light"><tr>
            <th>Area Name</th>';

    // Dynamic product columns
    foreach ($products as $prodName) {
        echo '<th>' . htmlspecialchars($prodName) . '</th>';
    }
    echo '<th>Total Devices Sold</th><th>Total Sales (₹)</th></tr></thead><tbody>';

    // Loop through areas
    while ($area = $areaResult->fetch_assoc()) {
        $areaId = $area['id'];

        // Initialize  product sales
        $productSales = array_fill_keys(array_keys($products), 0);
        $totalDevices = 0;
        $totalSales = 0;

        // Fetch sales data for this area (linked via user -> area)
        $salesQuery = $conn->prepare("
            SELECT sm.product_id, COUNT(sm.product_id) AS qty, SUM(p.off_cash) AS sales_amount
            FROM sales_master sm
            JOIN products p ON sm.product_id = p.id
            JOIN user_master u ON sm.user_id = u.user_id
            WHERE u.area_id = ?
              AND MONTH(sm.sales_date) = ?
              AND YEAR(sm.sales_date) = ?
            GROUP BY sm.product_id
        ");
        $salesQuery->bind_param("iii", $areaId, $month, $year);
        $salesQuery->execute();
        $salesResult = $salesQuery->get_result();

        while ($row = $salesResult->fetch_assoc()) {
            $pid = $row['product_id'];
            $qty = $row['qty'];
            $salesAmt = $row['sales_amount'];

            $productSales[$pid] = $qty;
            $totalDevices += $qty;
            $totalSales += ($qty * ($salesAmt / $qty)); // ensure off_cash used
        }

        // Row output
        echo '<tr>
                <td>' . htmlspecialchars($area['name']) . '</td>';
        foreach ($products as $pid => $pname) {
            echo '<td class="text-center">' . $productSales[$pid] . '</td>';
        }
        echo '<td class="text-center">' . $totalDevices . '</td>
              <td class="text-center">' . number_format($totalSales, 2) . '</td>
              </tr>';
    }
    // Calculate totals for the region
    // Rewind areaResult to fetch again for totals
    $areaResult->data_seek(0);
    $regionProductTotals = array_fill_keys(array_keys($products), 0);
    $regionTotalDevices = 0;
    $regionTotalSales = 0;

    while ($area = $areaResult->fetch_assoc()) {
        $areaId = $area['id'];

        // Fetch sales data for this area
        $salesQuery = $conn->prepare("
            SELECT sm.product_id, COUNT(sm.product_id) AS qty, SUM(p.off_cash) AS sales_amount
            FROM sales_master sm
            JOIN products p ON sm.product_id = p.id
            JOIN user_master u ON sm.user_id = u.user_id
            WHERE u.area_id = ?
              AND MONTH(sm.sales_date) = ?
              AND YEAR(sm.sales_date) = ?
            GROUP BY sm.product_id
        ");
        $salesQuery->bind_param("iii", $areaId, $month, $year);
        $salesQuery->execute();
        $salesResult = $salesQuery->get_result();

        while ($row = $salesResult->fetch_assoc()) {
            $pid = $row['product_id'];
            $qty = $row['qty'];
            $salesAmt = $row['sales_amount'];

            $regionProductTotals[$pid] += $qty;
            $regionTotalDevices += $qty;
            $regionTotalSales += ($qty * ($salesAmt / $qty));
        }
    }

    // Output totals row
    echo '<tr class="table-secondary fw-bold">';
    echo '<td>Total</td>';
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
<?php include 'footer.php'; // Include your footer file ?>
