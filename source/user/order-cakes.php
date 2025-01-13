<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
include("../conn.php");

// Fetch cakes from the database
$sql = "SELECT * FROM Products";
$result = $conn->query($sql);

$cakes = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cakes[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cakes</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/order-cakes.css">
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

    <div class="container mt-5">
        <h1 class="text-center mb-4" style="color: #d6336c;">Our Cakes</h1>
        <div class="row">
            <?php if (!empty($cakes)): ?>
                <?php foreach ($cakes as $cake): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card cake-card">
                            <!-- Placeholder Image -->
                            <img src="images/cake-placeholder.jpg" class="card-img-top" alt="Cake Image">
                            <div class="card-body">
                                <h5 class="card-title" style="color: #d6336c;"><?php echo htmlspecialchars($cake['name']); ?></h5>
                                <p class="card-text">Type: <?php echo htmlspecialchars($cake['type']); ?></p>
                                <p class="card-text">Base Price: $<?php echo number_format($cake['base_price'], 2); ?></p>
                                <p class="card-text">Available Quantity: <?php echo htmlspecialchars($cake['quantity']); ?></p>
                                <p class="card-text">Max Tiers: <?php echo htmlspecialchars($cake['max_tiers']); ?></p>
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#cakeModal<?php echo $cake['product_id']; ?>">View Details</button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Cake Details -->
                    <div class="modal fade" id="cakeModal<?php echo $cake['product_id']; ?>" tabindex="-1" aria-labelledby="cakeModalLabel<?php echo $cake['product_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="cakeModalLabel<?php echo $cake['product_id']; ?>"><?php echo htmlspecialchars($cake['name']); ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p><strong>Type:</strong> <?php echo htmlspecialchars($cake['type']); ?></p>
                                    <p><strong>Base Price:</strong> $<?php echo number_format($cake['base_price'], 2); ?></p>
                                    <p><strong>Max Tiers:</strong> <?php echo htmlspecialchars($cake['max_tiers']); ?></p>
                                    <p><strong>Available Quantity:</strong> <?php echo htmlspecialchars($cake['quantity']); ?></p>
                                    <p>Would you like to order this cake as is or customize it further?</p>
                                </div>
                                <div class="modal-footer">
                                    <form action="customize.php" method="GET" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $cake['product_id']; ?>">
                                        <button type="submit" class="btn btn-success">Customize</button>
                                    </form>
                                    <form action="payment.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="product_id" value="<?php echo $cake['product_id']; ?>">
                                        <button type="submit" class="btn btn-primary">Order Now</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">No cakes available at the moment. Please check back later!</p>
                </div>
            <?php endif; ?>
        </div>
        <div class="text-center mt-3">
            <a href="user-dashboard.php" class="btn btn-primary btn-lg">Go Back to Dashboard</a>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
