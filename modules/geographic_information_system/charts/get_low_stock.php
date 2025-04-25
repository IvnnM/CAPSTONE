<?php
// Include the database connection file
include_once('../../../config/database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Query to get products with low stock based on RestockThreshold
    $query = "
        SELECT p.ProductName, o.OnhandQty, o.RestockThreshold
        FROM ProductTb p
        JOIN InventoryTb i ON p.ProductID = i.ProductID
        JOIN OnhandTb o ON i.InventoryID = o.InventoryID
        WHERE o.OnhandQty <= o.RestockThreshold";

    $stmt = $conn->prepare($query);
    $stmt->execute();

    // Fetch the results as an associative array
    $lowStockData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($lowStockData);

} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

$conn = null;
?>
