<?php
session_start();
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
        $_SESSION['alert'] = 'Employee deleted successfully!';
        $_SESSION['alert_type'] = 'success';
    } else {
        $_SESSION['alert'] = 'Error: Could not delete the employee.';
        $_SESSION['alert_type'] = 'danger';
    }
} else {
    $_SESSION['alert'] = 'Invalid employee ID.';
    $_SESSION['alert_type'] = 'danger';
}

// Redirect back to the previous page or employee list
header("Location: employee_read.php");
exit;
?>
