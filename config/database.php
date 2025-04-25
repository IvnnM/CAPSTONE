<?php
// config/database.php
$servername = "localhost";
$username = "u581904928_dkatcaspst";
$password = "Dkatcaspst@1";
$dbname = "u581904928_dkat";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
