<?php
include '../../../config/database.php'; // Adjust the path if necessary

header('Content-Type: application/json');

try {
    // SQL to fetch all transactions without province and city filters
    $query = "SELECT MONTH(TransactionDate) as month, 
                     MONTHNAME(TransactionDate) as month_name, 
                     SUM(TotalPrice) as total 
              FROM TransacTb 
              GROUP BY month ORDER BY month"; // Group by month

    // Prepare and execute the statement
    $stmt = $conn->prepare($query);
    $stmt->execute(); // Execute without parameters

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $transactions = [];

    foreach ($result as $row) {
        $transactions[] = [
            'month' => (int) $row['month'], // Store month as integer
            'month_name' => $row['month_name'], // Month name for labels
            'total' => (float) $row['total'] // Total price for that month
        ];
    }

    echo json_encode(['transactions' => $transactions]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
