<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
// Check if user is logged in   
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}


// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get the action and request ID from the POST data
    $action = $_POST['method'] ?? '';
    $requestId = $_POST['id'] ?? '';

    // Validate input
    if (empty($action) || empty($requestId)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }

    // Process the action
    if ($action === 'accept') {
        // Logic to approve the stock request
     approveStockRequest($requestId);
       
    } elseif ($action === 'reject') {
        // Logic to reject the stock request
        rejectStockRequest($requestId);
       
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
    exit;
}

// Function to approve stock request
function approveStockRequest($requestId) {
    require_once 'server.php';
    $id = $_POST['id'];
    $userId = $_POST['user_id'];

    // $sql = "UPDATE stock_request_master SET status = 'Approved' WHERE id = '$id'";
    $id = $_POST['id'];

    // Fetch the stock request row
    $sql = "SELECT * FROM stock_request_master WHERE id = '$id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $stockRequest = $result->fetch_assoc();
        $pendingWith = $stockRequest['pending_with'];
        $raisedBy = $stockRequest['raised_by'];

        // Fetch the details of the stock request
        $detailSql = "SELECT * FROM stock_request_detail WHERE stock_request_id = '$id'";
        $detailResult = $conn->query($detailSql);

        if ($detailResult->num_rows > 0) {
            while ($detailRow = $detailResult->fetch_assoc()) {
                $productId = $detailRow['product_id'];
                $requestedQuantity = $detailRow['quantity'];

                // Check available quantity for the product
                $quantitySql = "SELECT quantity FROM user_products WHERE user_id = '$pendingWith' AND product_id = '$productId'";
                $quantityResult = $conn->query($quantitySql);

                if ($quantityResult->num_rows > 0) {
                    $quantityRow = $quantityResult->fetch_assoc();
                    $availableQuantity = $quantityRow['quantity'];

                    // Allocate the available quantity
                    $allocatedQuantity = min($requestedQuantity, $availableQuantity);

                    // Update the quantity for the raised_by user
                    $conn->query("UPDATE user_products SET quantity = quantity + $allocatedQuantity WHERE user_id = '$raisedBy' AND product_id = '$productId'");

                    // Update the quantity for the pending_with user
                    $conn->query("UPDATE user_products SET quantity = quantity - $allocatedQuantity WHERE user_id = '$pendingWith' AND product_id = '$productId'");
                }
            }

            $sql = "UPDATE stock_request_master SET status = 'Approved' WHERE id = '$id'";
            if ($conn->query($sql) === TRUE) {
                header('Location: stock_requests.php');
            } else {
                echo json_encode(["error" => "Error: " . $sql . " - " ]);
            }
        } else {
            echo json_encode(["error" => "No stock request details found"]);
        }
    } else {
        echo json_encode(["error" => "Stock request not found"]);
    }

    }


// Function to reject stock request
function rejectStockRequest($requestId) {
    require_once 'server.php';
    $id = $data['id'];
         $sql = "UPDATE stock_request_master SET status = 'Rejected' WHERE id = '$id'   ";

        if ($conn->query($sql) === TRUE) {
            header('Location: stock_requests.php');
        } else {
            echo json_encode(["error" => "Error: " . $sql . " - " ]);
        }

}
?>