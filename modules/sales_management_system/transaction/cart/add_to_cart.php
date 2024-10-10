<?php
session_start();
require_once('./../../../../config/database.php'); // Adjust the path as needed

// Check if the database connection is successful
if (!isset($conn)) {
    die("Database connection failed");
}

// Check if customer session is set and onhand_id is provided
// if (!isset($_SESSION['cust_email']) || !isset($_GET['onhand_id'])) {
//     header('Location: ../../../../views/customer_view.php'); 
//     exit();
// }

// Retrieve customer details from the session
$cust_email = $_SESSION['cust_email'];
$cust_name = $_SESSION['cust_name']; // Assuming cust_name is stored in session
$onhand_id = htmlspecialchars($_GET['onhand_id']);
$quantity = 1; // Default quantity, can be adjusted later to accept user input
$added_date = date('Y-m-d H:i:s');

// Fetch the price and promotional details from OnhandTb
$price_query = "SELECT RetailPrice, MinPromoQty, PromoPrice FROM OnhandTb WHERE OnhandID = :onhand_id";
$price_stmt = $conn->prepare($price_query);
$price_stmt->execute(['onhand_id' => $onhand_id]);
$price_info = $price_stmt->fetch(PDO::FETCH_ASSOC);

if (!$price_info) {
    die("Error: Product not found.");
}

// Determine the correct price based on quantity and promotional logic
$price = $price_info['RetailPrice'];
if ($quantity >= $price_info['MinPromoQty']) {
    $price = $price_info['PromoPrice']; // Use promotional price if applicable
}

// Check if item is already in the cart
$check_query = "SELECT * FROM CartTb WHERE CustEmail = :cust_email AND OnhandID = :onhand_id";
$check_stmt = $conn->prepare($check_query);
$check_stmt->execute(['cust_email' => $cust_email, 'onhand_id' => $onhand_id]);

if ($check_stmt->rowCount() > 0) {
    // Item already in the cart, update quantity and price
    $update_query = "UPDATE CartTb SET Quantity = Quantity + :quantity, Price = :price, AddedDate = :added_date WHERE CustEmail = :cust_email AND OnhandID = :onhand_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute([
        'quantity' => $quantity,
        'price' => $price,
        'added_date' => $added_date,
        'cust_email' => $cust_email,
        'onhand_id' => $onhand_id
    ]);
} else {
    // Item not in the cart, insert new record with price
    $insert_query = "INSERT INTO CartTb (CustName, CustEmail, OnhandID, Quantity, Price, AddedDate) VALUES (:cust_name, :cust_email, :onhand_id, :quantity, :price, :added_date)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->execute([
        'cust_name' => $cust_name,
        'cust_email' => $cust_email,
        'onhand_id' => $onhand_id,
        'quantity' => $quantity,
        'price' => $price, // Include the price here
        'added_date' => $added_date
    ]);
}

// Redirect back to the previous page
$previous_page = $_SERVER['HTTP_REFERER'];
header("Location: $previous_page");
exit();
?>
