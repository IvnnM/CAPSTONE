<?php
session_start(); // Start the session
include("./../../../config/database.php");

// Check if a transaction ID is provided in the URL
if (isset($_GET['id'])) {
    $transac_id = $_GET['id'];

    try {
        // Begin a transaction
        $conn->beginTransaction();

        // Fetch the OnhandID and Quantity from CartRecordTb for the provided TransacID
        $fetch_cart_query = "SELECT OnhandID, Quantity FROM CartRecordTb WHERE TransacID = :transac_id";
        $fetch_cart_stmt = $conn->prepare($fetch_cart_query);
        $fetch_cart_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
        $fetch_cart_stmt->execute();

        // Loop through the cart records and update OnhandTb quantities
        while ($cart_record = $fetch_cart_stmt->fetch(PDO::FETCH_ASSOC)) {
            $onhand_id = $cart_record['OnhandID'];
            $quantity = $cart_record['Quantity'];

            // Update the OnhandQty in OnhandTb by adding the quantity back
            $update_onhand_query = "UPDATE OnhandTb SET OnhandQty = OnhandQty + :quantity WHERE OnhandID = :onhand_id";
            $update_onhand_stmt = $conn->prepare($update_onhand_query);
            $update_onhand_stmt->bindParam(':quantity', $quantity, PDO::PARAM_INT);
            $update_onhand_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);
            $update_onhand_stmt->execute();
        }

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

        // Set success alert
        $_SESSION['alert'] = 'Order successfully declined.';
        $_SESSION['alert_type'] = 'success';
    } catch (Exception $e) {
        // Rollback the transaction in case of error
        $conn->rollBack();

        // Set error alert
        $_SESSION['alert'] = 'Error: Could not delete transaction. ' . $e->getMessage();
        $_SESSION['alert_type'] = 'danger';
    }
} else {
    // Set invalid transaction ID alert
    $_SESSION['alert'] = 'Invalid transaction ID.';
    $_SESSION['alert_type'] = 'danger';
}

// Redirect to a suitable page, e.g., the previous page or a list of transactions
header("Location: ../transaction/personnel/transac_read_pending.php");
exit();
?>
