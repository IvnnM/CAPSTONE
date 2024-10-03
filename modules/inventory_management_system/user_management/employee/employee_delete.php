<?php
include("../../../../config/database.php");

// Check if an Employee ID is provided in the URL
if (isset($_GET['id'])) {
    $emp_id = $_GET['id'];

    // Prepare the delete query
    $delete_query = "DELETE FROM EmpTb WHERE EmpID = :emp_id";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bindParam(':emp_id', $emp_id, PDO::PARAM_INT);

    // Execute the delete query
    if ($delete_stmt->execute()) {
        echo "<script>alert('Employee deleted successfully!');</script>";
    } else {
        echo "<script>alert('Error: Could not delete the employee.');</script>";
    }
} else {
    echo "<script>alert('Invalid employee ID.');</script>";
}

// Redirect back to the previous page or employee list
echo "<script>window.history.back();</script>";
exit;
?>
