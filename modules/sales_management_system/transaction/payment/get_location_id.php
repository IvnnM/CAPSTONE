<?php
// get_location_id.php
session_start();
require_once("../../../../config/database.php");

header('Content-Type: application/json');

try {
    // Get the selected province and city from the POST request
    $province = $_POST['province'] ?? null;
    $city = $_POST['city'] ?? null;

    if (!$province || !$city) {
        throw new Exception("Province or City not provided");
    }

    // Query the LocationTb table based on the selected province and city
    $stmt = $conn->prepare("SELECT LocationID FROM LocationTb WHERE Province = :province AND City = :city");
    $stmt->execute(['province' => $province, 'city' => $city]);
    $location = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($location) {
        echo json_encode(['status' => 'success', 'location_id' => $location['LocationID']]);
    } else {
        throw new Exception("Location not found");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
