<?php
session_start();

include("./../../../config/database.php");

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

// Display the store information
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Store Information</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <style>
        .store-info {
            margin-bottom: 20px;
        }
        .store-info label {
            font-weight: bold;
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <center>
        <div class="container">


            <h3>Store Information</h3>

            <hr style="border-top: 1px solid white;">

            <div class="store-info">
                <p><label>Store GCash Number:</label> <?= htmlspecialchars($store['StoreGcashNum']) ?></p>
                <p><label>Store Location:</label> <?= htmlspecialchars($store['Location']) ?></p>
                <p><label>Store Delivery Fee:</label> <?= htmlspecialchars($store['StoreDeliveryFee']) ?></p>
                <p>
                    <label>Store GCash QR Code:</label><br>
                    <?php if ($store['StoreGcashQR']): ?>
                        <img src="data:image/png;base64,<?= base64_encode($store['StoreGcashQR']) ?>" alt="GCash QR Code" style="max-width: 100%; height: auto;">
                    <?php else: ?>
                        <p>No QR Code available.</p>
                    <?php endif; ?>
                </p>
            </div>
            <a href="store_update.php">Edit Information</a><br>
            <a href="../../../views/admin_view.php#Store">Go back</a>
        </div>
    </center>
</body>
</html>

