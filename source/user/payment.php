<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
include("../conn.php");

// Check if an order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: order-cakes.php");
    exit();
}

$order_id = $_GET['order_id'];

// Fetch order details
$sql = "SELECT * FROM Orders WHERE order_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: order-cakes.php");
    exit();
}

// Process payment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['payment_method'])) {
    $payment_method = $_POST['payment_method'];
    
    // Start transaction
    $conn->begin_transaction();

    try {
        // Update order status
        $sql = "UPDATE Orders SET order_status = 'Pending' WHERE order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // Create sale record
        $sql = "INSERT INTO Sales (order_id, sale_amount, payment_method) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ids", $order_id, $order['total_price'], $payment_method);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // Redirect to a thank you page
        header("Location: thank-you.php?order_id=" . $order_id);
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $error_message = "An error occurred while processing your payment. Please try again.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/order-cakes.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .payment-form {
            max-width: 500px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Carlyn Cake Shop</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="payment-form">
            <h2 class="text-center mb-4" style="color: #d6336c;">Payment</h2>
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="total" class="form-label">Total Amount</label>
                    <input type="text" class="form-control" id="total" value="$<?php echo number_format($order['total_price'], 2); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Method</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="cash" value="Cash" required>
                        <label class="form-check-label" for="cash">Cash</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="payment_method" id="gcash" value="GCash" required>
                        <label class="form-check-label" for="gcash">GCash</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100" style="background-color: #d6336c; border-color: #d6336c;">Complete Payment</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

