<?php 
include '../../conn.php';  
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin/admin-login.php");
    exit();
}

$email = $_SESSION['admin_email']; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Summary</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../inventory-css/Inv_Summary.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../admin/admin-dashboard.php" id="navbarBrand">Admin</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../Dashboard.php">Inventory Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownInventory" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Inventory
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownInventory">
                            <li><a class="dropdown-item" href="../Inv_Products.php">Products</a></li>
                            <li><a class="dropdown-item" href="../Inv_Ingredients.php">Ingredients</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="../Inv_Reports.php">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../admin/admin-logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h2 class="mb-4">Inventory Summary</h2>

        <a href="../Inv_Reports.php" class="btn btn-secondary mb-4"><i class="bi bi-arrow-left"></i> Back to Reports</a>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-box-seam" style="font-size: 48px;"></i>
                        <h5 class="card-title mt-3">Total Products</h5>
                        <?php
                            $result = $conn->query("SELECT COUNT(*) AS total FROM Products");
                            $row = $result->fetch_assoc();
                            echo "<p class='card-text'>{$row['total']} Products</p>";
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-dollar" style="font-size: 48px;"></i>
                        <h5 class="card-title mt-3">Total Value of Inventory</h5>
                        <?php
                            $result = $conn->query("SELECT SUM(base_price * quantity) AS total_value FROM Products");
                            $row = $result->fetch_assoc();
                            echo "<p class='card-text'>{$row['total_value']}</p>";
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h4>Low Stock Products</h4>
            <?php 
                $productLowStockQuery = "SELECT * FROM Products WHERE quantity < 10";
                $productLowStockResult = $conn->query($productLowStockQuery);
                if ($productLowStockResult->num_rows > 0): 
            ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Base Price</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $productLowStockResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['name']; ?></td>
                                <td><?php echo $row['type']; ?></td>
                                <td><?php echo $row['base_price']; ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No low stock products.</p>
            <?php endif; ?>
        </div>

        <div class="mt-5">
            <h4>Low Stock Ingredients</h4>
            <?php 
                $ingredientLowStockQuery = "SELECT * FROM Ingredients WHERE quantity < 10";
                $ingredientLowStockResult = $conn->query($ingredientLowStockQuery);
                if ($ingredientLowStockResult->num_rows > 0): 
            ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $ingredientLowStockResult->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['ingredient_name']; ?></td>
                                <td><?php echo $row['quantity']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No low stock ingredients.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
