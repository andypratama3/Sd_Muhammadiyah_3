/**
 * table-handles.js — row & column resize drag handles di atas canvas
 *
 * PATCH: _showFloatPanel → alias ke _showStylePanel (table-style-panel.js)
 *        _updateFloatPanelPos → alias ke _updateStylePanelPos (table-style-panel.js)
 *
 * Depends: constants.js, utils.js, page-manager.js (saveStateForPage, renderPageThumbnails)
 */

// ── Row handle state ──────────────────────────────────────────
var _rowHandle = {
    pgData:       null,
    fabricObj:    null,
    tableId:      null,
    td:           null,
    handles:      [],   // [{rowIndex, lineObj}]
    dragging:     false,
    dragRowIndex: null,
    dragStartY:   null,
    dragStartH:   null,
    // bound event refs
    _onDown: null,
    _onMove: null,
    _onUp:   null,
};

// ── Col handle state ──────────────────────────────────────────
var _colHandle = {
    pgData:       null,
    fabricObj:    null,
    td:           null,
    handles:      [],   // [{colIndex, lineObj}]
    dragging:     false,
    dragColIndex: null,
    dragStartX:   null,
    dragStartW:   null,
};

// ─────────────────────────────────────────────────────────────
// ALIAS FIX — sambungkan nama lama ke fungsi di table-style-panel.js
// Dipanggil setelah kedua file di-load (DOMContentLoaded-safe)
// ─────────────────────────────────────────────────────────────

/**
 * _showFloatPanel — alias ke _showStylePanel
 * Dipanggil oleh _tryShowHandles saat tabel dipilih.
 */
function _showFloatPanel(pgData, obj, td) {
    if (typeof _showStylePanel === 'function') {
        _showStylePanel(pgData, obj, td);
    }
}

/**
 * _updateFloatPanelPos — alias ke _updateStylePanelPos
 * Dipanggil saat tabel di-drag agar panel ikut bergerak.
 */
function _updateFloatPanelPos(pgData, obj) {
    if (typeof _updateStylePanelPos === 'function') {
        _updateStylePanelPos(pgData, obj);
    }
}

// ─────────────────────────────────────────────────────────────
// ATTACH / DETACH to a page
// ─────────────────────────────────────────────────────────────

function attachTableHandles(pgData) {
    var fc = pgData.canvas;

    fc.on('selection:created', function (e) {
        var obj = e.selected && e.selected[0];
        _tryShowHandles(pgData, obj);
    });

    fc.on('selection:updated', function (e) {
        _clearAllHandles();
        var obj = e.selected && e.selected[0];
        _tryShowHandles(pgData, obj);
    });

    fc.on('selection:cleared', function () {
        _clearAllHandles();
    });

    // Ikuti posisi tabel saat di-drag
    fc.on('object:moving', function (e) {
        if (_rowHandle.fabricObj === e.target) {
            _repositionRowHandles();
            _repositionColHandles();
            _updateFloatPanelPos(pgData, e.target);
        }
    });
}

function _tryShowHandles(pgData, obj) {
    if (!obj || !obj.name) return;
    var td = pgData.tableStore[obj.name];
    if (!td || td.type === 'ttd') return;

    obj.set({ lockMovementX: false, lockMovementY: false });

    _rowHandle.pgData    = pgData;
    _rowHandle.fabricObj = obj;
    _rowHandle.tableId   = obj.name;
    _rowHandle.td        = td;

    _colHandle.pgData    = pgData;
    _colHandle.fabricObj = obj;
    _colHandle.td        = td;

    _drawRowHandles();
    _drawColHandles();
    _showFloatPanel(pgData, obj, td);
}

// ─────────────────────────────────────────────────────────────
// ROW HANDLES
// ─────────────────────────────────────────────────────────────

function _drawRowHandles() {
    _clearRowHandles();
    var s = _rowHandle;
    if (!s.td || !s.fabricObj) return;

    var fc      = s.pgData.canvas;
    var obj     = s.fabricObj;
    var td      = s.td;
    var scaleY  = obj.scaleY || 1;
    var scaleX  = obj.scaleX || 1;
    var objTop  = obj.top    || 0;
    var objLeft = obj.left   || 0;
    var objW    = (obj.width || td.totalWidth) * scaleX;

    obj.set({ perPixelTargetFind: false });

    var cumY = objTop;
    s.handles = [];

    for (var ri = 0; ri < td.rowHeights.length - 1; ri++) {
        cumY += td.rowHeights[ri] * scaleY;

        var hitRect = new fabric.Rect({
            left:        objLeft,
            top:         cumY - 6,
            width:       objW,
            height:      12,
            fill:        'rgba(56,189,248,0.15)',
            stroke:      '#38bdf8',
            strokeWidth: 2,
            selectable:  false,
            evented:     true,
            hoverCursor: 'row-resize',
            name:        '__handle_row_' + ri,
            excludeFromExport: true,
            originX:     'left',
            originY:     'top',
            hasBorders:  false,
            hasControls: false,
        });
        hitRect._handleRowIndex = ri;

        fc.add(hitRect);
        s.handles.push({ rowIndex: ri, lineObj: hitRect });
    }

    s.handles.forEach(function (h) { fc.bringToFront(h.lineObj); });
    fc.requestRenderAll();
    _attachDragListeners(fc);
}

