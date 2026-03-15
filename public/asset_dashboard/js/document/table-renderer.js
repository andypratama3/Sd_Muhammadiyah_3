/**
 * table-renderer.js — render tabel ke canvas & semua fungsi insert tabel
 *
 * PATCH:
 *  - createTablePlaceholder: setelah insert, otomatis split ke halaman berikutnya
 *    jika tabel melebihi batas bawah halaman (fix: 100+ baris overflow)
 *  - Jika totalHeight > available space dari dropY, langsung split saat insert
 *
 * Depends: constants.js, utils.js, page-manager.js (saveState, checkTableOverflow),
 *          variable-registry.js (registerVariable)
 */

// ─────────────────────────────────────────────────────────────
// CREATE TABLE PLACEHOLDER (canvas image)
// ─────────────────────────────────────────────────────────────

/**
 * _getTableModeFromModal — baca pilihan mode dari hidden input di modal tabel.
 * Dipanggil oleh semua fungsi insertXxxTable() sebelum createTablePlaceholder().
 * Default ke 'perorang' jika elemen tidak ditemukan.
 * @returns {'perorang'|'daftar'}
 */
function _getTableModeFromModal() {
    var el = document.getElementById('tableInsertMode');
    return (el && el.value === 'daftar') ? 'daftar' : 'perorang';
}

function createTablePlaceholder(tableData, startX, startY) {
    var canvas = getCanvas();
    if (!canvas) return;
    var pg = pages[currentPage];
    if (!pg) return;

    // FIX: pastikan table_mode selalu tersimpan di tableData.
    // Jika pemanggil sudah set (misal dari insertCustomTable), gunakan itu.
    // Jika belum, ambil dari modal (fallback 'perorang').
    if (!tableData.table_mode) {
        tableData.table_mode = _getTableModeFromModal();
    }
    var id = 'tbl_' + (++tableCounter);
    pg.tableStore[id] = tableData;

    // Register variabel otomatis
    if (tableData.autoRegisterVars) {
        tableData.autoRegisterVars.forEach(function (v) {
            registerVariable(v.name, v.label);
        });
    }

    // Hindari tumpuk persis dengan objek lain
    var occupied = canvas.getObjects().map(function (o) {
        return { x: o.left, y: o.top };
    });
    var dropY = startY;
    while (occupied.some(function (p) {
        return Math.abs(p.x - startX) < 20 && Math.abs(p.y - dropY) < 20;
    })) { dropY += 20; }

    // Cek apakah tabel perlu langsung di-split saat insert
    // (misal: 100 baris, totalHeight > sisa ruang halaman)
    var availH = (CANVAS_H - MARGIN) - dropY;

    if (tableData.totalHeight > availH && availH > 0) {
        // Split langsung saat insert — bagian atas di halaman ini, sisanya ke halaman berikut
        _splitTableOnInsert(pg, tableData, id, startX, dropY, availH);
        return;
    }

    // Tabel muat di halaman ini — render normal
    _renderAndPlace(pg, canvas, tableData, id, startX, dropY, true);
}

/**
 * Render tabel dan tempatkan di canvas.
 * @param {object}  pg         halaman
 * @param {object}  canvas     fabric canvas
 * @param {object}  td         table data
 * @param {string}  id         nama object
 * @param {number}  x          left
 * @param {number}  y          top
 * @param {boolean} setActive  apakah set sebagai active object
 */
function _renderAndPlace(pg, canvas, td, id, x, y, setActive) {
    // Render tabel ke offscreen canvas
    var offscreen   = renderTableToCanvas(td);
    var finalCanvas = document.createElement('canvas');
    finalCanvas.width  = td.totalWidth;
    finalCanvas.height = td.totalHeight;
    finalCanvas.getContext('2d').drawImage(offscreen, 0, 0, td.totalWidth, td.totalHeight);

    // FIX: Buat fabric.Image langsung dari HTMLCanvasElement (synchronous),
    // BUKAN dari toDataURL() → fromURL() yang async.
    // Dengan cara ini tabel langsung tampil begitu insert, tanpa perlu di-click dulu.
    var img = new fabric.Image(finalCanvas, {
        left:        x,
        top:         y,
        name:        id,
        selectable:  true,
        evented:     true,
        hasBorders:  true,
        hasControls: true,
        lockRotation: true,
    });
    img._isTable = true;

    canvas.add(img);
    if (setActive) canvas.setActiveObject(img);

    // renderAll() dipanggil synchronous — tabel langsung tampil tanpa klik
    canvas.renderAll();

    saveStateForPage(pg);
    renderPageThumbnails();
}

