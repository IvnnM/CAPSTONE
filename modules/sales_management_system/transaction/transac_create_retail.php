<?php
session_start();
include("./../../../includes/cdn.php"); 
include("./../../../config/database.php");

// Get Onhand ID from the query parameter
$onhand_id = $_GET['onhand_id'] ?? null;

if (!$onhand_id) {
    echo "<script>alert('Invalid Onhand ID.'); window.location.href='available_product.php';</script>";
    exit;
}

// Fetch the product details based on Onhand ID
$price_query = "SELECT RetailPrice, MinPromoQty, OnhandQty FROM OnhandTb WHERE OnhandID = :onhand_id";
$price_stmt = $conn->prepare($price_query);
$price_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);
$price_stmt->execute();
$price_record = $price_stmt->fetch(PDO::FETCH_ASSOC);

if (!$price_record) {
    echo "<script>alert('Product not found.'); window.location.href='available_product.php';</script>";
    exit;
}

// Fetch store location and delivery fee from StoreInfoTb
$store_query = "SELECT LocationID, StoreDeliveryFee FROM StoreInfoTb LIMIT 1";
$store_stmt = $conn->prepare($store_query);
$store_stmt->execute();
$store = $store_stmt->fetch(PDO::FETCH_ASSOC);

if (!$store) {
    echo "<script>alert('Store location not found.'); window.location.href='available_product.php';</script>";
    exit;
}

// Handle form submission for creating a transaction
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cust_name = $_POST['cust_name'];
    $cust_num = $_POST['cust_num'];
    $cust_email = $_POST['cust_email'];
    $location_id = $_POST['location_id'];
    $quantity = $_POST['quantity'];
    $cust_note = $_POST['cust_note']; // New field for customer note

    // Check if quantity exceeds MinPromoQty
    if ($quantity >= $price_record['MinPromoQty']) {
        echo "<script>alert('Quantity exceeds minimum promo quantity. Please purchase using the promo option.');</script>";
    } else {
        $price = $price_record['RetailPrice'];

        // Calculate delivery fee based on the distance between the customer's location and the store's location
        $delivery_fee = calculateDeliveryFee($location_id, $store['LocationID'], $conn, $store['StoreDeliveryFee']);
        
        $total_price = ($price * $quantity) + $delivery_fee;

        // Insert the new transaction record including CustNote
        $insert_query = "INSERT INTO TransacTb (CustName, CustNum, CustEmail, LocationID, OnhandID, Price, Quantity, DeliveryFee, TotalPrice, TransactionDate, Status, CustNote) 
                         VALUES (:cust_name, :cust_num, :cust_email, :location_id, :onhand_id, :price, :quantity, :delivery_fee, :total_price, NOW(), 'Pending', :cust_note)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindParam(':cust_name', $cust_name);
        $insert_stmt->bindParam(':cust_num', $cust_num);
        $insert_stmt->bindParam(':cust_email', $cust_email);
        $insert_stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':price', $price);
        $insert_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $insert_stmt->bindParam(':delivery_fee', $delivery_fee, PDO::PARAM_STR);
        $insert_stmt->bindParam(':total_price', $total_price, PDO::PARAM_STR);
        $insert_stmt->bindParam(':cust_note', $cust_note, PDO::PARAM_STR); // Bind the customer note

        if ($insert_stmt->execute()) {
            // Get the last inserted transaction ID
            $transaction_id = $conn->lastInsertId(); 
            echo "<script>alert('Transaction created successfully!'); window.location.href='./../store/store_read.php?transaction_id={$transaction_id}';</script>";
        } else {
            echo "<script>alert('Error: Could not create transaction.');</script>";
        }        
    }
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
        // Set minimum delivery fee to StoreDeliveryFee
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

<!DOCTYPE html>
<html>
<head>
    <title>Create Retail Transaction</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Fetch and populate province and city data
            $.ajax({
                url: "./../../../includes/get_location_data.php", // Adjust the path as necessary
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

                    // Event listener for province change
                    provinceDropdown.change(function() {
                        var selectedProvince = $(this).val();
                        updateCityDropdown(selectedProvince, null, cities);
                    });

                    // Function to update city dropdown
                    function updateCityDropdown(province, selectedCity, cities) {
                        cityDropdown.empty(); // Clear existing cities
                        cityDropdown.append("<option value=''>Select City</option>");
                        
                        // Filter and populate city dropdown based on selected province
                        cities.forEach(function(city) {
                            if (city.Province === province) {
                                var cityOption = $("<option>").val(city.LocationID).text(city.City);
                                cityDropdown.append(cityOption);
                            }
                        });
                    }
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
</head>
<body>
    <h3>Create Retail Transaction</h3>
    <form method="POST" action="">
        <label for="cust_name">Customer Name:</label>
        <input type="text" name="cust_name" required><br>

        <label for="cust_num">Customer Number:</label>
        <input type="text" name="cust_num" required><br>

        <label for="cust_email">Customer Email:</label>
        <input type="email" name="cust_email" required><br>

        <label for="cust_note">Customer Note:</label>
        <textarea name="cust_note" rows="4" cols="50"></textarea><br> <!-- New field for customer note -->

        <label for="province">Province:</label>
        <select id="province" name="province" required>
            <option value="">Select Province</option>
        </select><br>

        <label for="city">City:</label>
        <select id="city" name="city" required>
            <option value="">Select City</option>
        </select><br>

        <!-- Hidden field to store the selected LocationID -->
        <input type="hidden" name="location_id" id="location_id" required><br>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" min="1" required><br>

        <button type="submit">Create Transaction</button>
    </form>
    <br>
    <a href="available_product.php">Go to Available Product</a>
</body>
</html>