function _clearRowHandles() {
    var s = _rowHandle;
    if (!s.pgData) return;
    var fc = s.pgData.canvas;

    if (s.fabricObj) s.fabricObj.set({ perPixelTargetFind: false });
    s.handles.forEach(function (h) { fc.remove(h.lineObj); });
    s.handles      = [];
    s.dragging     = false;
    s.dragRowIndex = null;
    _detachDragListeners(fc);
}

function _repositionRowHandles() {
    var s = _rowHandle;
    if (!s.fabricObj || !s.handles.length) return;

    var obj     = s.fabricObj;
    var td      = s.td;
    var scaleY  = obj.scaleY || 1;
    var scaleX  = obj.scaleX || 1;
    var objTop  = obj.top    || 0;
    var objLeft = obj.left   || 0;
    var objW    = (obj.width || td.totalWidth) * scaleX;

    var cumY = objTop;
    s.handles.forEach(function (h, i) {
        cumY += td.rowHeights[i] * scaleY;
        h.lineObj.set({ x1: objLeft, x2: objLeft + objW, y1: cumY, y2: cumY });
        h.lineObj.setCoords();
    });
    s.pgData.canvas.requestRenderAll();
}

// ─────────────────────────────────────────────────────────────
// COL HANDLES
// ─────────────────────────────────────────────────────────────

function _drawColHandles() {
    var s = _colHandle;

    // Hapus handles lama
    if (s.pgData) {
        s.handles.forEach(function (h) { s.pgData.canvas.remove(h.lineObj); });
    }
    s.handles      = [];
    s.dragging     = false;
    s.dragColIndex = null;

    if (!s.td || !s.fabricObj) return;

    var fc      = s.pgData.canvas;
    var obj     = s.fabricObj;
    var td      = s.td;
    var scaleX  = obj.scaleX || 1;
    var scaleY  = obj.scaleY || 1;
    var objTop  = obj.top    || 0;
    var objLeft = obj.left   || 0;
    var objH    = td.totalHeight * scaleY;
    var cumX    = objLeft;

    for (var ci = 0; ci < td.colWidths.length - 1; ci++) {
        cumX += td.colWidths[ci] * scaleX;

        var hitRect = new fabric.Rect({
            left:        cumX - 6,
            top:         objTop,
            width:       12,
            height:      objH,
            fill:        'rgba(249,115,22,0.15)',
            stroke:      '#f97316',
            strokeWidth: 2,
            selectable:  false,
            evented:     false,     // deteksi manual via absolutePointer
            hoverCursor: 'col-resize',
            name:        '__handle_col_' + ci,
            excludeFromExport: true,
            originX:     'left',
            originY:     'top',
            hasBorders:  false,
            hasControls: false,
        });
        hitRect._handleColIndex = ci;

        fc.add(hitRect);
        fc.bringToFront(hitRect);
        s.handles.push({ colIndex: ci, lineObj: hitRect });
    }

    fc.requestRenderAll();
}

function _clearColHandles() {
    var s = _colHandle;
    if (!s.pgData) return;
    var fc = s.pgData.canvas;
    s.handles.forEach(function (h) { fc.remove(h.lineObj); });
    s.handles      = [];
    s.dragging     = false;
    s.dragColIndex = null;
    fc.requestRenderAll();
}

function _repositionColHandles() {
    var s = _colHandle;
    if (!s.fabricObj || !s.handles.length) return;

    var obj    = s.fabricObj;
    var td     = s.td;
    var scaleX = obj.scaleX || 1;
    var scaleY = obj.scaleY || 1;
    var objTop = obj.top    || 0;
    var objLeft= obj.left   || 0;
    var objH   = td.totalHeight * scaleY;
    var cumX   = objLeft;

    s.handles.forEach(function (h, i) {
        cumX += td.colWidths[i] * scaleX;
        h.lineObj.set({ left: cumX - 6, top: objTop, width: 12, height: objH });
        h.lineObj.setCoords();
    });
    s.pgData.canvas.requestRenderAll();
}

/** Hapus semua row + col handles sekaligus. */
function _clearAllHandles() {
    _clearRowHandles();
    _clearColHandles();
}

