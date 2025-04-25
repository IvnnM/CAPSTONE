<?php
include("./../../../../includes/cdn.html");
include("./../../../../config/database.php");

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fetch store details with debugging
$store_query = "
    SELECT s.StoreInfoID, 
           s.StoreGcashNum, 
           s.StoreGcashQR, 
           s.StoreDeliveryFee, 
           CONCAT(l.Province, ', ', l.City) AS Location,
           LENGTH(s.StoreGcashQR) as qr_length 
    FROM StoreInfoTb s
    JOIN LocationTb l ON s.LocationID = l.LocationID
    WHERE s.StoreInfoID = 2
";

$store_stmt = $conn->prepare($store_query);
$store_stmt->execute();
$store = $store_stmt->fetch(PDO::FETCH_ASSOC);

if (!$store) {
    echo "<script>alert('Store not found.'); window.history.back();</script>";
    exit;
}

// Debugging information
$debug_info = [];
$debug_info['QR Length'] = $store['qr_length'] ?? 'N/A';
$debug_info['QR Content Type'] = $store['StoreGcashQR'] ? substr(bin2hex($store['StoreGcashQR']), 0, 8) : 'N/A';

// Your existing cart query remains the same
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
$cart_stmt->bindParam(':cust_email', $_SESSION['cust_email'], PDO::PARAM_STR);
$cart_stmt->execute();
$cart_items = $cart_stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to check if data is a valid image
function isValidImageData($data) {
    if (empty($data)) return false;
    $signatures = [
        "\xFF\xD8\xFF" => 'image/jpeg',
        "\x89\x50\x4E\x47" => 'image/png',
    ];
    
    foreach ($signatures as $signature => $mime) {
        if (strncmp($data, $signature, strlen($signature)) === 0) {
            return $mime;
        }
    }
    return false;
}

// Fallback QR code path - adjust this path according to your project structure
$fallback_qr_path = "./../../../../assets/images/gcash-qr.png";
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
        
        .qr-code-container {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .qr-code-image {
            max-width: 200px;
            height: auto;
            margin: 10px 0;
        }
        
        .gcash-number {
            font-size: 1.2em;
            font-weight: bold;
            color: #0052cc;
            margin: 10px 0;
        }
        
        .card {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        
        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;  /* Enable vertical scrolling */
            max-height: 350px; /* Set a maximum height for scrolling */
        }
        /* Hide scrollbar but keep the content scrollable */
        .card-body::-webkit-scrollbar {
            display: none; /* Hide the scrollbar */
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
        }
        
        .col-md-6 {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .card-body {
            height: 100%;
        }

    </style>
</head>
<body>

<div class="container-fluid p-4">

    <div class="row">
        <!-- Gcash Details -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="qr-code-container">
                       <?php
                        // Function to mask part of the GCash number
                        function maskGcashNumber($number) {
                            // Show only the last 4 digits and mask the rest
                            return str_repeat('*', strlen($number) - 4) . substr($number, -4);
                        }
                        
                        $maskedGcashNumber = maskGcashNumber($store['StoreGcashNum']);
                        ?>
                        
                        <div class="gcash-number">
                            <?= htmlspecialchars($maskedGcashNumber) ?>
                        </div>

                        <?php
                        $qr_data = $store['StoreGcashQR'];
                        $mime_type = isValidImageData($qr_data);
                        
                        if ($qr_data && $mime_type): ?>
                            <img 
                                src="data:<?= $mime_type ?>;base64,<?= base64_encode($qr_data) ?>" 
                                alt="GCash QR Code" 
                                class="qr-code-image"
                            >
                        <?php else: ?>
                           <?php if (file_exists($fallback_qr_path)): ?>
                                <img 
                                    src="<?= $fallback_qr_path ?>" 
                                    alt="GCash QR Code" 
                                    class="qr-code-image"
                                >
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    QR Code not available. Please use the GCash number above.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Cart Details -->
        <div id="cart-details" class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if (!empty($cart_items)): ?>
                        <ul class="list-group">
                            <?php foreach ($cart_items as $item): ?>
                                <li class="list-group-item">
                                    <strong><?= htmlspecialchars($item['ProductName']) ?></strong><br>
                                    Quantity: <?= htmlspecialchars($item['Quantity']) ?><br>
                                    Price: ₱<?= number_format($item['Price'], 2) ?><br>
                                    Total: ₱<?= number_format($item['TotalPrice'], 2) ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <p class="text-muted">Your cart is empty.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Download Button -->
            <div class="text-center mt-4">
                <a href="download_receipt.php" class="btn btn-primary w-100">Download List</a>
            </div>
        </div>
    </div>
    
    
</div>

</body>
</html>



