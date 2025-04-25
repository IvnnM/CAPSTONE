<?php
include('./../../../../includes/database_connection.php'); // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['customer_coords'])) {
    $customer_coords = explode(',', $_POST['customer_coords']); // Get customer coordinates from the POST data

    if (count($customer_coords) !== 2) {
        echo json_encode(['error' => 'Invalid coordinates']);
        exit;
    }

    // Fetch the store's exact coordinates and delivery fee
    $query_store = "SELECT StoreExactCoordinates, StoreDeliveryFee FROM StoreInfoTb LIMIT 1";
    $stmt_store = $conn->prepare($query_store);
    $stmt_store->execute();
    $store_data = $stmt_store->fetch(PDO::FETCH_ASSOC);

    if ($store_data && !empty($store_data['StoreExactCoordinates'])) {
        $store_coords = explode(',', $store_data['StoreExactCoordinates']);
        $store_base_fee = $store_data['StoreDeliveryFee'];
    } else {
        echo json_encode(['error' => 'Store information not found']);
        exit;
    }

    // Haversine formula to calculate the distance between two lat/lon points
    function haversine($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371; // Earth radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        $distance = $earth_radius * $c; // Distance in kilometers
        return $distance;
    }

    // Get customer and store coordinates
    $customer_lat = $customer_coords[0];
    $customer_lng = $customer_coords[1];
    $store_lat = $store_coords[0];
    $store_lng = $store_coords[1];

    // Calculate the distance between customer and store
    $distance = haversine($customer_lat, $customer_lng, $store_lat, $store_lng);

    // Calculate the delivery fee based on distance
    // You can adjust this logic based on your business rules (e.g., charge more for long distances)
    $delivery_fee = $store_base_fee * $distance; // Example: multiply base fee by distance

    echo json_encode([
        'delivery_fee' => number_format($delivery_fee, 2),
        'distance' => number_format($distance, 2)
    ]);
    exit;
}

echo json_encode(['error' => 'Invalid request']);
exit;
?>
