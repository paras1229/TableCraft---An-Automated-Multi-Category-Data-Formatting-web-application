<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/category_defaults.php';

$userId = $_SESSION['user_id'];
$map = tc_category_map();

$stmt = mysqli_prepare($conn, 'SELECT id, category, title, accent_color, updated_at,
    (SELECT COUNT(*) FROM table_rows WHERE table_id = data_tables.id) AS row_count,
    (SELECT COUNT(*) FROM table_columns WHERE table_id = data_tables.id) AS col_count
    FROM data_tables WHERE user_id = ? ORDER BY updated_at DESC');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$tables = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$pageTitle = 'My Tables';
$activeNav = 'my_tables';
require __DIR__ . '/includes/header.php';
?>

<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div class="input-icon-group" style="max-width:320px;">
    <i class="bi bi-search"></i>
    <input type="text" id="tcSearchInput" class="form-control" placeholder="Search your tables...">
  </div>
  <select id="tcCategoryFilter" class="form-select" style="max-width:220px;">
    <option value="">All categories</option>
    <?php foreach ($map as $key => $c): ?><option value="<?= $key ?>"><?= htmlspecialchars($c['label']) ?></option><?php endforeach; ?>
  </select>
</div>

<?php if (empty($tables)): ?>
  <div class="tc-table-wrap">
    <div class="empty-state">
      <i class="bi bi-table"></i>
      <p class="fw-600 mb-1" style="color:var(--text)">You haven't created any tables yet</p>
      <p class="small mb-3">Head to the dashboard and pick a category to get started.</p>
      <a href="dashboard.php" class="btn btn-primary">Go to Dashboard</a>
    </div>
  </div>
<?php else: ?>
  <div class="tc-table-wrap">
    <table class="table tc-table mb-0" id="tcTablesTable">
      <thead><tr><th>Title</th><th>Category</th><th>Columns</th><th>Rows</th><th>Last updated</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($tables as $t): ?>
          <tr class="stagger-item" data-title="<?= strtolower(htmlspecialchars($t['title'])) ?>" data-category="<?= $t['category'] ?>">
            <td class="fw-600"><span class="d-inline-block rounded-circle me-2" style="width:10px;height:10px;background:<?= htmlspecialchars($t['accent_color']) ?>"></span><?= htmlspecialchars($t['title']) ?></td>
            <td><span class="badge-category"><?= htmlspecialchars($map[$t['category']]['label'] ?? $t['category']) ?></span></td>
            <td><?= (int)$t['col_count'] ?></td>
            <td><?= (int)$t['row_count'] ?></td>
            <td class="text-soft small"><?= date('d M Y, h:i A', strtotime($t['updated_at'])) ?></td>
            
            <td class="text-end">

  <!-- View Button -->
  <a href="table_view.php?id=<?= (int)$t['id'] ?>&mode=view"
     class="btn btn-sm btn-soft"
     title="View Records">

    <i class="bi bi-eye"></i>

  </a>

  <!-- Edit Button -->
  <a href="table_view.php?id=<?= (int)$t['id'] ?>"
     class="btn btn-sm btn-soft"
     title="Edit Table">

    <i class="bi bi-pencil-square"></i>

  </a>

  <!-- Delete Button -->
  <button
      class="btn btn-sm btn-link text-danger del-table-btn"
      data-id="<?= (int)$t['id'] ?>"
      data-name="<?= htmlspecialchars($t['title']) ?>"
      title="Delete Table">

    <i class="bi bi-trash"></i>

  </button>

</td>



            
            
            

          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

<script>
document.getElementById('tcSearchInput')?.addEventListener('input', filterTables);
document.getElementById('tcCategoryFilter')?.addEventListener('change', filterTables);
function filterTables() {
  const q = (document.getElementById('tcSearchInput').value || '').toLowerCase();
  const cat = document.getElementById('tcCategoryFilter').value;
  document.querySelectorAll('#tcTablesTable tbody tr').forEach(row => {
    const matchesText = row.dataset.title.includes(q);
    const matchesCat = !cat || row.dataset.category === cat;
    row.style.display = (matchesText && matchesCat) ? '' : 'none';
  });
}
document.addEventListener('click', function (e) {
  const btn = e.target.closest('.del-table-btn');
  if (!btn) return;
  if (!confirm('Delete "' + btn.dataset.name + '"? This cannot be undone.')) return;
  fetch('api/delete_table.php', {
    method: 'POST', headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ table_id: btn.dataset.id })
  }).then(r => r.json()).then(data => {
    if (data.success) { location.reload(); } else { alert(data.message || 'Could not delete table'); }
  });
});
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
