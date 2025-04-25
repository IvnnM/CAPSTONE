<?php
session_start();
include("../../../config/database.php"); // Ensure the correct path to your database configuration

header('Content-Type: application/json'); // Set the content type to JSON

try {
    // Prepare and execute the SQL query to fetch transactions with status 'ToShip'
    $query = "SELECT TransacID, CustName, CustNum, CustEmail, CustNote, LocationID, DeliveryFee, TotalPrice, TransactionDate, Status, ExactCoordinates 
              FROM TransacTb 
              WHERE Status = :status";
    $stmt = $conn->prepare($query);
    
    // Bind the parameter
    $status = 'ToShip';
    $stmt->bindParam(':status', $status);
    
    // Execute the query
    $stmt->execute();

    // Fetch the results
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return the transactions in JSON format
    echo json_encode([
        'success' => true,
        'data' => $transactions
    ]);
} catch (PDOException $e) {
    // Handle any errors
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
