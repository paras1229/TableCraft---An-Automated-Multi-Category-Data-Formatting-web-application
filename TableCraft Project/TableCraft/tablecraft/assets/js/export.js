/* =====================================================================
   TableCraft — Multi-format Export (PDF / DOC / PNG)
   Requires (loaded via CDN in table_view.php):
     - jsPDF + jspdf-autotable  -> PDF export
     - html2canvas              -> PNG export
   DOC export uses a browser-native "HTML to Word" blob (no library needed),
   which Microsoft Word / WPS / LibreOffice all open correctly.
   ===================================================================== */

function tcGetTableTitle() {
  const t = document.getElementById('tcTitleInput');
  return (t ? t.textContent.trim() : 'TableCraft-Export') || 'TableCraft-Export';
}

function tcSafeFileName(str) {
  return str.replace(/[^a-z0-9\-_ ]/gi, '').trim().replace(/\s+/g, '-') || 'tablecraft-table';
}

/* ---------------------------- PDF EXPORT -------------------------------- */
function exportTableToPDF() {
  const state = window.TC_TableEngine.state;
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: state.columns.length > 6 ? 'landscape' : 'portrait', unit: 'pt' });

  const head = [state.columns.map((c) => c.column_name)];
  const body = state.rows.map((row) =>
    state.columns.map((col) => {
      const cell = row.cells.find((c) => c.column_id === col.id);
      return cell ? cell.value : '';
    })
  );

  doc.setFontSize(16);
  doc.setTextColor(8, 14, 83);
  doc.text(tcGetTableTitle(), 40, 40);
  doc.setFontSize(10);
  doc.setTextColor(90, 95, 138);
  doc.text('Generated with TableCraft', 40, 56);

  doc.autoTable({
    head, body,
    startY: 72,
    theme: 'grid',
    headStyles: { fillColor: [8, 14, 83], textColor: 255, fontStyle: 'bold' },
    styles: { font: 'helvetica', fontSize: 9, cellPadding: 6 },
    alternateRowStyles: { fillColor: [247, 247, 247] }
  });

  doc.save(tcSafeFileName(tcGetTableTitle()) + '.pdf');
  tcToast('PDF downloaded');
}

/* ---------------------------- PNG EXPORT -------------------------------- */
function exportTableToPNG() {
  const area = document.getElementById('tcTableExportArea');
  tcToast('Rendering image…');
  html2canvas(area, { backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--surface') || '#ffffff', scale: 2 })
    .then((canvas) => {
      const link = document.createElement('a');
      link.download = tcSafeFileName(tcGetTableTitle()) + '.png';
      link.href = canvas.toDataURL('image/png');
      link.click();
      tcToast('PNG downloaded');
    })
    .catch(() => tcToast('Could not generate image', 'error'));
}

/* ---------------------------- DOC EXPORT -------------------------------- */
function exportTableToDOC() {
  const state = window.TC_TableEngine.state;
  const title = tcGetTableTitle();

  let rowsHtml = '<tr>' + state.columns.map((c) => '<th style="background:#080E53;color:#ffffff;padding:8px;border:1px solid #ccc;">' + c.column_name + '</th>').join('') + '</tr>';
  state.rows.forEach((row) => {
    rowsHtml += '<tr>' + state.columns.map((col) => {
      const cell = row.cells.find((c) => c.column_id === col.id);
      return '<td style="padding:8px;border:1px solid #ccc;">' + (cell ? cell.value : '') + '</td>';
    }).join('') + '</tr>';
  });

  const html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">' +
    '<head><meta charset="utf-8"><title>' + title + '</title></head>' +
    '<body style="font-family:Calibri, Arial, sans-serif;">' +
    '<h2 style="color:#080E53;">' + title + '</h2>' +
    '<table style="border-collapse:collapse;width:100%;">' + rowsHtml + '</table>' +
    '<p style="color:#5A5F8A;font-size:11px;margin-top:16px;">Generated with TableCraft</p>' +
    '</body></html>';

  const blob = new Blob(['\ufeff', html], { type: 'application/msword' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.download = tcSafeFileName(title) + '.doc';
  link.click();
  tcToast('DOC downloaded');
}

document.addEventListener('DOMContentLoaded', function () {
  const pdfBtn = document.getElementById('exportPdfBtn');
  const pngBtn = document.getElementById('exportPngBtn');
  const docBtn = document.getElementById('exportDocBtn');
  pdfBtn && pdfBtn.addEventListener('click', exportTableToPDF);
  pngBtn && pngBtn.addEventListener('click', exportTableToPNG);
  docBtn && docBtn.addEventListener('click', exportTableToDOC);
});
