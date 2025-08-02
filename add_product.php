<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Check if user is logged in   
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bootstrap Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Optional Bootstrap Theme -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap-theme.min.css" rel="stylesheet">
    <style>
        a {
            color:rgb(249, 250, 252) !important;
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
            <ul class="navbar-nav ml-auto">
            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">
                <span class="mr-1"><i class="fas fa-tachometer-alt"></i></span>Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="products.php">
                <span class="mr-1"><i class="fas fa-boxes"></i></span>Inventory
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#">
                <span class="mr-1"><i class="fas fa-users"></i></span>Users <span class="sr-only">(current)</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                <span class="mr-1"><i class="fas fa-sign-out-alt"></i></span>Logout
                </a>
            </li>
            </ul>
        </div>
        <!-- Font Awesome CDN -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    </nav>

    <!-- Content Section -->

    <?php
    require_once 'server.php';

    // Fetch data from user_master
    $sql = "SELECT 
  p.*, 
  COALESCE(SUM(up.quantity), 0) AS admin_quantity
FROM products p LEFT JOIN user_products up  ON p.id = up.product_id AND up.user_id = ".$_SESSION['user_id']." AND p.status = 1 GROUP BY p.id ORDER BY p.id DESC";
    $result = $conn->query($sql);
    ?>

    <div class="container my-5">
        <div class="card">
        <div class="card-header">
           <h2> Product List</h2>
             <a href="add.php" class="btn btn-primary mb-3">Add Product</a>

            <a href="add_stock.php" class="btn btn-danger">Add Stock</a>
            <a href="stock_requests.php" class="btn btn-danger">Pending Stock requests</a>
            <a href="display_stock_region.php" class="btn btn-danger">Region-wise Stock</a>
            </div>
        <div class="card-body"></div>
        <table id="usersTable" class="table table-striped table-bordered">
            <thead>
    <tr>
      <th>ID</th><th>Name</th><th>SKU</th><th>Price</th>
      <th>Stock Qty</th><th>Admin Qty</th><th>Status</th><th>Action</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = $result->fetch_assoc()) { ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['name'] ?></td>
        <td><?= $row['SKU'] ?></td>
        <td><?= $row['Price'] ?></td>
        <td><?= $row['quantity'] ?></td>
        <td><?= $row['admin_quantity'] ?></td> <!-- Quantity for admin -->
        <td><?= $row['status'] ? 'Active' : 'Inactive' ?></td>
        <td>
          <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
        </td>
      </tr>
    <?php } ?>
  </tbody>
        </table>

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