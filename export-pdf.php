<?php
require 'vendor/autoload.php';
require 'SERVER.php'; // Your DB connection

use Mpdf\Mpdf;

$month = $_POST['month'] ?? date('m');
$year = $_POST['year'] ?? date('Y');

// SQL query
$sql = "
  SELECT r.region_name, 
         COUNT(sm.product_id) AS total_items_sold,
         SUM(sm.total_amount) AS total_sales
  FROM sales_master sm
  JOIN user_master u ON sm.user_id = u.user_id
  JOIN regions r ON u.region_id = r.region_id
  WHERE MONTH(sm.sales_date) = ? AND YEAR(sm.sales_date) = ?
  GROUP BY r.region_id
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $month, $year);
$stmt->execute();
$result = $stmt->get_result();

$html = '<h3 style="text-align:center;">Region-wise Monthly Sales Report</h3>';
$html .= '<table border="1" cellspacing="0" cellpadding="8" width="100%">
            <tr>
              <th>Region</th>
              <th>Total Items Sold</th>
              <th>Total Sales (₹)</th>
            </tr>';

while ($row = $result->fetch_assoc()) {
  $html .= "<tr>
              <td>{$row['region_name']}</td>
              <td>{$row['total_items_sold']}</td>
              <td>" . number_format($row['total_sales'], 2) . "</td>
            </tr>";
}

$html .= '</table>';

$mpdf = new Mpdf();
$mpdf->WriteHTML($html);
$mpdf->Output("region-sales-report-$month-$year.pdf", 'I');
