<?php
// checkout_view.php
session_start();
require_once("../../../../config/database.php");

// Get customer session data
$custName = $_SESSION['cust_name'] ?? 'Guest';
$custNum = $_SESSION['cust_num'] ?? 'N/A';
$custEmail = $_SESSION['cust_email'] ?? 'N/A';

// Calculate total price from cart items
function calculateTotalPrice($conn, $custEmail) {
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
    
    return $totalPrice;
}

// Get the total price
$total_price = calculateTotalPrice($conn, $custEmail);

// Get delivery locations
$stmt = $conn->prepare("SELECT LocationID, Province, City FROM LocationTb");
$stmt->execute();
$locations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="../../../../assets/css/form.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6/turf.min.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
    <script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
    
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --error-color: #dc3545;
        }

        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        .checkout-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .checkout-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .form-section:last-child {
            border-bottom: none;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.25rem;
            margin-bottom: 1rem;
        }

        #map {
            height: 400px;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .price-summary {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }

        .grand-total {
            font-size: 1.25rem;
            font-weight: bold;
            color: var(--primary-color);
            border-top: 2px solid #dee2e6;
            padding-top: 0.5rem;
        }

        .location-info {
            background-color: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .invalid-location {
            background-color: #fff3f3;
            color: var(--error-color);
            padding: 0.75rem;
            border-radius: 0.5rem;
            margin-top: 1rem;
            display: none;
        }

        .loading-spinner {
            display: none;
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
            border: 0.2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spin 0.75s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="checkout-container">
    <!--<div>-->
    <!--    <h2>Customer Information</h2>-->
    <!--    <p>Name: <?php echo htmlspecialchars($custName); ?></p>-->
    <!--    <p>Phone Number: <?php echo htmlspecialchars($custNum); ?></p>-->
    <!--    <p>Email: <?php echo htmlspecialchars($custEmail); ?></p>-->
    <!--</div>-->
        <form id="checkoutForm" method="POST" action="checkout_process.php">
            <div class="checkout-card">
                <div class="p-3 mb-4 shadow rounded-3" style="background-color:#003366;">
                    <h2 class="text-center text-light">CHECKOUT</h2>
                </div>

                <!-- Delivery Instructions -->
                <div class="form-section">
                    <h3 class="section-title">Delivery Instructions</h3>
                    <div class="mb-3">
                        <label for="cust_note" class="form-label">Special Instructions (Optional)</label>
                        <textarea class="form-control" name="cust_note" id="cust_note" 
                                rows="3" placeholder="Any special instructions for delivery?"></textarea>
                    </div>
                </div>

                <!-- Location Section -->
                <div class="form-section">
                    <h3 class="section-title">Delivery Location</h3>
                    <!-- Location Selection -->
                    <div class="location-info">
                        <div class="mb-2">
                            <strong>Selected Area:</strong>
                            <span id="selected-province">Not selected</span>,
                            <span id="selected-city">Not selected</span>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="useCurrentLocation">
                            <label class="form-check-label" for="useCurrentLocation">
                                Use my current location
                            </label>
                        </div>
                    </div>

                    <div id="map"></div>    
                    <div class="invalid-location">
                        ⚠️ Selected location is outside our delivery area
                    </div>
                    
                    <input type="hidden" name="exact_coordinates" id="exact_coordinates" required>
                    <input type="hidden" name="location_id" id="location_id" required>
                    <input type="hidden" name="delivery_fee" id="delivery_fee_input" required>
                </div>
    
                <!-- Price Summary -->
                <div class="form-section">
                    <h3 class="section-title">Order Summary</h3>
                    <?php include("../transac_payment.php");?>
                    <div class="price-summary">
                        <div class="price-row">
                            <span>Subtotal</span>
                            <span id="subtotal">₱<?= number_format($total_price, 2) ?></span>
                        </div>
                        <div class="price-row">
                            <span>Delivery Fee</span>
                            <span id="delivery_fee">₱0.00</span>
                        </div>
                        <div class="price-row grand-total">
                            <span>Total</span>
                            <span id="grand_total">₱<?= number_format($total_price, 2) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Payment Confirmation -->
                <div class="form-section">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="payment_confirmation" 
                               name="payment_confirmation" required>
                        <label class="form-check-label" for="payment_confirmation">
                            I confirm that I will pay the total amount
                        </label>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success" id="checkoutButton">
                        <span class="loading-spinner"></span>
                        Complete Checkout
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="window.history.back();">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
        
    </div>

    <!-- Then your existing GeoJSON scripts -->
    <script src="./geojsonData/BATANGAS.js"></script>
    <script src="./geojsonData/CAVITE.js"></script>
    <script src="./geojsonData/LAGUNA.js"></script>
    <script src="./geojsonData/QUEZON.js"></script>
    <script src="./geojsonData/RIZAL.js"></script>

    <!-- Add these global variables before loading map_handler.js -->
    <script>
        window.locationData = <?= json_encode($locations) ?>;
    </script>

    <!-- Then your map handler script -->
    <script src="map_handler.js"></script>
    <script>
       document.addEventListener('DOMContentLoaded', async () => {
        // Initialize global mapHandler
        window.mapHandler = new MapHandler({
            locationData: window.locationData
        });
        await window.mapHandler.initialize();
    
        // Form submission handling
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if location is selected
            const locationId = document.getElementById('location_id').value;
            const coordinates = document.getElementById('exact_coordinates').value;
            
            if (!locationId || !coordinates) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Please select a valid delivery location',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
    
            // Show SweetAlert confirmation
            Swal.fire({
                title: 'Confirm Checkout',
                text: 'Are you sure you want to complete this checkout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, complete checkout!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading spinner
                    const button = document.getElementById('checkoutButton');
                    const spinner = button.querySelector('.loading-spinner');
                    button.disabled = true;
                    spinner.style.display = 'inline-block';
    
                    // Submit the form
                    this.submit();
                }
            });
        });
    });
    </script>
    
</body>
</html>