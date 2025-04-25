<?php
// Include the database connection file
include_once('../../../config/database.php');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if both province and city parameters are set
    if (isset($_GET['province']) && isset($_GET['city'])) {
        $selectedProvince = $_GET['province'];
        $selectedCity = $_GET['city'];

        // Updated query to use LEFT JOIN
        $query = "
            SELECT L.LatLng, SUM(T.TotalPrice) AS TotalRevenue
            FROM LocationTb L
            LEFT JOIN TransacTb T ON L.LocationID = T.LocationID
            WHERE L.Province = :province AND L.City = :city
            GROUP BY L.LatLng
        ";

        // Prepare and execute the query
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':province', $selectedProvince);
        $stmt->bindParam(':city', $selectedCity);
        $stmt->execute();

        // Fetch the results as an associative array
        $cityData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Return the data as JSON
        header('Content-Type: application/json');
        echo json_encode($cityData);
    } else {
        // If parameters are missing, return an error
        echo json_encode(["error" => "Province or city parameter is missing."]);
    }
} catch(PDOException $e) {
    // If there's an error, return it in JSON format
    echo json_encode(["error" => $e->getMessage()]);
}

// Close the connection
$conn = null;
?>
