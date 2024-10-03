<?php
include '../../config/database.php';

header('Content-Type: application/json');

try {
    $query = "SELECT Province, SUM(TotalPrice) AS total_transactions FROM TransacTb JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID GROUP BY Province";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $provinces = [];
    $total_transactions = [];
    
    foreach ($result as $row) {
        $provinces[] = $row['Province'];
        $total_transactions[] = (float) $row['total_transactions'];
    }

    echo json_encode(['provinces' => $provinces, 'total_transactions' => $total_transactions]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
