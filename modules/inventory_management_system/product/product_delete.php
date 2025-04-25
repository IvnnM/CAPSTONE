<?php
session_start();
include("./../../../config/database.php");

try {
    // Check if user is logged in and has admin rights
    if (!isset($_SESSION['AdminID'])) {
        throw new Exception('You must be logged in as an admin to delete products.');
    }

    // Check if an ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Invalid product ID.');
    }

    $product_id = $_GET['id'];

    // Begin transaction
    $conn->beginTransaction();

    // Delete the product - related records will be deleted automatically
    $delete_query = "DELETE FROM ProductTb WHERE ProductID = :product_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);
    
    if ($delete_stmt->execute()) {
        $conn->commit();
        $_SESSION['alert'] = 'Product and related records deleted successfully!';
        $_SESSION['alert_type'] = 'success';
    } else {
        throw new Exception('Could not delete the product.');
    }

} catch (PDOException $e) {
    $conn->rollBack();
    $_SESSION['alert'] = 'Database error: ' . $e->getMessage();
    $_SESSION['alert_type'] = 'danger';
    error_log('Product deletion error: ' . $e->getMessage());
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['alert'] = 'Error: ' . $e->getMessage();
    $_SESSION['alert_type'] = 'danger';
    error_log('Product deletion error: ' . $e->getMessage());
}

// Redirect back to the product list page
header("Location: product_read.php");
exit;
?>