/**
 * Split tabel saat insert: bagian yang muat → halaman ini,
 * sisa baris → halaman berikutnya via _placeTableOnNextPage (page-manager.js).
 * Rekursif: bagian bawah juga bisa overflow ke halaman ke-3, dst.
 */
function _splitTableOnInsert(pg, td, id, x, dropY, availH) {
    var canvas = pg.canvas;

    // Hitung sampai baris keberapa yang muat di availH
    var cumH        = 0;
    var splitRowIdx = -1;
    for (var ri = 0; ri < td.rowHeights.length; ri++) {
        cumH += td.rowHeights[ri];
        if (cumH > availH) {
            splitRowIdx = ri;
            break;
        }
    }

    // Tidak cukup ruang untuk header+1 baris → pindahkan semua ke halaman baru
    if (splitRowIdx <= 1) {
        delete pg.tableStore[id];
        _placeTableOnNextPage(pg, td);  // dari page-manager.js, rekursif otomatis
        switchPage(Math.min(pages.indexOf(pg) + 1, pages.length - 1));

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'info',
                title: 'Tabel dipindahkan ke halaman baru (tidak cukup ruang)',
                showConfirmButton: false, timer: 3000, timerProgressBar: true,
            });
        }
        return;
    }

    // Bagian atas (muat di halaman ini)
    var topRows    = td.rows.slice(0, splitRowIdx);
    var topHeights = td.rowHeights.slice(0, splitRowIdx);
    var topTd = Object.assign({}, td, {
        rows:        topRows,
        rowHeights:  topHeights,
        totalHeight: topHeights.reduce(function (a, b) { return a + b; }, 0),
    });
    pg.tableStore[id] = topTd;
    _renderAndPlace(pg, canvas, topTd, id, x, dropY, true);

    // Bagian bawah — header diulang, ditempatkan via page-manager (rekursif otomatis)
    var botRows    = [td.rows[0]].concat(td.rows.slice(splitRowIdx));
    var botHeights = [td.rowHeights[0]].concat(td.rowHeights.slice(splitRowIdx));
    var botTd = Object.assign({}, td, {
        rows:        botRows,
        rowHeights:  botHeights,
        totalHeight: botHeights.reduce(function (a, b) { return a + b; }, 0),
    });

    // _placeTableOnNextPage di page-manager.js sudah handle rekursif
    _placeTableOnNextPage(pg, botTd);

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'info',
            title: 'Tabel dibagi otomatis ke beberapa halaman',
            showConfirmButton: false, timer: 3000, timerProgressBar: true,
        });
    }
}

// ─────────────────────────────────────────────────────────────
// RENDER TABLE → OFFSCREEN CANVAS
// ─────────────────────────────────────────────────────────────

