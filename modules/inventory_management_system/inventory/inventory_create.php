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

// Check if a Product ID is provided in the URL
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Fetch product details for display
    $product_query = "SELECT p.*, c.CategoryName, i.InventoryQty 
                      FROM ProductTb p 
                      JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID 
                      LEFT JOIN InventoryTb i ON p.ProductID = i.ProductID 
                      WHERE p.ProductID = :product_id";
    $product_stmt = $conn->prepare($product_query);
    $product_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    $product_stmt->execute();
    $product = $product_stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        $_SESSION['alert'] = 'Product not found.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: product_read.php");
        exit;
    }
} else {
    $_SESSION['alert'] = 'Invalid product ID.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: product_read.php");
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
            $_SESSION['alert'] = 'Inventory updated successfully!';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Error: Could not update inventory.';
            $_SESSION['alert_type'] = 'danger';
        }
    } else {
        // Insert the new inventory record
        $insert_query = "INSERT INTO InventoryTb (ProductID, InventoryQty) VALUES (:product_id, :inventory_qty)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
        $insert_stmt->bindParam(':inventory_qty', $inventory_qty, PDO::PARAM_INT);

        if ($insert_stmt->execute()) {
            $_SESSION['alert'] = 'Inventory added successfully!';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['alert'] = 'Error: Could not add inventory.';
            $_SESSION['alert_type'] = 'danger';
        }
    }

    // Redirect to product read page
    header("Location: inventory_read.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Inventory</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h1 class="mb-4">Inventory Form</h1>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../../views/personnel_view.php#Inventory">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Inventory Form</li>
                    <li class="breadcrumb-item"><a href="../product/product_read.php">Product List</a></li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="">
            <h6>Product Information</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="product_name" value="<?= htmlspecialchars($product['ProductName']) ?>" id="product_name" readonly>
                        <label for="product_name">Product Name</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" name="category_name" value="<?= htmlspecialchars($product['CategoryName']) ?>" id="category_name" readonly>
                        <label for="category_name">Category</label>
                    </div>
                </div>
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['ProductID']) ?>">
                <input type="hidden" name="category_id" value="<?= htmlspecialchars($product['CategoryID']) ?>">
            </div>

            <hr>
            <h6>Current Inventory Quantity</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="current_inventory_qty" value="<?= htmlspecialchars($product['InventoryQty'] ?? 0) ?>" readonly>
                        <label for="current_inventory_qty">Current Inventory Quantity</label>
                    </div>
                </div>
            </div>

            <hr>
            <h6>Set Quantity</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" name="inventory_qty" id="inventory_qty" placeholder="Quantity" min="1" required>
                        <label for="inventory_qty">Quantity</label>
                    </div>
                </div>
            </div>

            <button class="btn btn-success w-100 mb-2" type="submit">Create</button>
            <?php if (isset($_SESSION['AdminID'])): ?>
                <a class="btn btn-secondary w-100 mb-2" href="../product/product_read.php">Cancel</a>
            <?php elseif (isset($_SESSION['EmpID'])): ?>
                <a class="btn btn-secondary w-100 mb-2" href="inventory_read.php">Cancel</a>
            <?php endif; ?>
            
        </form>
    </div>
</body>

</html>
