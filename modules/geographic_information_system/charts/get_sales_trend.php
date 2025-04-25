<?php
// Include the database connection file
include_once('../../../config/database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get the year parameter from the URL
    $year = isset($_GET['year']) ? intval($_GET['year']) : date("Y"); // Default to current year

    // Query to get sales data over the months
    $query = "
        SELECT 
            MONTH(t.TransactionDate) AS Month, 
            MONTHNAME(t.TransactionDate) AS MonthName,
            SUM(t.TotalPrice) AS Revenue
        FROM TransacTb t
        WHERE t.Status = 'Delivered' AND YEAR(t.TransactionDate) = ?
        GROUP BY MONTH(t.TransactionDate)
        ORDER BY MONTH(t.TransactionDate)";

    $stmt = $conn->prepare($query);
    $stmt->execute([$year]);

    // Fetch the results as an associative array
    $salesTrendData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if there are results and format data for response
    if ($salesTrendData) {
        // Create a result array with month names and revenue
        $result = [];
        foreach ($salesTrendData as $row) {
            $result[] = [
                'Month' => $row['MonthName'], // Use the full month name
                'Revenue' => floatval($row['Revenue']) // Ensure revenue is in float format
            ];
        }
    } else {
        // If no data is found, return an empty array
        $result = [];
    }

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($result);

} catch(PDOException $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

$conn = null;
?>
