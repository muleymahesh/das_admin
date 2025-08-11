<?php
// DB Connection
include 'server.php';

$region_id = isset($_GET['region_id']) ? (int)$_GET['region_id'] : 0;
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fetch Region Name
$region_name = '';
$res = $conn->query("SELECT region_name FROM regions WHERE region_id = $region_id");
if ($res && $row = $res->fetch_assoc()) {
    $region_name = $row['region_name'];
}

// Product-wise sales for region
$sql = "
    SELECT p.name AS product_name,
           SUM(1) AS total_qty,
           p.off_cash,
           SUM(1 * p.off_cash) AS total_sales
    FROM sales_master sm
    INNER JOIN customers c ON sm.customer_id = c.customer_id
    INNER JOIN products p ON sm.product_id = p.id
    WHERE c.region_id = ? AND DATE_FORMAT(sm.sales_date, '%Y-%m') = ?
    GROUP BY p.id, p.name, p.off_cash
    ORDER BY total_sales DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $region_id, $month);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo $region_name; ?> - Sales Details</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
    </style>
</head>
<body>
<h2>Sales Details - <?php echo htmlspecialchars($region_name); ?> (<?php echo $month; ?>)</h2>
<a href="region_sales_report.php?month=<?php echo $month; ?>">← Back to Report</a>
<br><br>

<table>
    <tr>
        <th>Product Name</th>
        <th>Off Cash (₹)</th>
        <th>Total Quantity Sold</th>
        <th>Total Sales (₹)</th>
    </tr>
    <?php while($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['product_name']); ?></td>
            <td><?php echo number_format($row['off_cash'], 2); ?></td>
            <td><?php echo (int)$row['total_qty']; ?></td>
            <td><?php echo number_format($row['total_sales'], 2); ?></td>
        </tr>
    <?php } ?>
</table>

</body>
</html>
