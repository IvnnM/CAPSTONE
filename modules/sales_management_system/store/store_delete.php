<?php
//store_delete.php
session_start();
include("./../../../config/database.php");

// Check if the user is logged in and has either an Employee ID or an Admin ID in the session
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    $_SESSION['alert'] = 'You must be logged in to access this page.';
    $_SESSION['alert_type'] = 'danger';
    header("Location: ./../../../login.php");
    exit;
}

// Check if a Store ID is provided in the URL
if (isset($_GET['store_id'])) {
    $store_id = $_GET['store_id'];

    // Prepare the delete query
    $delete_query = "DELETE FROM StoreInfoTb WHERE StoreInfoID = :store_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':store_id', $store_id, PDO::PARAM_INT);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        $_SESSION['alert'] = 'Store deleted successfully!';
        $_SESSION['alert_type'] = 'success';
    } else {
        $_SESSION['alert'] = 'Error: Could not delete store.';
        $_SESSION['alert_type'] = 'danger';
    }
} else {
    $_SESSION['alert'] = 'Invalid store ID.';
    $_SESSION['alert_type'] = 'warning';
}

// Redirect back to the store listing page
header("Location: store_read.php");
exit();
?>