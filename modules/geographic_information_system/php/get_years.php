<?php
// Include database configuration
include_once('../../../config/database.php');

try {
    // Query to get distinct years from the TransactionDate column
    $query = "
        SELECT DISTINCT YEAR(TransactionDate) AS year 
        FROM TransacTb 
        ORDER BY year DESC
    ";

    // Prepare and execute the query using PDO
    $stmt = $conn->prepare($query);
    $stmt->execute();

    // Fetch all distinct years
    $years = $stmt->fetchAll(PDO::FETCH_COLUMN); // Use FETCH_COLUMN to get an array of years

    // Return the years as a JSON response
    echo json_encode($years);
} catch (PDOException $e) {
    // Handle any errors
    echo json_encode(['error' => 'Failed to fetch years: ' . $e->getMessage()]);
}
?>
