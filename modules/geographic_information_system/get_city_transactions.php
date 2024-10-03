<?php
// /modules/geographic_information_system/get_city_transactions.php
include("../../config/database.php");

header('Content-Type: application/json');

try {
    if (isset($_GET['city'])) {
        $city = $_GET['city'];

        // Fetch total transactions (sum of TotalPrice) for the selected city
        $query = "
            SELECT SUM(TotalPrice) AS total_transactions 
            FROM TransacTb 
            JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID 
            WHERE LocationTb.City = :city
        ";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(':city', $city);
        $stmt->execute();
        $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

        // Return total transactions for the city in JSON format
        echo json_encode([
            'total_transactions' => (float) $transaction['total_transactions'] // Cast to float for currency
        ]);
    } else {
        // Return an error if city is not set
        echo json_encode(['error' => 'City parameter is missing.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
