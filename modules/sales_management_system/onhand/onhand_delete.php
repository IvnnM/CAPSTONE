<?php
session_start(); // Start the session for session-based alerts
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
        $_SESSION['alert'] = 'Onhand record deleted successfully!';
        $_SESSION['alert_type'] = 'success';
    } else {
        $_SESSION['alert'] = 'Error: Could not delete onhand record.';
        $_SESSION['alert_type'] = 'danger';
    }
} else {
    $_SESSION['alert'] = 'Invalid onhand ID.';
    $_SESSION['alert_type'] = 'warning';
}

// Redirect back to the previous page (or to the onhand listing page if desired)
header("Location: onhand_read.php"); // Replace 'onhand_read.php' with the correct URL if needed
exit();
?>
