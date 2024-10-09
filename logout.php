<?php
session_start();

// Destroy all sessions
session_unset();
session_destroy();

header("Location: /3CAPSTONE/index.php");
exit();
?>
