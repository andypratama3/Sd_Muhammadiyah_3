/**
 * html-export.js — generate HTML dari canvas untuk dikirim ke backend
 *
 * Depends: constants.js, utils.js
 */

// ─────────────────────────────────────────────────────────────
// CSS style string untuk elemen teks
// ─────────────────────────────────────────────────────────────

function buildTextStyle(obj, widthPx) {
    var scaledFontSize = (obj.fontSize || 16) * (obj.scaleY || 1);
    var parts = [
        'font-size:'    + pt(scaledFontSize) + 'pt',
        'font-family:'  + (obj.fontFamily || 'DejaVu Sans') + ',sans-serif',
        'color:'        + (obj.fill       || '#000000'),
        'font-weight:'  + (obj.fontWeight || 'normal'),
        'font-style:'   + (obj.fontStyle  || 'normal'),
        'text-align:'   + (obj.textAlign  || 'left'),
        'line-height:'  + (obj.lineHeight || 1.4),
        'width:'        + pt(widthPx) + 'pt',
        'white-space:normal',
        'word-wrap:break-word',
        'overflow:hidden',
    ];
    // FIX: gabungkan underline dan linethrough dalam satu property text-decoration
    // agar keduanya bisa aktif bersamaan (sebelumnya saling overwrite)
    var decorations = [];
    if (obj.underline)   decorations.push('underline');
    if (obj.linethrough) decorations.push('line-through');
    if (decorations.length) parts.push('text-decoration:' + decorations.join(' '));
    return parts.join(';');
}

// ─────────────────────────────────────────────────────────────
// BUILD TABLE HTML
// ─────────────────────────────────────────────────────────────

function buildTableHtml(tableId, objLeft, objTop, tableStore) {
    var td = tableStore[tableId];
    if (!td) return '';
    if (td.type === 'ttd') return buildTTDHtml(td, objLeft, objTop);

    var leftPt   = pt(objLeft);
    var topPt    = pt(objTop);
    var widthPt  = pt(td.totalWidth);
    var rows     = td.rows;
    var colWidths= td.colWidths;
    var rowHeights=td.rowHeights;
    var totalCols= colWidths.length;
    var hdrColor = td.headerColor || '#1a5276';
    var strColor = td.stripeColor || '#eaf2ff';
    var bdColor  = td.borderColor || '#adb5bd';

    // FIX: tabel mode daftar → variabel di data rows di-numbering per baris
    // {{nama_lengkap}} di baris 1 → {{nama_lengkap_1}}, baris 2 → {{nama_lengkap_2}}, dst.
    var isDaftar = (td.table_mode === 'daftar');

    var html =
        '<table style="position:absolute;left:' + leftPt + 'pt;top:' + topPt + 'pt;' +
        'width:' + widthPt + 'pt;border-collapse:collapse;table-layout:fixed;' +
        'font-family:DejaVu Sans,Arial,sans-serif;font-size:8pt;">';

    html += '<colgroup>';
    colWidths.forEach(function (w) {
        html += '<col style="width:' + pt(w) + 'pt">';
    });
    html += '</colgroup>';

    // Counter untuk penomoran variabel di tabel daftar (mulai dari 1, hanya baris data)
    var daftarRowIdx = 0;

    rows.forEach(function (row, rowIdx) {
        var rowHPt      = pt(rowHeights[rowIdx] || 20);
        var isMergedRow = row[0] && row[0].isMerged;
        var isHeader    = (rowIdx === 0);
        var evenStripe  = rowIdx % 2 === 1 ? '#ffffff' : strColor;

        // Hitung indeks baris data (bukan header) untuk penomoran daftar
        if (!isHeader && !isMergedRow) {
            daftarRowIdx++;
        }

        html += '<tr>';

        if (isMergedRow) {
            html +=
                '<td colspan="' + totalCols + '" style="' +
                'background:' + hdrColor + ';color:#ffffff;font-weight:bold;text-align:center;' +
                'padding:2pt 3pt;border:0.5pt solid ' + bdColor + ';' +
                'min-height:' + rowHPt + 'pt;height:auto;">' +
                escapeCellContent(row[0].text || '') +
                '</td>';
        } else {
            for (var ci = 0; ci < totalCols; ci++) {
                var cell     = row[ci] || { text: '', align: 'left' };
                var colWPt   = pt(colWidths[ci] || 60);
                var cellBg   = isHeader ? hdrColor : evenStripe;
                var color    = isHeader ? '#ffffff' : '#212529';
                var fw       = isHeader ? 'bold' : 'normal';
                var align    = cell.align || 'left';

                // FIX: jika mode daftar dan ini baris data (bukan header),
                // ganti {{varname}} → {{varname_N}} sesuai urutan baris
                var cellText = cell.text || '';
                if (isDaftar && !isHeader && daftarRowIdx > 0) {
                    cellText = cellText.replace(/\{\{([^}]+)\}\}/g, function(match, varName) {
                        varName = varName.trim();
                        // Jangan tambah nomor kalau sudah bernomor ({{nama_1}})
                        if (/_.+\d+$/.test(varName)) return match;
                        return '{{' + varName + '_' + daftarRowIdx + '}}';
                    });
                }

                html +=
                    '<td style="width:' + colWPt + 'pt;background:' + cellBg + ';' +
                    'border:0.5pt solid ' + bdColor + ';font-weight:' + fw + ';' +
                    'color:' + color + ';padding:2pt 3pt;vertical-align:top;' +
                    'text-align:' + align + ';word-wrap:break-word;' +
                    'min-height:' + rowHPt + 'pt;height:auto;">' +
                    escapeCellContent(cellText) +
                    '</td>';
            }
        }

        html += '</tr>';
    });

    html += '</table>';
    return html;
}

