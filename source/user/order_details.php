<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../user/user-login.php");
    exit();
}

$order_id = $_GET['id'] ?? 0;
$user_id = $_SESSION['user_id'];

// Fetch order details
$query = "SELECT o.*, s.payment_method 
          FROM Orders o 
          LEFT JOIN Sales s ON o.order_id = s.order_id 
          WHERE o.order_id = ? AND o.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header("Location: my_orders.php");
    exit();
}

// Fetch order items (customizations)
$query = "SELECT c.*, p.name AS product_name, p.base_price 
          FROM Customizations c 
          JOIN Products p ON c.product_id = p.product_id 
          WHERE c.user_id = ? AND c.created_at <= ? 
          ORDER BY c.created_at DESC LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $user_id, $order['order_date']);
$stmt->execute();
$items_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/my_orders.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="user-dashboard.php">Carlyn Cake Shop</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../ordering/order.php">Order</a>
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
        <h2 class="text-center mb-4">Order Details</h2>
        <a href="my_orders.php" class="btn btn-secondary mb-3">Back to My Orders</a>
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Order #<?php echo $order['order_id']; ?></h5>
                <p class="card-text">Date: <?php echo $order['order_date']; ?></p>
                <p class="card-text">Status: <?php echo $order['order_status']; ?></p>
                <p class="card-text">Total Price: ₱<?php echo number_format($order['total_price'], 2); ?></p>
                <p class="card-text">Delivery Fee: ₱<?php echo number_format($order['delivery_fee'], 2); ?></p>
                <p class="card-text">Pickup/Delivery: <?php echo $order['pickup_or_delivery']; ?></p>
                <p class="card-text">Contact Number: <?php echo $order['contact_number']; ?></p>
                <?php if ($order['pickup_or_delivery'] === 'Delivery'): ?>
                    <p class="card-text">Delivery Address: <?php echo $order['delivery_address']; ?></p>
                <?php endif; ?>
                <p class="card-text">Delivery/Pickup Date: <?php echo $order['delivery_date']; ?></p>
                <p class="card-text">Payment Method: <?php echo $order['payment_method']; ?></p>
            </div>
        </div>

        <h3 class="mb-3">Order Items</h3>
        <?php if ($items_result->num_rows > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Customizations</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $items_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td>₱<?php echo number_format($item['base_price'], 2); ?></td>
                            <td>
                                Tiers: <?php echo $item['tiers']; ?><br>
                                Size: <?php echo $item['size_in_inches']; ?> inches<br>
                                Flavor: <?php echo $item['flavor']; ?><br>
                                Message: <?php echo htmlspecialchars($item['message']); ?><br>
                                Instructions: <?php echo htmlspecialchars($item['specific_instructions']); ?><br>
                                Add-ons: <?php echo htmlspecialchars($item['add_ons']); ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No customizations found for this order.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>