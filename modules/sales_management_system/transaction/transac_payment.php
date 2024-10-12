<?php
include("./../../../../includes/cdn.html");
include("./../../../../config/database.php");

// Fetch store details
$store_query = "
    SELECT s.StoreInfoID, s.StoreGcashNum, s.StoreGcashQR, s.StoreDeliveryFee, CONCAT(l.Province, ', ', l.City) AS Location 
    FROM StoreInfoTb s
    JOIN LocationTb l ON s.LocationID = l.LocationID
";

$store_stmt = $conn->prepare($store_query);
$store_stmt->execute();
$store = $store_stmt->fetch(PDO::FETCH_ASSOC);

if (!$store) {
    echo "<script>alert('Store not found.'); window.history.back();</script>";
    exit;
}

// Fetch cart items (assuming session for customer is available)
$cart_query = "
    SELECT p.ProductName, c.Quantity, 
           o.RetailPrice, 
           o.MinPromoQty, 
           o.PromoPrice,
           CASE 
               WHEN c.Quantity >= o.MinPromoQty THEN o.PromoPrice 
               ELSE o.RetailPrice 
           END AS Price, 
           (c.Quantity * 
               CASE 
                   WHEN c.Quantity >= o.MinPromoQty THEN o.PromoPrice 
                   ELSE o.RetailPrice 
               END) AS TotalPrice
    FROM CartTb c
    JOIN OnhandTb o ON c.OnhandID = o.OnhandID
    JOIN ProductTb p ON o.InventoryID = p.ProductID
    WHERE c.CustEmail = :cust_email
";

$cart_stmt = $conn->prepare($cart_query);
$cart_stmt->bindParam(':cust_email', $_SESSION['cust_email'], PDO::PARAM_STR); // Assuming customer email is in the session
$cart_stmt->execute();
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Debugging: Check if the cart items are fetched
if ($cart_stmt->rowCount() == 0) {
    echo "<script>alert('No items found in the cart for this email.');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .store-info {
            margin-bottom: 20px;
        }
        .store-info label {
            font-weight: bold;
        }
        .cart-table th, .cart-table td {
            padding: 10px;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12 mb-2">
            <!-- Cart Table -->
            <div class="row">
                <div class="col-4">
                    <h4>Payment</h4>
                    <p><strong>GCash: <?= htmlspecialchars($store['StoreGcashNum']) ?></strong></p>
                    <?php if ($store['StoreGcashQR']): ?>
                        <img src="data:image/png;base64,<?= base64_encode($store['StoreGcashQR']) ?>" alt="GCash QR Code" style="max-width: 100%; height: auto;">
                    <?php else: ?>
                        <p>No QR Code available.</p>
                    <?php endif; ?>
                </div>
                <div class="col">
                    <h4>Items in Your Cart</h4>
                    <table class="table cart-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($cart_items)): ?>
                                <?php foreach ($cart_items as $item): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['ProductName']) ?></td>
                                        <td><?= htmlspecialchars($item['Quantity']) ?></td>
                                        <td>₱<?= number_format($item['Price'], 2) ?></td>
                                        <td>₱<?= number_format($item['TotalPrice'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Your cart is empty.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>
