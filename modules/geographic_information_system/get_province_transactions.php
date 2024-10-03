<?php
// /modules/geographic_information_system/get_province_transactions.php
include '../../config/database.php';

header('Content-Type: application/json');

try {
    if (isset($_GET['province'])) {
        $province = $_GET['province'];

        // Fetch total transactions (sum of TotalPrice) for the selected province
        $query = "
            SELECT SUM(TotalPrice) AS total_transactions 
            FROM TransacTb 
            JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID 
            WHERE LocationTb.Province = :province
        ";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':province', $province);
        $stmt->execute();

        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return total transactions for the province in JSON format
        echo json_encode([
            'total_transactions' => (float) $transaction['total_transactions'] // Cast to float for currency
        ]);
    } else {
        // Return an error if province is not set
        echo json_encode(['error' => 'Province parameter is missing.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
