<?php
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    redirectUrl("dashboard.php");} else {
    redirectUrl("login.php");}
exit;
?>
