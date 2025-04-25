<?php
session_start();
include("../../../config/database.php");

// Check if transacID is passed as a GET parameter
if (!isset($_GET['transacID'])) {
    echo json_encode(['error' => 'No transacID provided.']); // Error message for missing transacID
    exit;
}

// Prepare and execute the query
$transacID = $_GET['transacID'];
$query = "SELECT CartRecordTb.OnhandID, CartRecordTb.Quantity, CartRecordTb.Price, 
                 ProductTb.ProductName, ProductTb.ProductDesc, ProductTb.ProductImage
          FROM CartRecordTb 
          JOIN OnhandTb ON CartRecordTb.OnhandID = OnhandTb.OnhandID
          JOIN InventoryTb ON OnhandTb.InventoryID = InventoryTb.InventoryID
          JOIN ProductTb ON InventoryTb.ProductID = ProductTb.ProductID
          WHERE CartRecordTb.TransacID = :transacID";

$stmt = $conn->prepare($query);
$stmt->bindParam(':transacID', $transacID, PDO::PARAM_INT);

try {
    $stmt->execute();
    $cartRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return cart records as JSON
    echo json_encode($cartRecords);
} catch (PDOException $e) {
    // Return error message
    echo json_encode(['error' => $e->getMessage()]);
}
?>
