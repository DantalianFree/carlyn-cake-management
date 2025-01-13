<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
include("../conn.php");

// Check if a product_id or customization_id is provided
if (!isset($_POST['product_id']) && !isset($_GET['customization_id'])) {
    header("Location: order-cakes.php");
    exit();
}

if (isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $sql = "SELECT * FROM Products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $total_price = $product['base_price'];
} else {
    $customization_id = $_GET['customization_id'];
    $sql = "SELECT c.*, p.name, p.type, p.base_price FROM Customizations c JOIN Products p ON c.product_id = p.product_id WHERE c.customization_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $customization_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $customization = $result->fetch_assoc();
    $total_price = $customization['base_price'] * $customization['tiers']; // Simple price calculation, adjust as needed
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_order'])) {
    $pickup_or_delivery = $_POST['pickup_or_delivery'];
    $contact_number = $_POST['contact_number'];
    $delivery_address = isset($_POST['delivery_address']) ? $_POST['delivery_address'] : null;
    $delivery_date = isset($_POST['delivery_date']) ? $_POST['delivery_date'] : null;

    // Insert order into database
    $sql = "INSERT INTO Orders (user_id, total_price, pickup_or_delivery, contact_number, delivery_address, delivery_date) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idssss", $_SESSION['user_id'], $total_price, $pickup_or_delivery, $contact_number, $delivery_address, $delivery_date);
    $stmt->execute();

    // Redirect to payment page
    header("Location: payment.php?order_id=" . $conn->insert_id);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Order Summary</h1>
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Order Details</h5>
                        <?php if (isset($product)): ?>
                            <p><strong>Product:</strong> <?php echo htmlspecialchars($product['name']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($product['type']); ?></p>
                            <p><strong>Price:</strong> $<?php echo number_format($total_price, 2); ?></p>
                        <?php elseif (isset($customization)): ?>
                            <p><strong>Product:</strong> <?php echo htmlspecialchars($customization['name']); ?></p>
                            <p><strong>Type:</strong> <?php echo htmlspecialchars($customization['type']); ?></p>
                            <p><strong>Tiers:</strong> <?php echo htmlspecialchars($customization['tiers']); ?></p>
                            <p><strong>Size:</strong> <?php echo htmlspecialchars($customization['size_in_inches']); ?> inches</p>
                            <p><strong>Flavor:</strong> <?php echo htmlspecialchars($customization['flavor']); ?></p>
                            <p><strong>Message:</strong> <?php echo htmlspecialchars($customization['message']); ?></p>
                            <p><strong>Instructions:</strong> <?php echo htmlspecialchars($customization['specific_instructions']); ?></p>
                            <p><strong>Add-ons:</strong> <?php echo htmlspecialchars($customization['add_ons']); ?></p>
                            <p><strong>Total Price:</strong> $<?php echo number_format($total_price, 2); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <form action="" method="POST" class="mt-4">
                    <div class="mb-3">
                        <label for="pickup_or_delivery" class="form-label">Pickup or Delivery</label>
                        <select class="form-select" id="pickup_or_delivery" name="pickup_or_delivery" required onchange="toggleDeliveryFields()">
                            <option value="Pickup">Pickup</option>
                            <option value="Delivery">Delivery</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="contact_number" class="form-label">Contact Number</label>
                        <input type="tel" class="form-control" id="contact_number" name="contact_number" required>
                    </div>
                    <div id="delivery_fields" style="display: none;">
                        <div class="mb-3">
                            <label for="delivery_address" class="form-label">Delivery Address</label>
                            <textarea class="form-control" id="delivery_address" name="delivery_address" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="delivery_date" class="form-label">Delivery Date</label>
                            <input type="date" class="form-control" id="delivery_date" name="delivery_date">
                        </div>
                    </div>
                    <button type="submit" name="submit_order" class="btn btn-primary">Proceed to Payment</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleDeliveryFields() {
            var pickupOrDelivery = document.getElementById('pickup_or_delivery');
            var deliveryFields = document.getElementById('delivery_fields');
            if (pickupOrDelivery.value === 'Delivery') {
                deliveryFields.style.display = 'block';
            } else {
                deliveryFields.style.display = 'none';
            }
        }
    </script>
</body>
</html>

