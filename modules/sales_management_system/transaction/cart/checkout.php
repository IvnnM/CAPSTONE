<?php
session_start();
include("../../../../includes/cdn.html"); 
include("../../../../config/database.php");

// Check if the customer is logged in
if (!isset($_SESSION['cust_email'])) {
    echo "<p class='text-danger'>Please log in to view your cart.</p>";
    exit();
}

// Fetch cart items for the current customer
$cust_email = $_SESSION['cust_email'];
$query = "SELECT c.CartID, p.ProductName, c.Quantity, c.AddedDate, o.RetailPrice, o.OnhandID
          FROM CartTb c
          JOIN OnhandTb o ON c.OnhandID = o.OnhandID
          JOIN InventoryTb i ON o.InventoryID = i.InventoryID
          JOIN ProductTb p ON i.ProductID = p.ProductID
          WHERE c.CustEmail = :cust_email";
$stmt = $conn->prepare($query);
$stmt->execute(['cust_email' => $cust_email]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Check if the cart is empty
if (empty($cart_items)) {
    echo "<script>alert('Your cart is empty!'); window.location.href='customer_view.php';</script>";
    exit();
}

// Fetch store location and delivery fee from StoreInfoTb
$store_query = "SELECT LocationID, StoreDeliveryFee FROM StoreInfoTb LIMIT 1";
$store_stmt = $conn->prepare($store_query);
$store_stmt->execute();
$store = $store_stmt->fetch(PDO::FETCH_ASSOC);

// Handle form submission for creating transactions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $cust_name = $_SESSION['cust_name']; // Assuming you store this in session
  $cust_num = $_SESSION['cust_num']; // Assuming you store this in session
  $location_id = $_POST['location_id'];
  $cust_note = $_POST['cust_note']; // New field for customer note

  // Initialize a flag to track if all transactions were successful
  $allTransactionsSuccessful = true;

  // Loop through each item in the cart and create a transaction
  foreach ($cart_items as $item) {
    $quantity = $item['Quantity']; // Quantity from cart item
    $onhand_id = $item['OnhandID']; // Onhand ID from cart item
    $price = $item['RetailPrice']; // Price from cart item

    // Calculate delivery fee based on the distance between the customer's location and the store's location
    $delivery_fee = calculateDeliveryFee($location_id, $store['LocationID'], $conn, $store['StoreDeliveryFee']);
    
    $total_price = ($price * $quantity) + $delivery_fee;

    // Insert the new transaction record including CustNote
    $insert_query = "INSERT INTO TransacTb (CustName, CustNum, CustEmail, LocationID, OnhandID, Price, Quantity, DeliveryFee, TotalPrice, TransactionDate, Status, CustNote) 
                    VALUES (:cust_name, :cust_num, :cust_email, :location_id, :onhand_id, :price, :quantity, :delivery_fee, :total_price, NOW(), 'Pending', :cust_note)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bindParam(':cust_name', $cust_name);
    $insert_stmt->bindParam(':cust_num', $cust_num);
    $insert_stmt->bindParam(':cust_email', $_SESSION['cust_email']);
    $insert_stmt->bindParam(':location_id', $location_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);
    $insert_stmt->bindParam(':price', $price);
    $insert_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
    $insert_stmt->bindParam(':delivery_fee', $delivery_fee, PDO::PARAM_STR);
    $insert_stmt->bindParam(':total_price', $total_price, PDO::PARAM_STR);
    $insert_stmt->bindParam(':cust_note', $cust_note, PDO::PARAM_STR); // Bind the customer note

    if ($insert_stmt->execute()) {
        // Update OnhandQty in OnhandTb after transaction creation
        $update_query = "UPDATE OnhandTb SET OnhandQty = OnhandQty - :quantity WHERE OnhandID = :onhand_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
        $update_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);
        
        if (!$update_stmt->execute()) {
            $allTransactionsSuccessful = false; // Set flag to false if the update fails
            echo "<script>alert('Error: Could not update Onhand quantity for product with OnhandID $onhand_id.');</script>";
        }
    } else {
        $allTransactionsSuccessful = false; // Set flag to false if any transaction fails
        echo "<script>alert('Error: Could not create transaction for product with OnhandID $onhand_id.');</script>";
    }
  }


  // If all transactions were successful, delete cart items
  if ($allTransactionsSuccessful) {
      $delete_query = "DELETE FROM CartTb WHERE CustEmail = :cust_email";
      $delete_stmt = $conn->prepare($delete_query);
      $delete_stmt->bindParam(':cust_email', $_SESSION['cust_email']);
      
      if ($delete_stmt->execute()) {
          echo "<script>alert('Transactions created successfully and cart items removed!'); window.location.href='../transac_payment.php';</script>";
      } else {
          echo "<script>alert('Error: Could not remove cart items.');</script>";
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
                url: "../../../../includes/get_location_data.php", // Adjust the path as necessary
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.min.css">
</head>
<body>
    <div class="container">
        <h2>Create Retail Transaction</h2>
        <form method="POST" action="">
            <div class="mb-3" hidden>
                <label for="location_id" class="form-label">Location ID:</label>
                <input type="text" id="location_id" name="location_id" class="form-control" required readonly>
            </div>
            <div class="mb-3">
                <label for="cust_note" class="form-label">Customer Note:</label>
                <textarea id="cust_note" name="cust_note" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
                <label for="province" class="form-label">Province:</label>
                <select id="province" class="form-select" required>
                    <option value="">Select Province</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="city" class="form-label">City:</label>
                <select id="city" class="form-select" required>
                    <option value="">Select City</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Create Transaction</button>
        </form>

        <h3>Your Cart</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Added Date</th>
                    <th>Retail Price</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                        <td><?php echo htmlspecialchars($item['Quantity']); ?></td>
                        <td><?php echo htmlspecialchars($item['AddedDate']); ?></td>
                        <td><?php echo htmlspecialchars($item['RetailPrice']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
