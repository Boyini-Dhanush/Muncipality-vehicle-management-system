<?php
require_once 'config.php';
startSecureSession();

session_unset();
session_destroy();
header("Location: login.php");
exit();
?>