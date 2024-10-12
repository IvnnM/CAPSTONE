<?php
session_start();
include("./../../../../includes/cdn.html");
include("./../../../../config/database.php");
include("calculate_delivery_fee.php"); // Include delivery fee calculation function

// Fetch session data for customer email and number
$cust_email = $_SESSION['cust_email'];
$cust_num = $_SESSION['cust_num'];

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

    // Calculate delivery fee
    $delivery_fee = calculateDeliveryFee($locationID, $store['LocationID'], $conn, $store['StoreDeliveryFee']);
    if ($delivery_fee === false) {
        echo "<script>alert('Error calculating delivery fee. Please try again.');</script>";
        return;
    }

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

        $("#city").change(function() {
            var locationID = $(this).val();
            $("#location_id").val(locationID);

            // Call the backend to calculate the delivery fee
            $.ajax({
                url: './calculate_delivery_fee.php',
                method: 'POST',
                contentType: 'application/json', // Set content type
                data: JSON.stringify({
                    location_id: locationID,
                    store_location_id: <?= json_encode($store['LocationID']) ?>,
                    store_delivery_fee: <?= json_encode($store['StoreDeliveryFee']) ?>,
                    is_checkout: 'false' 
                }),
                dataType: 'json', // Expect JSON response
                success: function(response) {
                    if (response && response.delivery_fee !== undefined) {
                        var deliveryFee = parseFloat(response.delivery_fee).toFixed(2);
                        var totalPriceWithDelivery = (parseFloat(<?= $total_price ?>) + parseFloat(deliveryFee)).toFixed(2);

                        // Update the UI with delivery fee and total price
                        $("#delivery_fee").text("Delivery Fee: ₱" + deliveryFee);
                        $("#total_price_with_delivery").text("Total Price (with Delivery): ₱" + totalPriceWithDelivery);
                    } else {
                        alert("Error: Invalid response from server.");
                    }
                },
                error: function() {
                    alert("Error: Could not calculate delivery fee.");
                }
            });
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
                        <input type="hidden" name="location_id" id="location_id" required>
                    </div>

                    <div class="mb-3">
                        <label for="cust_note" class="form-label">Customer Note:</label>
                        <textarea class="form-control" name="cust_note" id="cust_note" rows="3" placeholder="Any specific instructions"></textarea>
                    </div>

                    <h5>Total Price: ₱<?= number_format($total_price, 2) ?></h5>
                    <h5 id="delivery_fee">Delivery Fee: ₱0.00</h5>
                    <h5 id="total_price_with_delivery">Total Price (with Delivery): ₱<?= number_format($total_price, 2) ?></h5>

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