// ─────────────────────────────────────────────────────────────
// DRAG EVENT LISTENERS
// ─────────────────────────────────────────────────────────────

function _attachDragListeners(fc) {
    _detachDragListeners(fc);

    _rowHandle._onDown = function (opt) {
        var pointer = opt.absolutePointer;
        if (!pointer) return;

        // Cek col handles (manual hit-test karena evented:false)
        var sc = _colHandle;
        for (var ci = 0; ci < sc.handles.length; ci++) {
            var ch  = sc.handles[ci].lineObj;
            var chL = ch.left;
            var chR = chL + (ch.width  || 12);
            var chT = ch.top;
            var chB = chT + (ch.height || 0);

            if (pointer.x >= chL && pointer.x <= chR &&
                pointer.y >= chT && pointer.y <= chB) {

                sc.dragging     = true;
                sc.dragColIndex = sc.handles[ci].colIndex;
                sc.dragStartX   = pointer.x;
                sc.dragStartW   = sc.td.colWidths[sc.dragColIndex];
                fc.selection    = false;
                if (sc.fabricObj) sc.fabricObj.set({ lockMovementX: true, lockMovementY: true });

                ch.set({ fill: 'rgba(239,68,68,0.25)', stroke: '#ef4444', strokeWidth: 3 });
                fc.requestRenderAll();
                return;
            }
        }

        // Cek row handles
        var sr = _rowHandle;
        for (var ri = 0; ri < sr.handles.length; ri++) {
            var rh  = sr.handles[ri].lineObj;
            var rhL = rh.left;
            var rhR = rhL + (rh.width  || 0);
            var rhT = rh.top;
            var rhB = rhT + (rh.height || 12);

            if (pointer.x >= rhL && pointer.x <= rhR &&
                pointer.y >= rhT && pointer.y <= rhB) {

                sr.dragging     = true;
                sr.dragRowIndex = sr.handles[ri].rowIndex;
                sr.dragStartY   = pointer.y;
                sr.dragStartH   = sr.td.rowHeights[sr.dragRowIndex];
                fc.selection    = false;
                if (sr.fabricObj) sr.fabricObj.set({ lockMovementX: true, lockMovementY: true });

                rh.set({ fill: 'rgba(244,63,94,0.25)', stroke: '#f43f5e', strokeWidth: 3 });
                fc.requestRenderAll();
                return;
            }
        }
    };

    _rowHandle._onMove = function (opt) {
        var pointer = opt.absolutePointer;
        if (!pointer) return;

        // Col drag
        if (_colHandle.dragging) {
            var sc   = _colHandle;
            var dx   = pointer.x - sc.dragStartX;
            var newW = Math.max(20, Math.round(sc.dragStartW + dx));
            sc.td.colWidths[sc.dragColIndex] = newW;
            sc.td.totalWidth = sc.td.colWidths.reduce(function (a, b) { return a + b; }, 0);
            _liveRerenderTable({ td: sc.td, fabricObj: sc.fabricObj, pgData: sc.pgData });
            _repositionColHandles();
            _repositionRowHandles();
            _showColResizeTooltip(newW, sc.pgData, sc.dragColIndex);
            return;
        }

        // Row drag
        if (_rowHandle.dragging) {
            var sr   = _rowHandle;
            var dy   = pointer.y - sr.dragStartY;
            var newH = Math.max(14, Math.round(sr.dragStartH + dy));
            sr.td.rowHeights[sr.dragRowIndex] = newH;
            sr.td.totalHeight = sr.td.rowHeights.reduce(function (a, b) { return a + b; }, 0);
            _liveRerenderTable(sr);
            _repositionRowHandles();
            _repositionColHandles();
            _showRowResizeTooltip(sr, newH);
        }
    };

    _rowHandle._onUp = function () {
        fc.selection = true;

        if (_colHandle.dragging) {
            var sc = _colHandle;
            sc.dragging = false;
            if (sc.fabricObj) sc.fabricObj.set({ lockMovementX: false, lockMovementY: false });
            sc.handles.forEach(function (h) {
                h.lineObj.set({ fill: 'rgba(249,115,22,0.15)', stroke: '#f97316', strokeWidth: 2 });
            });
            _hideColResizeTooltip();
            fc.requestRenderAll();
            saveStateForPage(sc.pgData);
            renderPageThumbnails();
        }

        if (_rowHandle.dragging) {
            var sr = _rowHandle;
            sr.dragging = false;
            if (sr.fabricObj) sr.fabricObj.set({ lockMovementX: false, lockMovementY: false });
            sr.handles.forEach(function (h) {
                h.lineObj.set({ fill: 'rgba(56,189,248,0.15)', stroke: '#38bdf8', strokeWidth: 2 });
            });
            _hideRowResizeTooltip();
            fc.requestRenderAll();
            saveStateForPage(sr.pgData);
            renderPageThumbnails();
        }
    };

    fc.on('mouse:down', _rowHandle._onDown);
    fc.on('mouse:move', _rowHandle._onMove);
    fc.on('mouse:up',   _rowHandle._onUp);
}

