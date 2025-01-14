<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../user/user-login.php");
    exit();
}

$product_id = $_GET['id'] ?? 0;
$query = "SELECT * FROM Products WHERE product_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: order.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Cake</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/customize_order.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="user-dashboard.php">Carlyn Cake Shop</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../cart/cart.php">Cart</a>
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

    <div class="container mt-5 mb-5">
        <h2 class="text-center mb-4">Customize Your Cake</h2>
        <a href="order.php" class="btn btn-secondary mb-3">Back to Order</a>
        <form action="../cart/add_to_cart.php" method="post">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <div class="mb-3">
                <label for="tiers" class="form-label">Number of Tiers (1-<?php echo $product['max_tiers']; ?>)</label>
                <input type="number" class="form-control" id="tiers" name="tiers" min="1" max="<?php echo $product['max_tiers']; ?>" required>
            </div>
            <div class="mb-3">
                <label for="size" class="form-label">Size (in inches)</label>
                <input type="number" class="form-control" id="size" name="size" required>
            </div>
            <div class="mb-3">
                <label for="flavor" class="form-label">Flavor</label>
                <select class="form-select" id="flavor" name="flavor" required>
                    <option value="Chocolate">Chocolate</option>
                    <option value="Vanilla">Vanilla</option>
                    <option value="Chiffon">Chiffon</option>
                    <option value="Red Velvet">Red Velvet</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Message (max 7 characters)</label>
                <input type="text" class="form-control" id="message" name="message" maxlength="7">
            </div>
            <div class="mb-3">
                <label for="instructions" class="form-label">Specific Instructions</label>
                <textarea class="form-control" id="instructions" name="instructions" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="add_ons" class="form-label">Add-ons</label>
                <textarea class="form-control" id="add_ons" name="add_ons" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add to Cart</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>