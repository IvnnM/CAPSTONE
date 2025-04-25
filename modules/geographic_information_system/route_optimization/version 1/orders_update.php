<?php
session_start();
include("./../../../config/database.php");

// Check if a transaction ID is provided in the POST request
if (isset($_POST['transac_id']) && !empty($_POST['transac_id'])) {
    $transac_id = $_POST['transac_id'];
    $new_status = '';

    // Determine the new status based on the action specified
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'ToShip':
                $new_status = 'ToShip';
                break;
            case 'deliver':
                $new_status = 'Delivered';
                break;
            case 'pending':
                $new_status = 'Pending';
                break;
            default:
                $_SESSION['alert'] = 'Invalid action.';
                $_SESSION['alert_type'] = 'danger';
                echo "<script>window.history.back();</script>";
                exit();
        }
    }

    // If the new status is not empty, proceed with the update
    if (!empty($new_status)) {
        // Start a transaction to ensure data integrity
        $conn->beginTransaction();

        // If the new status is 'ToShip', fetch quantity and OnhandID from CartRecordTb
        if ($new_status === 'ToShip') {
            // Fetch the transaction details to get the quantity and OnhandID
            $transac_query = "SELECT Quantity, OnhandID FROM CartRecordTb WHERE TransacID = :transac_id";
            $transac_stmt = $conn->prepare($transac_query);
            $transac_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
            $transac_stmt->execute();
            $transac = $transac_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if records exist for the given TransacID
            if ($transac && count($transac) > 0) {
                foreach ($transac as $item) {
                    $quantity_sold = $item['Quantity'];
                    $onhand_id = $item['OnhandID'];

                    // Update the OnhandQty in OnhandTb
                    $update_onhand_query = "UPDATE OnhandTb SET OnhandQty = OnhandQty - :quantity_sold WHERE OnhandID = :onhand_id";
                    $update_onhand_stmt = $conn->prepare($update_onhand_query);
                    $update_onhand_stmt->bindParam(':quantity_sold', $quantity_sold, PDO::PARAM_INT);
                    $update_onhand_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);

                    // Check if quantity update was successful
                    if (!$update_onhand_stmt->execute()) {
                        $conn->rollBack();
                        $_SESSION['alert'] = 'Error: Could not update product quantity.';
                        $_SESSION['alert_type'] = 'danger';
                        echo "<script>window.history.back();</script>";
                        exit();
                    }
                }
            } else {
                $conn->rollBack();
                $_SESSION['alert'] = 'Transaction details not found for the provided ID.';
                $_SESSION['alert_type'] = 'danger';
                echo "<script>window.history.back();</script>";
                exit();
            }
        }

        // Prepare the update query for transaction status
        $update_query = "UPDATE TransacTb SET Status = :new_status, TransactionDate = NOW() WHERE TransacID = :transac_id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindParam(':new_status', $new_status);
        $update_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);

        // Execute the update query
        if ($update_stmt->execute()) {
            // Commit the transaction
            $conn->commit();
            $_SESSION['alert'] = "Order has been $new_status successfully!";
            $_SESSION['alert_type'] = 'success';
        } else {
            // Roll back the transaction in case of error
            $conn->rollBack();
            $_SESSION['alert'] = 'Error: Could not update transaction status.';
            $_SESSION['alert_type'] = 'danger';
        }
    } else {
        $_SESSION['alert'] = 'Invalid action.';
        $_SESSION['alert_type'] = 'danger';
    }
} else {
    $_SESSION['alert'] = 'Invalid transaction ID.';
    $_SESSION['alert_type'] = 'danger';
}

// Redirect to a suitable page, e.g., the previous page or a list of transactions
echo "<script>window.history.back();</script>";
exit();
?>
