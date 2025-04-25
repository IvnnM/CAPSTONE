<?php
session_start();

// Destroy all sessions
session_unset();
session_destroy();

header("Location: /views/customer_view.php#Overview");
exit();
?>
