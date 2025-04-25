<?php
//fetch_data.php
session_start();
include("../../../config/database.php");

$response = [];

try {
    // Get Available Years for Filtering
    $sql = "SELECT DISTINCT YEAR(TransactionDate) as year 
            FROM TransacTb 
            ORDER BY year DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $response['available_years'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get selected year from request (default to current year if not specified)
    $selectedYear = isset($_GET['year']) && $_GET['year'] !== 'all' ? intval($_GET['year']) : 'all';

    // Get Transaction Statistics for selected year
    $sql = "SELECT 
    COUNT(*) as total_transactions,
    SUM(TotalPrice) as total_revenue,
    COUNT(CASE WHEN Status = 'Pending' THEN 1 END) as pending_orders,
    COUNT(CASE WHEN Status = 'ToShip' THEN 1 END) as to_ship_orders,
    COUNT(CASE WHEN Status = 'Delivered' THEN 1 END) as delivered_orders
    FROM TransacTb";

    if ($selectedYear !== 'all') {
        $sql .= " WHERE YEAR(TransactionDate) = :year";
    }

    $stmt = $conn->prepare($sql);

    if ($selectedYear !== 'all') {
        $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
    }

    $stmt->execute();
    $response['stats'] = $stmt->fetch(PDO::FETCH_ASSOC);


    // Get Quarterly Revenue for selected year
    $sql = "SELECT 
    YEAR(TransactionDate) as year,
    CONCAT('Q', QUARTER(TransactionDate)) as quarter,
    CONCAT(YEAR(TransactionDate), '-Q', QUARTER(TransactionDate)) as quarter_label,
    QUARTER(TransactionDate) as quarter_num,
    SUM(TotalPrice) as revenue,
    COUNT(*) as order_count
    FROM TransacTb";

    if ($selectedYear !== 'all') {
        $sql .= " WHERE YEAR(TransactionDate) = :year";
    }

    $sql .= " GROUP BY 
        YEAR(TransactionDate),
        QUARTER(TransactionDate)
    ORDER BY 
        YEAR(TransactionDate) ASC,
        QUARTER(TransactionDate) ASC";

    $stmt = $conn->prepare($sql);

    if ($selectedYear !== 'all') {
        $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
    }

    $stmt->execute();
    $response['quarterly_revenue'] = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Get Low Stock Items (not year dependent)
    $sql = "SELECT 
        OnhandID,
        InventoryID,
        OnhandQty,
        RetailPrice,
        RestockThreshold
    FROM OnhandTb
    WHERE OnhandQty <= RestockThreshold
    ORDER BY OnhandQty ASC
    LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $response['low_stock'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get Sales by Province for selected year
    $sql = "SELECT 
    l.Province,
    COUNT(t.TransacID) as order_count,
    SUM(t.TotalPrice) as total_revenue
    FROM LocationTb l
    LEFT JOIN TransacTb t ON l.LocationID = t.LocationID";

    if ($selectedYear !== 'all') {
        $sql .= " WHERE YEAR(t.TransactionDate) = :year";
    }
    
    $sql .= " GROUP BY l.Province
    ORDER BY total_revenue DESC";

    $stmt = $conn->prepare($sql);

    if ($selectedYear !== 'all') {
        $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
    }

    $stmt->execute();
    $response['province_sales'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Top Selling Products
    $sql = "SELECT 
            p.ProductName, 
            o.InventoryID, 
            SUM(c.Quantity) AS total_quantity_sold,
            SUM(c.Quantity * c.Price) AS total_revenue
        FROM CartRecordTb c
        JOIN OnhandTb o ON c.OnhandID = o.OnhandID
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID
        JOIN ProductTb p ON i.ProductID = p.ProductID";
    if ($selectedYear !== 'all') {
        $sql .= " JOIN TransacTb t ON c.TransacID = t.TransacID
                  WHERE YEAR(t.TransactionDate) = :year";
    }
    $sql .= " GROUP BY p.ProductName, o.InventoryID
              ORDER BY total_quantity_sold DESC
              LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if ($selectedYear !== 'all') {
        $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
    }
    $stmt->execute();
    $response['top_selling_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Least Selling Products
    $sql = "SELECT 
            p.ProductName, 
            o.InventoryID, 
            SUM(c.Quantity) AS total_quantity_sold,
            SUM(c.Quantity * c.Price) AS total_revenue
        FROM CartRecordTb c
        JOIN OnhandTb o ON c.OnhandID = o.OnhandID
        JOIN InventoryTb i ON o.InventoryID = i.InventoryID
        JOIN ProductTb p ON i.ProductID = p.ProductID";
    if ($selectedYear !== 'all') {
        $sql .= " JOIN TransacTb t ON c.TransacID = t.TransacID
                  WHERE YEAR(t.TransactionDate) = :year";
    }
    $sql .= " GROUP BY p.ProductName, o.InventoryID
              ORDER BY total_quantity_sold ASC
              LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if ($selectedYear !== 'all') {
        $stmt->bindParam(':year', $selectedYear, PDO::PARAM_INT);
    }
    $stmt->execute();
    $response['least_selling_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Include selected year in response
    $response['selected_year'] = $selectedYear;

    // Send JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch(PDOException $e) {
    // Handle errors
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
?>