function renderTableToCanvas(td) {
    var DPR        = Math.min(window.devicePixelRatio || 2, 3);
    var rows       = td.rows;
    var colWidths  = td.colWidths;
    var rowHeights = td.rowHeights;
    var totalCols  = colWidths.length;
    var headerColor= td.headerColor || '#1a5276';
    var stripeColor= td.stripeColor || '#eaf2ff';
    var borderColor= td.borderColor || '#adb5bd';

    var oc = document.createElement('canvas');
    oc.width  = td.totalWidth  * DPR;
    oc.height = td.totalHeight * DPR;

    var ctx = oc.getContext('2d');
    ctx.scale(DPR, DPR);

    var totalW = colWidths.reduce(function (a, w) { return a + (w || 60); }, 0);
    var curY   = 0;

    rows.forEach(function (row, rowIdx) {
        var rowH       = rowHeights[rowIdx] || 20;
        var isMergedRow = row[0] && row[0].isMerged;

        if (isMergedRow) {
            ctx.fillStyle = headerColor;
            ctx.fillRect(0, curY, totalW, rowH);
            ctx.strokeStyle = borderColor;
            ctx.lineWidth   = 0.5;
            ctx.strokeRect(0.25, curY + 0.25, totalW - 0.5, rowH - 0.5);

            var mergedText = (row[0].text || '')
                .replace(/\{\{[^}]+\}\}/g, '…')
                .replace(/\n/g, ' ');

            if (mergedText) {
                ctx.fillStyle    = '#ffffff';
                ctx.font         = 'bold 8px Arial';
                ctx.textBaseline = 'middle';
                ctx.textAlign    = 'center';
                var display = mergedText;
                while (display.length > 1 && ctx.measureText(display).width > totalW - 8) {
                    display = display.slice(0, -1);
                }
                if (display !== mergedText) display += '…';
                ctx.fillText(display, totalW / 2, curY + rowH / 2);
            }

        } else {
            var isHeader  = (rowIdx === 0);
            var rowBg     = isHeader ? headerColor : (rowIdx % 2 === 1 ? '#ffffff' : stripeColor);
            var textColor = isHeader ? '#ffffff' : '#212529';
            var fontWeight= isHeader ? 'bold' : 'normal';
            var fontSize  = isHeader ? 9 : 8;
            var curX      = 0;

            for (var ci = 0; ci < totalCols; ci++) {
                var cell  = row[ci] || { text: '', align: 'left' };
                var cellW = colWidths[ci] || 60;

                ctx.fillStyle   = rowBg;
                ctx.fillRect(curX, curY, cellW, rowH);
                ctx.strokeStyle = borderColor;
                ctx.lineWidth   = 0.5;
                ctx.strokeRect(curX + 0.25, curY + 0.25, cellW - 0.5, rowH - 0.5);

                var cellText = (cell.text || '')
                    .replace(/\{\{[^}]+\}\}/g, '…')
                    .replace(/\n/g, ' ');

                if (cellText) {
                    ctx.fillStyle    = textColor;
                    ctx.font         = fontWeight + ' ' + fontSize + 'px Arial';
                    ctx.textBaseline = 'middle';
                    var align = cell.align || (ci === 0 ? 'center' : 'left');
                    ctx.textAlign = align;
                    var maxW  = cellW - 6;
                    // FIX: hitung tx untuk semua alignment (left, center, right)
                    var tx;
                    if      (align === 'center') tx = curX + cellW / 2;
                    else if (align === 'right')  tx = curX + cellW - 3;
                    else                         tx = curX + 3;
                    var disp  = cellText;
                    while (disp.length > 1 && ctx.measureText(disp).width > maxW) {
                        disp = disp.slice(0, -1);
                    }
                    if (disp !== cellText) disp += '…';
                    ctx.fillText(disp, tx, curY + rowH / 2);
                }

                curX += cellW;
            }
        }

        curY += rowH;
    });

    return oc;
}

// ─────────────────────────────────────────────────────────────
// INSERT FUNCTIONS (sama persis dengan asli)
// ─────────────────────────────────────────────────────────────

