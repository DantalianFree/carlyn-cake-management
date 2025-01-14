<?php
include '../conn.php';  
session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../admin/admin-login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    $updateQuery = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param('si', $order_status, $order_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = 'Order status updated successfully.';
    } else {
        $_SESSION['error'] = 'Failed to update order status.';
    }

    header("Location: order_management.php");
    exit();
}
?>
