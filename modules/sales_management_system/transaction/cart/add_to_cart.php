<?php
session_start();
require_once('./../../../../config/database.php'); // Adjust the path as needed

// Check if the database connection is successful
if (!isset($conn)) {
    die("Database connection failed");
}

// Check if customer session is set
if (!isset($_SESSION['cust_email']) || !isset($_GET['onhand_id'])) {
    header('Location: ../../../../views/customer_view.php'); // Redirect if no session or product is selected
    exit();
}

$cust_email = $_SESSION['cust_email'];
$cust_name = $_SESSION['cust_name']; // Assuming cust_name is stored in session
$onhand_id = htmlspecialchars($_GET['onhand_id']);
$quantity = 1; // You can change this logic to accept quantity input from the user
$added_date = date('Y-m-d H:i:s');

// Check if item is already in the cart
$check_query = "SELECT * FROM CartTb WHERE CustEmail = :cust_email AND OnhandID = :onhand_id";
$check_stmt = $conn->prepare($check_query);
$check_stmt->execute(['cust_email' => $cust_email, 'onhand_id' => $onhand_id]);

if ($check_stmt->rowCount() > 0) {
    // Item already in the cart, update quantity
    $update_query = "UPDATE CartTb SET Quantity = Quantity + :quantity WHERE CustEmail = :cust_email AND OnhandID = :onhand_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->execute(['quantity' => $quantity, 'cust_email' => $cust_email, 'onhand_id' => $onhand_id]);
} else {
    // Item not in the cart, insert new record
    $insert_query = "INSERT INTO CartTb (CustName, CustEmail, OnhandID, Quantity, AddedDate) VALUES (:cust_name, :cust_email, :onhand_id, :quantity, :added_date)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->execute([
        'cust_name' => $cust_name,
        'cust_email' => $cust_email,
        'onhand_id' => $onhand_id,
        'quantity' => $quantity,
        'added_date' => $added_date
    ]);
}

$previous_page = $_SERVER['HTTP_REFERER'];
header("Location: $previous_page");
exit();
?>