function insertCustomTable() {
    var rowCount    = parseInt(document.getElementById('tableRows').value)      || 5;
    var colCount    = parseInt(document.getElementById('tableCols').value)      || 4;
    var tableWidth  = parseInt(document.getElementById('tableWidth').value)     || 642;
    var rowHeight   = parseInt(document.getElementById('tableRowHeight').value) || 24;
    var headerColor = document.getElementById('tableHeaderColor').value;
    var stripeColor = document.getElementById('tableStripeColor').value;
    var borderColor = document.getElementById('tableBorderColor').value;
    var hasRowNum   = document.getElementById('tableHasNo').checked;

    var autoVarEl  = document.getElementById('tableAutoVar');
    var autoVar    = autoVarEl ? autoVarEl.checked : false;

    // MODE TABEL:
    // 'list'   → 1 Excel = 1 PDF berisi daftar semua baris
    //            variabel per-baris: {{nama_1}}, {{nama_2}}, {{nama_3}} dst.
    //            Cocok untuk: daftar hadir, daftar peserta, rekap nilai kelas.
    // 'perorang' → 1 baris Excel = 1 PDF
    //            variabel flat: {{nama}}, {{nilai}}, {{keterangan}}
    //            Cocok untuk: surat per orang, rapot per siswa.
    var tableModeEl = document.getElementById('tableMode');
    var tableMode   = tableModeEl ? tableModeEl.value : 'perorang';

    console.log(tableMode);

    var headersRaw = document.getElementById('tableHeaders').value
        .split(',').map(function (s) { return s.trim(); });

    function toSlug(str) {
        return (str || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '')
            || 'kolom';
    }

    // Deduplikasi slug
    var usedSlugs   = {};
    var headerSlugs = [];
    for (var hi = 0; hi < colCount; hi++) {
        var slug = toSlug(headersRaw[hi] || ('kolom' + (hi + 1)));
        if (usedSlugs[slug]) { slug = slug + '_' + (hi + 1); }
        usedSlugs[slug] = true;
        headerSlugs.push(slug);
    }

    var headerRow = [];
    for (var c = 0; c < colCount; c++) {
        headerRow.push({ text: headersRaw[c] || 'Kolom ' + (c + 1), align: 'center' });
    }

    var tableRows      = [headerRow];
    var autoVars       = [];
    var registeredVars = {};

    for (var r = 0; r < rowCount; r++) {
        var row = [];
        for (var cc = 0; cc < colCount; cc++) {
            var cellText = '';

            if (hasRowNum && cc === 0) {
                // Kolom No — selalu statis
                cellText = String(r + 1);

            } else if (autoVar && tableMode === 'list') {
                // MODE LIST: variabel per-baris bernomor
                // {{nama_1}}, {{nama_2}}, ... {{nama_100}}
                var varNameList = headerSlugs[cc] + '_' + (r + 1);
                cellText = '{{' + varNameList + '}}';
                autoVars.push({ name: varNameList, label: (headersRaw[cc] || headerSlugs[cc]) + ' baris ' + (r + 1) });

            } else if (autoVar && tableMode === 'perorang') {
                // MODE PER ORANG: variabel flat, sama di semua baris
                // {{nama}}, {{nilai}}, {{keterangan}}
                var varNameFlat = headerSlugs[cc];
                cellText = '{{' + varNameFlat + '}}';
                if (!registeredVars[varNameFlat]) {
                    registeredVars[varNameFlat] = true;
                    autoVars.push({ name: varNameFlat, label: headersRaw[cc] || varNameFlat });
                }
            }

            row.push({ text: cellText, align: cc === 0 ? 'center' : 'left' });
        }
        tableRows.push(row);
    }

    var colSpecs = [];
    if (hasRowNum) {
        colSpecs.push(28);
        for (var i = 1; i < colCount; i++) colSpecs.push(null);
    } else {
        for (var j = 0; j < colCount; j++) colSpecs.push(null);
    }

    var colWidths = buildColWidths(colSpecs, tableWidth);

    createTablePlaceholder({
        type:             'custom',
        tableMode:        tableMode,   // simpan mode agar DocumentController bisa baca
        table_mode:       _getTableModeFromModal(), // FIX: mode per-tabel dari modal
        totalWidth:       tableWidth,
        totalHeight:      (rowHeight + 4) + rowCount * rowHeight,
        colWidths:        colWidths,
        rowHeights:       buildRowHeights(rowCount, rowHeight, rowHeight + 4),
        rows:             tableRows,
        headerColor:      headerColor,
        stripeColor:      stripeColor,
        borderColor:      borderColor,
        autoRegisterVars: autoVar ? autoVars : [],
    }, MARGIN, 200);
}

