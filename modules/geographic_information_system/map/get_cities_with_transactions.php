<?php
// Include the database connection file
include_once('../../../config/database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
  // Query to get cities with transactions and calculate total revenue per city
    $query = "
    SELECT L.City, L.LatLng, COALESCE(SUM(T.TotalPrice), 0) AS TotalRevenue
    FROM LocationTb L
    LEFT JOIN TransacTb T ON L.LocationID = T.LocationID
    GROUP BY L.City, L.LatLng
    HAVING TotalRevenue > 0
    ORDER BY L.City
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->execute();

    // Fetch the results as an associative array
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the fetched cities to error log
    error_log(print_r($cities, true)); // Log the result for debugging

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($cities);

} catch(PDOException $e) {
    // If there's an error, return it in JSON format
    echo json_encode(["error" => $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
