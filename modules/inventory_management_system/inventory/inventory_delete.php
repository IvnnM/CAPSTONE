<?php
include("./../../../config/database.php");

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $inventory_id = $_GET['id'];

    // Prepare the delete query
    $delete_query = "DELETE FROM InventoryTb WHERE InventoryID = :inventory_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':inventory_id', $inventory_id, PDO::PARAM_INT);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        echo "<script>alert('Inventory deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: Could not delete the inventory.');</script>";
    }
} else {
    echo "<script>alert('Invalid inventory ID.');</script>";
}

echo "<script>window.history.back();</script>";
exit;
?>
