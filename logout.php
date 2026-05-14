<?php
session_start();
session_unset();
session_destroy();
header("Location: index.php?msg=You have been successfully logged out.");
exit;
?>