function insertKelasMapelTable() {
    var tableType  = document.getElementById('kelasMapelType').value;
    var selectedKelasId = document.getElementById('kelasMapelKelas').value;
    var tableWidth = parseInt(document.getElementById('kelasMapelWidth').value) || 642;
    var rowHeight  = parseInt(document.getElementById('kelasMapelRowH').value)  || 24;
    var headerColor= document.getElementById('kelasMapelHeaderColor').value;
    var extraCols  = document.getElementById('kelasMapelKolom').value
        .split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    var autoVar    = document.getElementById('kelasMapelAutoVar').checked;
    var kelasList  = window.EDITOR_KELAS_LIST || [];
    var mapelList  = window.EDITOR_MAPEL_LIST || [];
    var tableRows  = [], autoVars = [], colSpecs = [], headerRow = [];

    if (tableType === 'daftar_kelas') {
        headerRow = [
            { text: 'No',       align: 'center' },
            { text: 'Nama Kelas', align: 'left' },
            { text: 'Kategori',  align: 'left' },
        ];
        extraCols.forEach(function (k) { headerRow.push({ text: k, align: 'center' }); });
        colSpecs = [28, null, 100];
        extraCols.forEach(function () { colSpecs.push(80); });
        tableRows.push(headerRow);

        var filteredKelas = selectedKelasId
            ? kelasList.filter(function (k) { return String(k.id) === String(selectedKelasId); })
            : kelasList;

        filteredKelas.forEach(function (kls, idx) {
            var varName = 'kelas_' + (kls.slug || String(kls.id));
            if (autoVar) autoVars.push({ name: varName, label: kls.name });
            var row = [
                { text: String(idx + 1), align: 'center' },
                { text: kls.name,        align: 'left' },
                { text: kls.category_kelas || '', align: 'left' },
            ];
            extraCols.forEach(function () {
                row.push({ text: autoVar ? '{{' + varName + '}}' : '', align: 'center' });
            });
            tableRows.push(row);
        });

    } else if (tableType === 'daftar_mapel') {
        headerRow = [{ text: 'No', align: 'center' }];
        extraCols.forEach(function (k) { headerRow.push({ text: k, align: 'center' }); });
        colSpecs = [28, null];
        extraCols.forEach(function () { colSpecs.push(80); });
        tableRows.push(headerRow);

        mapelList.forEach(function (mp, idx) {
            var varName = 'mapel_' + (mp.slug || String(mp.id));
            if (autoVar) autoVars.push({ name: varName, label: mp.name });
            var row = [
                { text: String(idx + 1), align: 'center' },
                { text: mp.name,         align: 'left' },
            ];
            extraCols.forEach(function () {
                row.push({ text: autoVar ? '{{' + varName + '}}' : '', align: 'center' });
            });
            tableRows.push(row);
        });

    } else if (tableType === 'jadwal_kelas') {
        headerRow = [
            { text: 'No',           align: 'center' },
            { text: 'Mata Pelajaran', align: 'left' },
            { text: 'Hari',          align: 'center' },
            { text: 'Jam',           align: 'center' },
        ];
        extraCols.forEach(function (k) { headerRow.push({ text: k, align: 'center' }); });
        colSpecs = [28, null, 80, 80];
        extraCols.forEach(function () { colSpecs.push(80); });
        tableRows.push(headerRow);

        mapelList.forEach(function (mp, idx) {
            var slug = mp.slug || idx;
            var row = [
                { text: String(idx + 1), align: 'center' },
                { text: mp.name,         align: 'left' },
                { text: autoVar ? '{{hari_' + slug + '}}' : '', align: 'center' },
                { text: autoVar ? '{{jam_'  + slug + '}}' : '', align: 'center' },
            ];
            extraCols.forEach(function () { row.push({ text: '', align: 'center' }); });
            tableRows.push(row);
        });
    }

    if (tableRows.length <= 1) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ toast: true, position: 'bottom-end', icon: 'warning', title: 'Tidak ada data kelas/mapel.', showConfirmButton: false, timer: 2500 });
        }
        return;
    }

    var colWidths = buildColWidths(colSpecs, tableWidth);
    var totalH    = (rowHeight + 4) + (tableRows.length - 1) * rowHeight;

    createTablePlaceholder({
        type:             'kelas_mapel',
        table_mode:       _getTableModeFromModal(),
        totalWidth:       tableWidth,
        totalHeight:      totalH,
        colWidths:        colWidths,
        rowHeights:       buildRowHeights(tableRows.length - 1, rowHeight, rowHeight + 4),
        rows:             tableRows,
        headerColor:      headerColor,
        stripeColor:      '#f0f7ff',
        borderColor:      '#adb5bd',
        autoRegisterVars: autoVar ? autoVars : [],
    }, MARGIN, 200);
}

