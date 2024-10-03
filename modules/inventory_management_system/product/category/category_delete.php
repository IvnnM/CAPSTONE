<?php
include("../../../../config/database.php");

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $category_id = $_GET['id'];

    // Prepare the delete query
    $delete_query = "DELETE FROM ProductCategoryTb WHERE CategoryID = :category_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        echo "<script>alert('Category deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: Could not delete the category.');</script>";
    }
} else {
    echo "<script>alert('Invalid category ID.');</script>";
}

echo "<script>window.history.back();</script>";
exit;
?>
