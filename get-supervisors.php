<?php
include 'server.php';

$result = $conn->query("SELECT user_id, name FROM user_master WHERE role_id > 1 ORDER BY name");

echo '<option value="">-- Select Supervisor --</option>';
while ($row = $result->fetch_assoc()) {
  echo "<option value='{$row['user_id']}'>" . htmlspecialchars($row['name']) . "</option>";
}
