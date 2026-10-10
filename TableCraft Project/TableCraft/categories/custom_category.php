<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

$rootPath  = '../';
$pageTitle = 'Custom Category Editor';
$activeNav = 'custom_category';
require __DIR__ . '/../includes/header.php';

$categoryKey = 'custom';
require __DIR__ . '/../includes/category_page_body.php';

require __DIR__ . '/../includes/footer.php';
