<?php
session_start();
require_once('./../../../../config/database.php'); // Adjust the path as needed

// Check if the customer is logged in
if (!isset($_SESSION['cust_email']) || !isset($_GET['cart_id'])) {
    header('Location: ../../../../views/customer_view.php'); // Redirect if no session or cart_id is provided
    exit();
}

$cust_email = $_SESSION['cust_email'];
$cart_id = htmlspecialchars($_GET['cart_id']);

// Prepare the delete statement
$delete_query = "DELETE FROM CartTb WHERE CartID = :cart_id AND CustEmail = :cust_email";
$delete_stmt = $conn->prepare($delete_query);

// Execute the delete statement
if ($delete_stmt->execute(['cart_id' => $cart_id, 'cust_email' => $cust_email])) {
    $_SESSION['alert'] = "Item removed from cart successfully.";
    $_SESSION['alert_type'] = "danger";
} else {
    $_SESSION['alert'] = "Failed to remove item from cart.";
    $_SESSION['alert_type'] = "danger";
}

// Redirect back to the cart page
header('Location: ../../../../views/customer_view.php'); // Redirect to the customer view (cart page)
exit();
?>
