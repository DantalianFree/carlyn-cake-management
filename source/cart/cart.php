<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../user/user-login.php");
    exit();
}

if (isset($_POST['remove_item'])) {
    $index = $_POST['item_index'];
    if (isset($_SESSION['cart'][$index])) {
        unset($_SESSION['cart'][$index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // Re-index the array
    }
}

$cart_items = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['type'] === 'customized') {
            $query = "SELECT c.*, p.name, p.base_price FROM Customizations c 
                      JOIN Products p ON c.product_id = p.product_id 
                      WHERE c.customization_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $item['id']);
        } else {
            $query = "SELECT * FROM Products WHERE product_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $item['id']);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $cart_item = $result->fetch_assoc();
        $cart_item['cart_index'] = $index;
        $cart_items[] = $cart_item;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/cart.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../user/user-dashboard.php">Carlyn Cake Shop</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../ordering/order.php">Order</a>
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
        <h2 class="text-center mb-4">Shopping Cart</h2>
        <a href="../ordering/order.php" class="btn btn-secondary mb-3">Back to Order</a>
        <?php if (empty($cart_items)): ?>
            <p class="text-center">Your cart is empty.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Customizations</th>
                        <th>Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>
                                <?php if (isset($item['tiers'])): ?>
                                    Tiers: <?php echo $item['tiers']; ?><br>
                                    Size: <?php echo $item['size_in_inches']; ?> inches<br>
                                    Flavor: <?php echo $item['flavor']; ?><br>
                                    Message: <?php echo htmlspecialchars($item['message']); ?><br>
                                    Instructions: <?php echo htmlspecialchars($item['specific_instructions']); ?><br>
                                    Add-ons: <?php echo htmlspecialchars($item['add_ons']); ?>
                                <?php else: ?>
                                    Standard product (no customization)
                                <?php endif; ?>
                            </td>
                            <td>₱<?php echo number_format($item['base_price'], 2); ?></td>
                            <td>
                                <form method="post">
                                    <input type="hidden" name="item_index" value="<?php echo $item['cart_index']; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-end mt-3">
                <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>