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
    <title>Sales Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../inventory-css/Inv_Summary.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .card {
            border: none;
            transition: transform 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-10px);
        }
        .table th, .table td {
            text-align: center;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="../admin/admin-dashboard.php">Admin</a>
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
                    <a class="nav-link" href="../admin/admin-logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Sales Report</h2>

    <a href="../Inv_Reports.php" class="btn btn-secondary mb-4"><i class="bi bi-arrow-left"></i> Back to Reports</a>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="bi bi-bar-chart-line" style="font-size: 48px; color: #007bff;"></i>
                    <h5 class="card-title mt-3">Total Sales</h5>
                    <?php
                        $salesQuery = "SELECT COUNT(*) AS total_sales FROM sales";
                        $salesResult = $conn->query($salesQuery);
                        $salesRow = $salesResult->fetch_assoc();
                        echo "<p class='card-text'>{$salesRow['total_sales']} Sales</p>";
                    ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="bi bi-currency-dollar" style="font-size: 48px; color: #007bff;"></i>
                    <h5 class="card-title mt-3">Total Sales Amount</h5>
                    <?php
                        $salesAmountQuery = "SELECT SUM(sale_amount) AS total_sales_amount FROM sales";
                        $salesAmountResult = $conn->query($salesAmountQuery);
                        $salesAmountRow = $salesAmountResult->fetch_assoc();
                        echo "<p class='card-text'>\${$salesAmountRow['total_sales_amount']}</p>";
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-5">
        <h4>Recent Sales</h4>
        <?php
            $recentSalesQuery = "SELECT * FROM sales ORDER BY transaction_date DESC LIMIT 10";
            $recentSalesResult = $conn->query($recentSalesQuery);
            if ($recentSalesResult->num_rows > 0): 
        ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Sale ID</th>
                        <th>Order ID</th>
                        <th>Sale Amount</th>
                        <th>Payment Method</th>
                        <th>Transaction Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $recentSalesResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['sale_id']; ?></td>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['sale_amount']; ?></td>
                            <td><?php echo $row['payment_method']; ?></td>
                            <td><?php echo $row['transaction_date']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No recent sales.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
