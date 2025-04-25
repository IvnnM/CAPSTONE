<?php
session_start();
include("../../../../config/database.php");

try {
    // Check if user is logged in and has admin rights
    if (!isset($_SESSION['AdminID'])) {
        throw new Exception('You must be logged in as an admin to delete categories.');
    }

    // Check if an ID is provided
    if (!isset($_GET['id'])) {
        throw new Exception('Invalid category ID.');
    }

    $category_id = $_GET['id'];

    // Begin transaction
    $conn->beginTransaction();

    // First check if category has any products
    $check_query = "SELECT COUNT(*) FROM ProductTb WHERE CategoryID = :category_id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    $check_stmt->execute();
    
    if ($check_stmt->fetchColumn() > 0) {
        throw new Exception('Cannot delete category: There are products still using this category. Please reassign or delete these products first.');
    }

    // If no products are using this category, proceed with deletion
    $delete_query = "DELETE FROM ProductCategoryTb WHERE CategoryID = :category_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
    
    if ($delete_stmt->execute()) {
        $conn->commit();
        $_SESSION['alert'] = 'Category deleted successfully!';
        $_SESSION['alert_type'] = 'success';
    } else {
        throw new Exception('Could not delete the category.');
    }

} catch (PDOException $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['alert'] = 'Database error: ' . $e->getMessage();
    $_SESSION['alert_type'] = 'danger';
    error_log('Category deletion error: ' . $e->getMessage());
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $_SESSION['alert'] = $e->getMessage();
    $_SESSION['alert_type'] = 'danger';
    error_log('Category deletion error: ' . $e->getMessage());
}

// Redirect back to the category list page
header("Location: category_read.php");
exit;
?>