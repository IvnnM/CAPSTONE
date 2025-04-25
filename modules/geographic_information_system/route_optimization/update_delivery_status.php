<?php
//update_delivery_status.php
session_start();
include("../../../config/database.php");

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $transacId = $_POST['transacId'];
        
        // Update the transaction status to 'Delivered'
        $query = "UPDATE TransacTb SET Status = 'Delivered' WHERE TransacID = :transacId";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':transacId', $transacId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Delivery completed successfully'
            ]);
        } else {
            throw new Exception('Failed to update status');
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?>