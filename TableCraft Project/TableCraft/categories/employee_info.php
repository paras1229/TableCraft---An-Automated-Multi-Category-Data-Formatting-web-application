<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$rootPath  = '../';
$pageTitle = 'Employee Info';
$activeNav = 'employee_info';
require __DIR__ . '/../includes/header.php';

$categoryKey = 'employee_info';
require __DIR__ . '/../includes/category_page_body.php';

require __DIR__ . '/../includes/footer.php';
