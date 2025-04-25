<?php
// Include the database connection file
include_once('../../../config/database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Get the year parameter from the URL
    $year = isset($_GET['year']) ? intval($_GET['year']) : date("Y"); // Default to current year

    // Query to get revenue by province for the specified year
    $query = "
        SELECT l.Province, SUM(t.TotalPrice) AS Revenue
        FROM LocationTb l
        JOIN TransacTb t ON l.LocationID = t.LocationID
        WHERE t.Status = 'Delivered' AND YEAR(t.TransactionDate) = ?
        GROUP BY l.Province
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->execute([$year]);

    // Fetch the results as an associative array
    $revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate the total revenue
    $totalRevenue = array_sum(array_column($revenueData, 'Revenue'));

    // Convert each province’s revenue to a percentage of the total
    foreach ($revenueData as &$data) {
        $data['Percentage'] = $totalRevenue > 0 ? ($data['Revenue'] / $totalRevenue) * 100 : 0;
        $data['Percentage'] = round($data['Percentage'], 2); // Round to 2 decimal places
    }

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
