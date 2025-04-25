<?php
include("../config/database.php");

header('Content-Type: application/json');

try {
    // Query for provinces
    $provinceQuery = "SELECT DISTINCT Province FROM LocationTb";
    $provinceStmt = $conn->prepare($provinceQuery);
    $provinceStmt->execute();
    $provinces = $provinceStmt->fetchAll(PDO::FETCH_ASSOC);

    // Query for cities
    $cityQuery = "SELECT City, Province, LocationID FROM LocationTb";
    $cityStmt = $conn->prepare($cityQuery);
    $cityStmt->execute();
    $cities = $cityStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare data to return
    $data = [
        'provinces' => $provinces,
        'cities' => $cities
    ];

    echo json_encode($data);
} catch (Exception $e) {
    // Return error message in case of failure
    echo json_encode(['error' => 'Failed to retrieve location data.']);
}
?>
