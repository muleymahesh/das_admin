<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);   
require 'server.php'; // DB connection

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$where = '';
$params = [];
$types = '';

if ($start_date && $end_date) {
    $where = "AND s.sales_date BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= 'ss';
} elseif ($start_date) {
    $where = "AND s.sales_date >= ?";
    $params[] = $start_date;
    $types .= 's';
} elseif ($end_date) {
    $where = "AND s.sales_date <= ?";
    $params[] = $end_date;
    $types .= 's';
}

$sql = "
SELECT 
    c.customer_id,
    c.name AS customer_name,
    c.mobile,
    c.email,
    s.sales_date,
    c.address,
    c.pincode,
    c.type,
    c.serialNo,
    u.name AS user_name,
    a.name AS area_name,
    r.region_name,
    COALESCE(SUM(p.off_cash), 0) AS total_purchase
FROM customers c
JOIN user_master u ON c.user_id = u.user_id
JOIN areas a ON u.area_id = a.id
JOIN regions r ON a.region_id = r.region_id
LEFT JOIN sales_master s ON c.customer_id = s.customer_id
LEFT JOIN products p ON s.product_id = p.id
WHERE 1=1
$where
GROUP BY c.customer_id
ORDER BY r.region_name, a.name, c.name
";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$customers = [];
while ($row = $result->fetch_assoc()) {
    $customers[] = $row;
}
// $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');

// $sql = "
// SELECT 
//     c.customer_id,
//     c.name AS customer_name,
//     c.mobile,
//     c.email,
//     c.address,
//     c.pincode,
//     c.type,
//     c.serialNo,
//     u.name AS user_name,
//     a.name AS area_name,
//     r.region_name,
//     COALESCE(SUM(p.off_cash), 0) AS total_purchase
// FROM customers c
// JOIN user_master u ON c.user_id = u.user_id
// JOIN areas a ON u.area_id = a.id
// JOIN regions r ON a.region_id = r.region_id
// LEFT JOIN sales_master s ON c.customer_id = s.customer_id
// LEFT JOIN products p ON s.product_id = p.id

// GROUP BY c.customer_id
// ORDER BY r.region_name, a.name, c.name
// ";

// $stmt = $conn->prepare($sql);
// $stmt->execute();
// $result = $stmt->get_result();
// $customers = [];
// while ($row = $result->fetch_assoc()) {
//     $customers[] = $row;
// }

include 'header.php'; // Include header if needed
?>

<div class="container" style="width:90%;">
    <h2 class="mb-4">
        Customer List Report
        <?php if ($start_date && $end_date): ?>
            - <?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?>
        <?php elseif ($start_date): ?>
            - From <?= htmlspecialchars($start_date) ?>
        <?php elseif ($end_date): ?>
            - Up to <?= htmlspecialchars($end_date) ?>
        <?php else: ?>
            - All Dates
        <?php endif; ?>
    </h2>

    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="<?= isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : '' ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="<?= isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : '' ?>">
        </div>
        <div class="col-md-3 align-self-end">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <div class="table-responsive">
        <table id="customersTable" class="table table-striped table-bordered table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Region</th>
                    <th>Area</th>
                    <th>Customer Name</th>
                    <th>Mobile</th>
                    <th>Date</th>
                    <th>Address</th>
                    <th>Pincode</th>
                    <th>Type</th>
                    <th>Serial No</th>
                    <th>Salesperson</th>
                    <th>Total Purchase (₹)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($customers)): ?>
                    <?php foreach ($customers as $row): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['sales_date']) ?></td>
                        <td><?= htmlspecialchars($row['region_name']) ?></td>
                        <td><?= htmlspecialchars($row['area_name']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['mobile']) ?></td>
                        <td><?= htmlspecialchars($row['address']) ?></td>
                        <td><?= htmlspecialchars($row['pincode']) ?></td>
                        <td><?= htmlspecialchars($row['type']) ?></td>
                        <td><?= htmlspecialchars($row['serialNo']) ?></td>
                        <td><?= htmlspecialchars($row['user_name']) ?></td>
                        <td><?= number_format($row['total_purchase'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center">No customers found for the selected filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
$(document).ready(function() {
    $('#customersTable').DataTable({
        "order": [[0, "asc"]], // Default sorting by region name
        "pageLength": 25 // Show 25 records per page
    });
});
</script>
<?php include 'footer.php'; // Include footer if needed ?>
