<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$rootPath  = '../';
$pageTitle = 'Shop Order';
$activeNav = 'shop_order';
require __DIR__ . '/../includes/header.php';

$categoryKey = 'shop_order';
require __DIR__ . '/../includes/category_page_body.php';

require __DIR__ . '/../includes/footer.php';
