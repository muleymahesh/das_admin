<?php
include 'server.php';

$area_id = (int)$_POST['area_id'];
$result = $conn->query("SELECT user_id, name FROM user_master WHERE role_id > 1 ORDER BY name");

echo '<option value="">-- Select Supervisor --</option>';
while ($row = $result->fetch_assoc()) {
  echo "<option value='{$row['user_id']}'>" . htmlspecialchars($row['name']) . "</option>";
}
