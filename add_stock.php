<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database connection
require_once 'server.php';
session_start();
// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}


// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $stock = $_POST['stock'];

    if (!empty($product_id) && !empty($stock)) {
       echo $update_query =  "UPDATE user_products SET quantity = quantity + $stock WHERE user_id = ".$_SESSION['user_id']." AND product_id = $product_id;";
      
        $stmt = $conn->prepare($update_query);
        // $stmt->bind_param('iii', $stock,$_SESSION['user_id'], $product_id);
        if ($stmt->execute()) {
            $message = "Stock updated successfully!";
        } else {
            $message = "Failed to update stock.";
        }
        $stmt->close();
    } else {
        $message = "Please fill in all fields.";
    }
}
// Fetch products from the database
$products = [];
 $query = "SELECT u.product_id,u.user_id, u.quantity, p.Price, p.name, p.commission, p.off_cash as offCash, p.SKU, p.cat_id FROM `user_products` u, `products` p where u.user_id = ".$_SESSION['user_id']." AND u.product_id=p.id AND p.status = 1;";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
}
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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
            <ul class="navbar-nav ml-auto"></ul>
            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="products.php">Inventory</a>  <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="users.php">Users</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
            </ul>
        </div>
    </nav>
    <div class="container my-5">
        <div class="card"></div>
        <div class="card-header">
           <h2> Add Stock</h2>
        </div>
        <div class="card-body"></div>
        <?php if (!empty($message)): ?>
            <p><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="form-group">
                <label for="product_id">Product</label>
                <select name="product_id" id="product_id" required>
                    <option value="">Select a product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['name'])."(". $product['quantity'].")"; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" name="stock" id="stock" min="1" required>
            </div>
            <button type="submit">Add Stock</button>
        </form>
    </div>
</body>
</html>