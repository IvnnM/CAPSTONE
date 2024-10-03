<?php
include("./../../../config/database.php");

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Prepare the delete query
    $delete_query = "DELETE FROM ProductTb WHERE ProductID = :product_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':product_id', $product_id, PDO::PARAM_INT);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        echo "<script>alert('Product deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: Could not delete the product.');</script>";
    }
} else {
    echo "<script>alert('Invalid product ID.');</script>";
}

echo "<script>window.history.back();</script>";
exit;
?>
