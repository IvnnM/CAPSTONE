<?php
// /modules/geographic_information_system/get_province_transactions.php
include '../../config/database.php'; // Correct path to the database

header('Content-Type: application/json');

try {
    if (isset($_GET['province'])) {
        $province = $_GET['province'];

        // Fetch total transactions (sum of TotalPrice) grouped by date for the selected province
        $query = "
            SELECT DATE(TransactionDate) AS transaction_date, SUM(TotalPrice) AS total_transactions
            FROM TransacTb 
            JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID 
            WHERE LocationTb.Province = :province
            GROUP BY DATE(TransactionDate)
            ORDER BY transaction_date
        ";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':province', $province);
        $stmt->execute();

        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare the response with dates and total transactions
        $response = [
            'transactions' => array_map(function($transaction) {
                return [
                    'date' => $transaction['transaction_date'],
                    'total' => (float) $transaction['total_transactions'] // Cast to float for currency
                ];
            }, $transactions)
        ];

        echo json_encode($response);
    } else {
        // Return an error if province is not set
        echo json_encode(['error' => 'Province parameter is missing.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
