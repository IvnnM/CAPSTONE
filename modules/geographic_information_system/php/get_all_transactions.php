<?php
include '../../../config/database.php'; // Adjust the path if necessary

header('Content-Type: application/json');

try {
    // SQL to fetch all transactions
    $query = "SELECT DATE(TransactionDate) as date, TotalPrice as total FROM TransacTb ORDER BY TransactionDate"; // Retrieve all transactions with their dates
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $transactions = [];
    
    foreach ($result as $row) {
        $transactions[] = [
            'date' => $row['date'],
            'total' => (float) $row['total']
        ];
    }

    echo json_encode(['transactions' => $transactions]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
