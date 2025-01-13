<?php 
include '../conn.php';  
session_start();

// Handle form submissions for updating or adding a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['edit_product'])) {
        $productId = $_POST['product_id'];
        $name = $_POST['name'];
        $type = $_POST['type'];
        $basePrice = $_POST['base_price'];
        $maxTiers = $_POST['max_tiers'];
        $quantity = $_POST['quantity'];

        $stmt = $conn->prepare("UPDATE Products SET name = ?, type = ?, base_price = ?, max_tiers = ?, quantity = ? WHERE product_id = ?");
        $stmt->bind_param("ssdiii", $name, $type, $basePrice, $maxTiers, $quantity, $productId);
        $stmt->execute();
    }

    if (isset($_POST['add_product'])) {
        $name = $_POST['name'];
        $type = $_POST['type'];
        $basePrice = $_POST['base_price'];
        $maxTiers = $_POST['max_tiers'];
        $quantity = $_POST['quantity'];

        $stmt = $conn->prepare("INSERT INTO Products (name, type, base_price, max_tiers, quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdii", $name, $type, $basePrice, $maxTiers, $quantity);
        $stmt->execute();
    }

    if (isset($_POST['delete_product'])) {
        $productId = $_POST['product_id'];

        $stmt = $conn->prepare("DELETE FROM Products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
    }
}

$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $searchQuery = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM Products WHERE name LIKE ?");
    $stmt->bind_param("s", $searchQuery);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT * FROM Products");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="inventory-css/Inv_Products.css">
</head>
<body>
<nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../admin/admin-dashboard.php" id="navbarBrand">Logo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
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
                        <a class="nav-link" href="#">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Settings</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Inventory Management</h2>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                Add New Product
            </button>
        </div>
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search products by name" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Base Price</th>
                    <th>Max Tiers</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['name']; ?></td>
                        <td><?php echo $row['type']; ?></td>
                        <td><?php echo $row['base_price']; ?></td>
                        <td><?php echo $row['max_tiers']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo $row['product_id']; ?>">
                                Edit
                            </button>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                <button type="submit" name="delete_product" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <div class="modal fade" id="editProductModal<?php echo $row['product_id']; ?>" tabindex="-1" aria-labelledby="editProductModalLabel<?php echo $row['product_id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="editProductModalLabel<?php echo $row['product_id']; ?>">Edit Product</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form method="POST">
                                    <div class="modal-body">
                                        <input type="hidden" name="product_id" value="<?php echo $row['product_id']; ?>">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Product Name</label>
                                            <input type="text" name="name" class="form-control" value="<?php echo $row['name']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="type" class="form-label">Type</label>
                                            <select name="type" class="form-control" required>
                                                <option value="fondant" <?php echo $row['type'] == 'Fondant' ? 'selected' : ''; ?>>Fondant</option>
                                                <option value="semifondant" <?php echo $row['type'] == 'Semifondant' ? 'selected' : ''; ?>>Semi-Fondant</option>
                                                <option value="icing" <?php echo $row['type'] == 'Icing' ? 'selected' : ''; ?>>Icing</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="base_price" class="form-label">Base Price</label>
                                            <input type="number" name="base_price" class="form-control" value="<?php echo $row['base_price']; ?>" step="0.01" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="max_tiers" class="form-label">Max Tiers</label>
                                            <input type="number" name="max_tiers" class="form-control" value="<?php echo $row['max_tiers']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <input type="number" name="quantity" class="form-control" value="<?php echo $row['quantity']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        <button type="submit" name="edit_product" class="btn btn-success">Save Changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type</label>
                            <select name="type" class="form-control" required>
                                <option value="fondant">Fondant</option>
                                <option value="semifondant">Semi-Fondant</option>
                                <option value="icing">Icing</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="base_price" class="form-label">Base Price</label>
                            <input type="number" name="base_price" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="max_tiers" class="form-label">Max Tiers</label>
                            <input type="number" name="max_tiers" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_product" class="btn btn-success">Add Product</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
