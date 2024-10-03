<?php
// /modules/geographic_information_system/gis_city_details.php
include("../../config/database.php");

$city = $_GET['city'];

// Fetch all transactions for the selected city
$query = "
    SELECT TransacTb.*, ProductTb.ProductName 
    FROM TransacTb
    LEFT JOIN OnhandTb ON TransacTb.OnhandID = OnhandTb.OnhandID
    LEFT JOIN InventoryTb ON OnhandTb.InventoryID = InventoryTb.InventoryID
    LEFT JOIN ProductTb ON InventoryTb.ProductID = ProductTb.ProductID
    LEFT JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID
    WHERE LocationTb.City = :city
";

$stmt = $conn->prepare($query);
$stmt->bindParam(':city', $city);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>City Transactions: <?php echo $city; ?></title>
</head>
<body>
    <h1>Transactions in <?php echo $city; ?></h1>

    <table>
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Customer Name</th>
                <th>Product</th>
                <th>Quantity</th>
                <th>Total Price</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transac): ?>
                <tr>
                    <td><?php echo $transac['TransacID']; ?></td>
                    <td><?php echo $transac['CustName']; ?></td>
                    <td><?php echo $transac['ProductName']; ?></td>
                    <td><?php echo $transac['Quantity']; ?></td>
                    <td><?php echo $transac['TotalPrice']; ?></td>
                    <td><?php echo $transac['TransactionDate']; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
