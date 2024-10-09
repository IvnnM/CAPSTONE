<?php
session_start();
include("../config/database.php");

// // Check if the admin session variable is set
// if (!isset($_SESSION['EmpID'])) {
//     // Redirect to login page if EmpID is not set
//     header("Location: ../index.php");
//     exit();
// }

?>
<?php include("../includes/personnel/header.php"); ?>


<?php include("../includes/personnel/footer.php"); ?>