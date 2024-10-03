<?php
session_start();

// Destroy all sessions
session_unset();
session_destroy();

// Redirect to the login page
header("Location: http://localhost/1CAPSTONE/index.php");
exit();
?>
