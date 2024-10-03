<?php
// Include database configuration
include_once('../../../config/database.php');

try {
    // Get the selected year, province, and city from the query parameters
    $year = isset($_GET['year']) ? intval($_GET['year']) : null;
    $province = isset($_GET['province']) ? $_GET['province'] : null;
    $city = isset($_GET['city']) ? $_GET['city'] : null;

    // Initialize query and parameters
    $query = "
        SELECT MONTH(TransactionDate) AS month, 
               MONTHNAME(TransactionDate) AS month_name, 
               SUM(TotalPrice) AS total 
        FROM TransacTb 
        INNER JOIN LocationTb ON TransacTb.LocationID = LocationTb.LocationID 
    ";

    // Filter by year if provided
    if ($year) {
        $query .= " WHERE YEAR(TransactionDate) = :year";
    }

    // Filter by province if provided
    if ($province) {
        $query .= $year ? " AND" : " WHERE";
        $query .= " LocationTb.Province = :province";
    }

    // Filter by city if provided
    if ($city) {
        $query .= $year || $province ? " AND" : " WHERE";
        $query .= " LocationTb.City = :city";
    }

    // Complete the query
    $query .= " GROUP BY MONTH(TransactionDate) 
                ORDER BY MONTH(TransactionDate)";

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind parameters
    if ($year) {
        $stmt->bindParam(':year', $year, PDO::PARAM_INT);
    }
    if ($province) {
        $stmt->bindParam(':province', $province, PDO::PARAM_STR);
    }
    if ($city) {
        $stmt->bindParam(':city', $city, PDO::PARAM_STR);
    }

    // Execute the statement
    $stmt->execute();

    // Fetch all transactions
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the transactions as a JSON response
    echo json_encode(['transactions' => $transactions]);
} catch (PDOException $e) {
    // Handle any errors
    echo json_encode(['error' => 'Failed to fetch transactions: ' . $e->getMessage()]);
}
?>
