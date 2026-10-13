<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/category_defaults.php';

$tableId = (int)($_GET['id'] ?? 0);
$userId  = $_SESSION['user_id'];
$map     = tc_category_map();

$search = isset($_GET['search']) ? $_GET['search'] : '';
$query = "SELECT * FROM students WHERE student_name LIKE '%$search%' OR subject LIKE '%$search%'";

// Check View Mode
$isViewMode = (($_GET['mode'] ?? '') === 'view');

$stmt = mysqli_prepare($conn, 'SELECT * FROM data_tables WHERE id = ? AND user_id = ?');
mysqli_stmt_bind_param($stmt, 'ii', $tableId, $userId);
mysqli_stmt_execute($stmt);
$table = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$table) {
    header('Location: dashboard.php');
    exit;
}

// Columns
$stmt = mysqli_prepare($conn, 'SELECT * FROM table_columns WHERE table_id = ? ORDER BY column_order ASC, id ASC');
mysqli_stmt_bind_param($stmt, 'i', $tableId);
mysqli_stmt_execute($stmt);
$columns = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

// Rows + cells
$stmt = mysqli_prepare($conn, 'SELECT id FROM table_rows WHERE table_id = ? ORDER BY row_order ASC, id ASC');
mysqli_stmt_bind_param($stmt, 'i', $tableId);
mysqli_stmt_execute($stmt);
$rowIds = array_column(mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC), 'id');
mysqli_stmt_close($stmt);

$rows = [];
if (!empty($rowIds)) {
    $placeholders = implode(',', array_fill(0, count($rowIds), '?'));
    $types = str_repeat('i', count($rowIds));
    $cellStmt = mysqli_prepare($conn, "SELECT row_id, column_id, cell_value FROM table_cells WHERE row_id IN ($placeholders)");
    mysqli_stmt_bind_param($cellStmt, $types, ...$rowIds);
    mysqli_stmt_execute($cellStmt);
    $allCells = mysqli_fetch_all(mysqli_stmt_get_result($cellStmt), MYSQLI_ASSOC);
    mysqli_stmt_close($cellStmt);

    $cellsByRow = [];
    foreach ($allCells as $c) { $cellsByRow[$c['row_id']][] = ['column_id' => (int)$c['column_id'], 'value' => $c['cell_value']]; }
    foreach ($rowIds as $rid) { $rows[] = ['row_id' => $rid, 'cells' => $cellsByRow[$rid] ?? []]; }
}

$columnsJs = array_map(function ($c) {
    return [
        'id' => (int)$c['id'], 'column_name' => $c['column_name'], 'column_type' => $c['column_type'], 'is_formula' => (int)$c['is_formula'],
    ];
}, $columns);

$initData = [
    'table_id' => (int)$table['id'],
    'category' => $table['category'],
    'title' => $table['title'],
    'is_commercial' => (int)$table['is_commercial'],
    'accent_color' => $table['accent_color'],
    'font_family' => $table['font_family'],
    'columns' => $columnsJs,
    'rows' => $rows,
];

$catInfo = $map[$table['category']] ?? ['label' => ucwords(str_replace('_', ' ', $table['category']))];

$rootPath  = '';
$pageTitle = $table['title'];
$activeNav = $table['category'] === 'custom' ? 'custom_category' : $table['category'];
require __DIR__ . '/includes/header.php';
?>



<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
  <div class="d-flex align-items-center gap-2">
    <a href="dashboard.php" class="btn btn-icon"><i class="bi bi-arrow-left"></i></a>
    <div>
      <span class="badge-category mb-1 d-inline-block"><?= htmlspecialchars($catInfo['label']) ?></span>
      <div id="tcSaveIndicator" class="text-success small">All changes saved</div>
    </div>
  </div>
  <div class="d-flex flex-wrap gap-2">
 

  <?php if (!$isViewMode): ?>

    <button class="btn btn-outline-primary btn-sm"
            id="addColBtn">
      <i class="bi bi-plus-lg me-1"></i>Column
    </button>

    <button class="btn btn-outline-primary btn-sm"
            id="addRowBtn">
      <i class="bi bi-plus-lg me-1"></i>Row
    </button>

    <button class="btn btn-primary btn-sm"
            id="manualSaveBtn">
      <i class="bi bi-save me-1"></i>Save
    </button>

  <?php endif; ?>

  <div class="dropdown">

    <button class="btn btn-soft btn-sm dropdown-toggle"
            data-bs-toggle="dropdown">

      <i class="bi bi-download me-1"></i>Export

    </button>

    <ul class="dropdown-menu dropdown-menu-end">

      <li>
        <a class="dropdown-item" href="#" id="exportPdfBtn">
          PDF
        </a>
      </li>

      <li>
        <a class="dropdown-item" href="#" id="exportDocBtn">
          Word (DOC)
        </a>
      </li>

      <li>
        <a class="dropdown-item" href="#" id="exportPngBtn">
          PNG Image
        </a>
      </li>

    </ul>

  </div>

