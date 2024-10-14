<?php
include("./../../../config/database.php");

// Check if a transaction ID is provided in the URL
if (isset($_GET['id'])) {
    $transac_id = $_GET['id'];

    try {
        // Begin a transaction
        $conn->beginTransaction();

        // Prepare the delete query for CartRecordTb
        $delete_cart_query = "DELETE FROM CartRecordTb WHERE TransacID = :transac_id";
        $delete_cart_stmt = $conn->prepare($delete_cart_query);
        $delete_cart_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
        $delete_cart_stmt->execute();

        // Prepare the delete query for TransacTb
        $delete_query = "DELETE FROM TransacTb WHERE TransacID = :transac_id";
        $delete_stmt = $conn->prepare($delete_query);
        $delete_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
        $delete_stmt->execute();

        // Commit the transaction
        $conn->commit();

        echo "<script>alert('Transaction and related cart records deleted successfully!');</script>";
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollBack();
        echo "<script>alert('Error: Could not delete transaction. " . $e->getMessage() . "');</script>";
    }
} else {
    echo "<script>alert('Invalid transaction ID.'); window.history.back();</script>";
}

echo "<script>window.history.back();</script>";
exit();
?>
