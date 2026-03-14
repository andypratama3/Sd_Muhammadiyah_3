/**
 * page-manager.js — manajemen halaman (multi-page), zoom, thumbnails
 *
 * Depends: constants.js, utils.js, ruler.js (drawMarginGuidesForPage),
 *          table-handles.js (attachTableHandles), table-style-panel.js (attachStylePanel)
 */

// ── Page creation ─────────────────────────────────────────────

function createPage() {
    var container = document.getElementById('canvasPagesContainer');
    var pageIndex = pages.length;

    // Wrapper DOM
    var wrapper = document.createElement('div');
    wrapper.className = 'page-block' + (pageIndex === 0 ? ' active-page' : '');
    wrapper.style.position = 'relative';

    var label = document.createElement('div');
    label.className = 'page-label';
    label.textContent = 'Halaman ' + (pageIndex + 1);
    wrapper.appendChild(label);

    // Canvas element
    var canvasEl = document.createElement('canvas');
    canvasEl.width  = CANVAS_W;
    canvasEl.height = CANVAS_H;
    wrapper.appendChild(canvasEl);
    container.appendChild(wrapper);

    // Fabric canvas
    var fc = new fabric.Canvas(canvasEl, {
        preserveObjectStacking: true,
        renderOnAddRemove:      false,
        skipTargetFind:         false,
        enableRetinaScaling:    false,
        imageSmoothingEnabled:  true,
        selection:              true,
        allowTouchScrolling:    false,
        stopContextMenu:        true,
        fireRightClick:         false,
    });
    fc.setBackgroundColor('white', fc.renderAll.bind(fc));

    // Default object styling
    fabric.Object.prototype.set({
        transparentCorners:  false,
        cornerColor:         '#0ea5e9',
        cornerStrokeColor:   '#ffffff',
        borderColor:         '#0ea5e9',
        cornerSize:          9,
        cornerStyle:         'circle',
        borderDashArray:     [4, 3],
        borderScaleFactor:   1.5,
        padding:             5,
    });

    fc.upperCanvasEl.style.zIndex = '3';

    // Overlay canvases (guide, grid, margin)
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
    var gridEl   = makeOverlay('overlay_grid',  'opacity:0.5;display:none;');
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
        // snap state
        _pageSnapPoints:  [],
        _objGuidePoints:  [],
        _activeSnapX:     null,
        _activeSnapY:     null,
        _prevSnapX:       null,
        _prevSnapY:       null,
        // guide fade
        _guideAlpha:      0,
        _guideAlphaRafId: null,
    };

    pages.push(pageData);

    attachPageEvents(pageData);
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
        alert('Minimal harus ada 1 halaman.');
        return;
    }
    if (!confirm('Hapus halaman ' + (currentPage + 1) + '?')) return;

    var pg = pages[currentPage];
    pg.canvas.dispose();
    pg.wrapper.remove();
    pages.splice(currentPage, 1);

    // Re-label semua halaman
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

        // Render thumbnail async
        var ctx = tc.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, tc.width, tc.height);

        var url = pg.canvas.toDataURL({ format: 'png', quality: 0.3 });
        var img = new Image();
        img.onload = function () {
            ctx.drawImage(img, 0, 0, tc.width, tc.height);
        };
        img.src = url;
    });
}

// ── Zoom ──────────────────────────────────────────────────────

function setZoom(z) {
    z = Math.min(2.5, Math.max(0.4, parseFloat(z.toFixed(2))));
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

    document.getElementById('zoomLabel').textContent = Math.round(z * 100) + '%';
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
    e.deltaY < 0 ? zoomIn() : zoomOut();
}, { passive: false });

// ── Undo / Redo ───────────────────────────────────────────────

function saveStateForPage(pgData) {
    if (pgData._isSaving) return;
    clearTimeout(pgData._saveTimer);
    pgData._saveTimer = setTimeout(function () {
        var json = JSON.stringify(
            pgData.canvas.toJSON(['name', 'excludeFromExport'])
        );
        pgData._history.push(json);
        if (pgData._history.length > 50) pgData._history.shift();
        pgData._historyRedo = [];
    }, 60);
}

function saveState() {
    if (pages[currentPage]) saveStateForPage(pages[currentPage]);
}

function undo() {
    var pg = pages[currentPage];
    if (!pg || pg._history.length < 2) return;
    pg._isSaving = true;
    pg._historyRedo.push(pg._history.pop());
    pg.canvas.loadFromJSON(pg._history[pg._history.length - 1], function () {
        pg.canvas.renderAll();
        pg._isSaving = false;
        renderPageThumbnails();
    });
}

function redo() {
    var pg = pages[currentPage];
    if (!pg || !pg._historyRedo.length) return;
    pg._isSaving = true;
    var snapshot = pg._historyRedo.pop();
    pg._history.push(snapshot);
    pg.canvas.loadFromJSON(snapshot, function () {
        pg.canvas.renderAll();
        pg._isSaving = false;
        renderPageThumbnails();
    });
}
