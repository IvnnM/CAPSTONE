<?php
// Include the database connection file
include_once('../../../config/database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get the year parameter from the URL
    $year = isset($_GET['year']) ? intval($_GET['year']) : date("Y"); // Default to current year

    // Query to get revenue by product for the specified year
    $query = "
        SELECT p.ProductName, SUM(t.TotalPrice) AS Revenue
        FROM ProductTb p
        JOIN InventoryTb i ON p.ProductID = i.ProductID
        JOIN OnhandTb o ON i.InventoryID = o.InventoryID
        JOIN CartRecordTb c ON o.OnhandID = c.OnhandID
        JOIN TransacTb t ON c.TransacID = t.TransacID
        WHERE t.Status = 'Delivered' AND YEAR(t.TransactionDate) = ?
        GROUP BY p.ProductName
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->execute([$year]);

    // Fetch the results as an associative array
    $revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($revenueData);

} catch(PDOException $e) {
    // If there's an error, return it in JSON format
    echo json_encode(["error" => $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
