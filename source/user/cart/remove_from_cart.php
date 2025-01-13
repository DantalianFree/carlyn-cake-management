<?php
session_start();
require_once("../../conn.php");

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get the input data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    if (!isset($data['index']) || !is_numeric($data['index'])) {
        throw new Exception('Invalid cart index');
    }

    $index = (int)$data['index'];

    // Check if cart exists and index is valid
    if (!isset($_SESSION['cart']) || !isset($_SESSION['cart'][$index])) {
        throw new Exception('Invalid cart item');
    }

    $conn->begin_transaction();

    // Get the item details before removing
    $removed_item = $_SESSION['cart'][$index];

    // Return the quantity to product inventory
    if (isset($removed_item['product_id']) && isset($removed_item['quantity'])) {
        $stmt = $conn->prepare("UPDATE Products SET quantity = quantity + ? WHERE product_id = ?");
        $stmt->bind_param("ii", $removed_item['quantity'], $removed_item['product_id']);
        $stmt->execute();

        if ($stmt->affected_rows === 0) {
            throw new Exception('Failed to update product quantity');
        }
    }

    // Remove the item from the cart
    array_splice($_SESSION['cart'], $index, 1);

    // Calculate new cart total
    $cart_total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $cart_total += isset($item['price']) ? (float)$item['price'] : 0;
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Item removed successfully',
        'cartCount' => count($_SESSION['cart']),
        'cartItems' => $_SESSION['cart'],
        'cartTotal' => number_format($cart_total, 2)
    ]);

} catch (Exception $e) {
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }
    
    error_log("Remove from cart error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>