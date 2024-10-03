<?php
include("../config/database.php");

// Fetch all unique provinces
$province_query = "SELECT DISTINCT Province FROM LocationTb ORDER BY Province";
$province_stmt = $conn->prepare($province_query);
$province_stmt->execute();
$provinces = $province_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all cities with LatLng for dropdowns
$city_query = "SELECT LocationID, Province, City, LatLng FROM LocationTb ORDER BY Province, City"; // Include LatLng
$city_stmt = $conn->prepare($city_query);
$city_stmt->execute();
$cities = $city_stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the data in JSON format
echo json_encode([
    'provinces' => $provinces,
    'cities' => $cities
]);
?>
