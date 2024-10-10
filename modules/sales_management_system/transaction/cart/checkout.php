<?php
session_start();
include("./../../../../includes/cdn.html");
include("./../../../../config/database.php");

// Fetch session data for customer email and number
$cust_email = $_SESSION['cust_email'];
$cust_num = $_SESSION['cust_num']; // Assuming CustNum is stored in the session

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

// Fetch store location and delivery fee from StoreInfoTb
$store_query = "SELECT LocationID, StoreDeliveryFee FROM StoreInfoTb LIMIT 1";
$store_stmt = $conn->prepare($store_query);
$store_stmt->execute();
$store = $store_stmt->fetch(PDO::FETCH_ASSOC);

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $locationID = trim($_POST['location_id']);
    $custNote = trim($_POST['cust_note']);
    
    // Calculate delivery fee based on the distance between the customer's location and the store's location
    $delivery_fee = calculateDeliveryFee($locationID, $store['LocationID'], $conn, $store['StoreDeliveryFee']);
    
    $total_price_with_delivery = $total_price + $delivery_fee;

    // Insert data into TransacTb
    $insert_query = "INSERT INTO TransacTb (CustName, CustEmail, CustNum, LocationID, DeliveryFee, TotalPrice, Status, CustNote) 
                    VALUES (:cust_name, :cust_email, :cust_num, :location_id, :delivery_fee, :total_price, 'Pending', :cust_note)";
    $stmt = $conn->prepare($insert_query);
    $stmt->execute([
        'cust_name' => $_SESSION['cust_name'],
        'cust_email' => $cust_email,
        'cust_num' => $cust_num,
        'location_id' => $locationID,
        'delivery_fee' => $delivery_fee,
        'total_price' => $total_price_with_delivery,
        'cust_note' => $custNote
    ]);

    // Update OnhandTb to subtract the purchased quantity
    foreach ($cart_items as $item) {
        $update_query = "UPDATE OnhandTb SET OnhandQty = OnhandQty - :quantity WHERE OnhandID = :onhand_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->execute([
            'quantity' => $item['Quantity'],
            'onhand_id' => $item['OnhandID']
        ]);
    }

    // Redirect or provide a success message
    echo "<script>alert('Checkout completed successfully.'); window.location.href = '../../../../views/customer_view.php';</script>";
}

// Function to calculate delivery fee based on distance
function calculateDeliveryFee($customerLocationID, $storeLocationID, $conn, $storeDeliveryFee) {
    // Fetch latitude and longitude for the customer location
    $cust_location_query = "SELECT LatLng FROM LocationTb WHERE LocationID = :customerLocationID";
    $cust_location_stmt = $conn->prepare($cust_location_query);
    $cust_location_stmt->bindParam(':customerLocationID', $customerLocationID, PDO::PARAM_INT);
    $cust_location_stmt->execute();
    $cust_location = $cust_location_stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch latitude and longitude for the store location
    $store_location_query = "SELECT LatLng FROM LocationTb WHERE LocationID = :storeLocationID";
    $store_location_stmt = $conn->prepare($store_location_query);
    $store_location_stmt->bindParam(':storeLocationID', $storeLocationID, PDO::PARAM_INT);
    $store_location_stmt->execute();
    $store_location = $store_location_stmt->fetch(PDO::FETCH_ASSOC);

    if ($cust_location && $store_location) {
        // Parse latitude and longitude
        list($cust_lat, $cust_lng) = explode(';', $cust_location['LatLng']);
        list($store_lat, $store_lng) = explode(';', $store_location['LatLng']);

        // Calculate the distance using the Haversine formula
        $distance = haversineGreatCircleDistance($cust_lat, $cust_lng, $store_lat, $store_lng);

        // Calculate the delivery fee based on distance and store's delivery fee
        $calculated_fee = $storeDeliveryFee * $distance; // Fee per km
        return max($calculated_fee, $storeDeliveryFee); // Ensure minimum fee is StoreDeliveryFee
    }

    return $storeDeliveryFee; // Return StoreDeliveryFee if location is not found
}

// Function to calculate the distance using Haversine formula
function haversineGreatCircleDistance($latFrom, $lonFrom, $latTo, $lonTo, $earthRadius = 6371) {
    // Convert from degrees to radians
    $latFrom = deg2rad($latFrom);
    $lonFrom = deg2rad($lonFrom);
    $latTo = deg2rad($latTo);
    $lonTo = deg2rad($lonTo);

    // Haversine formula
    $lonDelta = $lonTo - $lonFrom;
    $latDelta = $latTo - $latFrom;
    
    $a = sin($latDelta / 2) * sin($latDelta / 2) +
         cos($latFrom) * cos($latTo) *
         sin($lonDelta / 2) * sin($lonDelta / 2);
    
    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
    
    return $earthRadius * $c; // Returns distance in kilometers
}
?>

<script>
    $(document).ready(function() {
        // Fetch and populate province and city data
        $.ajax({
            url: "../../../../includes/get_location_data.php",
            method: "GET",
            dataType: "json",
            success: function(data) {
                var provinces = data.provinces;
                var cities = data.cities;
                var provinceDropdown = $("#province");
                var cityDropdown = $("#city");

                // Populate province dropdown
                provinces.forEach(function(province) {
                    provinceDropdown.append(
                        $("<option>").val(province.Province).text(province.Province)
                    );
                });
                console.log(data);

                // Event listener for province change
                provinceDropdown.change(function() {
                    var selectedProvince = $(this).val();
                    cityDropdown.empty();
                    cityDropdown.append("<option value=''>Select City</option>");

                    // Filter and populate city dropdown based on selected province
                    cities.forEach(function(city) {
                        if (city.Province === selectedProvince) {
                            cityDropdown.append(
                                $("<option>").val(city.LocationID).text(city.City)
                            );
                        }
                    });
                });
            },
            error: function() {
                alert("Error: Could not retrieve location data.");
            }
        });

        // Update LocationID based on selected city
        $("#city").change(function() {
            $("#location_id").val($(this).val());
        });
    });
</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Checkout</h2>
        
        <form method="POST">
            <div class="mb-3">
                <label for="province" class="form-label">Province:</label>
                <select id="province" name="province" class="form-select" required>
                    <option value="">Select Province</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label for="city" class="form-label">City:</label>
                <select id="city" name="city" class="form-select" required>
                    <option value="">Select City</option>
                </select>
                <!-- Hidden field to store selected LocationID -->
                <input type="hidden" name="location_id" id="location_id" required>
            </div>

            <div class="mb-3">
                <label for="cust_note" class="form-label">Customer Note:</label>
                <textarea class="form-control" name="cust_note" id="cust_note" rows="3" placeholder="Any specific instructions"></textarea>
            </div>

            <h5>Total Price: <?= number_format($total_price, 2) ?></h5>

            <button type="submit" class="btn btn-primary">Proceed to Checkout</button>
        </form>
    </div>
</body>
</html>
