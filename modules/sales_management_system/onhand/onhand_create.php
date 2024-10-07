<?php
session_start();
include("./../../../includes/cdn.php"); 
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = './../../../login.php';</script>";
    exit;
}

// Check if an Inventory ID is provided in the URL
if (isset($_GET['inventory_id'])) {
    $inventory_id = $_GET['inventory_id'];

    // Fetch inventory details for display
    $inventory_query = "SELECT i.*, p.ProductName, c.CategoryName 
                        FROM InventoryTb i 
                        JOIN ProductTb p ON i.ProductID = p.ProductID 
                        JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID 
                        WHERE i.InventoryID = :inventory_id";
    $inventory_stmt = $conn->prepare($inventory_query);
    $inventory_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
    $inventory_stmt->execute();
    $inventory = $inventory_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inventory) {
        echo "<script>alert('Inventory not found.'); window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid inventory ID.'); window.history.back();</script>";
    exit;
}

// Handle form submission for creating or updating on-hand record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $onhand_qty = $_POST['onhand_qty'];
    $retail_price = $_POST['retail_price'];
    $min_promo_qty = $_POST['min_promo_qty'];
    $promo_price = $_POST['promo_price'];

    // Check if there is an existing record for this Inventory ID
    $check_query = "SELECT OnhandID, OnhandQty FROM OnhandTb WHERE InventoryID = :inventory_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $existing_record = $check_stmt->fetch(PDO::FETCH_ASSOC);

    // Calculate the new inventory quantity
    $new_inventory_qty = $inventory['InventoryQty'] - $onhand_qty;

    if ($existing_record) {
        // If record exists, update the Onhand Quantity
        $new_onhand_qty = $existing_record['OnhandQty'] + $onhand_qty;

        $update_query = "UPDATE OnhandTb SET OnhandQty = :onhand_qty, RetailPrice = :retail_price, MinPromoQty = :min_promo_qty, PromoPrice = :promo_price WHERE OnhandID = :onhand_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':onhand_qty', $new_onhand_qty, PDO::PARAM_INT);
        $update_stmt->bindParam(':retail_price', $retail_price, PDO::PARAM_STR);
        $update_stmt->bindParam(':min_promo_qty', $min_promo_qty, PDO::PARAM_INT);
        $update_stmt->bindParam(':promo_price', $promo_price, PDO::PARAM_STR);
        $update_stmt->bindParam(':onhand_id', $existing_record['OnhandID'], PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            // Update the InventoryTb with the new quantity
            $inventory_update_query = "UPDATE InventoryTb SET InventoryQty = :inventory_qty WHERE InventoryID = :inventory_id";
            $inventory_update_stmt = $conn->prepare($inventory_update_query);
            $inventory_update_stmt->bindParam(':inventory_qty', $new_inventory_qty, PDO::PARAM_INT);
            $inventory_update_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
            $inventory_update_stmt->execute();

            echo "<script>alert('Onhand record updated successfully!');</script>";
            echo"<script>window.history.back();</script>";
            exit;
        } else {
            echo "<script>alert('Error: Could not update onhand record.');</script>";
        }
    } else {
        // If no record exists, insert a new on-hand record
        $insert_query = "INSERT INTO OnhandTb (InventoryID, OnhandQty, RetailPrice, MinPromoQty, PromoPrice) VALUES (:inventory_id, :onhand_qty, :retail_price, :min_promo_qty, :promo_price)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':onhand_qty', $onhand_qty, PDO::PARAM_INT);
        $insert_stmt->bindParam(':retail_price', $retail_price, PDO::PARAM_STR);
        $insert_stmt->bindParam(':min_promo_qty', $min_promo_qty, PDO::PARAM_INT);
        $insert_stmt->bindParam(':promo_price', $promo_price, PDO::PARAM_STR);

        if ($insert_stmt->execute()) {
            // Update the InventoryTb with the new quantity
            $inventory_update_query = "UPDATE InventoryTb SET InventoryQty = :inventory_qty WHERE InventoryID = :inventory_id";
            $inventory_update_stmt = $conn->prepare($inventory_update_query);
            $inventory_update_stmt->bindParam(':inventory_qty', $new_inventory_qty, PDO::PARAM_INT);
            $inventory_update_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
            $inventory_update_stmt->execute();

            echo "<script>alert('Onhand record added successfully!');</script>";
        } else {
            echo "<script>alert('Error: Could not add onhand record.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Onhand Record</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <script>
        function confirmCreation(event) {
            if (!confirm('Are you sure you want to create this onhand record?')) {
                event.preventDefault();
            }
        }

        // Function to validate onhand quantity against inventory quantity
        function validateQuantity() {
            const inventoryQty = parseInt(document.getElementById('inventory_qty').value);
            const onhandQty = parseInt(document.getElementById('onhand_qty').value);
            if (onhandQty > inventoryQty) {
                alert("Onhand Quantity cannot exceed Inventory Quantity.");
                document.getElementById('onhand_qty').value = inventoryQty; // Set to max allowed
            }
        }
    </script>
    <style>
        label, .form-control {
            font-size: small;
        }
    </style>
</head>
<body>
    <h1 class="mb-4">Onhand Form</h1>
    <hr style="border-top: 1px solid white;">
    <h6>Inventory Information</h6>
    <form method="POST" action="" onsubmit="confirmCreation(event)">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="product_name">Product Name:</label>
                <input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars($inventory['ProductName']) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="category_name">Category:</label>
                <input type="text" class="form-control" name="category_name" value="<?= htmlspecialchars($inventory['CategoryName']) ?>" readonly>
            </div>
        </div>

        <input type="hidden" name="inventory_id" value="<?= htmlspecialchars($inventory['InventoryID']) ?>">

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="inventory_qty">Inventory Quantity:</label>
                <input type="text" class="form-control" id="inventory_qty" value="<?= htmlspecialchars($inventory['InventoryQty']) ?>" readonly>
            </div>

        </div>
        <hr style="border-top: 1px solid white;">
        <h6>Set Quantity to Sell</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="onhand_qty">Onhand Quantity:</label>
                <input type="number" class="form-control" id="onhand_qty" name="onhand_qty" min="1" max="<?= htmlspecialchars($inventory['InventoryQty']) ?>" required oninput="validateQuantity()">
            </div>
        </div>
        <hr style="border-top: 1px solid white;">
        <h6>Set Prices</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="retail_price">Retail Price:</label>
                <input type="text" class="form-control" name="retail_price" required>
            </div>
            <div class="col-md-6">
                <label for="min_promo_qty">Minimum Promo Quantity:</label>
                <input type="number" class="form-control" name="min_promo_qty" min="1" required>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="promo_price">Promo Price:</label>
                <input type="text" class="form-control" name="promo_price" required>
            </div>
        </div>

        <button class="btn btn-success" type="submit">Create</button>
    </form>

    <br>
    <a href="onhand_read.php">Go to Onhand List</a>
    <br><br>
    <a href="../../inventory_management_system/inventory/inventory_read.php">Go to Inventory List</a>
</body>
</html>