function buildTTDHtml(td, objLeft, objTop) {
    var leftPt  = pt(objLeft);
    var topPt   = pt(objTop);
    var widthPt = pt(td.totalWidth);
    var colWPt  = pt(td.colW);
    var ttdHPt  = pt(td.ttdH);

    var html =
        '<table style="position:absolute;left:' + leftPt + 'pt;top:' + topPt + 'pt;' +
        'width:' + widthPt + 'pt;border-collapse:collapse;' +
        'font-family:DejaVu Sans,Arial,sans-serif;font-size:9pt;">';

    html += '<tr>';
    td.ttdData.forEach(function (col) {
        html += '<td style="width:' + colWPt + 'pt;text-align:center;vertical-align:top;padding:2pt;border:none;">' +
                escapeContent(col.label) + '</td>';
    });
    html += '</tr>';

    html += '<tr>';
    td.ttdData.forEach(function () {
        html += '<td style="width:' + colWPt + 'pt;height:' + ttdHPt + 'pt;border:none;"></td>';
    });
    html += '</tr>';

    html += '<tr>';
    td.ttdData.forEach(function (col) {
        html += '<td style="width:' + colWPt + 'pt;text-align:center;font-weight:bold;' +
                'text-decoration:underline;border:none;padding:1pt 2pt;">' +
                escapeContent(col.nama) + '</td>';
    });
    html += '</tr>';

    html += '<tr>';
    td.ttdData.forEach(function (col) {
        html += '<td style="width:' + colWPt + 'pt;text-align:center;border:none;' +
                'padding:0pt 2pt;font-size:8pt;">' +
                escapeContent(col.jabatan) + '</td>';
    });
    html += '</tr>';

    html += '</table>';
    return html;
}

// ─────────────────────────────────────────────────────────────
// GENERATE HTML PER HALAMAN
// ─────────────────────────────────────────────────────────────

