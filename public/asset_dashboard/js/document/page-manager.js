/**
 * page-manager.js — manajemen halaman, zoom, thumbnails, table overflow
 *
 * IMPROVEMENTS:
 *  - SweetAlert2 menggantikan alert/confirm biasa
 *  - Table overflow: jika tabel melebihi batas halaman, sisa baris otomatis
 *    pindah ke halaman berikutnya
 *  - Resize element: scaleX/scaleY dipertahankan, bukan diubah ulang
 *  - saveState debounce lebih stabil
 */

// ── Page creation ─────────────────────────────────────────────

function createPage() {
    var container = document.getElementById('canvasPagesContainer');
    var pageIndex = pages.length;

    var wrapper = document.createElement('div');
    wrapper.className = 'page-block' + (pageIndex === 0 ? ' active-page' : '');
    wrapper.style.position = 'relative';

    var label = document.createElement('div');
    label.className = 'page-label';
    label.textContent = 'Halaman ' + (pageIndex + 1);
    wrapper.appendChild(label);

    var canvasEl = document.createElement('canvas');
    canvasEl.width  = CANVAS_W;
    canvasEl.height = CANVAS_H;
    wrapper.appendChild(canvasEl);
    container.appendChild(wrapper);

    var fc = new fabric.Canvas(canvasEl, {
        preserveObjectStacking: true,
        renderOnAddRemove:      false,
        skipTargetFind:         false,
        enableRetinaScaling:    false,
        imageSmoothingEnabled:  true,
        selection:              true,
        allowTouchScrolling:    false,
        stopContextMenu:        true,
        fireRightClick:         true,  // enable right-click context menu
    });
    fc.setBackgroundColor('white', fc.renderAll.bind(fc));

    // Default object styling — Figma-like handles
    fabric.Object.prototype.set({
        transparentCorners:  false,
        cornerColor:         '#3b82f6',
        cornerStrokeColor:   '#ffffff',
        borderColor:         '#3b82f6',
        cornerSize:          8,
        cornerStyle:         'circle',
        borderDashArray:     null,
        borderScaleFactor:   1.5,
        padding:             5,
    });

    fc.upperCanvasEl.style.zIndex = '3';

    var fabricWrapperEl = fc.wrapperEl;

    function makeOverlay(id, extraStyle) {
        var c = document.createElement('canvas');
        c.id  = id + '_' + pageIndex;
        c.width  = CANVAS_W;
        c.height = CANVAS_H;
        c.style.cssText =
            'position:absolute;top:0;left:0;z-index:1;pointer-events:none;' +
            (extraStyle || '');
        fabricWrapperEl.appendChild(c);
        return c;
    }

    var guideEl  = makeOverlay('overlay_guide', '');
    var gridEl   = makeOverlay('overlay_grid',  'opacity:0.45;display:none;');
    var marginEl = makeOverlay('overlay_margin', '');

    var pageData = {
        canvas:       fc,
        wrapper:      wrapper,
        guideEl:      guideEl,
        gridEl:       gridEl,
        marginEl:     marginEl,
        tableStore:   {},
        _history:     [],
        _historyRedo: [],
        _isSaving:    false,
        _saveTimer:   null,
        _snapRafId:   null,
        _pageIndex:   pageIndex,
        _pageSnapPoints:  [],
        _objGuidePoints:  [],
        _activeSnapX:     null,
        _activeSnapY:     null,
        _prevSnapX:       null,
        _prevSnapY:       null,
        _guideAlpha:      0,
        _guideAlphaRafId: null,
        // FIX: snap pointer-lock state — wajib ada agar tidak crash sebelum mouse:down pertama
        _snapLockedAtX:   null,
        _snapLockedAtY:   null,
        _lastPointerX:    null,
        _lastPointerY:    null,
    };

    pages.push(pageData);

    attachPageEvents(pageData);
    attachTableHandles(pageData);
    attachStylePanel(pageData);
    drawMarginGuidesForPage(pageData, marginVisible);
    renderPageThumbnails();
    saveStateForPage(pageData);
    return pageData;
}

// ── Page switching ────────────────────────────────────────────

