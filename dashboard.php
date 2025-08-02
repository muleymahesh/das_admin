<!DOCTYPE html>
<?php include 'header.php'; ?>

    <!-- Cards Section -->
    <div class="container my-4">
        <div class="row">
            <!-- Number of Products Card -->
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Number of Products</div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                            include 'server.php';
                            $productQuery = "SELECT COUNT(*) as product_count FROM products";
                            $productResult = mysqli_query($conn, $productQuery);
                            $productRow = mysqli_fetch_assoc($productResult);
                            echo $productRow['product_count'];
                            ?>
                        </h5>
                    </div>
                </div>
            </div>
            <!-- Number of Regions Card -->
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Number of Regions</div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                            $regionQuery = "SELECT COUNT(*) as region_count FROM regions";
                            $regionResult = mysqli_query($conn, $regionQuery);
                            $regionRow = mysqli_fetch_assoc($regionResult);
                            echo $regionRow['region_count'];
                            ?>
                        </h5>
                    </div>
                </div>
            </div>
            <!-- Total Sales Card -->
            <div class="col-md-4">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">Total Sales</div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <?php
                            $salesQuery = "SELECT COALESCE(SUM(total_amount), 0) as total_sales FROM sales_master";
                            $salesResult = mysqli_query($conn, $salesQuery);
                            $salesRow = mysqli_fetch_assoc($salesResult);
                            echo "Rs. " . number_format($salesRow['total_sales'], 2);
                            ?>
                        </h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Data Table Section -->
    <div class="container my-5">
        <div class="card">
            <div class="card-header">
                <h2>Regions wise sales report </h2>
            </div>
            <div class="card-body">
                <table id="regionsTable" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Region Name</th>
                            <th>Total Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Include the server.php file to fetch data
                        include 'server.php';

                        // Fetch regions data
                        $query = "SELECT regions.region_id, regions.region_name, 
                          COALESCE(SUM(sales_master.total_amount), 0) as total_sales 
                          FROM regions 
                          LEFT JOIN user_master ON regions.region_id = user_master.region_id 
                          LEFT JOIN sales_master ON user_master.user_id = sales_master.user_id 
                          GROUP BY regions.region_id, regions.region_name";
                        $result = mysqli_query($conn, $query);

                        // Check if there are any records
                        if (mysqli_num_rows($result) > 0) {
                            // Loop through each record and display in the table
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                echo "<td>
                            <form method='POST' action='regionwise.php'>
                                <input type='hidden' name='region_name' value='" . $row['region_name'] . "'>
                                <input type='hidden' name='id' value='" . $row['region_id'] . "'>
                                <button type='submit' class='btn btn-link'>" . $row['region_name'] . "</button>
                            </form>
                              </td>";
                                echo "<td>" . $row['total_sales'] . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2'>No regions found</td></tr>";
                        }

                        // Close the database connection
                        mysqli_close($conn);
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


    <!-- <script>
        $(document).ready(function() {
            $('#areaTable').DataTable();
        });
    </script> -->

    <!-- DataTables JS and CSS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <!-- <script>
        $(document).ready(function() {
            $('#regionsTable').DataTable();
        });
    </script>  Footer -->
    <script>
        $(document).ready(function () {
            $('#areaTable').DataTable();
            $('#regionsTable').DataTable();
            $('#dailySalesTable').DataTable({
                "paging": true,
                "searching": true
            });
        });
    </script>
    <footer class="bg-danger text-center py-4">
        <p>&copy; 2023 My Site. All Rights Reserved.</p>
    </footer>

    <!-- Bootstrap JS and dependencies (jQuery and Popper.js) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>