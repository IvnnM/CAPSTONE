<?php
// get_order_details.php
session_start();
include("../../../config/database.php");

header('Content-Type: application/json');

try {
    if (!isset($_GET['transacId']) || empty($_GET['transacId'])) {
        throw new Exception('Transaction ID is required');
    }

    $transacId = trim($_GET['transacId']);
    
    // Join through all necessary tables to get product details
    $query = "SELECT 
                cr.CartRecordID, 
                cr.TransacID, 
                cr.CustName, 
                cr.Quantity, 
                cr.Price,
                cr.AddedDate,
                p.ProductName,
                p.ProductDesc as ProductDescription,
                (cr.Quantity * cr.Price) as Subtotal,
                pc.CategoryName
              FROM CartRecordTb cr
              LEFT JOIN OnhandTb oh ON cr.OnhandID = oh.OnhandID
              LEFT JOIN InventoryTb inv ON oh.InventoryID = inv.InventoryID
              LEFT JOIN ProductTb p ON inv.ProductID = p.ProductID
              LEFT JOIN ProductCategoryTb pc ON p.CategoryID = pc.CategoryID
              WHERE cr.TransacID = :transacId
              ORDER BY cr.AddedDate";
              
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':transacId', $transacId);
    $stmt->execute();
    
    $orderDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($orderDetails)) {
        throw new Exception('No order details found for this transaction');
    }

    echo json_encode([
        'success' => true,
        'data' => $orderDetails
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>