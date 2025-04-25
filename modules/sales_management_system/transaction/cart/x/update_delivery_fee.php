<?php
session_start();
include("./../../../../config/database.php");

// Check if the delivery fee is sent via POST
if (isset($_POST['delivery_fee'])) {
    $delivery_fee = floatval($_POST['delivery_fee']);
    $_SESSION['delivery_fee'] = $delivery_fee;

    // Respond with success
    echo json_encode(['success' => true, 'delivery_fee' => $delivery_fee]);
} else {
    // Respond with an error
    echo json_encode(['success' => false, 'message' => 'Delivery fee not provided']);
}
?>
