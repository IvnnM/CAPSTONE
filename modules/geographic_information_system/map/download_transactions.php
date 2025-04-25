<?php
session_start();
include("../../../config/database.php");

// Check if the user is logged in
// if (!isset($_SESSION['EmpID']) && !isset($_SESSION['AdminID'])) {
//     echo "<script>alert('You must be logged in to access this page.'); 
//     window.location.href = '../../../../login.php';</script>";
//     exit;
// }

// Get the selected province and city from the URL parameters
$selectedProvince = $_GET['province'] ?? null;
$selectedCity = $_GET['city'] ?? null;

if ($selectedProvince && $selectedCity) {
    // Prepare the query to fetch all transactions for the selected city
    $query = "
        SELECT T.TransacID, T.CustName, T.CustNum, T.CustEmail, T.DeliveryFee, T.TotalPrice, T.TransactionDate, T.Status
        FROM TransacTb T
        JOIN LocationTb L ON T.LocationID = L.LocationID
        WHERE L.Province = :province AND L.City = :city
        ORDER BY T.TransactionDate DESC
    ";

    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':province', $selectedProvince);
    $stmt->bindParam(':city', $selectedCity);
    $stmt->execute();

    // Fetch all transactions
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Set headers to trigger download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="transactions.csv"');

    // Open output stream
    $output = fopen('php://output', 'w');

    // Write column headings
    fputcsv($output, ['Transaction ID', 'Customer Name', 'Contact Number', 'Email', 'Delivery Fee', 'Total Price', 'Transaction Date', 'Status']);

    // Write data rows
    foreach ($transactions as $transaction) {
        fputcsv($output, [
            $transaction['TransacID'],
            $transaction['CustName'],
            $transaction['CustNum'],
            $transaction['CustEmail'],
            number_format($transaction['DeliveryFee'], 2),
            number_format($transaction['TotalPrice'], 2),
            $transaction['TransactionDate'],
            $transaction['Status'],
        ]);
    }

    // Close output stream
    fclose($output);
    exit();
} else {
    echo "Province or city parameter is missing.";
    exit();
}
?>
