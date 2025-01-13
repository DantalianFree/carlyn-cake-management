<?php
session_start();

// Ensure the user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Include the database connection file
include("../conn.php");

// Check if a product_id is provided
if (!isset($_GET['product_id'])) {
    header("Location: order-cakes.php");
    exit();
}

$product_id = $_GET['product_id'];

// Fetch product details
$sql = "SELECT * FROM Products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: order-cakes.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tiers = $_POST['tiers'];
    $size = $_POST['size'];
    $flavor = $_POST['flavor'];
    $message = $_POST['message'];
    $instructions = $_POST['instructions'];
    $add_ons = isset($_POST['add_ons']) ? implode(", ", $_POST['add_ons']) : '';

    // Insert customization into database
    $sql = "INSERT INTO Customizations (user_id, product_id, tiers, size_in_inches, flavor, message, specific_instructions, add_ons) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiisss", $_SESSION['user_id'], $product_id, $tiers, $size, $flavor, $message, $instructions, $add_ons);
    $stmt->execute();

    // Redirect to order summary
    header("Location: order-summary.php?customization_id=" . $conn->insert_id);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Cake</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/order-cakes.css">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .customize-form {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
    </style>
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
        <h1 class="text-center mb-4" style="color: #d6336c;">Customize Your Cake</h1>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="customize-form">
                    <form action="" method="POST">
                        <div class="mb-3">
                            <label for="tiers" class="form-label">Number of Tiers (1-<?php echo $product['max_tiers']; ?>)</label>
                            <input type="number" class="form-control" id="tiers" name="tiers" min="1" max="<?php echo $product['max_tiers']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="size" class="form-label">Size (in inches)</label>
                            <input type="number" class="form-control" id="size" name="size" min="6" max="20" required>
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
                            <label for="message" class="form-label">Message on Cake (max 7 characters)</label>
                            <input type="text" class="form-control" id="message" name="message" maxlength="7">
                        </div>
                        <div class="mb-3">
                            <label for="instructions" class="form-label">Specific Instructions</label>
                            <textarea class="form-control" id="instructions" name="instructions" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Add-ons</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Candles" id="candles" name="add_ons[]">
                                <label class="form-check-label" for="candles">Candles</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Sparklers" id="sparklers" name="add_ons[]">
                                <label class="form-check-label" for="sparklers">Sparklers</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="Cake Topper" id="topper" name="add_ons[]">
                                <label class="form-check-label" for="topper">Cake Topper</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="background-color: #d6336c; border-color: #d6336c;">Submit Customization</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

