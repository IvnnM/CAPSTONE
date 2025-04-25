<?php
// get_stores.php
header('Content-Type: application/json');
require_once("../../../../config/database.php");

function getAllStores($conn) {
    $query = "SELECT 
                s.StoreInfoID,
                s.StoreExactCoordinates,
                s.StoreDeliveryFee,
                l.Province,
                l.City
              FROM StoreInfoTb s
              JOIN LocationTb l ON s.LocationID = l.LocationID";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $stores = [];
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        // Split coordinates string into lat/lng array
        $coordinates = explode(',', $row['StoreExactCoordinates']);
        $stores[] = [
            'id' => $row['StoreInfoID'],
            'lat' => floatval(trim($coordinates[0])),
            'lng' => floatval(trim($coordinates[1])),
            'delivery_fee' => floatval($row['StoreDeliveryFee']),
            'province' => $row['Province'],
            'city' => $row['City']
        ];
    }
    
    echo json_encode($stores);
}

getAllStores($conn);