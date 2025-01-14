<?php
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: admin-login.php");
    exit();
}

$email = $_SESSION['admin_email']; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-dashboard.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin-logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="text-center">Welcome, Admin!</h1>
        <p class="text-center">Manage users, orders, and more.</p>

        <div class="row">
            <!-- Inventory Card -->
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Manage Inventory</h5>
                        <p class="card-text">View, update, and manage the inventory of products.</p>
                        <a href="../Inventory/Dashboard.php" class="btn btn-primary">View Inventory</a>
                    </div>
                </div>
            </div>

            <!-- Orders Management Card -->
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Manage Orders</h5>
                        <p class="card-text">View, update, and manage customer orders.</p>
                        <a href="order_management.php" class="btn btn-primary">View Orders</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
