<?php
// /modules/geographic_information_system/gis_data.php
include("../../../config/database.php");

header('Content-Type: application/json');

// Check if province parameter is set
if (isset($_GET['province'])) {
    $province = $_GET['province'];

    // Fetch city locations and total transactions for the selected province
    $query = "
        SELECT LocationTb.City, LocationTb.LatLng, SUM(TransacTb.TotalPrice) AS total_transactions
        FROM LocationTb
        LEFT JOIN TransacTb ON LocationTb.LocationID = TransacTb.LocationID
        WHERE LocationTb.Province = :province
        GROUP BY LocationTb.City
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':province', $province);
    $stmt->execute();
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return data in JSON format
    echo json_encode([
        'cities' => array_map(function($city) {
            list($lat, $lng) = explode(',', $city['LatLng']);
            return [
                'City' => $city['City'],
                'Lat' => $lat,
                'Lng' => $lng,
                'total_transactions' => (float) $city['total_transactions'] // Cast to float for currency
            ];
        }, $cities)
    ]);
}

// Check if city parameter is set
if (isset($_GET['city'])) {
    $city = $_GET['city'];

    // Fetch the total transactions (sum of TotalPrice) for the selected city
    $query = "
        SELECT SUM(TransacTb.TotalPrice) AS total_transactions
        FROM TransacTb
        LEFT JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID
        WHERE LocationTb.City = :city
    ";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':city', $city);
    $stmt->execute();
    $transaction = $stmt->fetch(PDO::FETCH_ASSOC);

    // Return total transactions for the city
    echo json_encode([
        'total_transactions' => (float) $transaction['total_transactions'] // Cast to float for currency
    ]);
}
?>