function switchPage(idx) {
    if (idx < 0 || idx >= pages.length) return;

    var oldPage = pages[currentPage];
    if (oldPage) {
        oldPage.canvas.discardActiveObject();
        oldPage.canvas.renderAll();
        oldPage.wrapper.classList.remove('active-page');
    }

    currentPage = idx;
    var newPage = pages[currentPage];
    newPage.wrapper.classList.add('active-page');

    updatePageIndicator();
    renderPageThumbnails();
    newPage.wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function addNewPage() {
    createPage();
    switchPage(pages.length - 1);
    saveState();
}

function removeCurrentPage() {
    if (pages.length <= 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Dapat Dihapus',
            text: 'Minimal harus ada 1 halaman.',
            confirmButtonText: 'OK',
            confirmButtonColor: '#1a5276',
        });
        return;
    }

    Swal.fire({
        title: 'Hapus Halaman ' + (currentPage + 1) + '?',
        text: 'Semua elemen di halaman ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="bi bi-trash"></i> Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        reverseButtons: true,
    }).then(function (result) {
        if (!result.isConfirmed) return;

        var pg = pages[currentPage];
        pg.canvas.dispose();
        pg.wrapper.remove();
        pages.splice(currentPage, 1);

        pages.forEach(function (p, i) {
            var lbl = p.wrapper.querySelector('.page-label');
            if (lbl) lbl.textContent = 'Halaman ' + (i + 1);
            p._pageIndex = i;
        });

        currentPage = Math.min(currentPage, pages.length - 1);
        pages[currentPage].wrapper.classList.add('active-page');
        updatePageIndicator();
        renderPageThumbnails();
        saveState();
    });
}

function updatePageIndicator() {
    var el = document.getElementById('pageIndicator');
    if (el) el.textContent = (currentPage + 1) + '/' + pages.length;
}

// ── Thumbnails ────────────────────────────────────────────────

function renderPageThumbnails() {
    var container = document.getElementById('pageThumbnails');
    if (!container) return;
    container.innerHTML = '';

    pages.forEach(function (pg, idx) {
        var item = document.createElement('div');
        item.className = 'thumb-item' + (idx === currentPage ? ' active' : '');

        var tc = document.createElement('canvas');
        tc.width  = 80;
        tc.height = Math.round(80 * (CANVAS_H / CANVAS_W));
        item.appendChild(tc);

        var lbl = document.createElement('div');
        lbl.className   = 'thumb-label';
        lbl.textContent = 'Hal. ' + (idx + 1);
        item.appendChild(lbl);

        item.addEventListener('click', function () { switchPage(idx); });
        container.appendChild(item);

        var ctx = tc.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, tc.width, tc.height);

        var url = pg.canvas.toDataURL({ format: 'png', quality: 0.25 });
        var img = new Image();
        img.onload = function () {
            ctx.drawImage(img, 0, 0, tc.width, tc.height);
        };
        img.src = url;
    });
}

// ── Zoom ──────────────────────────────────────────────────────

function setZoom(z) {
    z = Math.min(3.0, Math.max(0.3, parseFloat(z.toFixed(2))));
    currentZoom = z;

    pages.forEach(function (pg) {
        var fc = pg.canvas;
        fc.setZoom(z);
        fc.setWidth( Math.round(CANVAS_W * z));
        fc.setHeight(Math.round(CANVAS_H * z));
        fc.renderAll();

        [pg.guideEl, pg.gridEl, pg.marginEl].forEach(function (el) {
            el.style.transform       = 'scale(' + z + ')';
            el.style.transformOrigin = '0 0';
        });

        pg.wrapper.style.width  = Math.round(CANVAS_W * z) + 'px';
        pg.wrapper.style.height = Math.round(CANVAS_H * z) + 'px';

        if (fc.wrapperEl) {
            fc.wrapperEl.style.width  = Math.round(CANVAS_W * z) + 'px';
            fc.wrapperEl.style.height = Math.round(CANVAS_H * z) + 'px';
        }
    });

    var zlabel = document.getElementById('zoomLabel');
    if (zlabel) zlabel.textContent = Math.round(z * 100) + '%';
    drawRulersBase();
    pages.forEach(function (pg) { drawMarginGuidesForPage(pg, marginVisible); });
}

function zoomIn()    { setZoom(currentZoom + 0.1); }
function zoomOut()   { setZoom(currentZoom - 0.1); }
function zoomReset() { setZoom(1); }

// Ctrl+scroll to zoom
document.getElementById('editorContainer').addEventListener('wheel', function (e) {
    if (!e.ctrlKey) return;
    e.preventDefault();
    var delta = e.deltaY < 0 ? 0.1 : -0.1;
    setZoom(currentZoom + delta);
}, { passive: false });

// ── Undo / Redo ───────────────────────────────────────────────

