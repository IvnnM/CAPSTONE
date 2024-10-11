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
                echo "<script>alert('Invalid action.');</script>";
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
            $transac = $transac_stmt->fetch(PDO::FETCH_ASSOC);

            if ($transac) {
                $quantity_sold = $transac['Quantity'];
                $onhand_id = $transac['OnhandID'];

                // Update the OnhandQty in OnhandTb
                $update_onhand_query = "UPDATE OnhandTb SET OnhandQty = OnhandQty - :quantity_sold WHERE OnhandID = :onhand_id";
                $update_onhand_stmt = $conn->prepare($update_onhand_query);
                $update_onhand_stmt->bindParam(':quantity_sold', $quantity_sold, PDO::PARAM_INT);
                $update_onhand_stmt->bindParam(':onhand_id', $onhand_id, PDO::PARAM_INT);

                // Check if quantity update was successful
                if (!$update_onhand_stmt->execute()) {
                    $conn->rollBack();
                    echo "<script>alert('Error: Could not update product quantity.');</script>";
                    echo "<script>window.history.back();</script>";
                    exit();
                }
            } else {
                $conn->rollBack();
                echo "<script>alert('Transaction details not found.');</script>";
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
            echo "<script>alert('Transaction status updated to $new_status successfully!');</script>";
        } else {
            // Roll back the transaction in case of error
            $conn->rollBack();
            echo "<script>alert('Error: Could not update transaction status.');</script>";
        }
    } else {
        echo "<script>alert('Invalid action.');</script>";
    }
} else {
    echo "<script>alert('Invalid transaction ID.'); window.history.back();</script>";
}

// Redirect back to the previous page
echo "<script>window.history.back();</script>";
exit();
?>
