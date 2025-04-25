<?php
session_start();
require_once('./../../../../config/database.php'); // Adjust the path as needed

// Check if the database connection is successful
if (!isset($conn)) {
    die("Database connection failed");
}

// Check if user is logged in
if (!isset($_SESSION['customer_id']) || !isset($_SESSION['cust_email'])) {
    $_SESSION['alert'] = "Please login to access your account.";
    $_SESSION['alert_type'] = 'warning';
    header("Location: /3CAPSTONE/customer_login/login_form.php");
    exit;
}

// Retrieve customer details from the session
$cust_email = $_SESSION['cust_email'];
$onhand_id = isset($_GET['onhand_id']) ? htmlspecialchars($_GET['onhand_id']) : null;
$quantity = isset($_GET['quantity']) ? (int) $_GET['quantity'] : 1;
$added_date = date('Y-m-d H:i:s');

// Validate inputs
if (!$onhand_id || $quantity <= 0) {
    $_SESSION['alert'] = "Invalid product or quantity.";
    $_SESSION['alert_type'] = "danger";
    header("Location: ../../../../views/customer_view.php");
    exit;
}

// First, check the available OnhandQty and get product details
$inventory_query = "SELECT o.OnhandQty, o.RetailPrice, o.MinPromoQty, o.PromoPrice, p.ProductName 
                   FROM OnhandTb o 
                   JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
                   JOIN ProductTb p ON i.ProductID = p.ProductID 
                   WHERE o.OnhandID = :onhand_id";
$inventory_stmt = $conn->prepare($inventory_query);
$inventory_stmt->execute(['onhand_id' => $onhand_id]);
$inventory_info = $inventory_stmt->fetch(PDO::FETCH_ASSOC);

if (!$inventory_info) {
    $_SESSION['alert'] = "Error: Product not found.";
    $_SESSION['alert_type'] = "danger";
    header("Location: ../../../../views/customer_view.php");
    exit();
}

// Check if item is already in the cart to calculate total quantity
$cart_query = "SELECT Quantity FROM CartTb WHERE CustEmail = :cust_email AND OnhandID = :onhand_id";
$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->execute(['cust_email' => $cust_email, 'onhand_id' => $onhand_id]);
$existing_cart_item = $cart_stmt->fetch(PDO::FETCH_ASSOC);
$existing_quantity = $existing_cart_item ? $existing_cart_item['Quantity'] : 0;

// Calculate total quantity (existing + new)
$total_quantity = $existing_quantity + $quantity;

// Check if the total quantity exceeds available stock
if ($total_quantity > $inventory_info['OnhandQty']) {
    $_SESSION['alert'] = 'Quantity exceeds available stock for ' . htmlspecialchars($inventory_info['ProductName']) . 
                        '. Available quantity: ' . htmlspecialchars($inventory_info['OnhandQty']) . '.';
    $_SESSION['alert_type'] = "danger";
    header("Location: ../../../../views/customer_view.php#Products");
    exit();
}

// Determine the correct price based on quantity and promotional logic
$price = $inventory_info['RetailPrice'];
if ($total_quantity >= $inventory_info['MinPromoQty']) {
    $price = $inventory_info['PromoPrice']; // Use promotional price if applicable
}

// Update or insert cart item
if ($existing_cart_item) {
    // Item already in the cart, update quantity and price
    $update_query = "UPDATE CartTb SET Quantity = :total_quantity, Price = :price, AddedDate = :added_date 
                    WHERE CustEmail = :cust_email AND OnhandID = :onhand_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute([
        'total_quantity' => $total_quantity,
        'price' => $price,
        'added_date' => $added_date,
        'cust_email' => $cust_email,
        'onhand_id' => $onhand_id
    ]);
} else {
    // Item not in the cart, insert new record
    $insert_query = "INSERT INTO CartTb (CustEmail, OnhandID, Quantity, Price, AddedDate) 
                    VALUES (:cust_email, :onhand_id, :quantity, :price, :added_date)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->execute([
        'cust_email' => $cust_email,
        'onhand_id' => $onhand_id,
        'quantity' => $quantity,
        'price' => $price,
        'added_date' => $added_date
    ]);
}

// Set success message with quantity and cart link
$_SESSION['alert'] = "Successfully added $quantity item(s) to your cart. <a href='#' class='view-cart-link'>View Cart</a>";
$_SESSION['alert_type'] = "success";

// Redirect to customer view
header("Location: ../../../../views/customer_view.php#Products");
exit();
?>
