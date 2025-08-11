<?php include 'headercomposer init.php'; ?>

    <!-- Content Section -->

    <?php
    require_once 'server.php';

    // Fetch data from user_master
    
    $id = $_GET['id'];
    $data = $conn->query("SELECT * FROM products WHERE id = $id")->fetch_assoc();
    $categories = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");

    ?>

    <div class="container my-5">
        <div class="card">
            <div class="card-header">
                <h2>Edit Product</h2>

            </div>
            <div class="card-body">
                <form method="post" action="save_product.php">
                    <input type="hidden" name="id" value="<?= $data['id'] ?>">
                    <?php
                    function old($key, $default = '')
                    {
                        global $data;
                        return $data[$key] ?? $default;
                    }
                    ?>

                    <div class="mb-3">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="<?= old('name') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>SKU</label>
                        <input type="text" name="SKU" class="form-control" value="<?= old('SKU', '1') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Category</label>
                        <select name="cat_id" class="form-control" required>
                            <option value="">-- Select Category --</option>
                            <?php while ($cat = $categories->fetch_assoc()) { ?>
                                <option value="<?= $cat['category_id'] ?>" <?= old('cat_id') == $cat['category_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['category_name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Price</label>
                        <input type="text" name="Price" class="form-control" value="<?= old('Price') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label>Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="<?= old('quantity') ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label>Commission</label>
                        <input type="number" name="commission" class="form-control" value="<?= old('commission', 0) ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label>Off Cash</label>
                        <input type="number" name="off_cash" class="form-control" value="<?= old('off_cash') ?>"
                            required>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="Description" class="form-control"><?= old('Description') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="1" <?= old('status') == 1 ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= old('status') == 0 ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-warning" name="update">Update Product</button>
                    <a href="index.php" class="btn btn-secondary">Back</a>
                </form>
            </div>
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

  <?php include 'footer.php'; ?>