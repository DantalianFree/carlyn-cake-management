<?php
session_start();
session_destroy();
header("Location: user/user-login.php");
exit();
?>
