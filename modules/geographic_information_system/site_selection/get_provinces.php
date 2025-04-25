<?php
// Include the database connection file
include_once('../../../config/database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Query to get distinct provinces
    $query = "
        SELECT DISTINCT Province
        FROM LocationTb
        ORDER BY Province
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->execute();

    // Fetch the results as an associative array
    $provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the data as JSON
    header('Content-Type: application/json');
    echo json_encode($provinces);

} catch(PDOException $e) {
    // If there's an error, return it in JSON format
    echo json_encode(["error" => $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
