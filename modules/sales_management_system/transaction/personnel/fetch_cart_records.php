<?php
session_start();
include("../../../../config/database.php");

// Check if TransacID is set
if (!isset($_GET['transac_id'])) {
    echo "<tr><td colspan='6' class='text-center'>No Transaction ID provided.</td></tr>";
    exit;
}

$transac_id = $_GET['transac_id'];

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
        echo "<tr>
                <td>" . htmlspecialchars($record['OnhandID']) . "</td>
                <td>" . htmlspecialchars($record['ProductName']) . "</td> <!-- Product Name -->
                <td>" . htmlspecialchars($record['CategoryName']) . "</td> <!-- Category Name -->
                <td>" . htmlspecialchars($record['Quantity']) . "</td>
                <td>" . number_format(htmlspecialchars($record['Price']), 2) . "</td>
                <td>" . htmlspecialchars($record['AddedDate']) . "</td>

              </tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No cart records found for this transaction.</td></tr>";
}
?>
