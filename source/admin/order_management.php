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
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/order_management.css">
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
                    <a class="nav-link active" href="#">Order Management</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../admin/admin-logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <h2 class="mb-4">Manage Orders</h2>

    <a href="admin-dashboard.php" class="btn btn-secondary mb-4"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>

    <div class="mt-4">
        <?php
            $orderQuery = "SELECT * FROM orders ORDER BY created_at DESC";
            $orderResult = $conn->query($orderQuery);

            if ($orderResult->num_rows > 0): 

                // Fetch enum values for order_status
                $enumQuery = "SHOW COLUMNS FROM orders LIKE 'order_status'";
                $enumResult = $conn->query($enumQuery);
                $enumRow = $enumResult->fetch_assoc();
                preg_match('/^enum\(\'(.*)\'\)$/', $enumRow['Type'], $matches);
                $enumValues = explode("','", $matches[1]);

        ?>
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Order ID</th>
                        <th>User ID</th>
                        <th>Total Price</th>
                        <th>Order Status</th>
                        <th>Order Date</th>
                        <th>Contact Number</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $orderResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['user_id']; ?></td>
                            <td><?php echo number_format($row['total_price'], 2); ?></td>
                            <td>
                                <form action="update_order_status.php" method="post">
                                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                    <select name="order_status" class="form-select">
                                        <?php foreach ($enumValues as $value): ?>
                                            <option value="<?php echo $value; ?>" <?php echo ($value === $row['order_status']) ? 'selected' : ''; ?>>
                                                <?php echo ucfirst($value); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary mt-2 btn-sm">Update</button>
                                </form>
                            </td>
                            <td><?php echo date('d M Y H:i', strtotime($row['order_date'])); ?></td>
                            <td><?php echo $row['contact_number']; ?></td>
                            <td>
                                <a href="edit_order.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete_order.php?order_id=<?php echo $row['order_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
