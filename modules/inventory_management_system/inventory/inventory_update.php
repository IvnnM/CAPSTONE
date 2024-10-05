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
        echo "<script>alert('Inventory record not found.'); window.history.back();</script>";
        exit;
    }
} else {
    echo "<script>alert('Invalid inventory ID.'); window.history.back();</script>";
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
        echo "<script>alert('Inventory updated successfully!');</script>";
        echo "<script>window.location.href = 'inventory_read.php';</script>"; // Redirect to inventory list
    } else {
        echo "<script>alert('Error: Could not update inventory.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Inventory</title>
</head>
<body>
    <h3>Update Inventory</h3>
    <form method="POST" action="">
        <label for="product_name">Product Name:</label>
        <input type="text" name="product_name" value="<?= htmlspecialchars($inventory['ProductName']) ?>" readonly><br>

        <label for="category_name">Category:</label>
        <input type="text" name="category_name" value="<?= htmlspecialchars($inventory['CategoryName']) ?>" readonly><br>

        <input type="hidden" name="product_id" value="<?= htmlspecialchars($inventory['ProductID']) ?>">

        <label for="inventory_qty">Quantity:</label>
        <input type="number" name="inventory_qty" value="<?= htmlspecialchars($inventory['InventoryQty']) ?>" min="0" required><br>

        <button type="submit">Update</button>
    </form>
    <br>
    <a href="inventory_read.php">Back to Inventory List</a>
</body>
</html>
