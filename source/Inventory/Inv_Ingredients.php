<?php 
include '../conn.php';  
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin/admin-login.php");
    exit();
}

$email = $_SESSION['admin_email']; // Use 'admin_email' here instead of 'email'

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

$perPage = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $searchQuery = "%" . $search . "%";
    $stmt = $conn->prepare("SELECT * FROM Ingredients WHERE ingredient_name LIKE ? LIMIT ? OFFSET ?");
    $stmt->bind_param("sii", $searchQuery, $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM Ingredients LIMIT ? OFFSET ?");
    $stmt->bind_param("ii", $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
}

$totalStmt = $conn->prepare("SELECT COUNT(*) AS total FROM Ingredients");
$totalStmt->execute();
$totalResult = $totalStmt->get_result();
$total = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($total / $perPage);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="inventory-css/Inv_Ingredients.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                        <a class="nav-link" href="dashboard.php">Inventory Dashboard</a>
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
                        <a class="nav-link" href="Inv_Reports.php">Reports</a>
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
            <h2>Ingredients Management</h2>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addIngredientModal">
                Add New Ingredient
            </button>
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
                        <label for="addProductId" class="form-label">Product</label>
                        <select name="product_id" id="addProductId" class="form-control" required>
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
                        <label for="addIngredientName" class="form-label">Ingredient Name</label>
                        <input type="text" name="ingredient_name" id="addIngredientName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="addQuantity" class="form-label">Quantity</label>
                        <input type="number" name="quantity" id="addQuantity" class="form-control" required>
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
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search ingredients by name" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
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
                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#editIngredientModal" data-id="<?php echo $row['ingredient_id']; ?>" data-product-id="<?php echo $row['product_id']; ?>" data-ingredient-name="<?php echo $row['ingredient_name']; ?>" data-quantity="<?php echo $row['quantity']; ?>">
                                Edit
                            </button>
                            <form method="POST" style="display: inline-block;">
                                <input type="hidden" name="ingredient_id" value="<?php echo $row['ingredient_id']; ?>">
                                <button type="submit" name="delete_ingredient" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <nav>
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Previous</a>
                    </li>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <!-- Edit Ingredient Modal -->
    <div class="modal fade" id="editIngredientModal" tabindex="-1" aria-labelledby="editIngredientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editIngredientModalLabel">Edit Ingredient</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="ingredient_id" id="modalIngredientId">
                        <div class="mb-3">
                            <label for="product_id" class="form-label">Product</label>
                            <select name="product_id" id="modalProductId" class="form-control" required>
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
                            <input type="text" name="ingredient_name" id="modalIngredientName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" name="quantity" id="modalQuantity" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_quantity" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        var editModal = document.getElementById('editIngredientModal');
        editModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var productId = button.getAttribute('data-product-id');
            var ingredientName = button.getAttribute('data-ingredient-name');
            var quantity = button.getAttribute('data-quantity');

            var modalIngredientId = editModal.querySelector('#modalIngredientId');
            var modalProductId = editModal.querySelector('#modalProductId');
            var modalIngredientName = editModal.querySelector('#modalIngredientName');
            var modalQuantity = editModal.querySelector('#modalQuantity');

            modalIngredientId.value = id;
            modalProductId.value = productId;
            modalIngredientName.value = ingredientName;
            modalQuantity.value = quantity;
        });
    </script>

</body>
</html>
