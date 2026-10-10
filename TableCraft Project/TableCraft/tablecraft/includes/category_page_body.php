<?php
/**
 * Shared body for every /categories/*.php page.
 * Expects $categoryKey to be set before include.
 * Must be included AFTER header.php.
 */
require_once __DIR__ . '/category_defaults.php';
$map = tc_category_map();
$cat = $map[$categoryKey];
$userId = $_SESSION['user_id'];

$stmt = mysqli_prepare($conn, 'SELECT id, title, theme_mode, accent_color, updated_at,
    (SELECT COUNT(*) FROM table_rows WHERE table_id = data_tables.id) AS row_count,
    (SELECT COUNT(*) FROM table_columns WHERE table_id = data_tables.id) AS col_count
    FROM data_tables WHERE user_id = ? AND category = ? ORDER BY updated_at DESC');
mysqli_stmt_bind_param($stmt, 'is', $userId, $categoryKey);
mysqli_stmt_execute($stmt);
$tables = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);
?>

<div class="tc-card stagger-item p-4 mb-4" style="border:none; background:var(--surface);">
  <div class="d-flex flex-wrap align-items-center gap-3">
    <div class="icon-badge" style="width:60px;height:60px;font-size:1.7rem;margin-bottom:0;"><i class="bi <?= $cat['icon'] ?>"></i></div>
    <div class="flex-grow-1">
      <h4 class="fw-800 mb-1"><?= htmlspecialchars($cat['label']) ?></h4>
      <p class="text-soft mb-0"><?= htmlspecialchars($cat['desc']) ?></p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTableModal"><i class="bi bi-plus-lg me-1"></i> New Table</button>
  </div>
</div>

<?php if (empty($tables)): ?>
  <div class="tc-table-wrap">
    <div class="empty-state">
      <i class="bi <?= $cat['icon'] ?>"></i>
      <p class="fw-600 mb-1" style="color:var(--text)">No <?= htmlspecialchars(strtolower($cat['label'])) ?> tables yet</p>
      <p class="small mb-3">Default columns: <?= htmlspecialchars(implode(', ', array_map(function($c){ return $c['name']; }, $cat['columns']))) ?></p>
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newTableModal"><i class="bi bi-plus-lg me-1"></i> Create your first table</button>
    </div>
  </div>
<?php else: ?>
  <div class="row g-3">
    <?php $i = 0; foreach ($tables as $t): $i++; ?>
      <div class="col-md-6 col-xl-4">
        <div class="tc-card tc-elevate stagger-item p-3" style="animation-delay:<?= $i * 50 ?>ms">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge-category"><?= htmlspecialchars($cat['label']) ?></span>
            <div class="dropdown">
              <button class="btn btn-sm btn-link text-soft p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="../table_view.php?id=<?= $t['id'] ?>"><i class="bi bi-pencil-square me-2"></i>Open &amp; Edit</a></li>
                <li><a class="dropdown-item text-danger del-table-btn" href="#" data-id="<?= $t['id'] ?>" data-name="<?= htmlspecialchars($t['title']) ?>"><i class="bi bi-trash me-2"></i>Delete</a></li>
              </ul>
            </div>
          </div>
          <h6 class="fw-700 mb-2"><?= htmlspecialchars($t['title']) ?></h6>
          <div class="d-flex gap-3 text-soft small mb-3">
            <span><i class="bi bi-list-columns-reverse me-1"></i><?= (int)$t['col_count'] ?> cols</span>
            <span><i class="bi bi-rows me-1"></i><?= (int)$t['row_count'] ?> rows</span>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <span class="text-soft small"><?= date('d M Y', strtotime($t['updated_at'])) ?></span>
            <a href="../table_view.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-soft">Open <i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- New Table Modal -->
<div class="modal fade" id="newTableModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:var(--surface); color:var(--text); border-radius:var(--radius-md);">
      <form method="post" action="../new_table.php">
        <div class="modal-header" style="border-color:var(--border)">
          <h5 class="modal-title fw-800">New <?= htmlspecialchars($cat['label']) ?> Table</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="category" value="<?= htmlspecialchars($categoryKey) ?>">
          <label class="form-label">Table title</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. <?= htmlspecialchars($cat['label']) ?> - <?= date('M Y') ?>">
          <p class="text-soft small mt-3 mb-0">Starts with columns: <?= htmlspecialchars(implode(', ', array_map(function($c){ return $c['name']; }, $cat['columns']))) ?>. You can add, rename, reorder or delete columns afterwards.</p>
        </div>
        <div class="modal-footer" style="border-color:var(--border)">
          <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Table <i class="bi bi-arrow-right ms-1"></i></button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.del-table-btn');
  if (!btn) return;
  e.preventDefault();
  if (!confirm('Delete "' + btn.dataset.name + '"? This cannot be undone.')) return;
  fetch('../api/delete_table.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ table_id: btn.dataset.id })
  }).then(r => r.json()).then(data => {
    if (data.success) { location.reload(); } else { alert(data.message || 'Could not delete table'); }
  });
});
</script>
