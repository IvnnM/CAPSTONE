<?php
// calc_delv_fee.php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once("../../../../config/database.php");

/**
 * Calculate delivery fee based on distance and base store delivery fee
 * @param float $distance Distance in kilometers
 * @param float $baseDeliveryFee Store's base delivery fee for the first kilometer
 * @param float $additionalPerKm Additional fee per kilometer after the first kilometer
 * @return int Rounded calculated delivery fee
 */
function calculateDeliveryFee($distance, $baseDeliveryFee, $additionalPerKm = 10.0) {
    // Calculate delivery fee based on per kilometer rate
    if ($distance <= 1) {
        return round($baseDeliveryFee); // Base fee for 1 km or less, rounded to nearest peso
    } else {
        $extraDistance = $distance - 1;
        $totalFee = $baseDeliveryFee + ($extraDistance * $additionalPerKm);
        return round($totalFee); // Rounds to the nearest peso
    }
}

try {
    // Get and log raw input
    $rawInput = file_get_contents('php://input');
    error_log("Raw input: " . $rawInput);
    
    // Decode JSON input
    $data = json_decode($rawInput, true);
    
    // Validate input
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }
    
    if (!isset($data['store_id']) || !isset($data['distance'])) {
        throw new Exception('Missing required parameters: store_id and distance required');
    }
    
    $storeId = filter_var($data['store_id'], FILTER_VALIDATE_INT);
    $distance = filter_var($data['distance'], FILTER_VALIDATE_FLOAT);
    
    if ($storeId === false) {
        throw new Exception('Invalid store_id parameter');
    }
    
    if ($distance === false || $distance < 0) {
        throw new Exception('Invalid distance parameter');
    }
    
    // Log validated input
    error_log("Processing delivery fee calculation for store ID: $storeId, distance: $distance");
    
    // Get store's base delivery fee
    $query = "SELECT StoreDeliveryFee FROM StoreInfoTb WHERE StoreInfoID = :store_id";
    $stmt = $conn->prepare($query);
    $stmt->execute(['store_id' => $storeId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        throw new Exception("Store not found with ID: $storeId");
    }
    
    $baseDeliveryFee = floatval($result['StoreDeliveryFee']);
    $calculatedFee = calculateDeliveryFee($distance, $baseDeliveryFee);
    
    // Log calculation result
    error_log("Calculated delivery fee: $calculatedFee (base fee: $baseDeliveryFee, distance: $distance)");
    
    // Return calculated fee
    $response = [
        'success' => true,
        'delivery_fee' => $calculatedFee,
        'distance' => $distance,
        'base_fee' => $baseDeliveryFee
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    error_log("Delivery fee calculation error: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
