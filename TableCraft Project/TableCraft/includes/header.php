<?php
/**
 * Shared header / app shell.
 * Expects (optionally) before include:
 *   $rootPath  = '' for root-level pages, '../' for pages inside /categories or /api
 *   $pageTitle = string shown in <title> and topbar
 *   $activeNav = string key matching one of the nav items below
 */
$rootPath  = $rootPath  ?? '';
$pageTitle = $pageTitle ?? 'TableCraft';
$activeNav = $activeNav ?? '';

$userName  = $_SESSION['user_name']  ?? 'User';
$userColor = $_SESSION['avatar_color'] ?? '#1F50FF';
$initials  = '';
foreach (explode(' ', trim($userName)) as $part) { if ($part !== '') $initials .= strtoupper($part[0]); }
$initials = substr($initials, 0, 2) ?: 'U';

$navItems = [
    ['key' => 'dashboard',      'label' => 'Dashboard',              'icon' => 'bi-grid-1x2-fill',    'href' => $rootPath . 'dashboard.php'],
];
$categoryNav = [
    ['key' => 'student_record', 'label' => 'Student Record',         'icon' => 'bi-mortarboard-fill', 'href' => $rootPath . 'categories/student_record.php'],
    ['key' => 'shop_order',     'label' => 'Shop Order',             'icon' => 'bi-cart-fill',        'href' => $rootPath . 'categories/shop_order.php'],
    ['key' => 'employee_info',  'label' => 'Employee Info',          'icon' => 'bi-person-badge-fill','href' => $rootPath . 'categories/employee_info.php'],
    ['key' => 'library_books',  'label' => 'Library Book List',      'icon' => 'bi-book-fill',        'href' => $rootPath . 'categories/library_books.php'],
    ['key' => 'product_stock',  'label' => 'Product Stock',          'icon' => 'bi-box-seam-fill',    'href' => $rootPath . 'categories/product_stock.php'],
    ['key' => 'commercial',     'label' => 'Commercial Category',    'icon' => 'bi-cash-coin',        'href' => $rootPath . 'categories/commercial.php'],
    ['key' => 'custom_category','label' => 'Custom Category Editor', 'icon' => 'bi-sliders',          'href' => $rootPath . 'categories/custom_category.php'],
];
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> · TableCraft</title>

<!-- Theme applied before paint to avoid a flash of the wrong theme -->
<script>
(function(){ var t = localStorage.getItem('tc_theme') || 'bright'; document.documentElement.setAttribute('data-theme', t); })();
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link href="<?= $rootPath ?>assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="sidebar-backdrop"></div>
<div class="tc-shell">

  <aside class="tc-sidebar">
    <a href="<?= $rootPath ?>dashboard.php" class="tc-logo mb-4 px-1">
      <span class="tc-logo-mark">TC</span> TableCraft
    </a>

    <nav class="nav flex-column">
      <?php foreach ($navItems as $item): ?>
        <a href="<?= $item['href'] ?>" class="nav-link <?= $activeNav === $item['key'] ? 'active' : '' ?>">
          <i class="bi <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
        </a>
      <?php endforeach; ?>

      <div class="nav-section-title">Categories</div>
      <?php foreach ($categoryNav as $item): ?>
        <a href="<?= $item['href'] ?>" class="nav-link <?= $activeNav === $item['key'] ? 'active' : '' ?>">
          <i class="bi <?= $item['icon'] ?>"></i> <?= $item['label'] ?>
        </a>
      <?php endforeach; ?>

      <div class="nav-section-title">Library</div>
      <a href="<?= $rootPath ?>my_tables.php" class="nav-link <?= $activeNav === 'my_tables' ? 'active' : '' ?>">
        <i class="bi bi-table"></i> My Tables
      </a>
    </nav>

    <div class="mt-4 pt-3 px-1" style="border-top:1px solid var(--border)">
      <div class="d-flex align-items-center gap-2 small text-soft">
        <span class="pulse-dot"></span> System online
      </div>
    </div>
  </aside>

  <div class="tc-main">
    <header class="tc-topbar">
      <button class="btn btn-icon sidebar-toggle-btn" aria-label="Open menu"><i class="bi bi-list"></i></button>
      <h5 class="mb-0 fw-800 flex-grow-1"><?= htmlspecialchars($pageTitle) ?></h5>

      <div class="theme-switch" data-theme-toggle title="Toggle dark / bright mode">
        <div class="knob"><i class="bi bi-sun-fill"></i></div>
      </div>

      <div class="dropdown">
        <button class="btn btn-icon" style="background:<?= htmlspecialchars($userColor) ?>;color:#fff;border:none;font-weight:700;font-size:.8rem;" data-bs-toggle="dropdown">
          <?= htmlspecialchars($initials) ?>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text small text-soft">Signed in as<br><strong class="text-body"><?= htmlspecialchars($userName) ?></strong></span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="<?= $rootPath ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
        </ul>
      </div>
    </header>

    <main class="tc-content">