function generateHTMLForPage(pgData) {
    var canvas     = pgData.canvas;
    var tableStore = pgData.tableStore;

    var html = '<div class="page" style="position:relative;width:' + A4_W + 'pt;height:' + A4_H + 'pt;">';

    canvas.getObjects().forEach(function (obj) {
        if (obj.excludeFromExport) return;
        if (obj.name && (obj.name.startsWith('__') || obj.name === 'kop_logo_label')) return;

        var pos    = realTopLeft(obj);
        var leftPt = pt(pos.x);
        var topPt  = pt(pos.y);
        var widthPt= pt(pos.w);
        var heightPt=pt(pos.h);
        var posStyle = 'position:absolute;left:' + leftPt + 'pt;top:' + topPt + 'pt;';

        // Tabel
        if (obj.name && tableStore[obj.name]) {
            html += buildTableHtml(obj.name, pos.x, pos.y, tableStore);
            return;
        }

        // Teks
        if (obj.type === 'textbox' || obj.type === 'i-text') {
            html += '<div style="' + posStyle + buildTextStyle(obj, pos.w) + '">' +
                    escapeContent(obj.text) + '</div>';
            return;
        }

        // Gambar
        if (obj.type === 'image') {
            var dimStyle = 'width:' + widthPt + 'pt;height:' + heightPt + 'pt;';
            if (obj.name === 'logo' || obj.name === 'kop_logo') {
                html += '<div style="' + posStyle + dimStyle + '">{{logo}}</div>';
            } else {
                var src = obj.toDataURL ? obj.toDataURL({ format: 'png' }) : '';
                if (src) {
                    html += '<img src="' + src + '" style="' + posStyle + dimStyle + 'display:block;" />';
                }
            }
            return;
        }

        // Barcode group
        if (obj.type === 'group' && obj.name === 'barcode') {
            // FIX: gunakan realTopLeft (sudah handle originX/Y center) —
            // jangan hitung manual bLeft/bTop karena akan double-count offset
            var bPos   = realTopLeft(obj);
            var bScaleX = obj.scaleX || 1;
            var bScaleY = obj.scaleY || 1;
            var bBoxW   = 120 * bScaleX;
            var bBoxH   = 120 * bScaleY;

            html +=
                '<div style="position:absolute;left:' + pt(bPos.x) + 'pt;top:' + pt(bPos.y) + 'pt;' +
                'width:' + pt(bPos.w) + 'pt;font-family:DejaVu Sans,Arial,sans-serif;text-align:center;">' +
                '<div style="font-size:7.5pt;font-weight:bold;color:#1a5276;margin-bottom:2pt;">' +
                'Kepala Sekolah</div>' +
                '<div style="width:' + pt(bBoxW) + 'pt;height:' + pt(bBoxH) + 'pt;' +
                'margin:0 auto;display:flex;align-items:center;justify-content:center;">' +
                '{{barcode_signature}}</div>' +
                '<div style="font-size:7.5pt;font-weight:bold;color:#1a5276;margin-top:3pt;">' +
                '{{name_kepala_sekolah}}</div>' +
                '</div>';
            return;
        }

        // Garis
        if (obj.type === 'line') {
            var br    = obj.getBoundingRect(true);
            var swPt  = pt(obj.strokeWidth || 1);
            if (swPt < 0.75) swPt = 0.75;
            html +=
                '<div style="position:absolute;left:' + pt(br.left) + 'pt;top:' + pt(br.top) + 'pt;' +
                'width:' + pt(Math.max(br.width, 1)) + 'pt;height:' + swPt + 'pt;' +
                'background:' + (obj.stroke || '#000000') + '"></div>';
            return;
        }

        // Rect
        if (obj.type === 'rect') {
            if (obj.name === 'kop_logo') {
                html += '<div style="' + posStyle + 'width:' + widthPt + 'pt;height:' + heightPt + 'pt;">{{logo}}</div>';
                return;
            }
            var rectStyle = posStyle +
                'width:' + widthPt + 'pt;height:' + heightPt + 'pt;' +
                'background:' + (obj.fill || 'transparent') + ';';
            if (obj.stroke) {
                rectStyle += 'border:' + pt(obj.strokeWidth || 1) + 'pt solid ' + obj.stroke + ';';
            }
            html += '<div style="' + rectStyle + '"></div>';
            return;
        }

        // Group generik
        if (obj.type === 'group') {
            if (tableStore[obj.name]) {
                html += buildTableHtml(obj.name, pos.x, pos.y, tableStore);
                return;
            }
            var gScaleX = obj.scaleX || 1;
            var gScaleY = obj.scaleY || 1;
            var gHalfW  = (obj.width  || 0) / 2 * gScaleX;
            var gHalfH  = (obj.height || 0) / 2 * gScaleY;

            obj.getObjects().forEach(function (child) {
                if (child.type === 'textbox' || child.type === 'i-text') {
                    var cx = (obj.left || 0) + gHalfW + (child.left || 0) * gScaleX;
                    var cy = (obj.top  || 0) + gHalfH + (child.top  || 0) * gScaleY;
                    var cw = (child.width || 100) * (child.scaleX || 1) * gScaleX;
                    html +=
                        '<div style="position:absolute;left:' + pt(cx) + 'pt;top:' + pt(cy) + 'pt;' +
                        buildTextStyle(child, cw) + '">' +
                        escapeContent(child.text) + '</div>';
                }
            });
        }
    });

    html += '</div>';
    return html;
}

// ─────────────────────────────────────────────────────────────
// GENERATE (dipanggil saat form submit)
// ─────────────────────────────────────────────────────────────

function generateHTML() {
    var fullHtml = pages.map(function (pg) {
        return generateHTMLForPage(pg);
    }).join('\n');

    document.getElementById('html_template').value = fullHtml;

    var allPagesData = pages.map(function (pg) {
        var json = pg.canvas.toJSON(['name', 'excludeFromExport']);
        json._tableStore = pg.tableStore;
        return json;
    });

    document.getElementById('canvas_json').value = JSON.stringify({
        pages:   allPagesData,
        version: 2,
    });

    return true;
}