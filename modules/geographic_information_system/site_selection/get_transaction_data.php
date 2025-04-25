<?php
// include_once('../../../config/database.php');

// try {
//     // Query to get transaction data for cities in the selected province
//     $query = "
//         SELECT L.City, L.LatLng, SUM(T.TotalPrice) AS TotalRevenue
//         FROM LocationTb L
//         JOIN TransacTb T ON L.LocationID = T.LocationID
//         WHERE L.Province = :province
//         GROUP BY L.City, L.LatLng
//         ORDER BY TotalRevenue DESC
//     ";

//     $stmt = $conn->prepare($query);
//     $stmt->bindParam(':province', $_GET['province']);
//     $stmt->execute();
//     $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
//     header('Content-Type: application/json');
//     echo json_encode($data);
// } catch(PDOException $e) {
//     echo json_encode(["error" => $e->getMessage()]);
// }

// $conn = null;
?>
