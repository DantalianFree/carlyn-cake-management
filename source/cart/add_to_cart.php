<?php
session_start();
require_once '../conn.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $tiers = $_POST['tiers'];
    $size = $_POST['size'];
    $flavor = $_POST['flavor'];
    $message = $_POST['message'];
    $instructions = $_POST['instructions'];
    $add_ons = $_POST['add_ons'];

    // Insert customization into the database
    $query = "INSERT INTO Customizations (user_id, product_id, tiers, size_in_inches, flavor, message, specific_instructions, add_ons) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiiiisss", $_SESSION['user_id'], $product_id, $tiers, $size, $flavor, $message, $instructions, $add_ons);
    $stmt->execute();

    $customization_id = $stmt->insert_id;

    // Add to cart (you may want to create a separate cart table in your database)
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $_SESSION['cart'][] = ['type' => 'customized', 'id' => $customization_id];

    header("Location: cart.php");
    exit();
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = $_GET['id'] ?? 0;
    
    if ($product_id > 0) {
        // Add to cart without customization
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        $_SESSION['cart'][] = ['type' => 'product', 'id' => $product_id];

        header("Location: cart.php");
        exit();
    } else {
        // Invalid product ID
        header("Location: order.php");
        exit();
    }
} else {
    // Invalid request method
    header("Location: order.php");
    exit();
}