<?php
// File: /modules/geographic_information_system/php/get_forecast_data.php
include '../../../config/database.php';

header('Content-Type: application/json');

try {
    if (isset($_GET['province'])) {
        $province = $_GET['province'];

        // Adjust the SQL query based on whether a specific province or "ALL" is requested
        if ($province === 'ALL') {
            $query = "
                SELECT MONTH(TransactionDate) AS month, YEAR(TransactionDate) AS year, SUM(TotalPrice) AS total_sales
                FROM TransacTb
                GROUP BY MONTH(TransactionDate), YEAR(TransactionDate)
                ORDER BY year, month
            ";
        } else {
            $query = "
                SELECT MONTH(TransactionDate) AS month, YEAR(TransactionDate) AS year, SUM(TotalPrice) AS total_sales
                FROM TransacTb 
                JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID 
                WHERE LocationTb.Province = :province
                GROUP BY MONTH(TransactionDate), YEAR(TransactionDate)
                ORDER BY year, month
            ";
        }

        $stmt = $conn->prepare($query);
        if ($province !== 'ALL') {
            $stmt->bindParam(':province', $province);
        }
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if data is returned
        if (empty($data)) {
            echo json_encode(['data' => []]); // Return empty data if no records found
        } else {
            echo json_encode(['data' => $data]); // Return fetched data
        }
    } else {
        // Return an error if province is not set
        echo json_encode(['error' => 'Province parameter is missing.']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
