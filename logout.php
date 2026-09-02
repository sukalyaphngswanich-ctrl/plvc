<?php
require_once __DIR__ . '/includes/auth.php';
logoutUser();
redirectUrl("/login.php?logout=success");
?>
