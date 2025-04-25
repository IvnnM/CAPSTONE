<?php
header('Content-Type: application/json');

// Include database connection
include("../config/database.php");

// Function to calculate distance between two coordinates (in kilometers)
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Earth radius in kilometers

    // Convert degrees to radians
    $lat1 = deg2rad($lat1);
    $lon1 = deg2rad($lon1);
    $lat2 = deg2rad($lat2);
    $lon2 = deg2rad($lon2);

    // Haversine formula to calculate distance
    $dlat = $lat2 - $lat1;
    $dlon = $lon2 - $lon1;
    $a = sin($dlat / 2) * sin($dlat / 2) + cos($lat1) * cos($lat2) * sin($dlon / 2) * sin($dlon / 2);
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    $distance = $earthRadius * $c;

    return $distance;
}

try {
    // Get city-level data from the database
    $cityQuery = "
        SELECT l.City, l.Province, l.LatLng, 
               COUNT(t.TransacID) AS OrderCount, 
               SUM(t.TotalPrice) AS TotalRevenue, 
               AVG(t.TotalPrice) AS AvgOrderValue, 
               AVG(t.DeliveryFee) AS AvgDeliveryFee, 
               SUM(t.DeliveryFee) AS TotalDeliveryFees
        FROM LocationTb l
        LEFT JOIN TransacTb t ON l.LocationID = t.LocationID
        WHERE t.Status IN ('Delivered', 'ToShip')
        GROUP BY l.City, l.Province, l.LatLng
    ";
    $cityStmt = $conn->prepare($cityQuery);
    $cityStmt->execute();
    $cityData = $cityStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get province-level data from the database
    $provinceQuery = "
        SELECT l.Province, 
               COUNT(t.TransacID) AS TotalOrderCount, 
               SUM(t.TotalPrice) AS TotalRevenue, 
               AVG(t.TotalPrice) AS AvgOrderValue, 
               AVG(t.DeliveryFee) AS AvgDeliveryFee, 
               SUM(t.DeliveryFee) AS TotalDeliveryFees
        FROM LocationTb l
        LEFT JOIN TransacTb t ON l.LocationID = t.LocationID
        WHERE t.Status IN ('Delivered', 'ToShip')
        GROUP BY l.Province
    ";
    $provinceStmt = $conn->prepare($provinceQuery);
    $provinceStmt->execute();
    $provinceData = $provinceStmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize the final data structure
    $locations = [];

    // Calculate percentage of province revenue for each city
    foreach ($cityData as $cityRow) {
        // Extract latitude and longitude from LatLng
        $latLng = explode(",", $cityRow['LatLng']);
        $lat = floatval($latLng[0]);
        $lon = floatval($latLng[1]);

        // Get the total revenue for the province
        $provinceTotalRevenue = 0;
        foreach ($provinceData as $provinceRow) {
            if ($provinceRow['Province'] === $cityRow['Province']) {
                $provinceTotalRevenue = $provinceRow['TotalRevenue'];
                break;
            }
        }

        // Calculate distance between cities
        $distances = [];
        $deliveryFees = [];
        foreach ($cityData as $cityRow2) {
            if ($cityRow['City'] !== $cityRow2['City']) {
                // Extract latitude and longitude from LatLng for the other city
                $latLng2 = explode(",", $cityRow2['LatLng']);
                $lat2 = floatval($latLng2[0]);
                $lon2 = floatval($latLng2[1]);

                $distance = calculateDistance($lat, $lon, $lat2, $lon2);
                $distances[] = $distance;
                $deliveryFees[] = $cityRow2['AvgDeliveryFee'];
            }
        }

        // Add the city data to the locations array
        $locations[] = [
            'city' => $cityRow['City'],
            'province' => $cityRow['Province'],
            'coordinates' => [$lat, $lon],
            'metrics' => [
                'city_metrics' => [
                    'total_revenue' => (float)$cityRow['TotalRevenue'],
                    'order_count' => (int)$cityRow['OrderCount'],
                    'avg_order_value' => (float)$cityRow['AvgOrderValue'],
                    'avg_delivery_fee' => (float)$cityRow['AvgDeliveryFee'],
                    'revenue_per_order' => (float)($cityRow['TotalRevenue'] / $cityRow['OrderCount']),
                    'percentage_of_province_revenue' => (float)($cityRow['TotalRevenue'] / $provinceTotalRevenue * 100)
                ],
                'province_metrics' => [
                    'total_revenue' => (float)$provinceTotalRevenue,
                    'order_count' => (int)$provinceRow['TotalOrderCount'],
                    'avg_order_value' => (float)$provinceRow['AvgOrderValue'],
                    'avg_delivery_fee' => (float)$provinceRow['AvgDeliveryFee'],
                    'revenue_per_order' => (float)($provinceRow['TotalRevenue'] / $provinceRow['TotalOrderCount'])
                ]
            ],
            'distances' => [
                'avg_distance_to_others' => !empty($distances) ? array_sum($distances) / count($distances) : 0,
                'max_distance' => !empty($distances) ? max($distances) : 0,
                'min_distance' => !empty($distances) ? min($distances) : 0
            ]
        ];
    }

    // Return the locations as a JSON response
    echo json_encode($locations, JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    // Handle any database connection errors
    echo json_encode(['error' => $e->getMessage()]);
}
?>
