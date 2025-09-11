<?php
include 'server.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$user_id) die("User ID is required");

$result = $conn->query("SELECT * FROM user_master WHERE user_id = $user_id");
if (!$result || $result->num_rows === 0) die("User not found");

$user = $result->fetch_assoc();

// Fetch roles and regions
$roles = $conn->query("SELECT * FROM roles ORDER BY role_name");
$regions = $conn->query("SELECT * FROM regions ORDER BY region_name");
?>

<?php include 'header.php'; ?>
<div class="card">
    <div class="card-header">
        Edit User Details
    </div>
    <div class="card-body">
        <form method="POST" action="update-user.php" id="editUserForm">
            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Name</label>
                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" value="<?= htmlspecialchars($user['password']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Mobile</label>
                    <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($user['mobile']) ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Role</label>
                    <select name="role_id" class="form-control" required>
                        <option value="">-- Select Role --</option>
                        <?php while($role = $roles->fetch_assoc()) { ?>
                            <option value="<?= $role['role_id'] ?>" <?= $user['role_id'] == $role['role_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($role['role_name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Region</label>
                    <select name="region_id" id="region" class="form-control" required>
                        <option value="">-- Select Region --</option>
                        <?php while($region = $regions->fetch_assoc()) { ?>
                            <option value="<?= $region['region_id'] ?>" <?= $user['region_id'] == $region['region_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($region['region_name']) ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Area</label>
                    <select name="area_id" id="area" class="form-control" required>
                        <option value="">-- Loading Areas... --</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label>Supervisor</label>
                    <select name="supervisor_id" id="supervisor" class="form-control">
                        <option value="">-- Loading Supervisors... --</option>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Collection</label>
                    <input type="number" step="0.01" name="collection" class="form-control" value="<?= $user['collection'] ?>">
                </div>
                <div class="col-md-6">
                    <label>Commission</label>
                    <input type="number" step="0.01" name="commission" class="form-control" value="<?= $user['commission'] ?>">
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </div>
        </form>
    </div>
    <div class="card mt-4">
        <div class="card-header">
           Assigned Products
        </div>
        <div class="card-body">
            
<table class="table table-bordered">
  <thead>
    <tr>
      <th>#</th>
      <th>Product Name</th>
      <th>SKU</th>
      <th>Assigned Quantity</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $assigned_query = "
      SELECT p.name, p.SKU, up.quantity 
      FROM user_products up
      JOIN products p ON p.id = up.product_id
      WHERE up.user_id = $user_id
    ";

    $assigned_result = $conn->query($assigned_query);
    if ($assigned_result && $assigned_result->num_rows > 0) {
      $i = 1;
      while ($row = $assigned_result->fetch_assoc()) {
        echo "<tr>
                <td>{$i}</td>
                <td>" . htmlspecialchars($row['name']) . "</td>
                <td>" . htmlspecialchars($row['SKU']) . "</td>
                <td>" . (int)$row['quantity'] . "</td>
              </tr>";
        $i++;
      }
    } else {
      echo "  <button type=\"button\" id=\"assignProducts\" class=\"btn btn-secondary ms-2\">Assign All Products</button>
<div id=\"assignStatus\" class=\"mt-2\"></div>";
    }
    ?>
  </tbody>
</table>

          
        </div>
    </div>
</div>

<script>
  $("#assignProducts").click(function () {
    if (confirm("Are you sure you want to assign all products to this user?")) {
      $.post("assign-products.php", { user_id: <?= (int)$user['user_id'] ?> }, function (response) {
        $("#assignStatus").html('<div class="alert alert-info">' + response + '</div>');
        location.reload();
      });
    }
  });
</script>

<script>
$(document).ready(function () {
  function loadAreas(regionId, selectedAreaId = null) {
    if (!regionId) return;
    $.post("get-areas.php", { region_id: regionId, selected_area_id: selectedAreaId }, function (data) {
      $("#area").html(data);
      if (selectedAreaId) $("#area").val(selectedAreaId).change();
    });
  }

  function loadSupervisors(selectedSupervisorId = null) {
    $.post("get-supervisors.php", { selected_supervisor_id: selectedSupervisorId }, function (data) {
      $("#supervisor").html(data);
      if (selectedSupervisorId) $("#supervisor").val(selectedSupervisorId).change();
    });
  }

  // Load on page load
  loadAreas($("#region").val(), <?= (int)$user['area_id'] ?>);
  loadSupervisors( <?= (int)$user['supervisor_id'] ?>);

  // On change
  $("#region").change(function () {
    loadAreas($(this).val());
    $("#supervisor").html('<option value="">-- Select Supervisor --</option>');
  });

 
});
</script>
<?php include 'footer.php'; ?>