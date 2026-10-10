<?php
require_once __DIR__ . '/../config/db.php';
$_SESSION = [];
session_unset();
session_destroy();
header('Location: login.php');
exit;
