<?php
session_start();
require_once("../../conn.php");

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    $conn->begin_transaction();

    $data = $_POST;
    $product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT) ?? 1;

    if (!$product_id) {
        throw new Exception('Invalid product ID');
    }

    // Check product availability
    $stmt = $conn->prepare("SELECT name, type, base_price, quantity, max_tiers FROM Products WHERE product_id = ? AND quantity >= ? FOR UPDATE");
    $stmt->bind_param("ii", $product_id, $quantity);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Product not available in requested quantity');
    }

    $product = $result->fetch_assoc();

    // Calculate total price (add customization costs if any)
    $total_price = $product['base_price'];
    $customization_details = [];
    
    if (isset($data['customized']) && $data['customized'] == 1) {
        // Validate customization inputs
        $tiers = filter_input(INPUT_POST, 'tiers', FILTER_VALIDATE_INT);
        $size = filter_input(INPUT_POST, 'size_in_inches', FILTER_VALIDATE_INT);
        $flavor = filter_input(INPUT_POST, 'flavor', FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        $instructions = filter_input(INPUT_POST, 'specific_instructions', FILTER_SANITIZE_STRING);

        // Validate tiers
        if ($tiers < 1 || $tiers > $product['max_tiers']) {
            throw new Exception('Invalid number of tiers');
        }

        // Validate size
        if ($size < 6 || $size > 30) {
            throw new Exception('Invalid cake size');
        }

        // Validate message (max 7 words)
        if (!empty($message) && str_word_count($message) > 7) {
            throw new Exception('Message exceeds maximum word limit');
        }

        // Calculate additional costs
        if ($tiers > 1) {
            $tier_cost = ($tiers - 1) * ($product['base_price'] * 0.5); // 50% extra per additional tier
            $total_price += $tier_cost;
        }

        // Size adjustment (10% extra per 6 inches over base size)
        if ($size > 6) {
            $size_cost = floor(($size - 6) / 6) * ($product['base_price'] * 0.1);
            $total_price += $size_cost;
        }

        $customization_details = [
            'tiers' => $tiers,
            'size_in_inches' => $size,
            'flavor' => $flavor,
            'message' => $message,
            'specific_instructions' => $instructions
        ];
    }

    // Update product quantity in database
    $stmt = $conn->prepare("UPDATE Products SET quantity = quantity - ? WHERE product_id = ? AND quantity >= ?");
    $stmt->bind_param("iii", $quantity, $product_id, $quantity);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        throw new Exception('Product quantity update failed');
    }

    // Add to cart session
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $cart_item = [
        'product_id' => $product_id,
        'product_name' => $product['name'],
        'type' => $product['type'],
        'quantity' => $quantity,
        'price' => $total_price,
        'customized' => isset($data['customized']) ? (bool)$data['customized'] : false
    ];

    if (!empty($customization_details)) {
        $cart_item['customization'] = $customization_details;
    }

    $_SESSION['cart'][] = $cart_item;

    $conn->commit();

    echo json_encode([
        'success' => true,
        'cartCount' => count($_SESSION['cart']),
        'cartItems' => $_SESSION['cart'],
        'quantity_available' => true,
        'total_price' => $total_price
    ]);

} catch (Exception $e) {
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }
    error_log("Cart error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'quantity_available' => false
    ]);
}

$conn->close();
?>