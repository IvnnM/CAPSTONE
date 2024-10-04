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
    $onhand_qty = $_POST['onhand_qty'];
    $retail_price = $_POST['retail_price'];
    $min_promo_qty = $_POST['min_promo_qty'];
    $promo_price = $_POST['promo_price'];
    $inventory_id = $onhand['InventoryID']; // Get the inventory ID from the fetched record

    // Calculate the new inventory quantity
    $new_inventory_qty = $onhand['InventoryQty'] + ($onhand['OnhandQty'] - $onhand_qty);

    // Update the OnhandTb
    $update_query = "UPDATE OnhandTb SET OnhandQty = :onhand_qty, RetailPrice = :retail_price, MinPromoQty = :min_promo_qty, PromoPrice = :promo_price WHERE OnhandID = :onhand_id";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bindParam(':onhand_qty', $onhand_qty, PDO::PARAM_INT);
    $update_stmt->bindParam(':retail_price', $retail_price, PDO::PARAM_STR);
    $update_stmt->bindParam(':min_promo_qty', $min_promo_qty, PDO::PARAM_INT);
    $update_stmt->bindParam(':promo_price', $promo_price, PDO::PARAM_STR);
    $update_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);

    if ($update_stmt->execute()) {
        // Update the InventoryTb with the new quantity
        $inventory_update_query = "UPDATE InventoryTb SET InventoryQty = :inventory_qty WHERE InventoryID = :inventory_id";
        $inventory_update_stmt = $conn->prepare($inventory_update_query);
        $inventory_update_stmt->bindParam(':inventory_qty', $new_inventory_qty, PDO::PARAM_INT);
        $inventory_update_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);
        $inventory_update_stmt->execute();

        echo "<script>alert('Onhand record updated successfully!');</script>";
    } else {
        echo "<script>alert('Error: Could not update onhand record.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Onhand Record</title>
    <script>
        function confirmUpdate(event) {
            if (!confirm('Are you sure you want to update this onhand record?')) {
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
</head>
<body>
    <h3>Update Onhand Record</h3>
    <form method="POST" action="" onsubmit="confirmUpdate(event)">
        <label for="product_name">Product Name:</label>
        <input type="text" name="product_name" value="<?= htmlspecialchars($onhand['ProductName']) ?>" readonly><br>

        <label for="category_name">Category:</label>
        <input type="text" name="category_name" value="<?= htmlspecialchars($onhand['CategoryName']) ?>" readonly><br>

        <input type="hidden" name="inventory_id" value="<?= htmlspecialchars($onhand['InventoryID']) ?>">

        <label for="inventory_qty">Inventory Quantity:</label>
        <input type="text" id="inventory_qty" value="<?= htmlspecialchars($onhand['InventoryQty']) ?>" readonly><br>

        <label for="onhand_qty">Onhand Quantity:</label>
        <input type="number" id="onhand_qty" name="onhand_qty" min="0" max="<?= htmlspecialchars($onhand['InventoryQty'] + $onhand['OnhandQty']) ?>" value="<?= htmlspecialchars($onhand['OnhandQty']) ?>" required oninput="validateQuantity()"><br>

        <label for="retail_price">Retail Price:</label>
        <input type="text" name="retail_price" value="<?= htmlspecialchars($onhand['RetailPrice']) ?>" required><br>

        <label for="min_promo_qty">Minimum Promo Quantity:</label>
        <input type="number" name="min_promo_qty" min="1" value="<?= htmlspecialchars($onhand['MinPromoQty']) ?>" required><br>

        <label for="promo_price">Promo Price:</label>
        <input type="text" name="promo_price" value="<?= htmlspecialchars($onhand['PromoPrice']) ?>" required><br>

        <button type="submit">Update</button>
    </form>
    <br>
    <a href="onhand_read.php">Go to Onhand List</a>
    <br><br>
    <a href="../../inventory_management_system/inventory/inventory_read.php">Go to Inventory List</a>
</body>
</html>
