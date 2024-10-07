<?php
session_start();
include("./../../../includes/cdn.html"); 
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = './../../../login.php';</script>";
    exit;
}

// Check if an Onhand ID is provided in the URL
if (isset($_GET['onhand_id'])) {
    $onhand_id = $_GET['onhand_id'];

    // Fetch existing on-hand record for display
    $onhand_query = "SELECT o.*, i.InventoryQty, p.ProductName, c.CategoryName 
                     FROM OnhandTb o 
                     JOIN InventoryTb i ON o.InventoryID = i.InventoryID 
                     JOIN ProductTb p ON i.ProductID = p.ProductID 
                     JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID 
                     WHERE o.OnhandID = :onhand_id";
    $onhand_stmt = $conn->prepare($onhand_query);
    $onhand_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);
    $onhand_stmt->execute();
    $onhand = $onhand_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$onhand) {
        echo "<script>alert('Onhand record not found.'); window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid onhand ID.'); window.history.back();</script>";
    exit;
}

// Handle form submission for updating on-hand record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $additional_onhand_qty = $_POST['onhand_qty']; // Get the quantity to add
    $current_onhand_qty = $onhand['OnhandQty']; // Get the current Onhand Qty
    $inventory_qty = $onhand['InventoryQty']; // Get the Inventory Qty

    // Calculate the new onhand quantity
    $new_onhand_qty = $current_onhand_qty + $additional_onhand_qty;

    // Check if the new Onhand quantity exceeds Inventory quantity
    if ($new_onhand_qty > $inventory_qty) {
        echo "<script>alert('Error: The new onhand quantity exceeds available inventory.');</script>";
    } else {
        // Update the OnhandTb only for OnhandQty
        $update_query = "UPDATE OnhandTb SET OnhandQty = :onhand_qty WHERE OnhandID = :onhand_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':onhand_qty', $new_onhand_qty, PDO::PARAM_INT);
        $update_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            // Deduct the added quantity from the InventoryTb
            $new_inventory_qty = $inventory_qty - $additional_onhand_qty;
            $inventory_update_query = "UPDATE InventoryTb SET InventoryQty = :inventory_qty WHERE InventoryID = :inventory_id";
            $inventory_update_stmt = $conn->prepare($inventory_update_query);
            $inventory_update_stmt->bindParam(':inventory_qty', $new_inventory_qty, PDO::PARAM_INT);
            $inventory_update_stmt->bindParam(':inventory_id', $onhand['InventoryID'], PDO::PARAM_INT);

            if ($inventory_update_stmt->execute()) {
                echo "<script>alert('Onhand quantity updated successfully and inventory adjusted!');</script>";
            } else {
                echo "<script>alert('Error: Could not update inventory quantity.');</script>";
            }
        } else {
            echo "<script>alert('Error: Could not update onhand quantity.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Stocks to Onhand Record</title>
    <script>
        function confirmUpdate(event) {
            if (!confirm('Are you sure you want to add to this onhand quantity?')) {
                event.preventDefault();
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h3>Add Stocks to Onhand Record</h3>
    <form method="POST" action="" onsubmit="confirmUpdate(event);">
        <hr style="border-top: 1px solid white;">
        <h6>Add Stocks Information</h6>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="product_name">Product Name:</label>
                <input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars($onhand['ProductName']) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="category_name">Category:</label>
                <input type="text" class="form-control" name="category_name" value="<?= htmlspecialchars($onhand['CategoryName']) ?>" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="inventory_qty">Current Inventory Quantity:</label>
                <input type="text" class="form-control" id="inventory_qty" value="<?= htmlspecialchars($onhand['InventoryQty']) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="current_onhand_qty">Current Onhand Quantity:</label>
                <input type="text" class="form-control" id="current_onhand_qty" value="<?= htmlspecialchars($onhand['OnhandQty']) ?>" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="onhand_qty">Additional Quantity:</label>
                <input type="number" class="form-control" id="onhand_qty" name="onhand_qty" min="0" required>
            </div>
        </div>

        <button class="btn btn-success" type="submit">Update</button>
    </form>

    <br>
    <a href="onhand_read.php">Go to Onhand List</a>
    <br><br>
    <a href="../../inventory_management_system/inventory/inventory_read.php">Go to Inventory List</a>
</body>
</html>
