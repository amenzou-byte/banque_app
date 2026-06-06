<?php
require_once 'config/db.php';
require_once 'includes/functions.php';
// auth.php déjà inclus par functions.php — pas besoin de le réinclure
exigerConnexion();

if (estBanquier()) include 'admin_dashboard.php';
else include 'client_dashboard.php';
?>