var _raportKelompoks     = [];
var _raportAktifKelompok = {};

function insertRaportTable() {
    var tableWidth  = parseInt(document.getElementById('raportWidth').value)     || 642;
    var headerColor = document.getElementById('raportHeaderColor').value;
    var rowHeight   = parseInt(document.getElementById('raportRowHeight').value) || 20;
    var autoVar     = document.getElementById('raportAutoVar').checked;

    if (!_raportKelompoks.length) {
        var alertEl = document.getElementById('raportNoKelasAlert');
        if (alertEl) alertEl.style.display = 'block';
        return;
    }
    var alertEl2 = document.getElementById('raportNoKelasAlert');
    if (alertEl2) alertEl2.style.display = 'none';

    var aktifKelompoks = _raportKelompoks.filter(function (grp) {
        return _raportAktifKelompok[grp.kelompok.id] !== false;
    });

    var colSpecs  = [28, null, 52, null];
    var colWidths = buildColWidths(colSpecs, tableWidth);
    var headerNames = ['No', 'Muatan Pelajaran', 'Nilai\nAkhir', 'Capaian Kompetensi'];
    var tableRows = [], autoVars = [];

    tableRows.push(headerNames.map(function (h) {
        return { text: h, align: 'center' };
    }));

    var no = 1;
    aktifKelompoks.forEach(function (grp) {
        grp.mapels.forEach(function (mp) {
            if (autoVar) {
                autoVars.push({ name: mp.var_nilai,   label: 'Nilai '   + mp.nama });
                autoVars.push({ name: mp.var_capaian, label: 'Capaian ' + mp.nama });
            }
            tableRows.push([
                { text: String(no++),   align: 'center' },
                { text: mp.nama,        align: 'left'   },
                { text: autoVar ? '{{' + mp.var_nilai   + '}}' : '', align: 'center' },
                { text: autoVar ? '{{' + mp.var_capaian + '}}' : '', align: 'left'   },
            ]);
        });
    });

    var rowHeights = tableRows.map(function (row, ri) {
        return ri === 0 ? rowHeight + 4 : rowHeight;
    });

    createTablePlaceholder({
        type:             'raport',
        table_mode:       'perorang', // Raport selalu per orang — tiap siswa dapat PDF sendiri
        totalWidth:       tableWidth,
        totalHeight:      rowHeights.reduce(function (a, b) { return a + b; }, 0),
        colWidths:        colWidths,
        rowHeights:       rowHeights,
        rows:             tableRows,
        headerColor:      headerColor,
        stripeColor:      '#f5f9ff',
        borderColor:      '#888888',
        autoRegisterVars: autoVar ? autoVars : [],
    }, MARGIN, 200);
}

