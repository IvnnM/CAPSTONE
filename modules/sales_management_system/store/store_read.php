<?php
session_start();
include("./../../../includes/cdn.php");
include("./../../../config/database.php");

// Get Transaction ID from the query parameter
$transaction_id = $_GET['transaction_id'] ?? null;

if (!$transaction_id) {
    echo "<script>alert('Invalid Transaction ID.'); window.history.back();</script>";
    exit;
}

// Fetch store details
$store_query = "
    SELECT s.StoreInfoID, s.StoreGcashNum, s.StoreGcashQR, CONCAT(l.Province, ', ', l.City) AS Location 
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

// Fetch transaction details based on Transaction ID and get ProductName
$transaction_query = "
    SELECT t.*, o.OnhandQty, o.RetailPrice, o.PromoPrice, o.MinPromoQty, p.ProductName, l.Province, l.City
    FROM TransacTb t
    JOIN OnhandTb o ON t.OnhandID = o.OnhandID
    JOIN InventoryTb i ON o.InventoryID = i.InventoryID
    JOIN ProductTb p ON i.ProductID = p.ProductID
    JOIN LocationTb l ON t.LocationID = l.LocationID
    WHERE t.TransacID = :transaction_id
";

$transaction_stmt = $conn->prepare($transaction_query);
$transaction_stmt->bindParam(':transaction_id', $transaction_id, PDO::PARAM_INT);
$transaction_stmt->execute();
$transaction_record = $transaction_stmt->fetch(PDO::FETCH_ASSOC);

if (!$transaction_record) {
    echo "<script>alert('Transaction not found.'); window.history.back();</script>";
    exit;
}

// Display the store information and transaction details
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            max-width: 600px;
        }
        h3 {
            text-align: center;
        }
        .store-info, .transaction-info {
            margin-bottom: 20px;
        }
        .store-info label, .transaction-info label {
            font-weight: bold;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .transaction-id {
            font-size: 2em; /* Make the Transaction ID bigger */
            color: #333;
        }
    </style>
</head>
<body style="border: solid;">
  <center>
    <h3>Store Information</h3>
    <div class="store-info">
        <p><label>Store GCash Number:</label> <?= htmlspecialchars($store['StoreGcashNum']) ?></p>
        <p><label>Store Location:</label> <?= htmlspecialchars($store['Location']) ?></p>
        <p>
            <label>Store GCash QR Code:</label><br>
            <?php if ($store['StoreGcashQR']): ?>
                <img src="data:image/png;base64,<?= base64_encode($store['StoreGcashQR']) ?>" alt="GCash QR Code" style="max-width: 100%; height: auto;">
            <?php else: ?>
                <p>No QR Code available.</p>
            <?php endif; ?>
        </p>
        <p>Include Transaction ID in your payment message.</p>
    </div>

    <div class="transaction-info">
        <h3>Transaction Details</h3>
        <p class="transaction-id">Transaction ID: <?= htmlspecialchars($transaction_record['TransacID']) ?></p>
        <p><label>Customer Name:</label> <?= htmlspecialchars($transaction_record['CustName']) ?></p>
        <p><label>Customer Number:</label> <?= htmlspecialchars($transaction_record['CustNum']) ?></p>
        <p><label>Customer Email:</label> <?= htmlspecialchars($transaction_record['CustEmail']) ?></p>
        <p><label>Customer Location:</label> <?= htmlspecialchars($transaction_record['Province']) ?>, <?= htmlspecialchars($transaction_record['City']) ?></p>
        <p><label>Product Name:</label> <?= htmlspecialchars($transaction_record['ProductName']) ?></p>
        <p><label>Quantity:</label> <?= htmlspecialchars($transaction_record['Quantity']) ?></p>
        <p><label>Price:</label> <?= htmlspecialchars($transaction_record['Price']) ?></p>
        <p><label>Delivery Fee:</label> <?= htmlspecialchars($transaction_record['DeliveryFee']) ?></p>
        <p><label>Total Price:</label> <?= htmlspecialchars($transaction_record['TotalPrice']) ?></p>
        <p><label>Transaction Date:</label> <?= htmlspecialchars($transaction_record['TransactionDate']) ?></p>
        <p><label>Status:</label> <?= htmlspecialchars($transaction_record['Status']) ?></p>
    </div>


    <div class="back-link">
        <a href="../transaction/available_product.php">Back to Products</a>
    </div>
    </center>
</body>
</html>
