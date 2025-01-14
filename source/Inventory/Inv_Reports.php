<?php 
include '../conn.php';  
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
    <title>Inventory Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="inventory-css/Inv_Reports.css">
    <style>
        .card {
            border: none;
            transition: transform 0.3s ease-in-out;
        }
        .card:hover {
            transform: translateY(-10px);
        }
        .card-title {
            font-size: 1.25rem;
            color: #495057;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
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
                    <a class="nav-link" href="Dashboard.php">Inventory Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownInventory" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Inventory
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownInventory">
                        <li><a class="dropdown-item" href="Inv_Products.php">Products</a></li>
                        <li><a class="dropdown-item" href="Inv_Ingredients.php">Ingredients</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="#">Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/admin-logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Inventory Reports</h2>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="bi bi-bar-chart-line" style="font-size: 48px; color: #007bff;"></i>
                    <h5 class="card-title mt-3">Inventory Summary</h5>
                    <a href="Reports/Inv_Summary.php" class="btn btn-primary mt-3">View Report</a>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <i class="bi bi-file-earmark-text" style="font-size: 48px; color: #007bff;"></i>
                    <h5 class="card-title mt-3">Sales Report</h5>
                    <a href="Reports/Sales_Report.php" class="btn btn-primary mt-3">View Report</a>
                </div>
            </div>
        </div>
        <!-- Add more report sections as needed -->
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
