/* =====================================================================
   TableCraft — Dynamic Table Engine
   This is the core feature: user types data -> table is generated live,
   can be edited/added/deleted, styled, and saved to the database.
   Expects a global `TC_INIT` object printed server-side by table_view.php:
   {
     table_id, category, title, is_commercial,
     accent_color, font_family,
     columns: [{id, column_name, column_type, is_formula}],
     rows: [ { row_id, cells: [ {column_id, value}, ... ] }, ... ]
   }
   ===================================================================== */

(function () {
  if (typeof TC_INIT === 'undefined') return;

  let state = {
    table_id: TC_INIT.table_id,
    category: TC_INIT.category,
    title: TC_INIT.title,
    is_commercial: !!TC_INIT.is_commercial,
    accent_color: TC_INIT.accent_color || '#1F50FF',
    font_family: TC_INIT.font_family || 'Figtree',
    columns: JSON.parse(JSON.stringify(TC_INIT.columns || [])),
    rows: JSON.parse(JSON.stringify(TC_INIT.rows || []))
  };

  let uidCounter = -1; // negative temp ids for new, unsaved columns/rows
  const $ = (sel, ctx) => (ctx || document).querySelector(sel);
  const $$ = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));
  const thead = $('#tcThead');
  const tbody = $('#tcTbody');
  const tfoot = $('#tcTfoot');
  const titleInput = $('#tcTitleInput');
  const saveIndicator = $('#tcSaveIndicator');

  function num(v) {
    const n = parseFloat(String(v).replace(/[^0-9.\-]/g, ''));
    return isNaN(n) ? 0 : n;
  }

  function colIndexByName(fragment) {
    return state.columns.findIndex((c) => c.column_name.toLowerCase().includes(fragment));
  }

  /* ---------------- FORMULA ENGINE (for shop_order / product_stock / commercial) --- */
  function recalcFormulas() {
    if (!state.columns.some((c) => c.is_formula)) return;

    let qtyIdx, priceIdx, totalIdx, discIdx;

    if (state.category === 'shop_order') {
      qtyIdx = colIndexByName('quantity');
      priceIdx = colIndexByName('price');
      totalIdx = state.columns.findIndex((c) => c.is_formula);
    } else if (state.category === 'product_stock') {
      qtyIdx = colIndexByName('quantity');
      priceIdx = colIndexByName('price');
      totalIdx = state.columns.findIndex((c) => c.is_formula);
    } else if (state.category === 'commercial') {
      qtyIdx = colIndexByName('quantity');
      priceIdx = colIndexByName('price');
      discIdx = colIndexByName('discount');
      totalIdx = state.columns.findIndex((c) => c.is_formula);
    }

    if (totalIdx === undefined || totalIdx < 0 || qtyIdx < 0 || priceIdx < 0) return;

    state.rows.forEach((row) => {
      const qty = num(getCell(row, qtyIdx));
      const price = num(getCell(row, priceIdx));
      let total = qty * price;
      if (discIdx !== undefined && discIdx >= 0) {
        const disc = num(getCell(row, discIdx));
        total = total - (total * disc) / 100;
      }
      setCell(row, totalIdx, total.toFixed(2));
    });
  }

  function getCell(row, colIdx) {
    const colId = state.columns[colIdx] && state.columns[colIdx].id;
    const cell = row.cells.find((c) => c.column_id === colId);
    return cell ? cell.value : '';
  }
  function setCell(row, colIdx, value) {
    const colId = state.columns[colIdx].id;
    let cell = row.cells.find((c) => c.column_id === colId);
    if (!cell) { cell = { column_id: colId, value: '' }; row.cells.push(cell); }
    cell.value = value;
  }

  /* ---------------- RENDER ------------------------------------------------------- */
  function render() {
    recalcFormulas();

    // Header
    thead.innerHTML = '';
    const trh = document.createElement('tr');
    trh.appendChild(el('th', { style: 'width:36px' }, '#'));
    state.columns.forEach((col, i) => {
      const th = document.createElement('th');
      th.innerHTML =
        '<div class="d-flex align-items-center justify-content-between gap-2">' +
        '<span>' + escapeHtml(col.column_name) + (col.is_formula ? ' <i class="bi bi-lightning-charge-fill" title="Auto-calculated"></i>' : '') + '</span>' +
        '<div class="dropdown">' +
        '<button class="btn btn-sm btn-link text-white p-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>' +
        '<ul class="dropdown-menu dropdown-menu-end">' +
        '<li><a class="dropdown-item rename-col" href="#" data-i="' + i + '"><i class="bi bi-pencil me-2"></i>Rename</a></li>' +
        '<li><a class="dropdown-item move-col" href="#" data-i="' + i + '" data-dir="-1"><i class="bi bi-arrow-left me-2"></i>Move left</a></li>' +
        '<li><a class="dropdown-item move-col" href="#" data-i="' + i + '" data-dir="1"><i class="bi bi-arrow-right me-2"></i>Move right</a></li>' +
        '<li><hr class="dropdown-divider"></li>' +
        '<li><a class="dropdown-item text-danger del-col" href="#" data-i="' + i + '"><i class="bi bi-trash me-2"></i>Delete column</a></li>' +
        '</ul></div></div>';
      trh.appendChild(th);
    });
    trh.appendChild(el('th', { style: 'width:60px' }, ''));
    thead.appendChild(trh);

    // Body
    tbody.innerHTML = '';
    if (state.rows.length === 0) {
      const tr = document.createElement('tr');
      const td = document.createElement('td');
      td.colSpan = state.columns.length + 2;
      td.innerHTML = '<div class="empty-state py-4"><i class="bi bi-table"></i><p class="mb-2">No rows yet</p><button class="btn btn-sm btn-primary" id="emptyAddRow"><i class="bi bi-plus-lg me-1"></i>Add your first row</button></div>';
      tr.appendChild(td);
      tbody.appendChild(tr);
    } else {
      state.rows.forEach((row, rIdx) => {
        const tr = document.createElement('tr');
        tr.className = 'stagger-item';
        tr.style.animationDelay = Math.min(rIdx * 30, 300) + 'ms';
        tr.appendChild(el('td', { class: 'text-soft fw-600' }, String(rIdx + 1)));
        state.columns.forEach((col, cIdx) => {
          const td = document.createElement('td');
          if (col.is_formula) {
            td.textContent = getCell(row, cIdx);
            td.classList.add('fw-600', 'text-accent');
          } else {
            td.contentEditable = 'true';
            td.textContent = getCell(row, cIdx);
            td.dataset.row = rIdx;
            td.dataset.col = cIdx;
            if (col.column_type === 'number' || col.column_type === 'currency') td.classList.add('text-end');
          }
          tr.appendChild(td);
        });
        const tdActions = document.createElement('td');
        tdActions.innerHTML = '<button class="btn btn-sm btn-link text-danger del-row p-0" data-i="' + rIdx + '" title="Delete row"><i class="bi bi-trash"></i></button>';
        tr.appendChild(tdActions);
        tbody.appendChild(tr);
      });
    }

    // Footer (grand total, only when a formula column exists)
    tfoot.innerHTML = '';
    const formulaIdx = state.columns.findIndex((c) => c.is_formula);
    if (formulaIdx >= 0 && state.rows.length > 0) {
      const grand = state.rows.reduce((sum, row) => sum + num(getCell(row, formulaIdx)), 0);
      const tr = document.createElement('tr');
      tr.innerHTML = '<td class="fw-800">Grand Total</td>' +
        state.columns.map((c, i) => i === formulaIdx ? '<td class="fw-800 text-accent">' + grand.toFixed(2) + '</td>' : '<td></td>').join('') +
        '<td></td>';
      tfoot.appendChild(tr);
    }

    $('#tcRowCount') && ($('#tcRowCount').textContent = state.rows.length);
    $('#tcColCount') && ($('#tcColCount').textContent = state.columns.length);
    applyStyling();
  }

  function el(tag, attrs, text) {
    const e = document.createElement(tag);
    Object.entries(attrs || {}).forEach(([k, v]) => e.setAttribute(k, v));
    if (text !== undefined) e.textContent = text;
    return e;
  }
  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[m]));
  }

  function applyStyling() {
    document.documentElement.style.setProperty('--accent', state.accent_color);
    document.documentElement.style.setProperty('--accent-2', state.accent_color);
    const wrap = $('#tcTableExportArea');
    if (wrap) wrap.style.fontFamily = "'" + state.font_family + "', sans-serif";
  }

  /* ---------------- EVENTS -------------------------------------------------------- */
  document.addEventListener('click', function (e) {
    const addRowBtn = e.target.closest('#addRowBtn, #emptyAddRow');
    if (addRowBtn) { addRow(); }

    const addColBtn = e.target.closest('#addColBtn');
    if (addColBtn) { openAddColumnModal(); }

    const delRow = e.target.closest('.del-row');
    if (delRow) { state.rows.splice(parseInt(delRow.dataset.i, 10), 1); render(); queueSave(); }

    const delCol = e.target.closest('.del-col');
    if (delCol) {
      e.preventDefault();
      const i = parseInt(delCol.dataset.i, 10);
      if (state.columns.length <= 1) { tcToast('A table needs at least one column', 'error'); return; }
      const removedId = state.columns[i].id;
      state.columns.splice(i, 1);
      state.rows.forEach((r) => { r.cells = r.cells.filter((c) => c.column_id !== removedId); });
      render(); queueSave();
    }

    const moveCol = e.target.closest('.move-col');
    if (moveCol) {
      e.preventDefault();
      const i = parseInt(moveCol.dataset.i, 10);
      const dir = parseInt(moveCol.dataset.dir, 10);
      const j = i + dir;
      if (j < 0 || j >= state.columns.length) return;
      [state.columns[i], state.columns[j]] = [state.columns[j], state.columns[i]];
      render(); queueSave();
    }

    const renameCol = e.target.closest('.rename-col');
    if (renameCol) {
      e.preventDefault();
      const i = parseInt(renameCol.dataset.i, 10);
      const name = prompt('Rename column', state.columns[i].column_name);
      if (name && name.trim()) { state.columns[i].column_name = name.trim(); render(); queueSave(); }
    }

    const swatch = e.target.closest('.swatch');
    if (swatch) {
      state.accent_color = swatch.dataset.color;
      $$('.swatch').forEach((s) => s.classList.remove('active'));
      swatch.classList.add('active');
      applyStyling(); queueSave();
    }

    const fontThumb = e.target.closest('.font-thumb');
    if (fontThumb) {
      state.font_family = fontThumb.dataset.font;
      $$('.font-thumb').forEach((f) => f.classList.remove('active'));
      fontThumb.classList.add('active');
      applyStyling(); queueSave();
    }

    const manualSave = e.target.closest('#manualSaveBtn');
    if (manualSave) { saveTable(true); }
  });

  tbody.addEventListener('blur', function (e) {
    const td = e.target;
    if (!td.matches('[contenteditable="true"]')) return;
    const rIdx = parseInt(td.dataset.row, 10);
    const cIdx = parseInt(td.dataset.col, 10);
    if (isNaN(rIdx) || isNaN(cIdx)) return;
    setCell(state.rows[rIdx], cIdx, td.textContent.trim());
    recalcFormulasAndRefreshVisuals();
    queueSave();
  }, true);

  tbody.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.matches('[contenteditable="true"]')) {
      e.preventDefault();
      e.target.blur();
    }
  });

  titleInput && titleInput.addEventListener('blur', function () {
    state.title = titleInput.textContent.trim() || 'Untitled Table';
    queueSave();
  });

  function recalcFormulasAndRefreshVisuals() {
    if (!state.columns.some((c) => c.is_formula)) return;
    render();
  }

  function addRow() {
    const cells = state.columns.map((c) => ({ column_id: c.id, value: '' }));
    state.rows.push({ row_id: uidCounter--, cells });
    render();
    queueSave();
    setTimeout(() => {
      const lastRow = tbody.lastElementChild;
      const firstEditable = lastRow && lastRow.querySelector('[contenteditable="true"]');
      firstEditable && firstEditable.focus();
    }, 60);
  }

  function openAddColumnModal() {
    const name = prompt('New column name (e.g. "Remarks")');
    if (!name || !name.trim()) return;
    const type = (prompt('Column type: text / number / date / currency', 'text') || 'text').toLowerCase();
    const validType = ['text', 'number', 'date', 'currency'].includes(type) ? type : 'text';
    const newCol = { id: uidCounter--, column_name: name.trim(), column_type: validType, is_formula: 0 };
    state.columns.push(newCol);
    state.rows.forEach((r) => r.cells.push({ column_id: newCol.id, value: '' }));
    render();
    queueSave();
  }

  /* ---------------- SAVE (AJAX -> api/save_table.php) ----------------------------- */
  let saveTimer = null;
  function queueSave() {
    if (saveIndicator) { saveIndicator.textContent = 'Unsaved changes'; saveIndicator.className = 'text-warning small'; }
    clearTimeout(saveTimer);
    saveTimer = setTimeout(() => saveTable(false), 900);
  }

  function saveTable(manual) {
    if (saveIndicator) { saveIndicator.textContent = 'Saving…'; saveIndicator.className = 'text-soft small'; }
    fetch('api/save_table.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(state)
    })
      .then((r) => r.json())
      .then((data) => {
        if (data.success) {
          if (!state.table_id) state.table_id = data.table_id;
          // Replace any temp negative ids with real DB ids returned from server
          if (data.columns) state.columns = data.columns;
          if (data.rows) state.rows = data.rows;
          if (saveIndicator) { saveIndicator.textContent = 'All changes saved'; saveIndicator.className = 'text-success small'; }
          if (manual) tcToast('Table saved successfully');
        } else {
          if (saveIndicator) { saveIndicator.textContent = 'Save failed'; saveIndicator.className = 'text-danger small'; }
          if (manual) tcToast(data.message || 'Could not save table', 'error');
        }
      })
      .catch(() => {
        if (saveIndicator) { saveIndicator.textContent = 'Save failed — check connection'; saveIndicator.className = 'text-danger small'; }
      });
  }



  window.TC_TableEngine = { state, render, saveTable };


 render();
})();