function _detachDragListeners(fc) {
    if (_rowHandle._onDown) { fc.off('mouse:down', _rowHandle._onDown); _rowHandle._onDown = null; }
    if (_rowHandle._onMove) { fc.off('mouse:move', _rowHandle._onMove); _rowHandle._onMove = null; }
    if (_rowHandle._onUp)   { fc.off('mouse:up',   _rowHandle._onUp);   _rowHandle._onUp   = null; }
}

// ─────────────────────────────────────────────────────────────
// TOOLTIPS
// ─────────────────────────────────────────────────────────────

function _makeTooltip(id) {
    var tt = document.getElementById(id);
    if (!tt) {
        tt = document.createElement('div');
        tt.id = id;
        tt.style.cssText =
            'position:fixed;z-index:99999;background:rgba(15,23,42,0.92);color:#fff;' +
            'font:bold 11px/1 monospace;padding:4px 8px;border-radius:5px;' +
            'pointer-events:none;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,0.3);';
        document.body.appendChild(tt);
    }
    return tt;
}

function _showRowResizeTooltip(s, height) {
    var tt   = _makeTooltip('__rowResizeTooltip');
    var fc   = s.pgData.canvas;
    var rect = fc.wrapperEl.getBoundingClientRect();
    var activeHandle = s.handles[s.dragRowIndex];
    if (activeHandle) {
        var lineY = (activeHandle.lineObj.top || 0) * currentZoom;
        tt.style.left = (rect.left + 8) + 'px';
        tt.style.top  = (rect.top + lineY - 20) + 'px';
    }
    tt.textContent   = height + ' px';
    tt.style.display = 'block';
}

function _hideRowResizeTooltip() {
    var tt = document.getElementById('__rowResizeTooltip');
    if (tt) tt.style.display = 'none';
}

function _showColResizeTooltip(width, pgData, colIndex) {
    var tt   = _makeTooltip('__colResizeTooltip');
    var fc   = pgData.canvas;
    var rect = fc.wrapperEl.getBoundingClientRect();
    var h    = _colHandle.handles[colIndex];
    if (h) {
        var lx = ((h.lineObj.left || 0) + 6) * currentZoom;
        tt.style.left = (rect.left + lx + 6) + 'px';
        tt.style.top  = (rect.top + 8) + 'px';
    }
    tt.textContent   = width + ' px';
    tt.style.display = 'block';
}

function _hideColResizeTooltip() {
    var tt = document.getElementById('__colResizeTooltip');
    if (tt) tt.style.display = 'none';
}

// ─────────────────────────────────────────────────────────────
// LIVE RERENDER
// ─────────────────────────────────────────────────────────────

/**
 * Update gambar tabel di Fabric canvas secara langsung (tanpa replace object).
 * @param {{ td, fabricObj, pgData }} s
 */
function _liveRerenderTable(s) {
    var td  = s.td;
    var obj = s.fabricObj;
    var fc  = s.pgData.canvas;

    var offscreen   = renderTableToCanvas(td);
    var finalCanvas = document.createElement('canvas');
    finalCanvas.width  = td.totalWidth;
    finalCanvas.height = td.totalHeight;
    finalCanvas.getContext('2d').drawImage(offscreen, 0, 0, td.totalWidth, td.totalHeight);
    var dataUrl = finalCanvas.toDataURL();

    // Update <img> element langsung (lebih efisien dari replace object)
    var imgEl = obj.getElement ? obj.getElement() : null;
    if (imgEl) {
        imgEl.onload = function () {
            obj.set({ width: td.totalWidth, height: td.totalHeight });
            obj.setCoords();
            fc.requestRenderAll();
        };
        imgEl.src = dataUrl;
    } else {
        // Fallback: ganti object
        fabric.Image.fromURL(dataUrl, function (newImg) {
            var oldLeft = obj.left;
            var oldTop  = obj.top;
            var oldName = obj.name;

            newImg.set({
                left: oldLeft, top: oldTop, name: oldName,
                selectable: true, evented: true,
                hasBorders: true, hasControls: true, lockRotation: true,
            });
            newImg._isTable = true;

            fc.remove(obj);
            fc.add(newImg);
            fc.setActiveObject(newImg);
            s.fabricObj = newImg;
            fc.requestRenderAll();
        });
    }
}
