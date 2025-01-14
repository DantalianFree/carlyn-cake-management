<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../user/user-login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pickup_or_delivery = $_POST['pickup_or_delivery'];
    $contact_number = $_POST['contact_number'];
    $delivery_address = $_POST['delivery_address'] ?? null;
    $delivery_date = $_POST['delivery_date'];
    $payment_method = $_POST['payment_method'];

    // Calculate total price (you may want to implement a more sophisticated pricing system)
    $total_price = 0;
    foreach ($_SESSION['cart'] as $item) {
        $query = "SELECT p.base_price FROM Products p WHERE p.product_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $item['id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $total_price += $product['base_price'];
    }

    // Add delivery fee if applicable
    $delivery_fee = ($pickup_or_delivery === 'Delivery') ? 50.00 : 0;
    $total_price += $delivery_fee;

    // Create order
    $query = "INSERT INTO Orders (user_id, total_price, delivery_fee, pickup_or_delivery, contact_number, delivery_address, delivery_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iddssss", $_SESSION['user_id'], $total_price, $delivery_fee, $pickup_or_delivery, $contact_number, $delivery_address, $delivery_date);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    // Create sale record
    $query = "INSERT INTO Sales (order_id, sale_amount, payment_method) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ids", $order_id, $total_price, $payment_method);
    $stmt->execute();

    // Clear cart
    unset($_SESSION['cart']);

    header("Location: ../ordering/order_confirmation.php?order_id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../user/user-dashboard.php">Carlyn Cake Shop</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../user/my_orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Checkout</h2>
        <a href="cart.php" class="btn btn-secondary mb-3">Back to Cart</a>
        <form action="checkout.php" method="post">
            <div class="mb-3">
                <label for="pickup_or_delivery" class="form-label">Pickup or Delivery</label>
                <select class="form-select" id="pickup_or_delivery" name="pickup_or_delivery" required>
                    <option value="Pickup">Pickup</option>
                    <option value="Delivery">Delivery</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="contact_number" class="form-label">Contact Number</label>
                <input type="tel" class="form-control" id="contact_number" name="contact_number" maxlength="11" required>
            </div>
            <div class="mb-3" id="delivery_address_container" style="display: none;">
                <label for="delivery_address" class="form-label">Delivery Address</label>
                <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="delivery_date" class="form-label">Delivery/Pickup Date</label>
                <input type="date" class="form-control" id="delivery_date" name="delivery_date" required>
            </div>
            <div class="mb-3">
                <label for="payment_method" class="form-label">Payment Method</label>
                <select class="form-select" id="payment_method" name="payment_method" required>
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Place Order</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('pickup_or_delivery').addEventListener('change', function() {
            var deliveryAddressContainer = document.getElementById('delivery_address_container');
            if (this.value === 'Delivery') {
                deliveryAddressContainer.style.display = 'block';
            } else {
                deliveryAddressContainer.style.display = 'none';
            }
        });
    </script>
</body>
</html>