<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/category_defaults.php';

$userId = $_SESSION['user_id'];
$map = tc_category_map();

// ---- Stats ----------------------------------------------------------
$stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS c FROM data_tables WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$totalTables = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'] ?? 0);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, 'SELECT COUNT(DISTINCT category) AS c FROM data_tables WHERE user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$categoriesUsed = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'] ?? 0);
mysqli_stmt_close($stmt);

$stmt = mysqli_prepare($conn, 'SELECT COUNT(*) AS c FROM table_rows r JOIN data_tables t ON r.table_id = t.id WHERE t.user_id = ?');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$totalRows = (int)(mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'] ?? 0);
mysqli_stmt_close($stmt);

// ---- Recent tables ----------------------------------------------------
$stmt = mysqli_prepare($conn, 'SELECT id, category, title, updated_at FROM data_tables WHERE user_id = ? ORDER BY updated_at DESC LIMIT 6');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$recentTables = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$pageTitle = 'Dashboard';
$activeNav = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<!-- Welcome banner -->
<div class="tc-card stagger-item p-4 p-md-5 mb-4" style="background:linear-gradient(120deg, var(--navy), var(--blue) 130%); border:none; color:#fff; overflow:hidden; position:relative;">
  <div class="position-relative" style="z-index:2;">
    <p class="mb-1" style="color:rgba(255,255,255,.75)"><?= date('l, d F Y') ?></p>
    <h2 class="fw-800 mb-2" style="color:rgba(255, 255, 255, 1)">Welcome back, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]) ?> 👋</h2>
    <p class="mb-4" style="color:rgba(255,255,255,.8); max-width:520px;">Type your data once and TableCraft formats it into a clean, exportable table — pick a category below to get started.</p>
    <a href="categories/custom_category.php" class="btn btn-light fw-700 text-accent"><i class="bi bi-plus-lg me-1"></i> Create a new table</a>
  </div>
</div>

<!-- Stat tiles -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-4">
    <div class="stat-tile stagger-item" style="animation-delay:60ms">
      <div class="stat-icon" style="background:var(--blue-soft); color:var(--accent);"><i class="bi bi-table"></i></div>
      <div>
        <div class="stat-value" data-count-to="<?= $totalTables ?>">0</div>
        <div class="text-soft small">Tables created</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-4">
    <div class="stat-tile stagger-item" style="animation-delay:120ms">
      <div class="stat-icon" style="background:rgba(23,178,106,.12); color:#17B26A;"><i class="bi bi-collection"></i></div>
      <div>
        <div class="stat-value" data-count-to="<?= $categoriesUsed ?>">0</div>
        <div class="text-soft small">Categories used</div>
      </div>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-tile stagger-item" style="animation-delay:180ms">
      <div class="stat-icon" style="background:rgba(245,165,36,.14); color:#F5A524;"><i class="bi bi-list-columns-reverse"></i></div>
      <div>
        <div class="stat-value" data-count-to="<?= $totalRows ?>">0</div>
        <div class="text-soft small">Total rows stored</div>
      </div>
    </div>
  </div>
</div>

<!-- Categories -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 class="fw-800 mb-0">Popular Categories</h5>
  <a href="my_tables.php" class="small fw-600">View all my tables <i class="bi bi-arrow-right"></i></a>
</div>

<div class="row g-3 mb-4">
  <?php $i = 0;
  foreach ($map as $key => $cat): $i++; ?>
    <div class="col-6 col-lg-3">
      <a href="categories/<?= $key === 'custom' ? 'custom_category' : $key ?>.php" class="text-decoration-none">
        <div class="category-card stagger-item" style="animation-delay:<?= $i * 60 ?>ms">
          <div class="icon-badge"><i class="bi <?= $cat['icon'] ?>"></i></div>
          <h6 class="fw-700 mb-1" style="color:var(--text)"><?= htmlspecialchars($cat['label']) ?></h6>
          <p class="text-soft small mb-0"><?= htmlspecialchars($cat['desc']) ?></p>
          <span class="stretched-link"></span>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>

<!-- Recent tables -->
<div class="d-flex align-items-center justify-content-between mb-3">
  <h5 class="fw-800 mb-0">Recently Updated</h5>
</div>

<div class="tc-table-wrap mb-4">
  <?php if (empty($recentTables)): ?>
    <div class="empty-state">
      <i class="bi bi-table"></i>
      <p class="mb-1 fw-600" style="color:var(--text)">No tables yet</p>
      <p class="small mb-3">Pick a category above and create your first table.</p>
    </div>
  <?php else: ?>
    <table class="table tc-table mb-0">
      <thead>
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Last updated</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentTables as $t): ?>
          <tr class="stagger-item">
            <td class="fw-600"><i class="bi bi-file-earmark-spreadsheet text-accent me-2"></i><?= htmlspecialchars($t['title']) ?></td>
            <td><span class="badge-category"><?= htmlspecialchars($map[$t['category']]['label'] ?? $t['category']) ?></span></td>
            <td class="text-soft small"><?= date('d M Y, h:i A', strtotime($t['updated_at'])) ?></td>
            <td class="text-end"><a href="table_view.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-soft">Open <i class="bi bi-arrow-right"></i></a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>