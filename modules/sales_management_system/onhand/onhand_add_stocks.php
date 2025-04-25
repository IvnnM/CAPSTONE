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

// Check if an Onhand ID is provided in the URL
if (isset($_GET['onhand_id'])) {
    $onhand_id = filter_input(INPUT_GET, 'onhand_id', FILTER_VALIDATE_INT);

    if (!$onhand_id) {
        $_SESSION['alert'] = 'Invalid onhand ID.';
        $_SESSION['alert_type'] = 'warning';
        header("Location: onhand_read.php");
        exit;
    }

    // Fetch existing on-hand record for display
    $onhand_query = "
        SELECT o.*, i.InventoryQty, p.ProductName, c.CategoryName 
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
        $_SESSION['alert'] = 'Onhand record not found.';
        $_SESSION['alert_type'] = 'warning';
        header("Location: onhand_read.php");
        exit;
    }
} else {
    $_SESSION['alert'] = 'Invalid onhand ID.';
    $_SESSION['alert_type'] = 'warning';
    header("Location: onhand_read.php");
    exit;
}

// Handle form submission for updating on-hand record
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $additional_onhand_qty = filter_input(INPUT_POST, 'onhand_qty', FILTER_VALIDATE_INT);
    $current_onhand_qty = $onhand['OnhandQty'];
    $inventory_qty = $onhand['InventoryQty'];

    if (!$additional_onhand_qty || $additional_onhand_qty < 0) {
        $_SESSION['alert'] = 'Invalid quantity provided.';
        $_SESSION['alert_type'] = 'warning';
    } else {
        // Calculate the new onhand quantity
        $new_onhand_qty = $current_onhand_qty + $additional_onhand_qty;

        // Check if the additional onhand quantity exceeds the inventory quantity
        if ($additional_onhand_qty > $inventory_qty) {
            $_SESSION['alert'] = 'Error: The additional quantity exceeds available inventory.';
            $_SESSION['alert_type'] = 'danger';
        } else {
            // Update the OnhandTb to increase the onhand quantity
            $update_query = "UPDATE OnhandTb SET OnhandQty = :onhand_qty WHERE OnhandID = :onhand_id";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bindParam(':onhand_qty', $new_onhand_qty, PDO::PARAM_INT);
            $update_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);

            if ($update_stmt->execute()) {
                // Subtract the additional quantity from the InventoryTb
                $new_inventory_qty = $inventory_qty - $additional_onhand_qty;
                $inventory_update_query = "UPDATE InventoryTb SET InventoryQty = :inventory_qty WHERE InventoryID = :inventory_id";
                $inventory_update_stmt = $conn->prepare($inventory_update_query);
                $inventory_update_stmt->bindParam(':inventory_qty', $new_inventory_qty, PDO::PARAM_INT);
                $inventory_update_stmt->bindParam(':inventory_id', $onhand['InventoryID'], PDO::PARAM_INT);

                if ($inventory_update_stmt->execute()) {
                    $_SESSION['alert'] = 'Onhand quantity updated successfully and inventory adjusted!';
                    $_SESSION['alert_type'] = 'success';
                } else {
                    $_SESSION['alert'] = 'Error: Could not update inventory quantity.';
                    $_SESSION['alert_type'] = 'danger';
                }
            } else {
                $_SESSION['alert'] = 'Error: Could not update onhand quantity.';
                $_SESSION['alert_type'] = 'danger';
            }
        }
    }

    // Redirect back to the previous page (onhand_read.php) after the operation
    header("Location: onhand_read.php");
    exit;
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Stocks to Onhand Record</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Add Stocks to Onhand Record</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Add Stocks to Onhand Record</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="" onsubmit="confirmUpdate(event);">
            <h6>Add Stocks Information</h6>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="product_name" id="product_name" value="<?= htmlspecialchars($onhand['ProductName']) ?>" readonly>
                        <label for="product_name">Product Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="category_name" id="category_name" value="<?= htmlspecialchars($onhand['CategoryName']) ?>" readonly>
                        <label for="category_name">Category</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="inventory_qty" value="<?= htmlspecialchars($onhand['InventoryQty']) ?>" readonly>
                        <label for="inventory_qty">Current Inventory Quantity</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="current_onhand_qty" value="<?= htmlspecialchars($onhand['OnhandQty']) ?>" readonly>
                        <label for="current_onhand_qty">Current Onhand Quantity</label>
                    </div>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" id="onhand_qty" name="onhand_qty" min="0" required>
                        <label for="onhand_qty">Additional Quantity</label>
                    </div>
                </div>
            </div>

            <button class="btn btn-success w-100 mb-2" type="submit">Update</button>
            <a class="btn btn-secondary w-100 mb-2" href="onhand_read.php">Cancel</a>
        </form>
    </div>
</body>
</html>
