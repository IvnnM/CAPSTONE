<?php
session_start();
include("../../../../config/database.php");

// Check if TransacID is set
if (!isset($_GET['transac_id'])) {
    echo "<tr><td colspan='4' class='text-center'>No Transaction ID provided.</td></tr>";
    exit;
}

$transac_id = $_GET['transac_id'];

// Fetch cart records for the given TransacID
$sql = "SELECT * FROM CartRecordTb WHERE TransacID = :transac_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':transac_id', $transac_id, PDO::PARAM_INT);
$stmt->execute();
$cart_records = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generate table rows for cart records
if ($cart_records) {
    foreach ($cart_records as $record) {
        echo "<tr>
                <td>" . htmlspecialchars($record['OnhandID']) . "</td>
                <td>" . htmlspecialchars($record['Quantity']) . "</td>
                <td>" . number_format(htmlspecialchars($record['Price']), 2) . "</td>
                <td>" . htmlspecialchars($record['AddedDate']) . "</td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='4' class='text-center'>No cart records found for this transaction.</td></tr>";
}
?>