function saveStateForPage(pgData) {
    if (pgData._isSaving) return;
    clearTimeout(pgData._saveTimer);
    pgData._saveTimer = setTimeout(function () {
        try {
            var json = pgData.canvas.toJSON(['name', 'excludeFromExport', '_isTable']);
            // FIX: simpan tableStore bersama snapshot canvas agar undo/redo tidak kehilangan data tabel
            json._tableStore = JSON.parse(JSON.stringify(pgData.tableStore || {}));
            pgData._history.push(JSON.stringify(json));
            if (pgData._history.length > 60) pgData._history.shift();
            pgData._historyRedo = [];
        } catch (err) {
            console.warn('[saveStateForPage] error:', err);
        }
    }, 80);
}

function saveState() {
    if (pages[currentPage]) saveStateForPage(pages[currentPage]);
}

function undo() {
    var pg = pages[currentPage];
    if (!pg || pg._history.length < 2) return;
    pg._isSaving = true;
    pg._historyRedo.push(pg._history.pop());
    var snapshot = JSON.parse(pg._history[pg._history.length - 1]);
    // FIX: restore tableStore dari snapshot
    var restoredStore = snapshot._tableStore || {};
    delete snapshot._tableStore;
    pg.canvas.loadFromJSON(snapshot, function () {
        pg.tableStore = restoredStore;
        pg.canvas.renderAll();
        pg._isSaving = false;
        renderPageThumbnails();
        scheduleCoordUpdate(null);
    });
}

function redo() {
    var pg = pages[currentPage];
    if (!pg || !pg._historyRedo.length) return;
    pg._isSaving = true;
    var raw = pg._historyRedo.pop();
    pg._history.push(raw);
    var snapshot = JSON.parse(raw);
    // FIX: restore tableStore dari snapshot
    var restoredStore = snapshot._tableStore || {};
    delete snapshot._tableStore;
    pg.canvas.loadFromJSON(snapshot, function () {
        pg.tableStore = restoredStore;
        pg.canvas.renderAll();
        pg._isSaving = false;
        renderPageThumbnails();
        scheduleCoordUpdate(null);
    });
}

// ── TABLE OVERFLOW: auto-split ke halaman berikutnya ──────────
/**
 * Cek apakah tabel melewati batas bawah halaman setelah di-drag/resize.
 * Dipanggil dari object:modified.
 *
 * FIX: gunakan tinggi aktual (height * scaleY), bukan hanya height
 *      agar tabel yang sudah di-scale tetap terdeteksi overflow
 *
 * @param {object}       pgData  — halaman sumber
 * @param {fabric.Image} imgObj  — fabric object tabel
 * @returns {boolean}   true jika overflow terjadi dan split dilakukan
 */
function checkTableOverflow(pgData, imgObj) {
    if (!imgObj || !imgObj.name) return false;
    var td = pgData.tableStore[imgObj.name];
    if (!td || td.type === 'ttd') return false;

    var objTop    = imgObj.top    || 0;
    var scaleY    = imgObj.scaleY || 1;
    var scaleX    = imgObj.scaleX || 1;
    // FIX: gunakan tinggi aktual dengan scaleY
    var actualH   = (imgObj.height || td.totalHeight) * scaleY;
    var objBottom = objTop + actualH;
    var pageLimit = CANVAS_H - MARGIN;

    // Tidak overflow
    if (objBottom <= pageLimit) return false;

    var availableH = pageLimit - objTop;

    // Seluruh tabel di bawah batas atau tidak ada ruang sama sekali
    if (availableH <= td.rowHeights[0]) {
        _moveWholeTableToNextPage(pgData, imgObj, td);
        return true;
    }

    // Hitung sampai baris keberapa yang muat
    // Jika tabel di-scale, row heights juga perlu disesuaikan
    var scaledRowHeights = td.rowHeights.map(function(h) { return h * scaleY; });
    var cumH        = 0;
    var splitRowIdx = -1;

    for (var ri = 0; ri < scaledRowHeights.length; ri++) {
        cumH += scaledRowHeights[ri];
        if (cumH > availableH) {
            splitRowIdx = ri;
            break;
        }
    }

    // Minimal header + 1 data row harus muat
    if (splitRowIdx <= 1) {
        _moveWholeTableToNextPage(pgData, imgObj, td);
        return true;
    }

    // Split: bagian atas tetap di halaman ini, sisanya ke halaman berikut
    var topRows    = td.rows.slice(0, splitRowIdx);
    var topHeights = td.rowHeights.slice(0, splitRowIdx);
    var botRows    = [td.rows[0]].concat(td.rows.slice(splitRowIdx)); // header diulang
    var botHeights = [td.rowHeights[0]].concat(td.rowHeights.slice(splitRowIdx));

    // Update tabel halaman ini (bagian atas)
    var topTd = Object.assign({}, td, {
        rows:        topRows,
        rowHeights:  topHeights,
        totalHeight: topHeights.reduce(function (a, b) { return a + b; }, 0),
        totalWidth:  td.totalWidth * scaleX,
        colWidths:   td.colWidths.map(function(w) { return w * scaleX; }),
    });
    // Reset scale karena sudah di-absorb ke colWidths/totalWidth
    pgData.tableStore[imgObj.name] = topTd;
    _rerenderTableImmediate(pgData, imgObj, topTd);
    imgObj.set({ scaleX: 1, scaleY: 1 });
    imgObj.setCoords();
    pgData.canvas.renderAll();

    // Bagian bawah ke halaman berikutnya
    var bottomTd = Object.assign({}, td, {
        rows:        botRows,
        rowHeights:  botHeights,
        totalHeight: botHeights.reduce(function (a, b) { return a + b; }, 0),
    });
    _placeTableOnNextPage(pgData, bottomTd);

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'info',
            title: 'Tabel dibagi otomatis ke halaman berikutnya',
            showConfirmButton: false, timer: 2800, timerProgressBar: true,
        });
    }
    return true;
}

