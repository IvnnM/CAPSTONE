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

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $inventory_id = $_GET['id'];

    // Fetch the existing inventory details
    $inventory_query = "
        SELECT i.*, p.ProductName, c.CategoryName 
        FROM InventoryTb i 
        JOIN ProductTb p ON i.ProductID = p.ProductID 
        JOIN ProductCategoryTb c ON p.CategoryID = c.CategoryID 
        WHERE i.InventoryID = :inventory_id
    ";
    $inventory_stmt = $conn->prepare($inventory_query);
    $inventory_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
    $inventory_stmt->execute();

    // Check if the inventory record exists
    if ($inventory_stmt->rowCount() > 0) {
        $inventory = $inventory_stmt->fetch(PDO::FETCH_ASSOC);
    } else {
        $_SESSION['alert'] = 'Inventory record not found.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: inventory_read.php");
        exit;
    }
} else {
    $_SESSION['alert'] = 'Invalid inventory ID.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: inventory_read.php");
    exit;
}

// Handle form submission for updates
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_inventory_qty = $_POST['inventory_qty'];

    // Update the inventory record in the database
    $update_query = "UPDATE InventoryTb SET InventoryQty = :inventory_qty WHERE InventoryID = :inventory_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':inventory_qty', $new_inventory_qty, PDO::PARAM_INT);
    $update_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        $_SESSION['alert'] = 'Inventory updated successfully!';
        $_SESSION['alert_type'] = 'success';
        header("Location: inventory_read.php"); // Redirect to inventory list
        exit;
    } else {
        $_SESSION['alert'] = 'Error: Could not update inventory.';
        $_SESSION['alert_type'] = 'danger';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Inventory</title>
</head>
<body>
    <div class="container relative">
        <div class="sticky-top bg-light pb-2">
            <h3>Update Stock Quantity</h3>
            <!-- Breadcrumb Navigation -->
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="../../../views/personnel_view.php#Products">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Update Stock Quantity</li>
                </ol>
            </nav>
            <hr>
        </div>

        <form method="POST" action="">
            <h6>Update Inventory Information</h6>
            <div class="row mb-3">
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
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($inventory['ProductID']) ?>">
            </div>

            <hr>
            <h6>Update Quantity</h6>
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="number" class="form-control" name="inventory_qty" id="inventory_qty" value="<?= htmlspecialchars($inventory['InventoryQty']) ?>" min="0" required>
                        <label for="inventory_qty">Quantity</label>
                    </div>
                </div>
            </div>

            <button class="btn btn-success w-100 mb-2" type="submit">Update</button>
            <a class="btn btn-secondary w-100 mb-2" href="inventory_read.php">Cancel</a>
        </form>
    </div>
</body>
</html>
