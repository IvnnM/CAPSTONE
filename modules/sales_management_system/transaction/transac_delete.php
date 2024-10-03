<?php
include("./../../../config/database.php");

// Check if a transaction ID is provided in the URL
if (isset($_GET['id'])) {
    $transac_id = $_GET['id'];

    // Prepare the delete query
    $delete_query = "DELETE FROM TransacTb WHERE TransacID = :transac_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        echo "<script>alert('Transaction deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: Could not delete transaction.');</script>";
    }
} else {
    echo "<script>alert('Invalid transaction ID.'); window.history.back();</script>";
}

echo "<script>window.history.back();</script>";
exit();
?>
