<?php
include("./../../../config/database.php");

// Check if an Onhand ID is provided in the URL
if (isset($_GET['onhand_id'])) {
    $onhand_id = $_GET['onhand_id'];

    // Prepare the delete query
    $delete_query = "DELETE FROM OnhandTb WHERE OnhandID = :onhand_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        echo "<script>alert('Onhand record deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: Could not delete onhand record.');</script>";
    }
} else {
    echo "<script>alert('Invalid onhand ID.'); window.history.back();</script>";
}

echo "<script>window.history.back();</script>";
exit();
?>
