<?php
session_start();
include("./../../../../config/database.php");

// Get customer ID from the session
$customerId = $_SESSION['customer_id'] ?? 'unknown_customer';

// Fetch cart details
$cart_query = "
    SELECT p.ProductName, c.Quantity, 
           o.RetailPrice, 
           o.MinPromoQty, 
           o.PromoPrice,
           CASE 
               WHEN c.Quantity >= o.MinPromoQty THEN o.PromoPrice 
               ELSE o.RetailPrice 
           END AS Price, 
           (c.Quantity * 
               CASE 
                   WHEN c.Quantity >= o.MinPromoQty THEN o.PromoPrice 
                   ELSE o.RetailPrice 
               END) AS TotalPrice
    FROM CartTb c
    JOIN OnhandTb o ON c.OnhandID = o.OnhandID
    JOIN ProductTb p ON o.InventoryID = p.ProductID
    WHERE c.CustEmail = :cust_email
";

$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bindParam(':cust_email', $_SESSION['cust_email'], PDO::PARAM_STR);
$cart_stmt->execute();
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    echo "No items in the cart.";
    exit;
}

// Set file name dynamically based on customer ID
$filename = "customer_receipt_{$customerId}.csv";

// Generate CSV content
header('Content-Type: text/csv');
header("Content-Disposition: attachment; filename=\"$filename\"");

$output = fopen('php://output', 'w');

// Add CSV header
fputcsv($output, ['Product Name', 'Quantity', 'Price', 'Total']);

// Add cart data to CSV
foreach ($cart_items as $item) {
    fputcsv($output, [
        $item['ProductName'],
        $item['Quantity'],
        number_format($item['Price'], 2),
        number_format($item['TotalPrice'], 2)
    ]);
}

fclose($output);
exit;
?>
