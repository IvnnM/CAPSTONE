<?php
// checkout_process.php
session_start();

class CheckoutProcessor {
    private PDO $conn;
    private array $sessionData;
    private array $cartItems;
    private float $totalPrice = 0;
    
    // Constants
    private const REQUIRED_SESSIONS = ['cust_name', 'cust_num', 'cust_email'];
    private const ORDER_STATUS_PENDING = 'Pending';
    
    public function __construct(PDO $conn) {
        $this->conn = $conn;
        $this->validateSession();
        $this->sessionData = [
            'custName' => $_SESSION['cust_name'],
            'custNum' => $_SESSION['cust_num'],
            'custEmail' => $_SESSION['cust_email']
        ];
    }
    
    private function validateSession(): void {
        foreach (self::REQUIRED_SESSIONS as $session) {
            if (!isset($_SESSION[$session])) {
                $this->redirectWithError(
                    "Session expired. Please login again.",
                    "../../../../customer_login/login_form.php"
                );
            }
        }
    }
    
    public function processCheckout(): void {
        try {
            $this->conn->beginTransaction();
            
            $this->fetchCartItems();
            $this->validateCart();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest();
            }
            
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            $this->redirectWithError($e->getMessage());
        }
    }
    
    private function fetchCartItems(): void {
        $query = "SELECT 
                    c.CartID,
                    c.OnhandID,
                    c.Quantity,
                    c.AddedDate,
                    p.ProductName,
                    o.RetailPrice,
                    o.PromoPrice,
                    o.MinPromoQty,
                    o.OnhandQty
                  FROM CartTb c
                  JOIN OnhandTb o ON c.OnhandID = o.OnhandID
                  JOIN InventoryTb i ON o.InventoryID = i.InventoryID
                  JOIN ProductTb p ON i.ProductID = p.ProductID
                  WHERE c.CustEmail = :cust_email
                  FOR UPDATE";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute(['cust_email' => $this->sessionData['custEmail']]);
        $this->cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    private function validateCart(): void {
        if (empty($this->cartItems)) {
            throw new Exception("Your cart is empty.");
        }
        
        $insufficientStockItems = [];
        $this->totalPrice = 0;
        
        foreach ($this->cartItems as $item) {
            if ($item['Quantity'] > $item['OnhandQty']) {
                $insufficientStockItems[] = htmlspecialchars($item['ProductName']);
                continue;
            }
            
            $priceToUse = $item['Quantity'] >= $item['MinPromoQty'] 
                ? $item['PromoPrice'] 
                : $item['RetailPrice'];
                
            $this->totalPrice += $priceToUse * $item['Quantity'];
        }
        
        if (!empty($insufficientStockItems)) {
            throw new Exception("Insufficient stock for: " . implode(', ', $insufficientStockItems));
        }
    }
    
    private function handlePostRequest(): void {
        $deliveryFee = $this->validateDeliveryFee();
        $custNote = $this->sanitizeInput($_POST['cust_note'] ?? '');
        $exactCoordinates = $this->sanitizeInput($_POST['exact_coordinates'] ?? '');
        if (empty($exactCoordinates)) {
            throw new Exception("Exact coordinates are required.");
        }

        $locationId = $_POST['location_id'] ?? null;
        if (!$locationId) {
            throw new Exception("Location ID is required.");
        }
        $grandTotal = $this->totalPrice + $deliveryFee;
        
        $transacId = $this->createTransaction($deliveryFee, $grandTotal, $custNote, $exactCoordinates, $locationId);
        $this->processCartRecords($transacId);
        $this->clearCart();
        
        $this->conn->commit();
        
        $_SESSION['alert'] = "Order placed successfully! Your transaction ID is: " . $transacId;
        $_SESSION['alert_type'] = "success";
        
        header("Location: ../../../../views/customer_view.php");
        exit;
    }
    
    private function validateDeliveryFee(): float {
        if (!isset($_POST['delivery_fee'])) {
            throw new Exception("Delivery fee is required.");
        }
        
        $deliveryFee = filter_var($_POST['delivery_fee'], FILTER_VALIDATE_FLOAT);
        if ($deliveryFee === false || $deliveryFee < 0) {
            throw new Exception("Invalid delivery fee amount.");
        }
        
        return $deliveryFee;
    }
    
    private function createTransaction(float $deliveryFee, float $grandTotal, string $custNote, string $exactCoordinates, int $locationId): string {
        $stmt = $this->conn->prepare("
            INSERT INTO TransacTb (
                CustName, CustEmail, CustNum, DeliveryFee, 
                TotalPrice, Status, CustNote, ExactCoordinates, LocationID
            ) VALUES (
                :cust_name, :cust_email, :cust_num, :delivery_fee,
                :total_price, :status, :cust_note, :exact_coordinates, :location_id
            )
        ");
        
        $stmt->execute([
            'cust_name' => $this->sessionData['custName'],
            'cust_email' => $this->sessionData['custEmail'],
            'cust_num' => $this->sessionData['custNum'],
            'delivery_fee' => $deliveryFee,
            'total_price' => $grandTotal,
            'status' => self::ORDER_STATUS_PENDING,
            'cust_note' => $custNote,
            'exact_coordinates' => $exactCoordinates,
            'location_id' => $locationId
        ]);
        
        return $this->conn->lastInsertId();
    }
    
    private function processCartRecords(string $transacId): void {
        $cartRecordStmt = $this->conn->prepare("
            INSERT INTO CartRecordTb (
                TransacID, CustName, CustEmail, OnhandID,
                Quantity, AddedDate, Price
            ) VALUES (
                :transac_id, :cust_name, :cust_email, :onhand_id,
                :quantity, :added_date, :price
            )
        ");
        
        $updateStockStmt = $this->conn->prepare("
            UPDATE OnhandTb 
            SET OnhandQty = OnhandQty - :quantity 
            WHERE OnhandID = :onhand_id
        ");
        
        foreach ($this->cartItems as $item) {
            $priceToUse = $item['Quantity'] >= $item['MinPromoQty'] 
                ? $item['PromoPrice'] 
                : $item['RetailPrice'];
                
            $cartRecordStmt->execute([
                'transac_id' => $transacId,
                'cust_name' => $this->sessionData['custName'],
                'cust_email' => $this->sessionData['custEmail'],
                'onhand_id' => $item['OnhandID'],
                'quantity' => $item['Quantity'],
                'added_date' => $item['AddedDate'],
                'price' => $priceToUse
            ]);
            
            $updateStockStmt->execute([
                'quantity' => $item['Quantity'],
                'onhand_id' => $item['OnhandID']
            ]);
        }
    }
    
    private function clearCart(): void {
        $stmt = $this->conn->prepare("DELETE FROM CartTb WHERE CustEmail = :cust_email");
        $stmt->execute(['cust_email' => $this->sessionData['custEmail']]);
    }
    
    private function sanitizeInput(string $input): string {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    private function redirectWithError(string $message, string $location = "../../../../views/customer_view.php#Cart"): void {
        $_SESSION['alert'] = "Error: " . $message;
        $_SESSION['alert_type'] = "danger";
        header("Location: " . $location);
        exit;
    }
}

// Usage
try {
    require_once("./../../../../config/database.php");
    $checkout = new CheckoutProcessor($conn);
    $checkout->processCheckout();
} catch (Exception $e) {
    $_SESSION['alert'] = "Error: " . $e->getMessage();
    $_SESSION['alert_type'] = "danger";
    header("Location: ../../../../views/customer_view.php#Cart");
    exit;
}