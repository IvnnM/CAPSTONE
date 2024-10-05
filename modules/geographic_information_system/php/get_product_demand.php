<?php
include('../../../config/database.php');

$year = isset($_GET['year']) ? $_GET['year'] : null;
$province = isset($_GET['province']) ? $_GET['province'] : null;

// Adjust your SQL query based on the parameters
$sql = "
  SELECT P.ProductName, SUM(T.Quantity) AS total_quantity
  FROM TransacTb T
  JOIN OnhandTb O ON T.OnhandID = O.OnhandID
  JOIN InventoryTb I ON O.InventoryID = I.InventoryID
  JOIN ProductTb P ON I.ProductID = P.ProductID
  JOIN LocationTb L ON T.LocationID = L.LocationID
  WHERE 1=1
";

if ($year) {
    $sql .= " AND YEAR(T.TransactionDate) = :year";
}

if ($province) {
    $sql .= " AND L.Province = :province";
}

$sql .= " GROUP BY P.ProductName ORDER BY total_quantity DESC";

$stmt = $conn->prepare($sql);
if ($year) $stmt->bindParam(':year', $year);
if ($province) $stmt->bindParam(':province', $province);

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return results as JSON
echo json_encode(['products' => $products]);
?>
