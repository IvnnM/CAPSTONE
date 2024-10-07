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

// Check if a Product ID is provided in the URL
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Fetch product details for display
    $product_query = "SELECT p.*, c.CategoryName FROM ProductTb p JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID WHERE p.ProductID = :product_id";
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $product_stmt->execute();
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "<script>alert('Product not found.'); window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid product ID.'); window.history.back();</script>";
    exit;
}

// Handle form submission for creating inventory
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inventory_qty = $_POST['inventory_qty'];

    // Check if the product already exists in InventoryTb
    $check_query = "SELECT InventoryQty FROM InventoryTb WHERE ProductID = :product_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $check_stmt->execute();
    $existing_inventory = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_inventory) {
        // Update existing inventory quantity
        $new_qty = $existing_inventory['InventoryQty'] + $inventory_qty;
        $update_query = "UPDATE InventoryTb SET InventoryQty = :inventory_qty WHERE ProductID = :product_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':inventory_qty', $new_qty, PDO::PARAM_INT);
        $update_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            echo "<script>alert('Inventory updated successfully!');</script>";
        } else {
            echo "<script>alert('Error: Could not update inventory.');</script>";
        }
    } else {
        // Insert the new inventory record
        $insert_query = "INSERT INTO InventoryTb (ProductID, InventoryQty) VALUES (:product_id, :inventory_qty)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':inventory_qty', $inventory_qty, PDO::PARAM_INT);

        if ($insert_stmt->execute()) {
            echo "<script>alert('Inventory added successfully!');</script>";
            echo"<script>window.history.back();</script>";
        } else {
            echo "<script>alert('Error: Could not add inventory.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Inventory</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <h1 class="mb-4">Inventory Form</h1>


    <form method="POST" action="">
        <hr style="border-top: 1px solid white;">
        <h6>Product Information</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="product_name">Product Name:</label>
                <input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars($product['ProductName']) ?>" readonly>
            </div>
            <div class="col-md-6">
                <label for="category_name">Category:</label>
                <input type="text" class="form-control" name="category_name" value="<?= htmlspecialchars($product['CategoryName']) ?>" readonly>
            </div>
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['ProductID']) ?>">
            <input type="hidden" name="category_id" value="<?= htmlspecialchars($product['CategoryID']) ?>">
        </div>

        <hr style="border-top: 1px solid white;">
        <h6>Set Quantity to Stock</h6>
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="inventory_qty">Quantity:</label>
                <input type="number" class="form-control" name="inventory_qty" min="1" required>
            </div>
        </div>
        
        <button class="btn btn-success" type="submit">Create</button>
    </form>
    <br>
    <a href="inventory_read.php">Go to Inventory List</a>
    <br><br>
    <a href="../product/product_read.php">Go to Product List</a>
</body>
</html>
