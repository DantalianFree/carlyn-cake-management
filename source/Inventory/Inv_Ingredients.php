<?php 
include '../conn.php';  
session_start();

// Handle form submissions for updating quantity or adding a new ingredient
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $ingredientId = $_POST['ingredient_id'];
        $newQuantity = $_POST['new_quantity'];

        $stmt = $conn->prepare("UPDATE Ingredients SET quantity = ? WHERE ingredient_id = ?");
        $stmt->bind_param("ii", $newQuantity, $ingredientId);
        $stmt->execute();
    }

    if (isset($_POST['add_ingredient'])) {
        $productId = $_POST['product_id'];
        $ingredientName = $_POST['ingredient_name'];
        $quantity = $_POST['quantity'];

        $stmt = $conn->prepare("INSERT INTO Ingredients (product_id, ingredient_name, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $productId, $ingredientName, $quantity);
        $stmt->execute();
    }

    if (isset($_POST['delete_ingredient'])) {
        $ingredientId = $_POST['ingredient_id'];

        $stmt = $conn->prepare("DELETE FROM Ingredients WHERE ingredient_id = ?");
        $stmt->bind_param("i", $ingredientId);
        $stmt->execute();
    }
}

// Fetch all ingredients
$result = $conn->query("SELECT * FROM Ingredients");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/Inv_Ingredients.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">
            <i class="bi bi-list"></i> Logo
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle active" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Inventory
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="Inv_Products.php">Products</a></li>
                        <li><a class="dropdown-item" href="Inv_Ingredients.php">Ingredients</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Ingredients Management</h2>
            <!-- Trigger Button for Add Ingredient Modal -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                Add New Ingredient
            </button>
        </div>

        <!-- Ingredients Table -->
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product ID</th>
                    <th>Ingredient Name</th>
                    <th>Quantity</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['ingredient_id']; ?></td>
                        <td><?php echo $row['product_id']; ?></td>
                        <td><?php echo $row['ingredient_name']; ?></td>
                        <td><?php echo $row['quantity']; ?></td>
                        <td>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="ingredient_id" value="<?php echo $row['ingredient_id']; ?>">
                                <input type="number" name="new_quantity" placeholder="New Quantity" class="form-control mb-2">
                                <button type="submit" name="update_quantity" class="btn btn-primary btn-sm">Update Quantity</button>
                            </form>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="ingredient_id" value="<?php echo $row['ingredient_id']; ?>">
                                <button type="submit" name="delete_ingredient" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Ingredient Modal -->
    <div class="modal fade" id="addIngredientModal" tabindex="-1" aria-labelledby="addIngredientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addIngredientModalLabel">Add New Ingredient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select name="product_id" class="form-control" required>
                                <!-- Populate product options dynamically -->
                                <?php 
                                $productResult = $conn->query("SELECT product_id, `name` FROM Products");
                                while ($productRow = $productResult->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $productRow['product_id']; ?>">
                                        <?php echo $productRow['name']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="ingredient_name" class="form-label">Ingredient Name</label>
                            <input type="text" name="ingredient_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" name="quantity" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_ingredient" class="btn btn-success">Add Ingredient</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
