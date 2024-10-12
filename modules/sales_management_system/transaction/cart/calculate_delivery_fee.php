<?php
include("./../../../../config/database.php");

// Function to calculate delivery fee based on distance
function calculateDeliveryFee($customerLocationID, $storeLocationID, $conn, $storeDeliveryFee) {
    // Fetch latitude and longitude for the customer location
    $cust_location_query = "SELECT LatLng FROM LocationTb WHERE LocationID = :customerLocationID";
    $cust_location_stmt = $conn->prepare($cust_location_query);
    $cust_location_stmt->bindParam(':customerLocationID', $customerLocationID, PDO::PARAM_INT);
    $cust_location_stmt->execute();
    $cust_location = $cust_location_stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch latitude and longitude for the store location
    $store_location_query = "SELECT LatLng FROM LocationTb WHERE LocationID = :storeLocationID";
    $store_location_stmt = $conn->prepare($store_location_query);
    $store_location_stmt->bindParam(':storeLocationID', $storeLocationID, PDO::PARAM_INT);
    $store_location_stmt->execute();
    $store_location = $store_location_stmt->fetch(PDO::FETCH_ASSOC);

    if ($cust_location && $store_location) {
        // Parse latitude and longitude
        list($cust_lat, $cust_lng) = explode(';', $cust_location['LatLng']);
        list($store_lat, $store_lng) = explode(';', $store_location['LatLng']);

        // Calculate the distance using the Haversine formula
        $distance = haversineGreatCircleDistance($cust_lat, $cust_lng, $store_lat, $store_lng);

        // Calculate the delivery fee based on distance and store's delivery fee
        $calculated_fee = $storeDeliveryFee * $distance; // Fee per km
        return max($calculated_fee, $storeDeliveryFee); // Ensure minimum fee is StoreDeliveryFee
    }

    return $storeDeliveryFee; // Return StoreDeliveryFee if location is not found
}

// Function to calculate the distance using Haversine formula
function haversineGreatCircleDistance($latFrom, $lonFrom, $latTo, $lonTo, $earthRadius = 6371) {
    // Convert from degrees to radians
    $latFrom = deg2rad($latFrom);
    $lonFrom = deg2rad($lonFrom);
    $latTo = deg2rad($latTo);
    $lonTo = deg2rad($lonTo);

    // Haversine formula
    $lonDelta = $lonTo - $lonFrom;
    $latDelta = $latTo - $latFrom;
    
    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos($latFrom) * cos($latTo) *
         sin($lonDelta / 2) * sin($lonDelta / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c; // Returns distance in kilometers
}

// Handle the AJAX request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the request is for checkout and exit if true
    if (isset($_POST['is_checkout']) && $_POST['is_checkout'] === 'true') {
        exit; // Prevent further execution
    }

    $data = json_decode(file_get_contents("php://input"), true);
    $customerLocationID = $data['location_id'];
    $storeLocationID = $data['store_location_id'];
    $storeDeliveryFee = $data['store_delivery_fee'];

    // Calculate the delivery fee
    $delivery_fee = calculateDeliveryFee($customerLocationID, $storeLocationID, $conn, $storeDeliveryFee);

    // Return the result as JSON
    echo json_encode(['delivery_fee' => $delivery_fee]);
}
