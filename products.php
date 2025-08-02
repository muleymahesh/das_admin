<?php include 'header.php'; ?>


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
             <a href="add_product.php" class="btn btn-primary mb-3">Add Product</a>

            <a href="add_stock.php" class="btn btn-primary mb-3">Add Stock</a>
            <a href="stock_requests.php" class="btn btn-primary mb-3">Pending Stock requests</a>
            <a href="display_stock_region.php" class="btn btn-primary mb-3">Region-wise Stock</a>
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
          <a href="edit_product.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
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