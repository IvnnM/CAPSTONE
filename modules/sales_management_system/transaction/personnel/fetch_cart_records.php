<?php
session_start();
include("../../../../config/database.php");

// Check if TransacID is set
if (!isset($_GET['transac_id'])) {
    echo "<tr><td colspan='6' class='text-center'>No Transaction ID provided.</td></tr>";
    exit;
}

$transac_id = $_GET['transac_id'];
$total_price = 0;

// Fetch cart records for the given TransacID along with product name and category
$sql = "SELECT c.OnhandID, c.Quantity, c.Price, c.AddedDate, 
               p.ProductName, 
               pc.CategoryName
        FROM CartRecordTb c
        JOIN OnhandTb o ON c.OnhandID = o.OnhandID
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID
        JOIN ProductTb p ON i.ProductID = p.ProductID
        JOIN ProductCategoryTb pc ON p.CategoryID = pc.CategoryID
        WHERE c.TransacID = :transac_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
$stmt->execute();
$cart_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate table rows for cart records
if ($cart_records) {
    foreach ($cart_records as $record) {
        // Calculate line total
        $line_total = $record['Quantity'] * $record['Price'];
        $total_price += $line_total;

        echo "<tr>";
        echo '<td data-label="Product Name">' . htmlspecialchars($record['ProductName']) . '</td>';
        echo '<td data-label="Category">' . htmlspecialchars($record['CategoryName']) . '</td>';
        echo '<td data-label="Quantity">' . htmlspecialchars($record['Quantity']) . '</td>';
        echo '<td data-label="Price">P' . number_format($record['Price'], 2) . '</td>';
        echo "</tr>";
    }
    
    // Add a hidden row to pass total price to JavaScript
    echo "<tr id='total-price-row' data-total-price='" . number_format($total_price, 2) . "' style='display:none;'></tr>";
} else {
    echo "<tr><td colspan='6' class='text-center'>No cart records found for this transaction.</td></tr>";
}
?>