<?php
session_start();
include("./../../../includes/cdn.html"); 
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    $_SESSION['alert'] = 'You must be logged in to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ./../../../login.php");
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
        $_SESSION['alert'] = 'Inventory not found.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: onhand_read.php");
        exit;
    }
} else {
    $_SESSION['alert'] = 'Invalid inventory ID.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: onhand_read.php");
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

            $_SESSION['alert'] = 'Onhand record updated successfully!';
            $_SESSION['alert_type'] = 'success';
            header("Location: onhand_read.php");
            exit;
        } else {
            $_SESSION['alert'] = 'Error: Could not update onhand record.';
            $_SESSION['alert_type'] = 'danger';
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

            $_SESSION['alert'] = 'Onhand record added successfully!';
            $_SESSION['alert_type'] = 'success';
            header("Location: onhand_read.php");
            exit;
        } else {
            $_SESSION['alert'] = 'Error: Could not add onhand record.';
            $_SESSION['alert_type'] = 'danger';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Onhand Record</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h1 class="mb-4">Onhand Form</h1>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Inventory">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Onhand Form</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="" onsubmit="confirmCreation(event)">
            <h6>Inventory Information</h6>
            <div class="row mb-3">
                <input type="hidden" name="inventory_id" value="<?= htmlspecialchars($inventory['InventoryID']) ?>">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars($inventory['ProductName']) ?>" id="product_name" readonly>
                        <label for="product_name">Product Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="category_name" value="<?= htmlspecialchars($inventory['CategoryName']) ?>" id="category_name" readonly>
                        <label for="category_name">Category</label>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="inventory_qty" value="<?= htmlspecialchars($inventory['InventoryQty']) ?>" readonly>
                        <label for="inventory_qty">Inventory Quantity</label>
                    </div>
                </div>
            </div>

            <hr>
            <h6>Set Quantity to Sell</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="onhand_qty" name="onhand_qty" min="1" max="<?= htmlspecialchars($inventory['InventoryQty']) ?>" required oninput="validateQuantity()">
                        <label for="onhand_qty">Onhand Quantity</label>
                    </div>
                </div>
            </div>

            <hr>
            <h6>Set Prices</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="retail_price" id="retail_price" placeholder="Retail Price" required>
                        <label for="retail_price">Retail Price</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" name="min_promo_qty" id="min_promo_qty" min="1" placeholder="Minimum Promo Quantity" required>
                        <label for="min_promo_qty">Minimum Promo Quantity</label>
                    </div>
                </div>
                <div class="col-md-6 mt-2">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="promo_price" id="promo_price" placeholder="Promo Price" required>
                        <label for="promo_price">Promo Price</label>
                    </div>
                </div>
            </div>

            <button class="btn btn-success w-100 mb-2" type="submit">Create</button>
            <a class="btn btn-secondary w-100 mb-2" href="../../inventory_management_system/inventory/inventory_read.php">Cancel</a>
        </form>
    </div>
    <script>
        // Function to validate onhand quantity against inventory quantity
        function validateQuantity() {
            const inventoryQty = parseInt(<?= json_encode($inventory['InventoryQty']) ?>);
            const onhandQty = parseInt(document.getElementById('onhand_qty').value);
            if (onhandQty > inventoryQty) {
                alert("Onhand Quantity cannot exceed Inventory Quantity.");
                document.getElementById('onhand_qty').value = inventoryQty; // Set to max allowed
            }
        }
    </script>
</body>
</html>