</div>


    
  
</div>




<div class="row g-3">
  <div class="col-lg-9">
    <div id="tcTableExportArea">
<!-- Search Input for Filter -->
<div style="margin-bottom: 15px;">
    <input type="text" id="tableSearchInput" placeholder="Search data" onkeyup="filterTable()" style="padding: 8px 12px; width: 300px; border: 1px solid #ccc; border-radius: 4px;">
</div>
      <h4 id="tcTitleInput" class="fw-800 mb-3" contenteditable="true" spellcheck="false"><?= htmlspecialchars($table['title']) ?></h4>
      <div class="tc-table-wrap">
        <div class="table-responsive">
          <table class="table tc-table mb-0">
            <thead id="tcThead"></thead>
            <tbody id="tcTbody">
              <script>
function filterTable() {
    let input = document.getElementById("tableSearchInput");
    let filter = input.value.toLowerCase();
    let table = document.querySelector("table");
    let tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) { // i = 1 રાખ્યું છે જેથી ટેબલ હેડર (th) સ્કિપ થાય
        let tdList = tr[i].getElementsByTagName("td");
        let rowMatch = false;
        
        for (let j = 0; j < tdList.length; j++) {
            let td = tdList[j];
            if (td) {
                let txtValue = td.textContent || td.innerText;
                if (txtValue.toLowerCase().indexOf(filter) > -1) {
                    rowMatch = true;
                    break;
                }
            }
        }
        
        if (rowMatch) {
            tr[i].style.display = "";
        } else {
            tr[i].style.display = "none";
        }
    }
}
</script>
            </tbody>
            <tfoot id="tcTfoot"></tfoot>
          </table>
        </div>
      </div>
      <div class="d-flex gap-3 text-soft small mt-2">
        <span><i class="bi bi-list-columns-reverse me-1"></i><span id="tcColCount">0</span> columns</span>
        <span><i class="bi bi-rows me-1"></i><span id="tcRowCount">0</span> rows</span>
      </div>
    </div>
  </div>

  <?php if (!$isViewMode): ?>

<div class="col-lg-3">

  <!-- Your existing Table Design card -->

  <!-- Your existing Tips card -->

</div>

<?php endif; ?>
  <div class="col-lg-3">
    <div class="tc-card p-3 mb-3">
      <h6 class="fw-700 mb-3"><i class="bi bi-palette me-2"></i>Table Design</h6>
      <p class="small text-soft mb-2">Accent color</p>
      <div class="d-flex gap-2 flex-wrap mb-3">
        <?php $colors = ['#1F50FF', '#080E53', '#17B26A', '#F5A524', '#E5484D', '#8B5CF6']; foreach ($colors as $c): ?>
          <span class="swatch <?= $table['accent_color'] === $c ? 'active' : '' ?>" style="background:<?= $c ?>" data-color="<?= $c ?>"></span>
        <?php endforeach; ?>
      </div>
      <p class="small text-soft mb-2">Table font</p>
      <div class="d-flex gap-2 flex-wrap">
        <?php $fonts = ['Figtree', 'Poppins', 'Inter', 'Roboto', 'Merriweather', 'Courier Prime']; foreach ($fonts as $f): ?>
          <span class="font-thumb small <?= $table['font_family'] === $f ? 'active' : '' ?>" data-font="<?= $f ?>" style="font-family:'<?= $f ?>',sans-serif;"><?= $f ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="tc-card p-3">
      <h6 class="fw-700 mb-3"><i class="bi bi-info-circle me-2"></i>Tips</h6>
      <ul class="small text-soft ps-3 mb-0">
        <li class="mb-2">Click any cell to edit it directly.</li>
        <li class="mb-2">Use the ⋮ menu on a column header to rename, reorder or delete it.</li>
        <li class="mb-2">Changes auto-save a second after you stop typing.</li>
        <?php if ($table['is_commercial'] || in_array($table['category'], ['shop_order', 'product_stock'])): ?>
          <li>The <strong>Total</strong> column is calculated automatically.</li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</div>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Inter:wght@400;600;700&family=Roboto:wght@400;500;700&family=Merriweather:wght@400;700&family=Courier+Prime&display=swap" rel="stylesheet">

<script>
const TC_INIT = <?= json_encode($initData, JSON_UNESCAPED_UNICODE) ?>;
const TC_READ_ONLY = <?= $isViewMode ? 'true' : 'false' ?>;
</script>





<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.8.2/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="assets/js/table-builder.js"></script>
<script src="assets/js/export.js"></script>

<?php require __DIR__ . '/includes/footer.php'; ?>
