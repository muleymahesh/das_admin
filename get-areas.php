<?php
include 'server.php';

$region_id = (int)$_POST['region_id'];
$result = $conn->query("SELECT * FROM areas ORDER BY name");

echo '<option value="">-- Select Area --</option>';
while ($row = $result->fetch_assoc()) {
  echo "<option value='{$row['id']}'>" . htmlspecialchars($row['name']) . "</option>";
}
