<?php
session_start();
include('../../../../config/database.php'); // Adjust path as necessary

$cust_email = $_SESSION['cust_email'];

// Check if cart_id is provided
if (!isset($_POST['cart_id'])) {
    $_SESSION['alert'] = 'No cart item specified.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../views/customer_view.php");
    exit();
}

$cart_id = $_POST['cart_id'];

// Fetch the current quantity and OnhandQty for the cart item
$query = "SELECT c.Quantity, p.ProductName, o.OnhandQty 
          FROM CartTb c 
          JOIN OnhandTb o ON c.OnhandID = o.OnhandID 
          JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
          JOIN ProductTb p ON i.ProductID = p.ProductID 
          WHERE c.CartID = :cart_id AND c.CustEmail = :cust_email";
$stmt = $conn->prepare($query);
$stmt->execute(['cart_id' => $cart_id, 'cust_email' => $cust_email]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    $_SESSION['alert'] = 'Cart item not found.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ../../../../views/customer_view.php");
    exit();
}

// Process the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_quantity = (int)$_POST['quantity'];

    // Validate new quantity
    if ($new_quantity < 1) {
        $_SESSION['alert'] = 'Quantity must be at least 1.';
        $_SESSION['alert_type'] = 'danger';
    } elseif ($new_quantity > $item['OnhandQty']) {
        // Check if the new quantity exceeds the available stock
        $_SESSION['alert'] = 'Quantity exceeds available stock for ' . htmlspecialchars($item['ProductName']) . '. Available quantity: ' . htmlspecialchars($item['OnhandQty']) . '.';
        $_SESSION['alert_type'] = 'danger';
    } else {
        // Update the cart item in the database
        $update_query = "UPDATE CartTb SET Quantity = :quantity WHERE CartID = :cart_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute(['quantity' => $new_quantity, 'cart_id' => $cart_id]);

        $_SESSION['alert'] = 'Quantity updated successfully.';
        $_SESSION['alert_type'] = 'success';
    }

    header("Location: ../../../../views/customer_view.php#Products");
    exit();
}