function insertUnggulanTable() {
    var programName = document.getElementById('unggulanNama').value.trim() || 'PROGRAM UNGGULAN';
    var itemsRaw    = document.getElementById('unggulanItems').value
        .split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
    var extraCols   = document.getElementById('unggulanKolom').value
        .split(',').map(function (s) { return s.trim(); });
    var tableWidth  = parseInt(document.getElementById('unggulanWidth').value)  || 642;
    var headerColor = document.getElementById('unggulanHeaderColor').value;
    var mergeHeader = document.getElementById('unggulanMergeHeader').checked;

    var colCount = 2 + extraCols.length;
    var headerRow = [{ text: 'No', align: 'center' }, { text: 'Program', align: 'left' }];
    extraCols.forEach(function (k) { headerRow.push({ text: k, align: 'center' }); });

    var colSpecs = [25, null];
    extraCols.forEach(function () { colSpecs.push(70); });

    var colWidths = buildColWidths(colSpecs, tableWidth);
    var rowH      = 22;
    var tableRows = [];

    if (mergeHeader) {
        var mergedRow = [];
        for (var mc = 0; mc < colCount; mc++) {
            mergedRow.push({ text: mc === 0 ? programName : '', isMerged: mc === 0, align: 'center' });
        }
        tableRows.push(mergedRow);
    }

    tableRows.push(headerRow);

    itemsRaw.forEach(function (item, i) {
        var row = [
            { text: String(i + 1), align: 'center' },
            { text: item,          align: 'left' },
        ];
        extraCols.forEach(function () { row.push({ text: '-', align: 'center' }); });
        tableRows.push(row);
    });

    var rowHeights = tableRows.map(function (row, i) {
        if (i === 0 && mergeHeader) return 20;
        if (i === 0 || i === 1)     return rowH + 4;
        return rowH;
    });

    createTablePlaceholder({
        type:        'unggulan',
        table_mode:  _getTableModeFromModal(),
        totalWidth:  tableWidth,
        totalHeight: rowHeights.reduce(function (a, b) { return a + b; }, 0),
        colWidths:   colWidths,
        rowHeights:  rowHeights,
        rows:        tableRows,
        headerColor: headerColor,
        stripeColor: '#f5f9ff',
        borderColor: '#adb5bd',
    }, MARGIN, 200);
}

function insertEkskulTable() {
    var itemsRaw    = document.getElementById('ekskulItems').value
        .split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
    var extraCols   = document.getElementById('ekskulKolom').value
        .split(',').map(function (s) { return s.trim(); });
    var tableWidth  = parseInt(document.getElementById('ekskulWidth').value)  || 642;
    var headerColor = document.getElementById('ekskulHeaderColor').value;

    var colSpecs = [25, null];
    extraCols.forEach(function () { colSpecs.push(80); });

    var colWidths = buildColWidths(colSpecs, tableWidth);
    var rowH      = 22;
    var headerRow = [
        { text: 'No', align: 'center' },
        { text: 'Ekstrakurikuler', align: 'left' },
    ];
    extraCols.forEach(function (k) { headerRow.push({ text: k, align: 'center' }); });

    var tableRows = [headerRow];
    itemsRaw.forEach(function (item, i) {
        var row = [
            { text: String(i + 1), align: 'center' },
            { text: item,          align: 'left' },
        ];
        extraCols.forEach(function () { row.push({ text: '-', align: 'center' }); });
        tableRows.push(row);
    });

    createTablePlaceholder({
        type:        'ekskul',
        table_mode:  _getTableModeFromModal(),
        totalWidth:  tableWidth,
        totalHeight: (rowH + 4) + itemsRaw.length * rowH,
        colWidths:   colWidths,
        rowHeights:  buildRowHeights(itemsRaw.length, rowH, rowH + 4),
        rows:        tableRows,
        headerColor: headerColor,
        stripeColor: '#f0f7ff',
        borderColor: '#adb5bd',
    }, MARGIN, 200);
}

