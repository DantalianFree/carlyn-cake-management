<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$order_id = $_GET['order_id'] ?? 0;
$query = "SELECT * FROM Orders WHERE order_id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: user-dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/order_confirmation.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="user-dashboard.php">Carlyn Cake Shop</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="order.php">Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Order Confirmation</h2>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Order #<?php echo $order['order_id']; ?></h5>
                <p class="card-text">Total Price: $<?php echo number_format($order['total_price'], 2); ?></p>
                <p class="card-text">Delivery Fee: $<?php echo number_format($order['delivery_fee'], 2); ?></p>
                <p class="card-text">Status: <?php echo $order['order_status']; ?></p>
                <p class="card-text">Pickup/Delivery: <?php echo $order['pickup_or_delivery']; ?></p>
                <p class="card-text">Contact Number: <?php echo $order['contact_number']; ?></p>
                <?php if ($order['pickup_or_delivery'] === 'Delivery'): ?>
                    <p class="card-text">Delivery Address: <?php echo $order['delivery_address']; ?></p>
                <?php endif; ?>
                <p class="card-text">Delivery/Pickup Date: <?php echo $order['delivery_date']; ?></p>
            </div>
        </div>
        <div class="text-center mt-4">
            <a href="user-dashboard.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>