<?php
session_start();
include("./../../../../includes/cdn.html");
include("./../../../../config/database.php");

// Fetch session data for customer email and location ID
$cust_email = $_SESSION['cust_email'];
$locationID = $_SESSION['location_id']; // Fetch LocationID from the session

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

    // Insert data into TransacTb
    $insert_query = "INSERT INTO TransacTb (CustName, CustEmail, CustNum, LocationID, DeliveryFee, TotalPrice, Status, CustNote) 
                    VALUES (:cust_name, :cust_email, :cust_num, :location_id, :delivery_fee, :total_price, 'Pending', :cust_note)";
    $stmt = $conn->prepare($insert_query);
    $stmt->execute([
        'cust_name' => $_SESSION['cust_name'],
        'cust_email' => $cust_email,
        'cust_num' => $cust_num, // Use cust_num from form input
        'location_id' => $locationID,
        'delivery_fee' => $delivery_fee, // Include delivery fee
        'total_price' => $grand_total, // Use grand total
        'cust_note' => $custNote
    ]);

    // Get the last inserted TransacID
    $transac_id = $conn->lastInsertId();

    // Insert items from CartTb into CartRecordTb
    foreach ($cart_items as $item) {
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
            'price' => $item['RetailPrice']
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

    // Redirect or provide a success message
    echo "<script>window.location.href = '../../../../views/customer_view.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container-fluid mt-5">
        <div class="row">
            <div class="col-7">
                <?php include('../transac_payment.php'); ?>
            </div>
            <div class="col-4 ms-4 p-4 border rounded">
                <h2>Checkout</h2>
                <hr>
                <form method="POST">
                    <div class="mb-3">
                        <label for="cust_num" class="form-label">Contact Number:</label>
                        <input type="text" class="form-control" name="cust_num" id="cust_num" required>
                    </div>

                    <div class="mb-3">
                        <label for="cust_note" class="form-label">Customer Note:</label>
                        <textarea class="form-control" name="cust_note" id="cust_note" rows="3" placeholder="Any specific instructions"></textarea>
                    </div>

                    <h5>Total Price: ₱<?= number_format($total_price, 2) ?></h5>
                    <h5>Delivery Fee: ₱<?= number_format($delivery_fee, 2) ?></h5>
                    <h5>Grand Total: ₱<?= number_format($grand_total, 2) ?></h5>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="payment_confirmation" name="payment_confirmation" required>
                        <label class="form-check-label" for="payment_confirmation">
                            I confirm that I understand I need to pay before proceeding to checkout.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Proceed to Checkout</button>
                </form>
                <button type="button" class="btn btn-secondary w-100" onclick="window.history.back();">Cancel</button>
            </div>
        </div>
    </div>
</body>
</html>
