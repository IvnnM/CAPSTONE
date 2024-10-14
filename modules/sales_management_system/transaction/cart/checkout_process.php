<?php
session_start();
include("./../../../../config/database.php");

// Fetch session data for customer email and location ID
$cust_email = $_SESSION['cust_email'];
$locationID = $_SESSION['location_id'];

// Fetch cart items for the current customer
$query = "SELECT c.CartID, p.ProductName, c.Quantity, c.AddedDate, o.RetailPrice, o.MinPromoQty, o.PromoPrice, o.OnhandID
          FROM CartTb c
          JOIN OnhandTb o ON c.OnhandID = o.OnhandID
          JOIN InventoryTb i ON o.InventoryID = i.InventoryID
          JOIN ProductTb p ON i.ProductID = p.ProductID
          WHERE c.CustEmail = :cust_email";
$stmt = $conn->prepare($query);
$stmt->execute(['cust_email' => $cust_email]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total price
$total_price = 0;
foreach ($cart_items as $item) {
    $price_to_use = $item['Quantity'] >= $item['MinPromoQty'] ? $item['PromoPrice'] : $item['RetailPrice'];
    $total_price += $price_to_use * $item['Quantity'];
}

// Get delivery fee from session
$delivery_fee = isset($_SESSION['delivery_fee']) ? $_SESSION['delivery_fee'] : 0;

// Calculate grand total
$grand_total = $total_price + $delivery_fee;

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $custNote = trim($_POST['cust_note']);
    $cust_num = trim($_POST['cust_num']); // Get cust_num from form input
    $exact_coordinates = trim($_POST['exact_coordinates']); // Get ExactCoordinates from form input

    // Insert data into TransacTb
    $insert_query = "INSERT INTO TransacTb (CustName, CustEmail, CustNum, LocationID, DeliveryFee, TotalPrice, Status, CustNote, ExactCoordinates) 
                    VALUES (:cust_name, :cust_email, :cust_num, :location_id, :delivery_fee, :total_price, 'Pending', :cust_note, :exact_coordinates)";
    $stmt = $conn->prepare($insert_query);
    
    try {
        $stmt->execute([
            'cust_name' => $_SESSION['cust_name'],
            'cust_email' => $cust_email,
            'cust_num' => $cust_num,
            'location_id' => $locationID,
            'delivery_fee' => $delivery_fee,
            'total_price' => $grand_total, // Ensure grand_total includes the delivery fee
            'cust_note' => $custNote,
            'exact_coordinates' => $exact_coordinates
        ]);
    } catch (PDOException $e) {
        // Handle the exception if needed
        echo "Error: " . $e->getMessage();
        exit;
    }

    // Get the last inserted TransacID
    $transac_id = $conn->lastInsertId();

    // Insert items from CartTb into CartRecordTb
    foreach ($cart_items as $item) {
        // Use the correct price (promo or retail) for CartRecordTb
        $price_to_use = $item['Quantity'] >= $item['MinPromoQty'] ? $item['PromoPrice'] : $item['RetailPrice'];
        
        $insert_cart_record_query = "INSERT INTO CartRecordTb (TransacID, CustName, CustEmail, OnhandID, Quantity, AddedDate, Price) 
                                      VALUES (:transac_id, :cust_name, :cust_email, :onhand_id, :quantity, :added_date, :price)";
        $insert_cart_record_stmt = $conn->prepare($insert_cart_record_query);
        $insert_cart_record_stmt->execute([
            'transac_id' => $transac_id,
            'cust_name' => $_SESSION['cust_name'],
            'cust_email' => $cust_email,
            'onhand_id' => $item['OnhandID'],
            'quantity' => $item['Quantity'],
            'added_date' => $item['AddedDate'],
            'price' => $price_to_use // Correct price based on promo or retail
        ]);
    }

    // Update OnhandTb to subtract the purchased quantity
    foreach ($cart_items as $item) {
        $update_query = "UPDATE OnhandTb SET OnhandQty = OnhandQty - :quantity WHERE OnhandID = :onhand_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([
            'quantity' => $item['Quantity'],
            'onhand_id' => $item['OnhandID']
        ]);
    }

    // Clear the CartTb for the user after checkout
    $clear_cart_query = "DELETE FROM CartTb WHERE CustEmail = :cust_email";
    $clear_cart_stmt = $conn->prepare($clear_cart_query);
    $clear_cart_stmt->execute(['cust_email' => $cust_email]);

    $_SESSION['alert'] = "Transaction completed successfully!";
    $_SESSION['alert_type'] = "success"; // You can set this to "danger" for error messages

    // Redirect or provide a success message
    echo "<script>window.location.href = '../../../../views/customer_view.php';</script>";
}
?>
