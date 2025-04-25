<?php
// get_cart_total.php
session_start();
require_once("../../../../config/database.php");

header('Content-Type: application/json');

try {
    $custEmail = $_SESSION['cust_email'] ?? null;
    
    if (!$custEmail) {
        throw new Exception("User not logged in");
    }

    $query = "SELECT 
                c.Quantity,
                o.RetailPrice,
                o.PromoPrice,
                o.MinPromoQty
              FROM CartTb c
              JOIN OnhandTb o ON c.OnhandID = o.OnhandID
              WHERE c.CustEmail = :cust_email";
              
    $stmt = $conn->prepare($query);
    $stmt->execute(['cust_email' => $custEmail]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $totalPrice = 0;
    foreach ($cartItems as $item) {
        $priceToUse = $item['Quantity'] >= $item['MinPromoQty'] 
            ? $item['PromoPrice'] 
            : $item['RetailPrice'];
        $totalPrice += $priceToUse * $item['Quantity'];
    }
    
    echo json_encode(['total' => $totalPrice]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}