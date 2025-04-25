<?php
session_start();
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
        $_SESSION['alert'] = 'Inventory deleted successfully!';
        $_SESSION['alert_type'] = 'success';
    } else {
        $_SESSION['alert'] = 'Error: Could not delete the inventory.';
        $_SESSION['alert_type'] = 'danger';
    }
} else {
    $_SESSION['alert'] = 'Invalid inventory ID.';
    $_SESSION['alert_type'] = 'danger';
}

// Redirect back to the previous page or a specific inventory management page
header("Location: inventory_read.php"); // Change to the relevant page as needed
exit;
?>
