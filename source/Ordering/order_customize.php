<?php
include '../conn.php';  
session_start();

// Fetch ENUM values from the database
$flavorQuery = "SHOW COLUMNS FROM customizations LIKE 'flavor'";
$result = $conn->query($flavorQuery);
$row = $result->fetch_assoc();
$enumValues = str_replace(['enum(', ')', "'"], '', $row['Type']);
$flavors = explode(',', $enumValues);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $productId = $_POST['product_id'];
    $tiers = $_POST['tiers'];
    $sizeInches = $_POST['size_in_inches'];
    $flavor = $_POST['flavor'];
    $message = $_POST['message'];
    $specificInstructions = $_POST['specific_instructions'];

    // Handling multiple image uploads
    $referenceImages = $_FILES['reference_images'];

    // Prepare insert statement
    $stmt = $conn->prepare("INSERT INTO customizations (user_id, product_id, tiers, size_in_inches, flavor, message, specific_instructions, reference_images) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    // Convert uploaded images to JSON format
    $imagePaths = [];
    for ($i = 0; $i < count($referenceImages['name']); $i++) {
        $targetDir = "../uploads/";
        $targetFile = $targetDir . basename($referenceImages['name'][$i]);
        move_uploaded_file($referenceImages['tmp_name'][$i], $targetFile);
        $imagePaths[] = $targetFile;
    }
    $imageJson = json_encode($imagePaths);

    $stmt->bind_param("iiisssss", $userId, $productId, $tiers, $sizeInches, $flavor, $message, $specificInstructions, $imageJson);
    $stmt->execute();

    // Redirect to a confirmation page or success message
    header('Location: success.php');
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customize Your Cake</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/order_customize.css">
    <style>
        body {
            background-color: #FFF6F9; 
        }
        
        .container {
            max-width: 800px;
            padding: 2rem;
            background-color: #FFFDF5; 
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        h2 {
            color: #4A0045; /* Darker pinkish-red for headings */
            font-weight: bold;
        }

        .form-control {
            border: 1px solid #FFD9E3; /* Pink borders for form inputs */
            background-color: #FFFDF5; /* Light pink background */
            color: #4A0045;
        }

        .form-control:focus {
            border-color: #FF7794; /* Highlighted pink border */
            box-shadow: 0 0 10px rgba(255, 119, 148, 0.5); /* Pink glow effect */
        }

        .btn-success {
            background-color: #FFBDC4; /* Soft pink button */
            border-color: #FFBDC4;
            color: white;
        }

        .btn-success:hover {
            background-color: #FF8491; /* Slightly darker hover effect */
            border-color: #FF8491;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Customize Your Cake</h2>
        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" value="1"> <!-- Example product_id -->
            <div class="mb-3">
                <label for="tiers" class="form-label">Number of Tiers</label>
                <input type="number" name="tiers" class="form-control" min="1" max="5" required>
            </div>
            <div class="mb-3">
                <label for="size_in_inches" class="form-label">Size (in inches)</label>
                <input type="number" name="size_in_inches" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="flavor" class="form-label">Flavor</label>
                <select name="flavor" class="form-control" required>
                    <?php foreach ($flavors as $flavor): ?>
                        <option value="<?php echo trim($flavor); ?>"><?php echo ucfirst(trim($flavor)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="message" class="form-label">Dedication Message (Max 7 words)</label>
                <input type="text" name="message" class="form-control" maxlength="35" required>
            </div>
            <div class="mb-3">
                <label for="specific_instructions" class="form-label">Specific Instructions</label>
                <textarea name="specific_instructions" class="form-control"></textarea>
            </div>
            <div class="mb-3">
                <label for="reference_images" class="form-label">Attach Reference Images</label>
                <input type="file" name="reference_images[]" class="form-control" multiple>
            </div>
            <button type="submit" class="btn btn-success">Save Customization</button>
        </form>
    </div>
</body>
</html>


