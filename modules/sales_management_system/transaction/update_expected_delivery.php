<?php
session_start();
include("./../../../config/database.php");

// Check if the user is logged in
if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
    echo "<script>alert('You must be logged in to access this page.'); 
    window.location.href = '../../../../login.php';</script>";
    exit;
}

// Check if the form is submitted with required data
if ($_SERVER["REQUEST_METHOD"] == "POST" && 
    isset($_POST['transac_id']) && 
    isset($_POST['expected_delivery_date'])) {
    
    // Additional server-side validation
    $transac_id = $_POST['transac_id'];
    $expected_date = $_POST['expected_delivery_date'];
    
    // Validate that transaction ID is not empty and is numeric
    if (empty($transac_id) || !is_numeric($transac_id)) {
        echo "<script>
                alert('Invalid Transaction ID.');
                window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
              </script>";
        exit;
    }
    
    // Validate date format and ensure it's not in the past
    $today = date('Y-m-d');
    if (empty($expected_date)) {
        echo "<script>
                alert('Expected Delivery Date cannot be empty.');
                window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
              </script>";
        exit;
    }
    
    // Validate date is not in the past
    if ($expected_date < $today) {
        echo "<script>
                alert('Expected Delivery Date cannot be in the past.');
                window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
              </script>";
        exit;
    }
    
    try {
        // First, verify the transaction exists
        $check_sql = "SELECT TransacID FROM TransacTb WHERE TransacID = :transac_id AND Status = 'ToShip'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bindParam(':transac_id', $transac_id);
        $check_stmt->execute();
        
        if ($check_stmt->rowCount() == 0) {
            echo "<script>
                    alert('Transaction not found or not eligible for update.');
                    window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
                  </script>";
            exit;
        }
        
        // Prepare SQL to update the expected delivery date
        $sql = "UPDATE TransacTb 
                SET ExpectedDeliveryDate = :expected_date 
                WHERE TransacID = :transac_id";
        
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':expected_date', $expected_date);
        $stmt->bindParam(':transac_id', $transac_id);
        
        // Execute the update
        if ($stmt->execute()) {
            // Successful update
            echo "<script>
                    alert('Expected Delivery Date updated successfully.');
                    window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
                  </script>";
            exit;
        } else {
            // Update failed
            echo "<script>
                    alert('Failed to update Expected Delivery Date.');
                    window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
                  </script>";
            exit;
        }
    } catch(PDOException $e) {
        // Handle any database errors
        echo "<script>
                alert('Database Error: " . htmlspecialchars($e->getMessage()) . "');
                window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
              </script>";
        exit;
    }
} else {
    // Redirect if accessed without proper POST data
    echo "<script>
            alert('Invalid request.');
            window.location.href = '" . $_SERVER['HTTP_REFERER'] . "';
          </script>";
    exit;
}
?>