function insertAbsensiTable() {
    var rowCount    = parseInt(document.getElementById('absensiRows').value)  || 30;
    var tableWidth  = parseInt(document.getElementById('absensiWidth').value) || 642;
    var headerColor = document.getElementById('absensiHeaderColor').value;
    var extraCols   = document.getElementById('absensiKolom').value
        .split(',').map(function (s) { return s.trim(); });

    var headerRow = [
        { text: 'No',         align: 'center' },
        { text: 'Nama Siswa', align: 'left' },
        { text: 'NIS',        align: 'center' },
    ];
    extraCols.forEach(function (k) { headerRow.push({ text: k, align: 'center' }); });

    var colSpecs = [25, null, 60];
    extraCols.forEach(function () { colSpecs.push(40); });

    var colWidths = buildColWidths(colSpecs, tableWidth);
    var rowH      = 20;
    var tableRows = [headerRow];

    for (var r = 0; r < rowCount; r++) {
        var row = [
            { text: String(r + 1), align: 'center' },
            { text: '',            align: 'left' },
            { text: '',            align: 'center' },
        ];
        extraCols.forEach(function () { row.push({ text: '', align: 'center' }); });
        tableRows.push(row);
    }

    createTablePlaceholder({
        type:        'absensi',
        table_mode:  _getTableModeFromModal(),
        totalWidth:  tableWidth,
        totalHeight: (rowH + 4) + rowCount * rowH,
        colWidths:   colWidths,
        rowHeights:  buildRowHeights(rowCount, rowH, rowH + 4),
        rows:        tableRows,
        headerColor: headerColor,
        stripeColor: '#f0f7ff',
        borderColor: '#adb5bd',
    }, MARGIN, 200);
}

function insertTTDArea() {
    var canvas = getCanvas();
    if (!canvas) return;
    var pg = pages[currentPage];
    if (!pg) return;

    var rawLines   = document.getElementById('ttdKolom').value.trim().split('\n').filter(Boolean);
    var tableWidth = parseInt(document.getElementById('ttdWidth').value)  || 642;
    var ttdHeight  = parseInt(document.getElementById('ttdHeight').value) || 80;
    var posY       = parseInt(document.getElementById('ttdPosY').value)   || 950;
    var colCount   = rawLines.length;
    var colWidth   = tableWidth / (colCount || 1);

    var ttdData = rawLines.map(function (line) {
        var parts = line.split(',');
        return {
            label:   (parts[0] || '').trim(),
            nama:    (parts[1] || '').trim(),
            jabatan: (parts[2] || '').trim(),
        };
    });

    var id = 'ttd_' + (++tableCounter);
    pg.tableStore[id] = {
        type:       'ttd',
        ttdData:    ttdData,
        totalWidth: tableWidth,
        colW:       colWidth,
        ttdH:       ttdHeight,
    };

    // Register variabel dari nama & jabatan
    ttdData.forEach(function (col) {
        [col.nama, col.jabatan].forEach(function (s) {
            var matches = (s || '').match(/\{\{([^}]+)\}\}/g);
            if (matches) {
                matches.forEach(function (mv) {
                    var varName = mv.replace(/[{}]/g, '').trim();
                    registerVariable(varName, friendlyVarLabel(varName));
                });
            }
        });
    });

    var objs = [];
    var cx   = 0;

    ttdData.forEach(function (col) {
        objs.push(new fabric.Rect({
            left: cx, top: 0, width: colWidth, height: 20 + ttdHeight + 40,
            fill: '#fafafa', stroke: '#dee2e6', strokeWidth: 0.5,
        }));
        objs.push(new fabric.Textbox(col.label, {
            left: cx + 4, top: 4, width: colWidth - 8, height: 16,
            fontSize: 9, fontFamily: 'Arial', textAlign: 'center',
            fill: '#333', selectable: false, evented: false,
        }));
        objs.push(new fabric.Textbox(col.nama || '( __________________ )', {
            left: cx + 4, top: 20 + ttdHeight + 4, width: colWidth - 8, height: 16,
            fontSize: 8, fontFamily: 'Arial', textAlign: 'center',
            fontWeight: 'bold', fill: '#1a5276', selectable: false, evented: false,
        }));
        if (col.jabatan) {
            objs.push(new fabric.Textbox(col.jabatan, {
                left: cx + 4, top: 20 + ttdHeight + 18, width: colWidth - 8, height: 16,
                fontSize: 7, fontFamily: 'Arial', textAlign: 'center',
                fill: '#555', selectable: false, evented: false,
            }));
        }
        cx += colWidth;
    });

    var group = new fabric.Group(objs, { left: MARGIN, top: posY, name: id });
    canvas.add(group);
    canvas.setActiveObject(group);
    canvas.requestRenderAll();
    saveState();
}