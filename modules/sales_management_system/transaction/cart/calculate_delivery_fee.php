<?php

// Function to calculate delivery fee based on distance
function calculateDeliveryFee($customerLocationID, $conn) {
    // Get customer location LatLng and store's base delivery fee and location
    $storeDetails = getStoreDetails($conn);
    if ($storeDetails === null) {
        error_log("Store details could not be retrieved.");
        return 0; // Return a default delivery fee of 0 if store details are not found
    }

    $locations = getLatLng($customerLocationID, $storeDetails['LocationID'], $conn);
    if ($locations === null) {
        error_log("Location data could not be retrieved.");
        return $storeDetails['StoreDeliveryFee']; // Return the base fee if location is not found
    }

    // Parse latitude and longitude
    [$cust_lat, $cust_lng] = explode(';', $locations['customer']);
    [$store_lat, $store_lng] = explode(';', $locations['store']);

    // Calculate distance using the Haversine formula
    $distance = haversineDistance($cust_lat, $cust_lng, $store_lat, $store_lng);

    // Calculate the delivery fee based on distance
    $calculatedFee = $storeDetails['StoreDeliveryFee'] * $distance;
    return max($calculatedFee, $storeDetails['StoreDeliveryFee']); // Ensure the minimum fee
}

// Fetch latitude and longitude for customer and store locations
function getLatLng($customerLocationID, $storeLocationID, $conn) {
    $query = "SELECT LocationID, LatLng FROM LocationTb WHERE LocationID IN (:customerLocationID, :storeLocationID)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':customerLocationID', $customerLocationID, PDO::PARAM_INT);
    $stmt->bindParam(':storeLocationID', $storeLocationID, PDO::PARAM_INT);

    if (!$stmt->execute()) {
        error_log("Error fetching locations: " . implode(", ", $stmt->errorInfo()));
        return null; // Return null if the query fails
    }

    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($locations === false || count($locations) < 2) {
        error_log("No locations found for customerLocationID: $customerLocationID or storeLocationID: $storeLocationID");
        return null; // Return null if either location is not found or query fails
    }

    $result = [];
    foreach ($locations as $location) {
        if ($location['LocationID'] == $customerLocationID) {
            $result['customer'] = $location['LatLng'];
        } else {
            $result['store'] = $location['LatLng'];
        }
    }
    return $result;
}

// Fetch store's delivery fee and location
function getStoreDetails($conn) {
    $query = "SELECT LocationID, StoreDeliveryFee FROM StoreInfoTb LIMIT 1"; // Fetch one record
    $stmt = $conn->prepare($query);
    $stmt->execute();

    $storeDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($storeDetails === false) {
        error_log("Failed to fetch store details.");
        return null; // Return null if the query fails
    }

    return $storeDetails; // Return the fetched details
}

// Calculate the distance using the Haversine formula
function haversineDistance($latFrom, $lonFrom, $latTo, $lonTo, $earthRadius = 6371) {
    // Convert from degrees to radians
    $latFrom = deg2rad($latFrom);
    $lonFrom = deg2rad($lonFrom);
    $latTo = deg2rad($latTo);
    $lonTo = deg2rad($lonTo);

    // Haversine formula
    $latDelta = $latTo - $latFrom;
    $lonDelta = $lonTo - $lonFrom;

    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos($latFrom) * cos($latTo) *
         sin($lonDelta / 2) * sin($lonDelta / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return $earthRadius * $c; // Returns distance in kilometers
}

// Check if location_id session is set and calculate delivery fee
if (isset($_SESSION['location_id'])) {
    $customerLocationID = $_SESSION['location_id']; // Get the location ID from session

    // Calculate the delivery fee
    $deliveryFee = calculateDeliveryFee($customerLocationID, $conn);

    // Store the delivery fee in session
    $_SESSION['delivery_fee'] = $deliveryFee;

    // Return the result as JSON

} else {
    // If location_id is not set, return a default message
    echo json_encode(['error' => 'Location ID is not set.']);
}
?>