function _rerenderTableImmediate(pgData, imgObj, td) {
    // FIX: synchronous — render langsung ke canvas element baru,
    // replace element di imgObj tanpa async onload
    var offscreen = renderTableToCanvas(td);
    var fc2 = document.createElement('canvas');
    fc2.width  = td.totalWidth;
    fc2.height = td.totalHeight;
    fc2.getContext('2d').drawImage(offscreen, 0, 0, td.totalWidth, td.totalHeight);

    // Ganti internal element Fabric.Image dengan canvas baru (synchronous)
    imgObj.setElement(fc2);
    imgObj.set({ width: td.totalWidth, height: td.totalHeight, scaleX: 1, scaleY: 1 });
    imgObj.setCoords();
    pgData.canvas.renderAll();
}

/**
 * Pindahkan seluruh tabel ke halaman berikutnya (karena tidak ada ruang sama sekali).
 */
function _moveWholeTableToNextPage(pgData, imgObj, td) {
    var canvas = pgData.canvas;
    var oldName = imgObj.name;
    canvas.remove(imgObj);
    delete pgData.tableStore[oldName];
    canvas.discardActiveObject();
    canvas.renderAll();

    _placeTableOnNextPage(pgData, td);
    saveStateForPage(pgData);

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'info',
            title: 'Tabel dipindahkan ke halaman berikutnya (tidak cukup ruang)',
            showConfirmButton: false, timer: 3000, timerProgressBar: true,
        });
    }
}

function _placeTableOnNextPage(srcPgData, td) {
    var srcIdx   = pages.indexOf(srcPgData);
    var nextIdx  = srcIdx + 1;

    if (nextIdx >= pages.length) {
        createPage();
        // createPage menambah ke pages array, nextIdx masih valid
    }

    var nextPg = pages[nextIdx];
    if (!nextPg) return;

    var startX = MARGIN;
    var startY = MARGIN;

    var id = 'tbl_' + (++tableCounter);
    nextPg.tableStore[id] = td;

    var offscreen = renderTableToCanvas(td);
    var finalCanvas = document.createElement('canvas');
    finalCanvas.width  = td.totalWidth;
    finalCanvas.height = td.totalHeight;
    finalCanvas.getContext('2d').drawImage(offscreen, 0, 0, td.totalWidth, td.totalHeight);

    // FIX: synchronous — tidak pakai fromURL agar langsung tampil tanpa klik
    var img = new fabric.Image(finalCanvas, {
        left:        startX,
        top:         startY,
        name:        id,
        selectable:  true,
        evented:     true,
        hasBorders:  true,
        hasControls: true,
        lockRotation: true,
    });
    img._isTable = true;
    nextPg.canvas.add(img);
    nextPg.canvas.renderAll();
    saveStateForPage(nextPg);
    renderPageThumbnails();

    // Rekursif cek: bagian bawah juga mungkin overflow ke halaman ke-3, dst.
    if (typeof checkTableOverflow === 'function') {
        checkTableOverflow(nextPg, img);
    }
}