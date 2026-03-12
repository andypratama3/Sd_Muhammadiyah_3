/**
 * template-editor.js  — FIGMA-LIKE SMOOTH EDITION
 *
 * Core changes vs previous version:
 *  1. SPRING PHYSICS drag — lerp-based position smoothing so movement
 *     feels fluid, never "snapping" or "jumping". Snapping locks in
 *     gracefully through interpolation, not by teleporting the object.
 *  2. SMART GUIDE LINES — full-canvas guide lines drawn with sub-pixel
 *     anti-aliasing, glow, opacity fade-in/out animation.
 *  3. HYSTERESIS SNAP — two thresholds (pull-in vs release) so guides
 *     never flicker when you're near a snap point.
 *  4. DISTANCE LABELS — Figma-style px dimension readout when near a
 *     snapped guide.
 *  5. TABLE ROW DRAG HANDLES — drag batas baris langsung di canvas
 *     untuk resize tinggi baris secara live.
 *  6. TABLE STYLE FLOAT PANEL — panel warna header/stripe muncul
 *     saat tabel dipilih, tanpa modal.
 *  7. ACCURATE A4 DIMENSIONS — canvas grid 1:1 sesuai ukuran PDF A4
 *     menggunakan 96dpi standar CSS browser.
 */

// ============================================================
// CONSTANTS — A4 @ 96 DPI (CSS standard)
// ============================================================
//
//  A4 fisik   : 210 mm × 297 mm
//  96 DPI     : 1 inch = 96 px, 1 mm = 96/25.4 = 3.7795 px
//  Canvas px  : 210mm × 3.7795 = 793.70 → 794 px
//               297mm × 3.7795 = 1122.5 → 1123 px  ✓ sama seperti sebelumnya
//
//  Export pt  : 1 pt = 1/72 inch, 1 px = 72/96 = 0.75 pt  (exact)
//               794 px × 0.75 = 595.5 pt  (A4 spec = 595.28 pt, diff < 0.04%)
//               1123 px × 0.75 = 842.25 pt (A4 spec = 841.89 pt, diff < 0.04%)
//
//  Margin     : 20 mm = 75.59 px → 76 px  (standar DomPDF @page margin)
//  Grid minor : 5 mm  = 18.90 px
//  Grid major : 10 mm = 37.80 px

var MM_TO_PX  = 96 / 25.4;          // 3.779527... px per mm
var PX_TO_PT  = 72 / 96;            // 0.75 pt per px  (exact)

var CANVAS_W  = Math.round(210 * MM_TO_PX);   // 794 px
var CANVAS_H  = Math.round(297 * MM_TO_PX);   // 1123 px

// Margin 20mm — sesuai @page { margin: 20mm } di DomPDF
var MARGIN    = Math.round(20 * MM_TO_PX);     // 76 px

// Grid spacing dalam px (presisi fisik)
var GRID_MINOR_PX = 5  * MM_TO_PX;   // 5mm  = 18.90 px
var GRID_MAJOR_PX = 10 * MM_TO_PX;   // 10mm = 37.80 px

// Export ke PDF (pt)
var A4_W      = 210 * (72 / 25.4);   // 595.276 pt
var A4_H      = 297 * (72 / 25.4);   // 841.890 pt

// Backward-compat: fungsi pt() pakai PX_TO_PT yang exact
var R = PX_TO_PT; // alias, dipakai oleh fungsi pt()

// ============================================================
// STATE
// ============================================================
var TABLE_STORE = {};
var tableCounter = 0;
var pages = [];
var currentPage = 0;
var snapEnabled = true;
var currentZoom = 1;
var _clipboard = null;

// ─── Snap / guide constants ──────────────────────────────────
var SNAP_THRESHOLD = 6;
var SNAP_RELEASE = 10;
var GUIDE_FADE_IN = 0.22;
var GUIDE_FADE_OUT = 0.15;

// ============================================================
// UTILS
// ============================================================
function pt(px) {
    // 1 px = 0.75 pt (exact at 96 dpi — CSS standard)
    return parseFloat((px * PX_TO_PT).toFixed(2));
}

// Konversi mm ke px (untuk kalkulasi internal)
function mmToPx(mm) { return mm * MM_TO_PX; }

// Konversi px ke mm (untuk tampilan ruler & debug)
function pxToMm(px) { return px / MM_TO_PX; }

function escHtml(s) {
    return (s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

function escapeContent(text) {
    return (text || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/\{\{([^}]+)\}\}/g, function (m, v) {
            return '{{' + v.trim() + '}}';
        });
}

function escapeCellContent(text) {
    return (text || '')
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/\n/g, '<br>')
        .replace(/\{\{([^}]+)\}\}/g, function (m, v) {
            return '{{' + v.trim() + '}}';
        });
}

function realTopLeft(obj) {
    var w = (obj.width || 0) * (obj.scaleX || 1),
        h = (obj.height || 0) * (obj.scaleY || 1);
    var ox = obj.originX || 'left',
        oy = obj.originY || 'top';
    var x = obj.left || 0,
        y = obj.top || 0;
    if (ox === 'center') x = x - w / 2;
    else if (ox === 'right') x = x - w;
    if (oy === 'center') y = y - h / 2;
    else if (oy === 'bottom') y = y - h;
    return { x: x, y: y, w: w, h: h };
}

function buildColWidths(specs, totalW) {
    var fixed = 0, fills = 0;
    specs.forEach(function (s) {
        s === null ? fills++ : (fixed += s);
    });
    var fillW = fills > 0 ? Math.max(20, (totalW - fixed) / fills) : 0;
    return specs.map(function (s) { return s === null ? fillW : s; });
}

function buildRowHeights(dataRows, rowH, headerH) {
    var arr = [headerH];
    for (var i = 0; i < dataRows; i++) arr.push(rowH);
    return arr;
}

// ============================================================
// ACTIVE CANVAS HELPERS
// ============================================================
function getCanvas() {
    return pages[currentPage] ? pages[currentPage].canvas : null;
}

function getTableStore() {
    return pages[currentPage] ? pages[currentPage].tableStore : TABLE_STORE;
}

// ============================================================
// PAGE MANAGEMENT
// ============================================================
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
    canvasEl.width = CANVAS_W;
    canvasEl.height = CANVAS_H;
    wrapper.appendChild(canvasEl);
    container.appendChild(wrapper);

    var fc = new fabric.Canvas(canvasEl, {
        preserveObjectStacking: true,
        renderOnAddRemove: false,
        skipTargetFind: false,
        enableRetinaScaling: false,
        imageSmoothingEnabled: true,
        selection: true,
        allowTouchScrolling: false,
        stopContextMenu: true,
        fireRightClick: false,
    });
    fc.setBackgroundColor('white', fc.renderAll.bind(fc));

    fabric.Object.prototype.set({
        transparentCorners: false,
        cornerColor: '#0ea5e9',
        cornerStrokeColor: '#ffffff',
        borderColor: '#0ea5e9',
        cornerSize: 9,
        cornerStyle: 'circle',
        borderDashArray: [4, 3],
        borderScaleFactor: 1.5,
        padding: 5,
    });

    var fabricWrapperEl = fc.wrapperEl;
    fc.upperCanvasEl.style.zIndex = '3';

    function makeOverlay(id, extraStyle) {
        var c = document.createElement('canvas');
        c.id = id + '_' + pageIndex;
        c.width = CANVAS_W;
        c.height = CANVAS_H;
        c.style.cssText = 'position:absolute;top:0;left:0;z-index:1;pointer-events:none;' + (extraStyle || '');
        fabricWrapperEl.appendChild(c);
        return c;
    }

    var guideEl  = makeOverlay('overlay_guide', '');
    var gridEl   = makeOverlay('overlay_grid', 'opacity:0.5;display:none;');
    var marginEl = makeOverlay('overlay_margin', '');

    var pageData = {
        canvas: fc,
        wrapper: wrapper,
        guideEl: guideEl,
        gridEl: gridEl,
        marginEl: marginEl,
        tableStore: {},
        _history: [],
        _historyRedo: [],
        _isSaving: false,
        _saveTimer: null,
        _snapRafId: null,
        _pageIndex: pageIndex,
        _pageSnapPoints: [],
        _objGuidePoints: [],
        _activeSnapX: null,
        _activeSnapY: null,
        _prevSnapX: null,
        _prevSnapY: null,
        _guideAlpha: 0,
        _guideAlphaRafId: null,
    };
    pages.push(pageData);

    attachPageEvents(pageData);
    drawMarginGuidesForPage(pageData, marginVisible);
    renderPageThumbnails();
    saveStateForPage(pageData);
    return pageData;
}

function switchPage(idx) {
    if (idx < 0 || idx >= pages.length) return;
    var old = pages[currentPage];
    if (old) {
        old.canvas.discardActiveObject();
        old.canvas.renderAll();
        old.wrapper.classList.remove('active-page');
    }
    currentPage = idx;
    var pg = pages[currentPage];
    pg.wrapper.classList.add('active-page');
    updatePageIndicator();
    renderPageThumbnails();
    pg.wrapper.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function addNewPage() {
    createPage();
    switchPage(pages.length - 1);
    saveState();
}

function removeCurrentPage() {
    if (pages.length <= 1) { alert('Minimal harus ada 1 halaman.'); return; }
    if (!confirm('Hapus halaman ' + (currentPage + 1) + '?')) return;
    var pg = pages[currentPage];
    pg.canvas.dispose();
    pg.wrapper.remove();
    pages.splice(currentPage, 1);
    pages.forEach(function (p, i) {
        var l = p.wrapper.querySelector('.page-label');
        if (l) l.textContent = 'Halaman ' + (i + 1);
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

function renderPageThumbnails() {
    var container = document.getElementById('pageThumbnails');
    if (!container) return;
    container.innerHTML = '';
    pages.forEach(function (pg, idx) {
        var item = document.createElement('div');
        item.className = 'thumb-item' + (idx === currentPage ? ' active' : '');
        var tc = document.createElement('canvas');
        tc.width = 80;
        tc.height = Math.round(80 * (CANVAS_H / CANVAS_W));
        item.appendChild(tc);
        var lbl = document.createElement('div');
        lbl.className = 'thumb-label';
        lbl.textContent = 'Hal. ' + (idx + 1);
        item.appendChild(lbl);
        item.addEventListener('click', function () { switchPage(idx); });
        container.appendChild(item);
        var ctx = tc.getContext('2d');
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, tc.width, tc.height);
        var url = pg.canvas.toDataURL({ format: 'png', quality: 0.3 });
        var img = new Image();
        img.onload = function () { ctx.drawImage(img, 0, 0, tc.width, tc.height); };
        img.src = url;
    });
}

// ============================================================
// ZOOM
// ============================================================
function setZoom(z) {
    z = Math.min(2.5, Math.max(0.4, parseFloat(z.toFixed(2))));
    currentZoom = z;
    pages.forEach(function (pg) {
        var fc = pg.canvas;
        fc.setZoom(z);
        fc.setWidth(Math.round(CANVAS_W * z));
        fc.setHeight(Math.round(CANVAS_H * z));
        fc.renderAll();
        [pg.guideEl, pg.gridEl, pg.marginEl].forEach(function (el) {
            el.style.transform = 'scale(' + z + ')';
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

document.getElementById('editorContainer').addEventListener('wheel', function (e) {
    if (!e.ctrlKey) return;
    e.preventDefault();
    e.deltaY < 0 ? zoomIn() : zoomOut();
}, { passive: false });

// ============================================================
// RULERS
// ============================================================
var rulerVisible = true;
var rulerH = document.getElementById('rulerH');
var rulerV = document.getElementById('rulerV');
var ctxH = rulerH.getContext('2d', { willReadFrequently: true });
var ctxV = rulerV.getContext('2d', { willReadFrequently: true });
var _rulerHBase = null, _rulerVBase = null, _rulerRafId = null;

function toggleRulerVis(show) {
    rulerVisible = show;
    rulerH.style.display = show ? 'block' : 'none';
    rulerV.style.display = show ? 'block' : 'none';
    document.getElementById('rulerCorner').style.display = show ? 'block' : 'none';
}

function drawRulersBase() {
    if (!rulerVisible) return;

    var W = Math.round(CANVAS_W * currentZoom);
    var H = Math.round(CANVAS_H * currentZoom);
    rulerH.width = W;
    rulerV.height = H;

    // px per mm di layar (sudah termasuk zoom)
    var pxPerMm = MM_TO_PX * currentZoom;

    // Pilih step tick: ≥10px per tick
    // Urutan: 1, 2, 5, 10, 20, 50mm
    var tickSteps = [1, 2, 5, 10, 20, 50];
    var step = 50;
    for (var si = 0; si < tickSteps.length; si++) {
        if (tickSteps[si] * pxPerMm >= 10) { step = tickSteps[si]; break; }
    }

    // ── Horizontal ruler (X = mm lebar A4 = 210mm) ────────
    ctxH.fillStyle = '#f1f3f5';
    ctxH.fillRect(0, 0, W, 20);

    // Border bawah
    ctxH.fillStyle = '#ced4da';
    ctxH.fillRect(0, 19, W, 1);

    ctxH.font = '8.5px "SF Mono",Consolas,monospace';
    ctxH.textBaseline = 'top';

    for (var mm = 0; mm <= 210; mm += step) {
        var x = Math.round(mm * pxPerMm);
        if (x > W) break;
        var isMajor = (mm % (step * 2) === 0) || step >= 10;
        var tickH   = isMajor ? 10 : 5;
        ctxH.fillStyle = isMajor ? '#495057' : '#adb5bd';
        ctxH.fillRect(x, 20 - tickH, 1, tickH);
        if (isMajor && x > 1) {
            ctxH.fillStyle = '#495057';
            ctxH.fillText(mm, x + 2, 2);
        }
    }

    // ── Vertical ruler (Y = mm tinggi A4 = 297mm) ─────────
    ctxV.fillStyle = '#f1f3f5';
    ctxV.fillRect(0, 0, 20, H);

    // Border kanan
    ctxV.fillStyle = '#ced4da';
    ctxV.fillRect(19, 0, 1, H);

    ctxV.font = '8.5px "SF Mono",Consolas,monospace';

    for (var mmv = 0; mmv <= 297; mmv += step) {
        var y = Math.round(mmv * pxPerMm);
        if (y > H) break;
        var isMajorV = (mmv % (step * 2) === 0) || step >= 10;
        var tickW    = isMajorV ? 10 : 5;
        ctxV.fillStyle = isMajorV ? '#495057' : '#adb5bd';
        ctxV.fillRect(20 - tickW, y, tickW, 1);
        if (isMajorV && y > 1) {
            ctxV.save();
            ctxV.translate(14, y - 2);
            ctxV.rotate(-Math.PI / 2);
            ctxV.fillStyle = '#495057';
            ctxV.fillText(mmv, 0, 0);
            ctxV.restore();
        }
    }

    _rulerHBase = ctxH.getImageData(0, 0, rulerH.width, 20);
    _rulerVBase = ctxV.getImageData(0, 0, 20, rulerV.height);
}

function drawRulerCursor(px, py) {
    if (!rulerVisible || !_rulerHBase || !_rulerVBase) return;

    var x  = Math.round(px * currentZoom);
    var y  = Math.round(py * currentZoom);
    var xMm = (px / MM_TO_PX).toFixed(1);
    var yMm = (py / MM_TO_PX).toFixed(1);

    ctxH.putImageData(_rulerHBase, 0, 0);
    ctxV.putImageData(_rulerVBase, 0, 0);

    // Garis cursor
    ctxH.fillStyle = 'rgba(220,53,69,0.9)'; ctxH.fillRect(x, 0, 1, 20);
    ctxV.fillStyle = 'rgba(220,53,69,0.9)'; ctxV.fillRect(0, y, 20, 1);

    // Bubble mm di horizontal ruler
    var bubW = 36, bubH = 14;
    var bubX = Math.min(x + 3, rulerH.width - bubW - 2);
    ctxH.fillStyle = 'rgba(220,53,69,0.9)';
    ctxH.beginPath();
    ctxH.roundRect ? ctxH.roundRect(bubX, 2, bubW, bubH, 3)
                   : ctxH.rect(bubX, 2, bubW, bubH);
    ctxH.fill();
    ctxH.fillStyle = '#fff';
    ctxH.font = 'bold 8px "SF Mono",Consolas,monospace';
    ctxH.textBaseline = 'middle';
    ctxH.textAlign = 'center';
    ctxH.fillText(xMm + 'mm', bubX + bubW / 2, 2 + bubH / 2);
    ctxH.textAlign = 'left';

    // Bubble mm di vertical ruler
    var bvW = 14, bvH = 36;
    var bvY = Math.min(y + 3, rulerV.height - bvH - 2);
    ctxV.fillStyle = 'rgba(220,53,69,0.9)';
    ctxV.beginPath();
    ctxV.roundRect ? ctxV.roundRect(2, bvY, bvW, bvH, 3)
                   : ctxV.rect(2, bvY, bvW, bvH);
    ctxV.fill();
    ctxV.save();
    ctxV.translate(2 + bvW / 2, bvY + bvH / 2);
    ctxV.rotate(-Math.PI / 2);
    ctxV.fillStyle = '#fff';
    ctxV.font = 'bold 8px "SF Mono",Consolas,monospace';
    ctxV.textBaseline = 'middle';
    ctxV.textAlign = 'center';
    ctxV.fillText(yMm + 'mm', 0, 0);
    ctxV.restore();
}

// ============================================================
// GRID
// ============================================================
var gridVisible = false;

function toggleGrid(show) {
    gridVisible = show;
    pages.forEach(function (pg) {
        var gc  = pg.gridEl;
        var ctx = gc.getContext('2d');
        if (!show) { gc.style.display = 'none'; return; }

        gc.style.display = 'block';
        ctx.clearRect(0, 0, CANVAS_W, CANVAS_H);

        // ── Grid minor: setiap 5mm ─────────────────────────
        // 5mm = 5 × 3.7795 = 18.898 px
        ctx.beginPath();
        ctx.strokeStyle = 'rgba(173,214,255,0.45)';
        ctx.lineWidth   = 0.5;
        for (var mmx = 0; mmx <= 210; mmx += 5) {
            var gx = mmx * MM_TO_PX;
            ctx.moveTo(gx, 0); ctx.lineTo(gx, CANVAS_H);
        }
        for (var mmy = 0; mmy <= 297; mmy += 5) {
            var gy = mmy * MM_TO_PX;
            ctx.moveTo(0, gy); ctx.lineTo(CANVAS_W, gy);
        }
        ctx.stroke();

        // ── Grid major: setiap 10mm ────────────────────────
        ctx.beginPath();
        ctx.strokeStyle = 'rgba(100,149,237,0.55)';
        ctx.lineWidth   = 0.75;
        for (var mmx2 = 0; mmx2 <= 210; mmx2 += 10) {
            var gx2 = mmx2 * MM_TO_PX;
            ctx.moveTo(gx2, 0); ctx.lineTo(gx2, CANVAS_H);
        }
        for (var mmy2 = 0; mmy2 <= 297; mmy2 += 10) {
            var gy2 = mmy2 * MM_TO_PX;
            ctx.moveTo(0, gy2); ctx.lineTo(CANVAS_W, gy2);
        }
        ctx.stroke();

        // ── Label mm setiap 50mm ───────────────────────────
        ctx.font      = '8px "SF Mono",Consolas,monospace';
        ctx.fillStyle = 'rgba(100,149,237,0.7)';
        ctx.textBaseline = 'top';
        for (var lmx = 10; lmx <= 200; lmx += 50) {
            for (var lmy = 10; lmy <= 280; lmy += 50) {
                ctx.fillText(lmx + ',' + lmy, lmx * MM_TO_PX + 2, lmy * MM_TO_PX + 2);
            }
        }
    });
}

// ============================================================
// MARGIN GUIDES
// ============================================================
var marginVisible = true;

function drawMarginGuidesForPage(pg, show) {
    var mc  = pg.marginEl;
    var ctx = mc.getContext('2d');
    ctx.clearRect(0, 0, CANVAS_W, CANVAS_H);
    if (!show) return;

    var M = MARGIN; // 20mm = 76px (exact)

    // ── Area bayangan di luar margin ───────────────────────
    ctx.fillStyle = 'rgba(0,0,0,0.04)';
    // Kiri
    ctx.fillRect(0, 0, M, CANVAS_H);
    // Kanan
    ctx.fillRect(CANVAS_W - M, 0, M, CANVAS_H);
    // Atas
    ctx.fillRect(M, 0, CANVAS_W - M * 2, M);
    // Bawah
    ctx.fillRect(M, CANVAS_H - M, CANVAS_W - M * 2, M);

    // ── Garis margin utama ─────────────────────────────────
    ctx.strokeStyle = 'rgba(13,110,253,0.5)';
    ctx.lineWidth   = 1;
    ctx.setLineDash([6, 4]);
    ctx.beginPath();
    ctx.moveTo(M, 0);            ctx.lineTo(M, CANVAS_H);
    ctx.moveTo(CANVAS_W - M, 0); ctx.lineTo(CANVAS_W - M, CANVAS_H);
    ctx.moveTo(0, M);            ctx.lineTo(CANVAS_W, M);
    ctx.moveTo(0, CANVAS_H - M); ctx.lineTo(CANVAS_W, CANVAS_H - M);
    ctx.stroke();
    ctx.setLineDash([]);

    // ── Label "20mm" di pojok ──────────────────────────────
    ctx.font      = '9px "SF Mono",Consolas,monospace';
    ctx.fillStyle = 'rgba(13,110,253,0.6)';
    ctx.textBaseline = 'top';
    ctx.fillText('20mm', M + 3, M + 3);
}

function toggleMarginGuides(show) {
    marginVisible = show;
    pages.forEach(function (pg) { drawMarginGuidesForPage(pg, show); });
}

// ============================================================
// SMART GUIDES
// ============================================================
function drawSmartGuides(pgData, obj, snapX, snapY, objGuides, alpha) {
    var ctx = pgData.guideEl.getContext('2d');
    ctx.clearRect(0, 0, CANVAS_W, CANVAS_H);
    if (alpha <= 0) return;

    var b1 = obj.getBoundingRect(true);
    var b1R = b1.left + b1.width;
    var b1B = b1.top + b1.height;

    ctx.save();
    ctx.globalAlpha = Math.min(1, alpha);

    if (objGuides && objGuides.length) {
        ctx.save();
        ctx.strokeStyle = 'rgba(14,165,233,0.55)';
        ctx.lineWidth = 1;
        ctx.setLineDash([4, 3]);
        ctx.shadowColor = 'rgba(14,165,233,0.3)';
        ctx.shadowBlur = 3;
        objGuides.forEach(function (g) {
            var b2 = g.origin.getBoundingRect(true);
            ctx.beginPath();
            if (g.type === 'x') {
                var yMin = Math.min(b1.top, b2.top) - 10;
                var yMax = Math.max(b1B, b2.top + b2.height) + 10;
                ctx.moveTo(g.ref, yMin); ctx.lineTo(g.ref, yMax);
            } else {
                var xMin = Math.min(b1.left, b2.left) - 10;
                var xMax = Math.max(b1R, b2.left + b2.width) + 10;
                ctx.moveTo(xMin, g.ref); ctx.lineTo(xMax, g.ref);
            }
            ctx.stroke();
        });
        ctx.setLineDash([]); ctx.restore();
    }

    function drawSnapLine(isX, ref) {
        ctx.save();
        ctx.strokeStyle = 'rgba(244,63,94,0.15)'; ctx.lineWidth = 1; ctx.setLineDash([]);
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, 0); ctx.lineTo(ref, CANVAS_H); }
        else      { ctx.moveTo(0, ref); ctx.lineTo(CANVAS_W, ref); }
        ctx.stroke();

        ctx.strokeStyle = 'rgba(244,63,94,0.22)'; ctx.lineWidth = 7;
        ctx.shadowColor = 'rgba(244,63,94,0.3)'; ctx.shadowBlur = 10;
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, b1.top - 20); ctx.lineTo(ref, b1B + 20); }
        else      { ctx.moveTo(b1.left - 20, ref); ctx.lineTo(b1R + 20, ref); }
        ctx.stroke();

        ctx.strokeStyle = '#f43f5e'; ctx.lineWidth = 1.5;
        ctx.shadowColor = 'rgba(244,63,94,0.55)'; ctx.shadowBlur = 5;
        ctx.setLineDash([]);
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, b1.top - 20); ctx.lineTo(ref, b1B + 20); }
        else      { ctx.moveTo(b1.left - 20, ref); ctx.lineTo(b1R + 20, ref); }
        ctx.stroke();

        ctx.fillStyle = '#f43f5e'; ctx.shadowBlur = 5;
        var dots = isX ? [[ref, b1.top],[ref, b1B]] : [[b1.left, ref],[b1R, ref]];
        dots.forEach(function (d) {
            ctx.beginPath(); ctx.arc(d[0], d[1], 3.5, 0, Math.PI * 2); ctx.fill();
        });
        ctx.restore();
    }

    if (snapX) drawSnapLine(true, snapX.ref);
    if (snapY) drawSnapLine(false, snapY.ref);

    if (snapX || snapY) {
        ctx.save(); ctx.shadowBlur = 0;
        var parts = [];
        if (snapX) parts.push('X ' + Math.round(snapX.ref));
        if (snapY) parts.push('Y ' + Math.round(snapY.ref));
        var txt = parts.join('  ');
        ctx.font = 'bold 9.5px -apple-system,monospace';
        var tw = ctx.measureText(txt).width;
        var px = 6, bh = 16, bw = tw + px * 2;
        var bx = (snapX ? snapX.ref : b1.left) + 5;
        var by = b1.top - bh - 6;
        if (bx + bw > CANVAS_W - 4) bx = (snapX ? snapX.ref : b1.left) - bw - 5;
        if (by < 4) by = b1.top + 5;
        ctx.fillStyle = 'rgba(244,63,94,0.92)';
        _rrect(ctx, bx, by, bw, bh, 4); ctx.fill();
        ctx.fillStyle = '#fff'; ctx.textBaseline = 'middle'; ctx.textAlign = 'left';
        ctx.fillText(txt, bx + px, by + bh / 2);
        ctx.restore();
    }
    ctx.restore();
}

function _rrect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
}

function fadeGuides(pgData, targetAlpha, obj, snapX, snapY, objGuides) {
    if (pgData._guideAlphaRafId) cancelAnimationFrame(pgData._guideAlphaRafId);
    function step() {
        var diff = targetAlpha - pgData._guideAlpha;
        var speed = targetAlpha > 0 ? GUIDE_FADE_IN : GUIDE_FADE_OUT;
        if (Math.abs(diff) < 0.015) {
            pgData._guideAlpha = targetAlpha;
            if (targetAlpha === 0) pgData.guideEl.getContext('2d').clearRect(0, 0, CANVAS_W, CANVAS_H);
            else if (obj) drawSmartGuides(pgData, obj, snapX, snapY, objGuides || [], 1);
            pgData._guideAlphaRafId = null;
            return;
        }
        pgData._guideAlpha = Math.max(0, Math.min(1, pgData._guideAlpha + (diff > 0 ? speed : -speed)));
        if (obj) drawSmartGuides(pgData, obj, snapX, snapY, objGuides || [], pgData._guideAlpha);
        pgData._guideAlphaRafId = requestAnimationFrame(step);
    }
    step();
}

// ============================================================
// ────────────────────────────────────────────────────────────
//  TABLE ROW DRAG HANDLES + STYLE FLOAT PANEL
// ────────────────────────────────────────────────────────────
// ============================================================

// ─── State tunggal untuk drag handle ────────────────────────
var _tblHandle = {
    pgData:       null,
    fabricObj:    null,
    tableId:      null,
    td:           null,
    handles:      [],    // [{rowIndex, lineObj}]
    dragging:     false,
    dragRowIndex: null,
    dragStartY:   null,
    dragStartH:   null,
    // bound event refs supaya bisa di-off
    _onDown:  null,
    _onMove:  null,
    _onUp:    null,
};

// ─── Pasang sistem drag handle ke sebuah page ────────────────
function attachTableHandles(pgData) {
    var fc = pgData.canvas;

    fc.on('selection:created', function (e) {
        var obj = e.selected && e.selected[0];
        _tryShowHandles(pgData, obj);
    });
    fc.on('selection:updated', function (e) {
        _clearHandles();
        var obj = e.selected && e.selected[0];
        _tryShowHandles(pgData, obj);
    });
    fc.on('selection:cleared', function () {
        _clearHandles();
    });

    // Update posisi handle saat tabel di-move
    fc.on('object:moving', function (e) {
        if (_tblHandle.fabricObj === e.target) {
            _repositionHandles();
            _updateFloatPanelPos(pgData, e.target);
        }
    });
}

function _tryShowHandles(pgData, obj) {
    if (!obj || !obj.name) return;
    var td = pgData.tableStore[obj.name];
    if (!td || td.type === 'ttd') return;

    _tblHandle.pgData    = pgData;
    _tblHandle.fabricObj = obj;
    _tblHandle.tableId   = obj.name;
    _tblHandle.td        = td;

    _drawHandles();
    _showFloatPanel(pgData, obj, td);
}

// ─── Gambar garis-garis handle di canvas ────────────────────
function _drawHandles() {
    _clearHandles();
    var s   = _tblHandle;
    if (!s.td || !s.fabricObj) return;

    var fc     = s.pgData.canvas;
    var obj    = s.fabricObj;
    var td     = s.td;
    var scaleY = obj.scaleY || 1;
    var scaleX = obj.scaleX || 1;
    var objTop = obj.top  || 0;
    var objLeft= obj.left || 0;
    var objW   = (obj.width  || td.totalWidth) * scaleX;

    var cumY = objTop;
    s.handles = [];

    for (var ri = 0; ri < td.rowHeights.length - 1; ri++) {
        cumY += td.rowHeights[ri] * scaleY;

        var line = new fabric.Line(
            [objLeft, cumY, objLeft + objW, cumY],
            {
                stroke: '#38bdf8',
                strokeWidth: 2,
                selectable: false,
                evented: true,
                hoverCursor: 'row-resize',
                name: '__handle_row_' + ri,
                excludeFromExport: true,
                padding: 7,
            }
        );
        line._handleRowIndex = ri;

        fc.add(line);
        fc.bringToFront(line);
        s.handles.push({ rowIndex: ri, lineObj: line });
    }

    fc.requestRenderAll();
    _attachDragEvents(fc);
}

// ─── Hapus semua handle ──────────────────────────────────────
function _clearHandles() {
    var s = _tblHandle;
    if (!s.pgData) return;
    var fc = s.pgData.canvas;

    s.handles.forEach(function (h) { fc.remove(h.lineObj); });
    s.handles      = [];
    s.dragging     = false;
    s.dragRowIndex = null;
    _detachDragEvents(fc);

    fc.requestRenderAll();
}

// ─── Update posisi semua handle ──────────────────────────────
function _repositionHandles() {
    var s = _tblHandle;
    if (!s.fabricObj || !s.handles.length) return;

    var obj    = s.fabricObj;
    var td     = s.td;
    var scaleY = obj.scaleY || 1;
    var scaleX = obj.scaleX || 1;
    var objTop = obj.top  || 0;
    var objLeft= obj.left || 0;
    var objW   = (obj.width || td.totalWidth) * scaleX;

    var cumY = objTop;
    s.handles.forEach(function (h, i) {
        cumY += td.rowHeights[i] * scaleY;
        h.lineObj.set({ x1: objLeft, x2: objLeft + objW, y1: cumY, y2: cumY });
        h.lineObj.setCoords();
    });
    s.pgData.canvas.requestRenderAll();
}

// ─── Drag event handlers ─────────────────────────────────────
function _attachDragEvents(fc) {
    _detachDragEvents(fc);

    _tblHandle._onDown = function (opt) {
        var target = opt.target;
        if (!target || typeof target._handleRowIndex === 'undefined') return;

        var s         = _tblHandle;
        s.dragging    = true;
        s.dragRowIndex= target._handleRowIndex;
        s.dragStartY  = opt.absolutePointer.y;
        s.dragStartH  = s.td.rowHeights[s.dragRowIndex];

        // Lock movement tabel supaya tidak ikut geser
        fc.selection = false;
        if (s.fabricObj) s.fabricObj.set({ lockMovementX: true, lockMovementY: true });

        // Highlight handle aktif
        target.set({ stroke: '#f43f5e', strokeWidth: 3 });
        fc.requestRenderAll();
    };

    _tblHandle._onMove = function (opt) {
        var s = _tblHandle;
        if (!s.dragging) return;

        var dy        = opt.absolutePointer.y - s.dragStartY;
        var newHeight = Math.max(14, Math.round(s.dragStartH + dy));

        s.td.rowHeights[s.dragRowIndex] = newHeight;
        s.td.totalHeight = s.td.rowHeights.reduce(function (a, b) { return a + b; }, 0);

        _liveRerenderTable(s);
        _repositionHandles();

        // Update tooltip tinggi di handle yang aktif
        _showHandleTooltip(s, newHeight);
    };

    _tblHandle._onUp = function () {
        var s = _tblHandle;
        if (!s.dragging) return;

        s.dragging = false;
        fc.selection = true;
        if (s.fabricObj) s.fabricObj.set({ lockMovementX: false, lockMovementY: false });

        // Reset warna semua handle
        s.handles.forEach(function (h) {
            h.lineObj.set({ stroke: '#38bdf8', strokeWidth: 2 });
        });

        _removeHandleTooltip();
        fc.requestRenderAll();
        saveStateForPage(s.pgData);
        renderPageThumbnails();
    };

    fc.on('mouse:down', _tblHandle._onDown);
    fc.on('mouse:move', _tblHandle._onMove);
    fc.on('mouse:up',   _tblHandle._onUp);
}

function _detachDragEvents(fc) {
    if (_tblHandle._onDown) { fc.off('mouse:down', _tblHandle._onDown); _tblHandle._onDown = null; }
    if (_tblHandle._onMove) { fc.off('mouse:move', _tblHandle._onMove); _tblHandle._onMove = null; }
    if (_tblHandle._onUp)   { fc.off('mouse:up',   _tblHandle._onUp);   _tblHandle._onUp   = null; }
}

// ─── Tooltip kecil saat drag handle ─────────────────────────
function _showHandleTooltip(s, height) {
    var tt = document.getElementById('__handleTooltip');
    if (!tt) {
        tt = document.createElement('div');
        tt.id = '__handleTooltip';
        tt.style.cssText =
            'position:fixed;z-index:99999;background:rgba(15,23,42,0.92);color:#fff;' +
            'font:bold 11px/1 monospace;padding:4px 8px;border-radius:5px;' +
            'pointer-events:none;white-space:nowrap;box-shadow:0 2px 8px rgba(0,0,0,0.3);';
        document.body.appendChild(tt);
    }

    // Posisi mengikuti mouse
    var fc   = s.pgData.canvas;
    var rect = fc.wrapperEl.getBoundingClientRect();
    var activeHandle = s.handles[s.dragRowIndex];
    if (activeHandle) {
        var lineY = activeHandle.lineObj.y1 * currentZoom;
        tt.style.left = (rect.left + 8) + 'px';
        tt.style.top  = (rect.top + lineY - 20) + 'px';
    }
    tt.textContent = height + ' px';
    tt.style.display = 'block';
}

function _removeHandleTooltip() {
    var tt = document.getElementById('__handleTooltip');
    if (tt) tt.style.display = 'none';
}

// ─── Live rerender tabel (update src gambar langsung) ────────
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

    // Cara paling efisien: update element <img> langsung di Fabric
    var imgEl = obj.getElement ? obj.getElement() : null;
    if (imgEl) {
        imgEl.onload = function () {
            obj.set({ width: td.totalWidth, height: td.totalHeight });
            obj.setCoords();
            fc.requestRenderAll();
        };
        imgEl.src = dataUrl;
    } else {
        // Fallback jika getElement tidak tersedia
        fabric.Image.fromURL(dataUrl, function (newImg) {
            var oldLeft = obj.left, oldTop = obj.top, oldName = obj.name;
            newImg.set({
                left: oldLeft, top: oldTop, name: oldName,
                selectable: true, evented: true, hasBorders: true,
                hasControls: true, lockRotation: true,
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

// ============================================================
// TABLE STYLE FLOAT PANEL  (header color + stripe color)
// ============================================================
function attachStylePanel(pgData) {
    var fc = pgData.canvas;

    fc.on('selection:created', function (e) {
        var obj = e.selected && e.selected[0];
        _maybeShowPanel(pgData, obj);
    });
    fc.on('selection:updated', function (e) {
        _removeFloatPanel();
        var obj = e.selected && e.selected[0];
        _maybeShowPanel(pgData, obj);
    });
    fc.on('selection:cleared', function () { _removeFloatPanel(); });
    fc.on('object:moving', function (e) {
        if (_tblHandle.fabricObj === e.target) {
            _updateFloatPanelPos(pgData, e.target);
        }
    });
}

function _maybeShowPanel(pgData, obj) {
    if (!obj || !obj.name) return;
    var td = pgData.tableStore[obj.name];
    if (!td || td.type === 'ttd') return;
    _showFloatPanel(pgData, obj, td);
}

function _showFloatPanel(pgData, obj, td) {
    _removeFloatPanel();

    var panel = document.createElement('div');
    panel.id  = '__tableStylePanel';

    // Posisi awal
    var pos = _calcPanelPos(pgData, obj);

    panel.style.cssText =
        'position:fixed;z-index:9999;' +
        'background:rgba(255,255,255,0.97);' +
        'border:1px solid #e2e8f0;' +
        'border-radius:12px;' +
        'padding:8px 12px;' +
        'box-shadow:0 4px 24px rgba(0,0,0,0.13),0 1px 4px rgba(0,0,0,0.07);' +
        'display:flex;align-items:center;gap:10px;' +
        'font-size:0.8rem;font-family:inherit;' +
        'top:' + pos.top + 'px;left:' + pos.left + 'px;' +
        'animation:_panelIn .18s cubic-bezier(.34,1.56,.64,1) both;';

    // Injeksi keyframe animasi sekali saja
    if (!document.getElementById('__panelKeyframes')) {
        var styleEl = document.createElement('style');
        styleEl.id  = '__panelKeyframes';
        styleEl.textContent =
            '@keyframes _panelIn{from{opacity:0;transform:translateY(6px) scale(.97)}to{opacity:1;transform:none}}';
        document.head.appendChild(styleEl);
    }

    panel.innerHTML =
        // Judul
        '<span style="color:#64748b;font-weight:600;white-space:nowrap;display:flex;align-items:center;gap:4px;">' +
        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/><path d="M2 12h20"/></svg>' +
        'Warna Tabel</span>' +

        // Header color
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;margin:0">' +
        '<span style="color:#94a3b8;font-size:.75rem;white-space:nowrap;">Header</span>' +
        '<div style="position:relative;width:28px;height:28px;">' +
        '<input type="color" id="__fpHeader" value="' + (td.headerColor || '#1a5276') + '" ' +
        'style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;border:none;">' +
        '<div id="__fpHeaderSwatch" style="width:28px;height:28px;border-radius:7px;border:2px solid #e2e8f0;' +
        'background:' + (td.headerColor || '#1a5276') + ';pointer-events:none;"></div>' +
        '</div></label>' +

        // Stripe color
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;margin:0">' +
        '<span style="color:#94a3b8;font-size:.75rem;white-space:nowrap;">Stripe</span>' +
        '<div style="position:relative;width:28px;height:28px;">' +
        '<input type="color" id="__fpStripe" value="' + (td.stripeColor || '#eaf2ff') + '" ' +
        'style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;border:none;">' +
        '<div id="__fpStripeSwatch" style="width:28px;height:28px;border-radius:7px;border:2px solid #e2e8f0;' +
        'background:' + (td.stripeColor || '#eaf2ff') + ';pointer-events:none;"></div>' +
        '</div></label>' +

        // Separator
        '<div style="width:1px;height:22px;background:#e2e8f0;"></div>' +

        // Tombol Terapkan
        '<button id="__fpApply" type="button" ' +
        'style="background:#0ea5e9;color:#fff;border:none;border-radius:8px;' +
        'padding:5px 11px;font-size:.78rem;font-weight:600;cursor:pointer;' +
        'display:flex;align-items:center;gap:4px;white-space:nowrap;' +
        'transition:background .15s;">' +
        '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>' +
        'Terapkan</button>' +

        // Tombol tutup
        '<button id="__fpClose" type="button" ' +
        'style="background:none;border:none;color:#94a3b8;cursor:pointer;' +
        'padding:2px;display:flex;align-items:center;border-radius:5px;' +
        'transition:color .15s;">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
        '</button>';

    document.body.appendChild(panel);

    // Live preview swatch saat pilih warna
    document.getElementById('__fpHeader').addEventListener('input', function () {
        document.getElementById('__fpHeaderSwatch').style.background = this.value;
    });
    document.getElementById('__fpStripe').addEventListener('input', function () {
        document.getElementById('__fpStripeSwatch').style.background = this.value;
    });

    // Terapkan
    document.getElementById('__fpApply').addEventListener('click', function () {
        td.headerColor = document.getElementById('__fpHeader').value;
        td.stripeColor = document.getElementById('__fpStripe').value;
        _liveRerenderTable({ td: td, fabricObj: obj, pgData: pgData });
        saveStateForPage(pgData);
        renderPageThumbnails();
    });

    // Tutup
    document.getElementById('__fpClose').addEventListener('click', _removeFloatPanel);

    // Hover effect tombol
    var applyBtn = document.getElementById('__fpApply');
    applyBtn.addEventListener('mouseenter', function () { this.style.background = '#0284c7'; });
    applyBtn.addEventListener('mouseleave', function () { this.style.background = '#0ea5e9'; });
}

function _removeFloatPanel() {
    var p = document.getElementById('__tableStylePanel');
    if (p) p.remove();
}

function _calcPanelPos(pgData, obj) {
    var wrapper = pgData.canvas.wrapperEl;
    var rect    = wrapper.getBoundingClientRect();
    var zoom    = pgData.canvas.getZoom();
    var panelTop  = rect.top  + (obj.top  + (obj.height || 0) * (obj.scaleY || 1)) * zoom + 10;
    var panelLeft = rect.left + obj.left  * zoom;
    // Jaga supaya tidak keluar viewport
    panelTop  = Math.min(panelTop,  window.innerHeight - 80);
    panelLeft = Math.min(panelLeft, window.innerWidth  - 360);
    panelLeft = Math.max(panelLeft, 8);
    return { top: panelTop, left: panelLeft };
}

function _updateFloatPanelPos(pgData, obj) {
    var p = document.getElementById('__tableStylePanel');
    if (!p) return;
    var pos = _calcPanelPos(pgData, obj);
    p.style.top  = pos.top  + 'px';
    p.style.left = pos.left + 'px';
}

// ============================================================
// PAGE EVENTS
// ============================================================
function attachPageEvents(pgData) {
    var fc = pgData.canvas;

    fc.on('mouse:down', function () {
        var idx = pages.indexOf(pgData);
        if (idx !== -1 && idx !== currentPage) setTimeout(function () { switchPage(idx); }, 0);
    });

    fc.on('mouse:move', function (opt) {
        if (pgData !== pages[currentPage]) return;
        var p = opt.absolutePointer || opt.pointer;
        if (!p) return;
        if (_rulerRafId) return;
        _rulerRafId = requestAnimationFrame(function () {
            _rulerRafId = null;
            drawRulerCursor(p.x, p.y);
        });
    });

    fc.on('mouse:down', function (e) {
        if (pgData !== pages[currentPage]) return;
        pgData._pageSnapPoints = [];
        pgData._objGuidePoints = [];
        pgData._activeSnapX = null;
        pgData._activeSnapY = null;
        pgData._prevSnapX   = null;
        pgData._prevSnapY   = null;
        pgData._snapEngagePointer = null;

        if (!e.target) return;
        var obj = e.target;

        pgData._pageSnapPoints = [
            { ref: CANVAS_W / 2, type: 'x' }, { ref: CANVAS_H / 2, type: 'y' },
            { ref: MARGIN,       type: 'x' }, { ref: CANVAS_W - MARGIN, type: 'x' },
            { ref: MARGIN,       type: 'y' }, { ref: CANVAS_H - MARGIN, type: 'y' },
            { ref: 0,            type: 'x' }, { ref: CANVAS_W, type: 'x' },
            { ref: 0,            type: 'y' }, { ref: CANVAS_H, type: 'y' },
        ];

        fc.getObjects().forEach(function (o) {
            if (o === obj || !o.selectable) return;
            if (o.name && (o.name.startsWith('__') || o.name.startsWith('kop_line'))) return;
            var b = o.getBoundingRect(true);
            pgData._objGuidePoints.push(
                { ref: b.left,                type: 'x', origin: o },
                { ref: b.left + b.width / 2,  type: 'x', origin: o },
                { ref: b.left + b.width,       type: 'x', origin: o },
                { ref: b.top,                 type: 'y', origin: o },
                { ref: b.top + b.height / 2,  type: 'y', origin: o },
                { ref: b.top + b.height,       type: 'y', origin: o }
            );
        });
    });

    fc.on('object:moving', function (e) {
        var obj     = e.target;
        var pointer = e.pointer;
        var zoom    = fc.getZoom();
        var T_IN    = SNAP_THRESHOLD / zoom;
        var T_OUT   = SNAP_RELEASE   / zoom;

        var oBox = obj.getBoundingRect(true);
        var oCX  = oBox.left + oBox.width  / 2;
        var oCY  = oBox.top  + oBox.height / 2;
        var newSnapX = null, newSnapY = null;

        pgData._pageSnapPoints.forEach(function (sp) {
            if (sp.type === 'x' && !newSnapX) {
                var wasSnapped = pgData._activeSnapX && Math.abs(pgData._activeSnapX.ref - sp.ref) < 0.5;
                if (wasSnapped) {
                    if (Math.abs(pointer.x - sp.ref) < T_OUT) newSnapX = sp;
                } else {
                    var edges = [oBox.left, oCX, oBox.left + oBox.width];
                    for (var i = 0; i < 3; i++) { if (Math.abs(edges[i] - sp.ref) < T_IN) { newSnapX = sp; break; } }
                }
            }
            if (sp.type === 'y' && !newSnapY) {
                var wasSnappedY = pgData._activeSnapY && Math.abs(pgData._activeSnapY.ref - sp.ref) < 0.5;
                if (wasSnappedY) {
                    if (Math.abs(pointer.y - sp.ref) < T_OUT) newSnapY = sp;
                } else {
                    var edgesY = [oBox.top, oCY, oBox.top + oBox.height];
                    for (var j = 0; j < 3; j++) { if (Math.abs(edgesY[j] - sp.ref) < T_IN) { newSnapY = sp; break; } }
                }
            }
        });

        if (newSnapX) {
            var dL = Math.abs(oBox.left - newSnapX.ref);
            var dC = Math.abs(oCX - newSnapX.ref);
            var dR = Math.abs(oBox.left + oBox.width - newSnapX.ref);
            var mD = Math.min(dL, dC, dR);
            if (mD === dL) obj.set('left', newSnapX.ref);
            else if (mD === dC) obj.set('left', newSnapX.ref - oBox.width / 2);
            else obj.set('left', newSnapX.ref - oBox.width);
            obj.setCoords();
        }
        if (newSnapY) {
            var dT  = Math.abs(oBox.top - newSnapY.ref);
            var dCY = Math.abs(oCY - newSnapY.ref);
            var dB  = Math.abs(oBox.top + oBox.height - newSnapY.ref);
            var mDY = Math.min(dT, dCY, dB);
            if (mDY === dT)  obj.set('top', newSnapY.ref);
            else if (mDY === dCY) obj.set('top', newSnapY.ref - oBox.height / 2);
            else obj.set('top', newSnapY.ref - oBox.height);
            obj.setCoords();
        }

        pgData._activeSnapX = newSnapX;
        pgData._activeSnapY = newSnapY;

        oBox = obj.getBoundingRect(true);
        oCX  = oBox.left + oBox.width  / 2;
        oCY  = oBox.top  + oBox.height / 2;

        var visibleObjGuides = [];
        var VISUAL_T = 4 / zoom;
        var seenX = {}, seenY = {};
        pgData._objGuidePoints.forEach(function (g) {
            var key = Math.round(g.ref);
            if (g.type === 'x' && !seenX[key]) {
                var edges = [oBox.left, oCX, oBox.left + oBox.width];
                for (var i = 0; i < 3; i++) { if (Math.abs(edges[i] - g.ref) < VISUAL_T) { visibleObjGuides.push(g); seenX[key] = true; break; } }
            } else if (g.type === 'y' && !seenY[key]) {
                var edgesY = [oBox.top, oCY, oBox.top + oBox.height];
                for (var j = 0; j < 3; j++) { if (Math.abs(edgesY[j] - g.ref) < VISUAL_T) { visibleObjGuides.push(g); seenY[key] = true; break; } }
            }
        });

        var hadSnap = !!(pgData._prevSnapX || pgData._prevSnapY);
        var hasSnap = !!(newSnapX || newSnapY);
        var hasAny  = hasSnap || visibleObjGuides.length > 0;

        if (hasAny) {
            if (!hadSnap && pgData._guideAlpha < 0.9) {
                fadeGuides(pgData, 1, obj, newSnapX, newSnapY, visibleObjGuides);
            } else {
                if (pgData._guideAlphaRafId) { cancelAnimationFrame(pgData._guideAlphaRafId); pgData._guideAlphaRafId = null; }
                pgData._guideAlpha = 1;
                drawSmartGuides(pgData, obj, newSnapX, newSnapY, visibleObjGuides, 1);
            }
        } else {
            if (pgData._guideAlpha > 0) fadeGuides(pgData, 0, null, null, null, null);
        }

        pgData._prevSnapX = newSnapX;
        pgData._prevSnapY = newSnapY;

        scheduleCoordUpdate(obj);
    });

    fc.on('mouse:up', function () {
        pgData._activeSnapX = null; pgData._activeSnapY = null;
        pgData._prevSnapX   = null; pgData._prevSnapY   = null;
        pgData._pageSnapPoints = []; pgData._objGuidePoints = [];
        if (pgData._guideAlpha > 0) fadeGuides(pgData, 0, null, null, null, null);
    });

    fc.on('object:modified', function () {
        pgData._prevSnapX = null; pgData._prevSnapY = null;
        if (pgData._guideAlpha > 0) fadeGuides(pgData, 0, null, null, null, null);
        saveStateForPage(pgData);
        renderPageThumbnails();
    });

    fc.on('selection:created', function () { updateToolbar(); });
    fc.on('selection:updated', updateToolbar);
    fc.on('selection:cleared', function () {
        document.getElementById('formatToolbar').style.display = 'none';
        updateCoords(null);
    });
    fc.on('object:scaling',  function (e) { scheduleCoordUpdate(e.target); });
    fc.on('object:rotating', function (e) { scheduleCoordUpdate(e.target); });
    fc.on('object:added',    function () { saveStateForPage(pgData); });
    fc.on('object:removed',  function () { saveStateForPage(pgData); });

    // ── Pasang sistem table handles + style panel ──────────
    attachTableHandles(pgData);
    attachStylePanel(pgData);
}

// ============================================================
// UNDO / REDO
// ============================================================
function saveStateForPage(pgData) {
    if (pgData._isSaving) return;
    clearTimeout(pgData._saveTimer);
    pgData._saveTimer = setTimeout(function () {
        var json = JSON.stringify(pgData.canvas.toJSON(['name', 'excludeFromExport']));
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
        pg.canvas.renderAll(); pg._isSaving = false; renderPageThumbnails();
    });
}

function redo() {
    var pg = pages[currentPage];
    if (!pg || !pg._historyRedo.length) return;
    pg._isSaving = true;
    var n = pg._historyRedo.pop();
    pg._history.push(n);
    pg.canvas.loadFromJSON(n, function () {
        pg.canvas.renderAll(); pg._isSaving = false; renderPageThumbnails();
    });
}

// ============================================================
// VARIABLE REGISTRY
// ============================================================
var variableRegistry = [];

function registerVariable(name, label) {
    if (variableRegistry.find(function (v) { return v.name === name; })) return;
    variableRegistry.push({ name: name, label: label || name });
    renderVariableChips();
}

function renderVariableChips() {
    var panel = document.getElementById('variablePanel');
    var el    = document.getElementById('variableChips');
    if (!variableRegistry.length) { panel.style.display = 'none'; return; }
    panel.style.display = 'block';
    el.innerHTML = '';
    variableRegistry.forEach(function (v) {
        var c = document.createElement('button');
        c.type = 'button';
        c.className = 'btn btn-primary btn-sm d-flex align-items-center gap-1';
        c.style.cssText = 'border-radius:20px;font-size:0.8rem';
        c.innerHTML =
            '<i class="bi bi-braces" style="font-size:0.7rem"></i>' +
            '<span>' + escHtml(v.label) + '</span>' +
            '<code style="font-size:0.7rem;color:rgba(255,255,255,0.75);margin-left:2px">{{' + escHtml(v.name) + '}}</code>';
        c.addEventListener('click', function () { placeVariableOnCanvas(v.name); });
        el.appendChild(c);
    });
}

function placeVariableOnCanvas(name) {
    var canvas = getCanvas();
    if (!canvas) return;
    var t = new fabric.Textbox('{{' + name + '}}', {
        left: MARGIN + 10, top: 200, width: 220,
        fontSize: 16, fontFamily: 'Arial', fill: '#1a56db', name: 'var_' + name,
    });
    canvas.add(t); canvas.setActiveObject(t); canvas.requestRenderAll();
}

// ============================================================
// MODAL: VARIABEL
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var ni = document.getElementById('varNameInput');
    var li = document.getElementById('varLabelInput');
    var pc = document.getElementById('varPreviewCode');
    if (ni) {
        ni.addEventListener('input', function () {
            ni.classList.remove('is-invalid');
            pc.textContent = '{{ ' + (ni.value.trim().replace(/\s+/g, '_').toLowerCase() || 'nama_variabel') + ' }}';
        });
    }
    var presetBtns = document.getElementById('presetButtons');
    if (presetBtns) {
        presetBtns.addEventListener('click', function (e) {
            var b = e.target.closest('button[data-name]');
            if (!b) return;
            ni.value = b.dataset.name; li.value = b.dataset.label;
            pc.textContent = '{{ ' + b.dataset.name + ' }}';
            ni.classList.remove('is-invalid');
        });
    }
    var btnConfirm = document.getElementById('btnConfirmVariable');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            var raw = ni.value.trim();
            if (!raw) { ni.classList.add('is-invalid'); ni.focus(); return; }
            var name  = raw.replace(/\s+/g, '_').toLowerCase();
            var label = li.value.trim() || name;
            registerVariable(name, label);
            placeVariableOnCanvas(name);
            ni.value = ''; li.value = '';
            pc.textContent = '{{ nama_variabel }}';
            bootstrap.Modal.getInstance(document.getElementById('modalVariable')).hide();
        });
    }
    if (ni) {
        ni.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btnConfirmVariable').click(); }
        });
    }
    var btnDeselect = document.getElementById('btnDeselect');
    if (btnDeselect) {
        btnDeselect.addEventListener('click', function () {
            var canvas = getCanvas();
            if (!canvas) return;
            canvas.discardActiveObject(); canvas.requestRenderAll();
        });
    }
});

// ============================================================
// MODAL: KOP SURAT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var btnKop = document.getElementById('btnAddKop');
    if (!btnKop) return;
    btnKop.addEventListener('click', function () {
        var canvas    = getCanvas();
        if (!canvas) return;
        var logoSize  = parseInt(document.getElementById('kopLogoSize').value) || 90;
        var line1 = document.getElementById('kopLine1').value.trim();
        var line2 = document.getElementById('kopLine2').value.trim();
        var line3 = document.getElementById('kopLine3').value.trim();
        var line4 = document.getElementById('kopLine4').value.trim();
        var line5 = document.getElementById('kopLine5').value.trim();
        var line6 = document.getElementById('kopLine6').value.trim();
        var borderType = document.getElementById('kopBorderStyle').value;
        var logoFile   = document.getElementById('kopLogoFile').files[0];

        function buildKop(logoDataUrl) {
            canvas.getObjects().filter(function (o) {
                return o.name && o.name.startsWith('kop_');
            }).forEach(function (o) { canvas.remove(o); });

            var CW = CANVAS_W, KT = 20, LW = logoSize, TX = LW + 34, TW = CW - TX - 20;
            var textItems = [
                line1 ? [line1, 11, 'bold',   '#000000'] : null,
                line2 ? [line2, 16, 'bold',   '#c0392b'] : null,
                line3 ? [line3, 12, 'bold',   '#1a5276'] : null,
                line4 ? [line4, 10, 'normal', '#000000'] : null,
                line5 ? [line5, 10, 'normal', '#000000'] : null,
            ].filter(Boolean);
            var lineHeights = textItems.map(function (t) { return t[1] * 1.4; });
            var totalH = lineHeights.reduce(function (a, b) { return a + b; }, 0);
            var curY   = KT + Math.max(0, (LW - totalH) / 2);

            textItems.forEach(function (t, i) {
                canvas.add(new fabric.Textbox(t[0], {
                    left: TX, top: curY, width: TW, fontSize: t[1],
                    fontFamily: 'Arial', fontWeight: t[2], textAlign: 'center',
                    fill: t[3], name: 'kop_text',
                }));
                curY += lineHeights[i];
            });

            if (line6) canvas.add(new fabric.Textbox(line6, {
                left: 20, top: KT + LW + 4, width: LW + 30, fontSize: 9,
                fontFamily: 'Arial', fill: '#000', name: 'kop_npsn',
            }));

            var lineY = KT + LW + (line6 ? 16 : 4);
            if (borderType === 'double') {
                canvas.add(new fabric.Line([20, lineY, CW - 20, lineY],           { stroke: '#000', strokeWidth: 3, name: 'kop_line', selectable: false, evented: false }));
                canvas.add(new fabric.Line([20, lineY + 5, CW - 20, lineY + 5], { stroke: '#000', strokeWidth: 1, name: 'kop_line', selectable: false, evented: false }));
            } else if (borderType === 'single') {
                canvas.add(new fabric.Line([20, lineY, CW - 20, lineY],           { stroke: '#000', strokeWidth: 2, name: 'kop_line', selectable: false, evented: false }));
            }

            function addLogoObj(imgObj) {
                imgObj.scaleToWidth(LW); imgObj.scaleToHeight(LW);
                imgObj.set({ left: 20, top: KT, name: 'kop_logo' });
                canvas.add(imgObj); canvas.sendToBack(imgObj);
                canvas.requestRenderAll(); saveState();
                var modalEl = document.getElementById('modalKop');
                if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
            }

            if (logoDataUrl) {
                fabric.Image.fromURL(logoDataUrl, addLogoObj);
            } else {
                canvas.add(new fabric.Rect({
                    left: 20, top: KT, width: LW, height: LW,
                    fill: '#f0f0f0', stroke: '#bbb', strokeWidth: 1,
                    rx: 4, ry: 4, name: 'kop_logo',
                }));
                canvas.add(new fabric.Text('{{logo}}', {
                    left: 20 + LW / 2, top: KT + LW / 2, fontSize: 9,
                    fontFamily: 'Arial', fill: '#888',
                    originX: 'center', originY: 'center',
                    name: 'kop_logo_label', selectable: false, evented: false,
                }));
                canvas.requestRenderAll(); saveState();
                var modalEl = document.getElementById('modalKop');
                if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
            }
        }

        if (logoFile) {
            var r = new FileReader();
            r.onload = function (ev) { buildKop(ev.target.result); };
            r.readAsDataURL(logoFile);
        } else buildKop(null);
    });
});

// ============================================================
// MODAL: TABLE INSERT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var btnInsert = document.getElementById('btnInsertTable');
    if (!btnInsert) return;
    btnInsert.addEventListener('click', function () {
        var activeTab = document.querySelector('#tableTabs .nav-link.active');
        if (!activeTab) return;
        var href = activeTab.getAttribute('href');
        if (href === '#tabCustom')          insertCustomTable();
        else if (href === '#tabKelasMapel') insertKelasMapelTable();
        else if (href === '#tabRaport')     insertRaportTable();
        else if (href === '#tabProgramUnggulan') insertUnggulanTable();
        else if (href === '#tabEkskul')     insertEkskulTable();
        else if (href === '#tabAbsensi')    insertAbsensiTable();
        else if (href === '#tabTTD')        insertTTDArea();
        var modalEl = document.getElementById('modalTable');
        if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
    });
});

// ============================================================
// TABLE HELPERS
// ============================================================
function createTablePlaceholder(tableData, sx, sy) {
    var canvas = getCanvas();
    if (!canvas) return;
    var pg = pages[currentPage];
    if (!pg) return;
    var id = 'tbl_' + (++tableCounter);
    pg.tableStore[id] = tableData;
    if (tableData.autoRegisterVars) tableData.autoRegisterVars.forEach(function (v) { registerVariable(v.name, v.label); });

    var occupied = canvas.getObjects().map(function (o) { return { x: o.left, y: o.top }; });
    var dropY = sy;
    while (occupied.some(function (p) { return Math.abs(p.x - sx) < 20 && Math.abs(p.y - dropY) < 20; })) dropY += 20;

    var offscreen   = renderTableToCanvas(tableData);
    var finalCanvas = document.createElement('canvas');
    finalCanvas.width  = tableData.totalWidth;
    finalCanvas.height = tableData.totalHeight;
    finalCanvas.getContext('2d').drawImage(offscreen, 0, 0, tableData.totalWidth, tableData.totalHeight);

    fabric.Image.fromURL(finalCanvas.toDataURL(), function (img) {
        img.set({
            left: sx, top: dropY, name: id,
            selectable: true, evented: true,
            hasBorders: true, hasControls: true, lockRotation: true,
        });
        img._isTable = true;
        canvas.add(img); canvas.setActiveObject(img);
        canvas.requestRenderAll(); saveState();
    });
}

function renderTableToCanvas(td) {
    var DPR  = Math.min(window.devicePixelRatio || 2, 3);
    var rows = td.rows, colW = td.colWidths, rowHeights = td.rowHeights;
    var totalCols = colW.length;
    var hdrColor = td.headerColor || '#1a5276';
    var strColor = td.stripeColor || '#eaf2ff';
    var bdColor  = td.borderColor || '#adb5bd';

    var oc = document.createElement('canvas');
    oc.width  = td.totalWidth  * DPR;
    oc.height = td.totalHeight * DPR;
    var ctx = oc.getContext('2d');
    ctx.scale(DPR, DPR);

    var totalW = 0;
    colW.forEach(function (w) { totalW += (w || 60); });

    var curY = 0;
    rows.forEach(function (row, ri) {
        var rH = rowHeights[ri] || 20;
        var isMergedRow = row[0] && row[0].isMerged;

        if (isMergedRow) {
            ctx.fillStyle = hdrColor;
            ctx.fillRect(0, curY, totalW, rH);
            ctx.strokeStyle = bdColor; ctx.lineWidth = 0.5;
            ctx.strokeRect(0.25, curY + 0.25, totalW - 0.5, rH - 0.5);
            var txt = (row[0].text || '').replace(/\{\{[^}]+\}\}/g, '…').replace(/\n/g, ' ');
            if (txt) {
                ctx.fillStyle = '#ffffff'; ctx.font = 'bold 8px Arial';
                ctx.textBaseline = 'middle'; ctx.textAlign = 'center';
                var display = txt;
                while (display.length > 1 && ctx.measureText(display).width > totalW - 8) display = display.slice(0, -1);
                if (display !== txt) display += '…';
                ctx.fillText(display, totalW / 2, curY + rH / 2);
            }
        } else {
            var isHeader = (ri === 0);
            var bg       = isHeader ? hdrColor : (ri % 2 === 1 ? '#ffffff' : strColor);
            var textFill = isHeader ? '#ffffff' : '#212529';
            var fw       = isHeader ? 'bold' : 'normal';
            var fs       = isHeader ? 9 : 8;
            var curX = 0;

            for (var ci = 0; ci < totalCols; ci++) {
                var cell = row[ci] || { text: '', align: 'left' };
                var cW   = colW[ci] || 60;
                ctx.fillStyle = bg;
                ctx.fillRect(curX, curY, cW, rH);
                ctx.strokeStyle = bdColor; ctx.lineWidth = 0.5;
                ctx.strokeRect(curX + 0.25, curY + 0.25, cW - 0.5, rH - 0.5);
                var rawText = (cell.text || '').replace(/\{\{[^}]+\}\}/g, '…').replace(/\n/g, ' ');
                if (rawText) {
                    ctx.fillStyle = textFill;
                    ctx.font      = fw + ' ' + fs + 'px Arial';
                    ctx.textBaseline = 'middle';
                    var align  = cell.align || (ci === 0 ? 'center' : 'left');
                    ctx.textAlign = align;
                    var maxW  = cW - 6;
                    var tx    = align === 'center' ? curX + cW / 2 : curX + 3;
                    var disp  = rawText;
                    while (disp.length > 1 && ctx.measureText(disp).width > maxW) disp = disp.slice(0, -1);
                    if (disp !== rawText) disp += '…';
                    ctx.fillText(disp, tx, curY + rH / 2);
                }
                curX += cW;
            }
        }
        curY += rH;
    });
    return oc;
}

function insertCustomTable() {
    var rows      = parseInt(document.getElementById('tableRows').value)      || 5;
    var cols      = parseInt(document.getElementById('tableCols').value)      || 4;
    var tWidth    = parseInt(document.getElementById('tableWidth').value)     || 750;
    var rowH      = parseInt(document.getElementById('tableRowHeight').value) || 24;
    var hdrColor  = document.getElementById('tableHeaderColor').value;
    var strColor  = document.getElementById('tableStripeColor').value;
    var bdColor   = document.getElementById('tableBorderColor').value;
    var hasNo     = document.getElementById('tableHasNo').checked;
    var headersRaw= document.getElementById('tableHeaders').value.split(',').map(function (s) { return s.trim(); });

    var hdrRow = [];
    for (var c = 0; c < cols; c++) hdrRow.push({ text: headersRaw[c] || 'Kolom ' + (c + 1), align: 'center' });

    var tableRows = [hdrRow];
    for (var r = 0; r < rows; r++) {
        var row = [];
        for (var cc = 0; cc < cols; cc++) row.push({ text: (hasNo && cc === 0) ? String(r + 1) : '', align: cc === 0 ? 'center' : 'left' });
        tableRows.push(row);
    }

    var colSpecs = [];
    if (hasNo) { colSpecs.push(28); for (var i = 1; i < cols; i++) colSpecs.push(null); }
    else { for (var j = 0; j < cols; j++) colSpecs.push(null); }

    var colWidths = buildColWidths(colSpecs, tWidth);
    createTablePlaceholder({
        type: 'custom', totalWidth: tWidth,
        totalHeight: (rowH + 4) + rows * rowH,
        colWidths: colWidths,
        rowHeights: buildRowHeights(rows, rowH, rowH + 4),
        rows: tableRows, headerColor: hdrColor, stripeColor: strColor, borderColor: bdColor,
    }, MARGIN, 200);
}

function insertKelasMapelTable() {
    var tType    = document.getElementById('kelasMapelType').value;
    var kelasId  = document.getElementById('kelasMapelKelas').value;
    var tWidth   = parseInt(document.getElementById('kelasMapelWidth').value)  || 754;
    var rowH     = parseInt(document.getElementById('kelasMapelRowH').value)   || 24;
    var hdrColor = document.getElementById('kelasMapelHeaderColor').value;
    var kolomRaw = document.getElementById('kelasMapelKolom').value.split(',').map(function (s) { return s.trim(); }).filter(Boolean);
    var autoVar  = document.getElementById('kelasMapelAutoVar').checked;
    var kelasList= window.EDITOR_KELAS_LIST || [], mapelList = window.EDITOR_MAPEL_LIST || [];
    var tableRows= [], autoVars = [], colSpecs = [], hdrRow = [];

    if (tType === 'daftar_kelas') {
        hdrRow = [{ text: 'No', align: 'center' }, { text: 'Nama Kelas', align: 'left' }, { text: 'Kategori', align: 'left' }];
        kolomRaw.forEach(function (k) { hdrRow.push({ text: k, align: 'center' }); });
        colSpecs = [28, null, 100]; kolomRaw.forEach(function () { colSpecs.push(80); });
        tableRows.push(hdrRow);
        var filteredKelas = kelasId ? kelasList.filter(function (k) { return String(k.id) === String(kelasId); }) : kelasList;
        filteredKelas.forEach(function (kls, idx) {
            var varName = 'kelas_' + (kls.slug || String(kls.id));
            if (autoVar) autoVars.push({ name: varName, label: kls.name });
            var row = [{ text: String(idx + 1), align: 'center' }, { text: kls.name, align: 'left' }, { text: kls.category_kelas || '', align: 'left' }];
            kolomRaw.forEach(function () { row.push({ text: autoVar ? '{{' + varName + '}}' : '', align: 'center' }); });
            tableRows.push(row);
        });
    } else if (tType === 'daftar_mapel') {
        hdrRow = [{ text: 'No', align: 'center' }];
        kolomRaw.forEach(function (k) { hdrRow.push({ text: k, align: 'center' }); });
        colSpecs = [28, null]; kolomRaw.forEach(function () { colSpecs.push(80); });
        tableRows.push(hdrRow);
        mapelList.forEach(function (mp, idx) {
            var varName = 'mapel_' + (mp.slug || String(mp.id));
            if (autoVar) autoVars.push({ name: varName, label: mp.name });
            var row = [{ text: String(idx + 1), align: 'center' }, { text: mp.name, align: 'left' }];
            kolomRaw.forEach(function () { row.push({ text: autoVar ? '{{' + varName + '}}' : '', align: 'center' }); });
            tableRows.push(row);
        });
    } else if (tType === 'jadwal_kelas') {
        hdrRow = [{ text: 'No', align: 'center' }, { text: 'Mata Pelajaran', align: 'left' }, { text: 'Hari', align: 'center' }, { text: 'Jam', align: 'center' }];
        kolomRaw.forEach(function (k) { hdrRow.push({ text: k, align: 'center' }); });
        colSpecs = [28, null, 80, 80]; kolomRaw.forEach(function () { colSpecs.push(80); });
        tableRows.push(hdrRow);
        mapelList.forEach(function (mp, idx) {
            var row = [{ text: String(idx + 1), align: 'center' }, { text: mp.name, align: 'left' },
                { text: autoVar ? '{{hari_' + (mp.slug || idx) + '}}' : '', align: 'center' },
                { text: autoVar ? '{{jam_'  + (mp.slug || idx) + '}}' : '', align: 'center' }];
            kolomRaw.forEach(function () { row.push({ text: '', align: 'center' }); });
            tableRows.push(row);
        });
    }

    if (tableRows.length <= 1) { alert('Tidak ada data kelas/mapel.'); return; }
    var colWidths = buildColWidths(colSpecs, tWidth);
    var totalH    = (rowH + 4) + (tableRows.length - 1) * rowH;
    createTablePlaceholder({
        type: 'kelas_mapel', totalWidth: tWidth, totalHeight: totalH,
        colWidths: colWidths, rowHeights: buildRowHeights(tableRows.length - 1, rowH, rowH + 4),
        rows: tableRows, headerColor: hdrColor, stripeColor: '#f0f7ff', borderColor: '#adb5bd',
        autoRegisterVars: autoVar ? autoVars : [],
    }, MARGIN, 200);
}

var _raportKelompoks = [], _raportAktifKelompok = {};

(function loadTingkatKelas() {
    fetch('/dashboard/surat/templates/api/kelas-list', { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
        .then(function (data) {
            var sel = document.getElementById('raportTingkatKelas');
            if (!sel) return;
            data.forEach(function (tk) {
                var opt = document.createElement('option');
                opt.value = tk.id;
                var categoryLabel = '';
                if (tk.category_kelas) {
                    try {
                        var parsed = JSON.parse(tk.category_kelas);
                        categoryLabel = ' (' + (Array.isArray(parsed) ? parsed.join(', ') : tk.category_kelas) + ')';
                    } catch (e) { categoryLabel = ' (' + tk.category_kelas + ')'; }
                }
                opt.textContent = tk.name + categoryLabel;
                sel.appendChild(opt);
            });
            if (!window.EDITOR_KELAS_LIST || !window.EDITOR_KELAS_LIST.length) window.EDITOR_KELAS_LIST = data;
        }).catch(function () {});
})();

document.addEventListener('DOMContentLoaded', function () {
    var selKelas = document.getElementById('raportTingkatKelas');
    var btnLoad  = document.getElementById('btnLoadMapel');
    if (selKelas) selKelas.addEventListener('change', function () {
        var id = this.value;
        if (btnLoad) btnLoad.disabled = !id;
        if (id) loadMapelFromAPI(id);
    });
    if (btnLoad) btnLoad.addEventListener('click', function () {
        var id = document.getElementById('raportTingkatKelas').value;
        if (id) loadMapelFromAPI(id);
    });
});

function loadMapelFromAPI(tingkatId) {
    var preview = document.getElementById('raportMapelPreview');
    if (!preview) return;
    preview.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat...</div>';
    fetch('/dashboard/surat/templates/api/mapel-list?kelas_id=' + tingkatId, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error(); return r.json(); })
        .then(function (data) {
            var mapels = Array.isArray(data) ? data : (data.kelompoks ? null : []);
            if (mapels) {
                _raportKelompoks = [{ kelompok: { id: 'all', nama: 'Mata Pelajaran', warna_header: '#1a5276' }, mapels: mapels.map(function (mp) {
                    var slug = (mp.slug || mp.name.toLowerCase().replace(/\s+/g, '_')).replace(/[^a-z0-9_]/g, '');
                    return { nama: mp.name, var_nilai: 'nilai_' + slug, var_capaian: 'capaian_' + slug };
                })}];
            } else _raportKelompoks = data.kelompoks || [];
            _raportAktifKelompok = {};
            _raportKelompoks.forEach(function (k) { _raportAktifKelompok[k.kelompok.id] = true; });
            renderRaportPreview(); renderKelompokToggles();
        }).catch(function () { preview.innerHTML = '<div class="alert alert-danger small py-2">Gagal memuat mapel.</div>'; });
}

function renderRaportPreview() {
    var preview = document.getElementById('raportMapelPreview');
    if (!preview) return;
    if (!_raportKelompoks.length) { preview.innerHTML = '<div class="alert alert-warning small py-2">Tidak ada mapel.</div>'; return; }
    var html = '';
    _raportKelompoks.forEach(function (grp) {
        var kel = grp.kelompok;
        if (_raportAktifKelompok[kel.id] === false) return;
        html += '<div class="mb-2"><div class="px-2 py-1 rounded-top small fw-bold text-white d-flex justify-content-between" style="background:' + (kel.warna_header || '#1a5276') + '">';
        html += '<span>' + escHtml(kel.nama) + '</span><span class="badge bg-white text-dark">' + grp.mapels.length + ' mapel</span></div>';
        html += '<table class="table table-sm table-bordered mb-0" style="font-size:0.8rem"><thead class="table-light"><tr><th style="width:30px">No</th><th>Mata Pelajaran</th><th style="width:80px">Var Nilai</th><th style="width:100px">Var Capaian</th></tr></thead><tbody>';
        grp.mapels.forEach(function (mp, i) {
            html += '<tr><td class="text-center">' + (i + 1) + '</td><td>' + escHtml(mp.nama) + '</td><td><code class="text-primary small">{{' + mp.var_nilai + '}}</code></td><td><code class="text-success small">{{' + mp.var_capaian + '}}</code></td></tr>';
        });
        html += '</tbody></table></div>';
    });
    preview.innerHTML = html || '<p class="text-muted small">Semua kelompok di-nonaktifkan.</p>';
}

function renderKelompokToggles() {
    var container = document.getElementById('raportKelompokToggle');
    if (!container) return;
    container.innerHTML = '';
    _raportKelompoks.forEach(function (grp) {
        var kel = grp.kelompok, aktif = _raportAktifKelompok[kel.id] !== false;
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm ' + (aktif ? 'btn-primary' : 'btn-outline-secondary');
        btn.style.fontSize = '0.75rem';
        btn.textContent = kel.nama;
        btn.addEventListener('click', function () {
            _raportAktifKelompok[kel.id] = !_raportAktifKelompok[kel.id];
            renderRaportPreview(); renderKelompokToggles();
        });
        container.appendChild(btn);
    });
}

function insertRaportTable() {
    var tWidth   = parseInt(document.getElementById('raportWidth').value)     || 754;
    var hdrColor = document.getElementById('raportHeaderColor').value;
    var rowH     = parseInt(document.getElementById('raportRowHeight').value) || 40;
    var autoVar  = document.getElementById('raportAutoVar').checked;

    if (!_raportKelompoks.length) {
        var ae = document.getElementById('raportNoKelasAlert');
        if (ae) ae.style.display = 'block';
        return;
    }
    var ae2 = document.getElementById('raportNoKelasAlert');
    if (ae2) ae2.style.display = 'none';

    var aktif = _raportKelompoks.filter(function (grp) { return _raportAktifKelompok[grp.kelompok.id] !== false; });
    var colSpecs = [28, null, 52, null];
    var colWidths= buildColWidths(colSpecs, tWidth);
    var hdrNames = ['No', 'Muatan Pelajaran', 'Nilai\nAkhir', 'Capaian Kompetensi'];
    var tableRows= [], autoVars = [];
    tableRows.push(hdrNames.map(function (h) { return { text: h, align: 'center' }; }));

    var no = 1;
    aktif.forEach(function (grp) {
        grp.mapels.forEach(function (mp) {
            if (autoVar) {
                autoVars.push({ name: mp.var_nilai,   label: 'Nilai '   + mp.nama.substring(0, 18) });
                autoVars.push({ name: mp.var_capaian, label: 'Capaian ' + mp.nama.substring(0, 15) });
            }
            tableRows.push([
                { text: String(no++), align: 'center' },
                { text: mp.nama,      align: 'left'   },
                { text: autoVar ? '{{' + mp.var_nilai   + '}}' : '', align: 'center' },
                { text: autoVar ? '{{' + mp.var_capaian + '}}' : '', align: 'left'   },
            ]);
        });
    });

    var rowHeights = tableRows.map(function (row, ri) { return ri === 0 ? rowH + 4 : rowH; });
    createTablePlaceholder({
        type: 'raport', totalWidth: tWidth,
        totalHeight: rowHeights.reduce(function (a, b) { return a + b; }, 0),
        colWidths: colWidths, rowHeights: rowHeights, rows: tableRows,
        headerColor: hdrColor, stripeColor: '#f5f9ff', borderColor: '#888888',
        autoRegisterVars: autoVar ? autoVars : [],
    }, MARGIN, 200);
}

function insertUnggulanTable() {
    var namaProgram = document.getElementById('unggulanNama').value.trim() || 'PROGRAM UNGGULAN';
    var itemsRaw    = document.getElementById('unggulanItems').value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
    var kolomRaw    = document.getElementById('unggulanKolom').value.split(',').map(function (s) { return s.trim(); });
    var tWidth      = parseInt(document.getElementById('unggulanWidth').value)  || 754;
    var hdrColor    = document.getElementById('unggulanHeaderColor').value;
    var mergeHeader = document.getElementById('unggulanMergeHeader').checked;
    var cols   = 2 + kolomRaw.length;
    var hdrRow = [{ text: 'No', align: 'center' }, { text: 'Program', align: 'left' }];
    kolomRaw.forEach(function (k) { hdrRow.push({ text: k, align: 'center' }); });
    var cSpecs = [25, null]; kolomRaw.forEach(function () { cSpecs.push(70); });
    var colWidths = buildColWidths(cSpecs, tWidth), rowH = 22, tableRows = [];

    if (mergeHeader) {
        var mr = [];
        for (var mc = 0; mc < cols; mc++) mr.push({ text: mc === 0 ? namaProgram : '', isMerged: mc === 0, align: 'center' });
        tableRows.push(mr);
    }
    tableRows.push(hdrRow);
    itemsRaw.forEach(function (item, i) {
        var row = [{ text: String(i + 1), align: 'center' }, { text: item, align: 'left' }];
        kolomRaw.forEach(function () { row.push({ text: '-', align: 'center' }); });
        tableRows.push(row);
    });

    var rH = tableRows.map(function (r, i) { return (i === 0 && mergeHeader) ? 20 : (i === 0 || i === 1) ? rowH + 4 : rowH; });
    createTablePlaceholder({
        type: 'unggulan', totalWidth: tWidth,
        totalHeight: rH.reduce(function (a, b) { return a + b; }, 0),
        colWidths: colWidths, rowHeights: rH, rows: tableRows,
        headerColor: hdrColor, stripeColor: '#f5f9ff', borderColor: '#adb5bd',
    }, MARGIN, 200);
}

function insertEkskulTable() {
    var itemsRaw = document.getElementById('ekskulItems').value.split('\n').map(function (s) { return s.trim(); }).filter(Boolean);
    var kolomRaw = document.getElementById('ekskulKolom').value.split(',').map(function (s) { return s.trim(); });
    var tWidth   = parseInt(document.getElementById('ekskulWidth').value)  || 754;
    var hdrColor = document.getElementById('ekskulHeaderColor').value;
    var cSpecs   = [25, null]; kolomRaw.forEach(function () { cSpecs.push(80); });
    var colWidths= buildColWidths(cSpecs, tWidth), rowH = 22;
    var hdrRow   = [{ text: 'No', align: 'center' }, { text: 'Ekstrakurikuler', align: 'left' }];
    kolomRaw.forEach(function (k) { hdrRow.push({ text: k, align: 'center' }); });
    var tableRows = [hdrRow];
    itemsRaw.forEach(function (item, i) {
        var row = [{ text: String(i + 1), align: 'center' }, { text: item, align: 'left' }];
        kolomRaw.forEach(function () { row.push({ text: '-', align: 'center' }); });
        tableRows.push(row);
    });
    createTablePlaceholder({
        type: 'ekskul', totalWidth: tWidth,
        totalHeight: (rowH + 4) + itemsRaw.length * rowH,
        colWidths: colWidths, rowHeights: buildRowHeights(itemsRaw.length, rowH, rowH + 4),
        rows: tableRows, headerColor: hdrColor, stripeColor: '#f0f7ff', borderColor: '#adb5bd',
    }, MARGIN, 200);
}

function insertAbsensiTable() {
    var rows     = parseInt(document.getElementById('absensiRows').value)  || 30;
    var tWidth   = parseInt(document.getElementById('absensiWidth').value) || 754;
    var hdrColor = document.getElementById('absensiHeaderColor').value;
    var kolomRaw = document.getElementById('absensiKolom').value.split(',').map(function (s) { return s.trim(); });
    var hdrRow   = [{ text: 'No', align: 'center' }, { text: 'Nama Siswa', align: 'left' }, { text: 'NIS', align: 'center' }];
    kolomRaw.forEach(function (k) { hdrRow.push({ text: k, align: 'center' }); });
    var cSpecs   = [25, null, 60]; kolomRaw.forEach(function () { cSpecs.push(40); });
    var colWidths= buildColWidths(cSpecs, tWidth), rowH = 20, tableRows = [hdrRow];
    for (var r = 0; r < rows; r++) {
        var row = [{ text: String(r + 1), align: 'center' }, { text: '', align: 'left' }, { text: '', align: 'center' }];
        kolomRaw.forEach(function () { row.push({ text: '', align: 'center' }); });
        tableRows.push(row);
    }
    createTablePlaceholder({
        type: 'absensi', totalWidth: tWidth,
        totalHeight: (rowH + 4) + rows * rowH,
        colWidths: colWidths, rowHeights: buildRowHeights(rows, rowH, rowH + 4),
        rows: tableRows, headerColor: hdrColor, stripeColor: '#f0f7ff', borderColor: '#adb5bd',
    }, MARGIN, 200);
}

function insertTTDArea() {
    var canvas = getCanvas();
    if (!canvas) return;
    var pg = pages[currentPage];
    if (!pg) return;
    var rawKolom = document.getElementById('ttdKolom').value.trim().split('\n').filter(Boolean);
    var tWidth   = parseInt(document.getElementById('ttdWidth').value)  || 754;
    var ttdH     = parseInt(document.getElementById('ttdHeight').value) || 80;
    var posY     = parseInt(document.getElementById('ttdPosY').value)   || 950;
    var cols     = rawKolom.length, colW = tWidth / (cols || 1);
    var ttdData  = rawKolom.map(function (line) {
        var parts = line.split(',');
        return { label: (parts[0] || '').trim(), nama: (parts[1] || '').trim(), jabatan: (parts[2] || '').trim() };
    });
    var id = 'ttd_' + (++tableCounter);
    pg.tableStore[id] = { type: 'ttd', ttdData: ttdData, totalWidth: tWidth, colW: colW, ttdH: ttdH };
    ttdData.forEach(function (col) {
        [col.nama, col.jabatan].forEach(function (s) {
            var m = (s || '').match(/\{\{([^}]+)\}\}/g);
            if (m) m.forEach(function (mv) { registerVariable(mv.replace(/[{}]/g, '').trim(), mv.replace(/[{}]/g, '').trim()); });
        });
    });
    var objs = [], cx = 0;
    ttdData.forEach(function (col) {
        objs.push(new fabric.Rect({ left: cx, top: 0, width: colW, height: 20 + ttdH + 40, fill: '#fafafa', stroke: '#dee2e6', strokeWidth: 0.5 }));
        objs.push(new fabric.Textbox(col.label, { left: cx + 4, top: 4, width: colW - 8, height: 16, fontSize: 9, fontFamily: 'Arial', textAlign: 'center', fill: '#333', selectable: false, evented: false }));
        objs.push(new fabric.Textbox(col.nama || '( __________________ )', { left: cx + 4, top: 20 + ttdH + 4, width: colW - 8, height: 16, fontSize: 8, fontFamily: 'Arial', textAlign: 'center', fontWeight: 'bold', fill: '#1a5276', selectable: false, evented: false }));
        if (col.jabatan) objs.push(new fabric.Textbox(col.jabatan, { left: cx + 4, top: 20 + ttdH + 18, width: colW - 8, height: 16, fontSize: 7, fontFamily: 'Arial', textAlign: 'center', fill: '#555', selectable: false, evented: false }));
        cx += colW;
    });
    var group = new fabric.Group(objs, { left: MARGIN, top: posY, name: id });
    canvas.add(group); canvas.setActiveObject(group);
    canvas.requestRenderAll(); saveState();
}

// ============================================================
// FORMAT TOOLBAR
// ============================================================
var _coordRafId = null;

function scheduleCoordUpdate(obj) {
    if (_coordRafId) return;
    _coordRafId = requestAnimationFrame(function () { _coordRafId = null; updateCoords(obj); });
}

function updateCoords(obj) {
    var x = '-', y = '-', w = '-', h = '-';
    if (obj) {
        var xPx = Math.round(obj.left || 0);
        var yPx = Math.round(obj.top  || 0);
        var wPx = Math.round((obj.width  || 0) * (obj.scaleX || 1));
        var hPx = Math.round((obj.height || 0) * (obj.scaleY || 1));
        // Tampilkan px dan mm
        x = xPx + ' (' + (xPx / MM_TO_PX).toFixed(1) + 'mm)';
        y = yPx + ' (' + (yPx / MM_TO_PX).toFixed(1) + 'mm)';
        w = wPx + ' (' + (wPx / MM_TO_PX).toFixed(1) + 'mm)';
        h = hPx + ' (' + (hPx / MM_TO_PX).toFixed(1) + 'mm)';
    }
    var cx = document.getElementById('coordX'); if (cx) cx.textContent = x;
    var cy = document.getElementById('coordY'); if (cy) cy.textContent = y;
    var cw = document.getElementById('coordW'); if (cw) cw.textContent = w;
    var ch = document.getElementById('coordH'); if (ch) ch.textContent = h;
}

function updateToolbar() {
    var canvas = getCanvas();
    if (!canvas) return;
    var obj = canvas.getActiveObject();
    if (!obj) return;
    var ft = document.getElementById('formatToolbar');
    if (!ft) return;
    ft.style.display = 'block';
    var op = Math.round((obj.opacity || 1) * 100);
    document.getElementById('objOpacity').value = op;
    document.getElementById('opacityVal').textContent = op;
    document.getElementById('objX').value = Math.round(obj.left || 0);
    document.getElementById('objY').value = Math.round(obj.top  || 0);
    document.getElementById('objWidth').value  = Math.round((obj.width  || 0) * (obj.scaleX || 1));
    document.getElementById('objHeight').value = Math.round((obj.height || 0) * (obj.scaleY || 1));
    document.getElementById('objRotate').value = Math.round(obj.angle || 0);
    document.getElementById('rotateVal').textContent = Math.round(obj.angle || 0);
    if (obj.type === 'textbox' || obj.type === 'i-text') {
        document.getElementById('fontSize').value   = obj.fontSize   || 16;
        document.getElementById('fontFamily').value = obj.fontFamily || 'Arial';
        document.getElementById('fontColor').value  = obj.fill       || '#000000';
        var lh = obj.lineHeight || 1.4;
        document.getElementById('lineHeightSlider').value = Math.round(lh * 10);
        document.getElementById('lineHeightVal').textContent = lh.toFixed(1);
    }
    var li = document.getElementById('lockIcon'), ll = document.getElementById('lockLabel');
    if (li) li.className  = obj.lockMovementX ? 'bi bi-lock-fill text-warning' : 'bi bi-lock';
    if (ll) ll.textContent = obj.lockMovementX ? 'Buka' : 'Kunci';
    updateCoords(obj);
}

function applyFormat(p, v)  { var o = getCanvas() && getCanvas().getActiveObject(); if (!o) return; o.set(p, v); getCanvas().requestRenderAll(); saveState(); }
function applyWidth(v)      { var o = getCanvas() && getCanvas().getActiveObject(); if (!o) return; o.type === 'textbox' ? o.set('width', v) : o.set('scaleX', v / (o.width || 1)); getCanvas().requestRenderAll(); saveState(); }
function applyHeight(v)     { var o = getCanvas() && getCanvas().getActiveObject(); if (!o) return; o.set('scaleY', v / (o.height || 1)); getCanvas().requestRenderAll(); saveState(); }
function applyOpacity(v)    { var o = getCanvas() && getCanvas().getActiveObject(); if (!o) return; o.set('opacity', v / 100); getCanvas().requestRenderAll(); saveState(); }
function applyRotation(v)   { var o = getCanvas() && getCanvas().getActiveObject(); if (!o) return; o.set('angle', v); getCanvas().requestRenderAll(); saveState(); }
function applyPosition(ax, v) { var o = getCanvas() && getCanvas().getActiveObject(); if (!o) return; ax === 'x' ? o.set('left', v) : o.set('top', v); o.setCoords(); getCanvas().requestRenderAll(); saveState(); }
function toggleBold()        { var o = getCanvas() && getCanvas().getActiveObject(); if (!o || o.type !== 'textbox') return; o.set('fontWeight',  o.fontWeight  === 'bold'    ? 'normal' : 'bold');    getCanvas().requestRenderAll(); saveState(); }
function toggleItalic()      { var o = getCanvas() && getCanvas().getActiveObject(); if (!o || o.type !== 'textbox') return; o.set('fontStyle',   o.fontStyle   === 'italic'  ? 'normal' : 'italic');  getCanvas().requestRenderAll(); saveState(); }
function toggleUnderline()   { var o = getCanvas() && getCanvas().getActiveObject(); if (!o || o.type !== 'textbox') return; o.set('underline',   !o.underline);  getCanvas().requestRenderAll(); saveState(); }
function toggleStrikethrough(){ var o = getCanvas() && getCanvas().getActiveObject(); if (!o || o.type !== 'textbox') return; o.set('linethrough', !o.linethrough); getCanvas().requestRenderAll(); saveState(); }

// ============================================================
// ALIGNMENT & DISTRIBUTE
// ============================================================
function alignObj(dir) {
    var canvas = getCanvas(); if (!canvas) return;
    var o = canvas.getActiveObject(); if (!o) return;
    var b = o.getBoundingRect(true);
    if      (dir === 'left')    o.set('left', 0);
    else if (dir === 'hcenter') o.set('left', (CANVAS_W - b.width) / 2);
    else if (dir === 'right')   o.set('left', CANVAS_W - b.width);
    else if (dir === 'top')     o.set('top',  0);
    else if (dir === 'vcenter') o.set('top',  (CANVAS_H - b.height) / 2);
    else if (dir === 'bottom')  o.set('top',  CANVAS_H - b.height);
    o.setCoords(); canvas.requestRenderAll(); saveState();
}

function distributeObjects(axis) {
    var canvas = getCanvas(); if (!canvas) return;
    var sel = canvas.getActiveObject();
    if (!sel || sel.type !== 'activeSelection') { alert('Pilih 2+ objek.'); return; }
    var objs = sel.getObjects();
    if (objs.length < 3) { alert('Butuh minimal 3 objek.'); return; }
    if (axis === 'h') {
        objs.sort(function (a, b) { return a.left - b.left; });
        var g = (objs[objs.length - 1].left - objs[0].left) / (objs.length - 1);
        objs.forEach(function (o, i) { o.set('left', objs[0].left + i * g); o.setCoords(); });
    } else {
        objs.sort(function (a, b) { return a.top - b.top; });
        var gV = (objs[objs.length - 1].top - objs[0].top) / (objs.length - 1);
        objs.forEach(function (o, i) { o.set('top', objs[0].top + i * gV); o.setCoords(); });
    }
    canvas.requestRenderAll(); saveState();
}

// ============================================================
// LAYER / COPY / PASTE / LOCK
// ============================================================
function bringForward()  { var c = getCanvas(); if (!c) return; var o = c.getActiveObject(); if (!o) return; c.bringForward(o);  c.requestRenderAll(); saveState(); }
function sendBackward()  { var c = getCanvas(); if (!c) return; var o = c.getActiveObject(); if (!o) return; c.sendBackwards(o); c.requestRenderAll(); saveState(); }

function copySelected() {
    var canvas = getCanvas(); if (!canvas) return;
    var o = canvas.getActiveObject(); if (!o) return;
    o.clone(function (c) { _clipboard = c; });
}

function pasteClipboard() {
    var canvas = getCanvas(); if (!canvas || !_clipboard) return;
    _clipboard.clone(function (c) {
        canvas.discardActiveObject();
        c.set({ left: (_clipboard.left || 0) + 20, top: (_clipboard.top || 0) + 20, evented: true });
        if (c.type === 'activeSelection') {
            c.canvas = canvas;
            c.forEachObject(function (o) { canvas.add(o); });
            c.setCoords();
        } else canvas.add(c);
        canvas.setActiveObject(c); canvas.requestRenderAll(); saveState();
        _clipboard = c;
    });
}

function toggleLock() {
    var canvas = getCanvas(); if (!canvas) return;
    var o = canvas.getActiveObject(); if (!o) return;
    var locked = o.lockMovementX;
    o.set({ lockMovementX: !locked, lockMovementY: !locked, lockRotation: !locked, lockScalingX: !locked, lockScalingY: !locked, hasControls: locked, hasBorders: locked });
    canvas.requestRenderAll();
    var li = document.getElementById('lockIcon'), ll = document.getElementById('lockLabel');
    if (li) li.className  = !locked ? 'bi bi-lock-fill text-warning' : 'bi bi-lock';
    if (ll) ll.textContent = !locked ? 'Buka' : 'Kunci';
}

// ============================================================
// ADD ELEMENTS
// ============================================================
function addText() {
    var canvas = getCanvas(); if (!canvas) return;
    var usedTops = canvas.getObjects().map(function (o) { return Math.round(o.top || 0); });
    var newTop = 180;
    while (usedTops.indexOf(newTop) !== -1) newTop += 24;
    canvas.add(new fabric.Textbox('Tulis teks di sini', { left: MARGIN + 10, top: newTop, width: 300, fontSize: 16, fontFamily: 'Arial', fill: '#000' }));
    canvas.requestRenderAll();
}

function triggerImageUpload() { document.getElementById('imageUpload').click(); }

function addImage(e) {
    var canvas = getCanvas(); if (!canvas) return;
    var f = e.target.files[0]; if (!f) return;
    var reader = new FileReader();
    reader.onload = function (ev) {
        fabric.Image.fromURL(ev.target.result, function (img) {
            img.scaleToWidth(200); img.set({ left: 100, top: 100 });
            canvas.add(img); canvas.requestRenderAll(); saveState();
        });
    };
    reader.readAsDataURL(f); e.target.value = '';
}

function triggerLogoUpload() { document.getElementById('logoUpload').click(); }

function addLogoImage(e) {
    var canvas = getCanvas(); if (!canvas) return;
    var f = e.target.files[0]; if (!f) return;
    var reader = new FileReader();
    reader.onload = function (ev) {
        fabric.Image.fromURL(ev.target.result, function (img) {
            img.scaleToWidth(100); img.set({ left: 40, top: 30, name: 'logo' });
            canvas.add(img); canvas.requestRenderAll(); saveState();
        });
    };
    reader.readAsDataURL(f); e.target.value = '';
}

function addBarcode() {
    var canvas = getCanvas(); if (!canvas) return;
    var g = new fabric.Group([
        new fabric.Rect({ width: 120, height: 120, fill: '#fff', stroke: '#333', strokeWidth: 1, rx: 4, ry: 4 }),
        new fabric.Text('{{barcode_signature}}', { fontSize: 9, fontFamily: 'Courier New', fill: '#333', textAlign: 'center', originX: 'center', originY: 'center', left: 60, top: 60 }),
    ], { left: 620, top: 860, name: 'barcode' });
    canvas.add(g); canvas.requestRenderAll(); saveState();
}

function removeSelected() {
    var canvas = getCanvas(); if (!canvas) return;
    var pg = pages[currentPage]; if (!pg) return;
    var obj = canvas.getActiveObject();
    if (!obj) { alert('Pilih elemen terlebih dahulu.'); return; }
    if (obj.name && obj.name.startsWith('kop_')) {
        if (!confirm('Hapus seluruh kop surat?')) return;
        canvas.getObjects().filter(function (o) { return o.name && o.name.startsWith('kop_'); }).forEach(function (o) { canvas.remove(o); });
    } else {
        if (obj.name && pg.tableStore[obj.name]) delete pg.tableStore[obj.name];
        canvas.remove(obj);
    }
    canvas.discardActiveObject(); canvas.requestRenderAll(); saveState();
}

// ============================================================
// HTML GENERATE
// ============================================================
function textStyle(obj, wPx) {
    var scaledFs = (obj.fontSize || 16) * (obj.scaleY || 1);
    var parts = [
        'font-size:'    + pt(scaledFs) + 'pt',
        'font-family:'  + (obj.fontFamily || 'DejaVu Sans') + ',sans-serif',
        'color:'        + (obj.fill || '#000000'),
        'font-weight:'  + (obj.fontWeight || 'normal'),
        'font-style:'   + (obj.fontStyle  || 'normal'),
        'text-align:'   + (obj.textAlign  || 'left'),
        'line-height:'  + (obj.lineHeight || 1.4),
        'width:'        + pt(wPx) + 'pt',
        'white-space:normal', 'word-wrap:break-word', 'overflow:hidden',
    ];
    if (obj.underline)   parts.push('text-decoration:underline');
    if (obj.linethrough) parts.push('text-decoration:line-through');
    return parts.join(';');
}

function buildTableHtml(tableId, objLeft, objTop, tableStore) {
    var td = tableStore[tableId]; if (!td) return '';
    if (td.type === 'ttd') return buildTTDHtml(td, objLeft, objTop);

    var lPt = pt(objLeft), tPt = pt(objTop), wPt = pt(td.totalWidth);
    var rows = td.rows, colW = td.colWidths, rowH = td.rowHeights;
    var totalCols = colW.length;
    var hdrColor = td.headerColor || '#1a5276', strColor = td.stripeColor || '#eaf2ff', bdColor = td.borderColor || '#adb5bd';

    var html = '<table style="position:absolute;left:' + lPt + 'pt;top:' + tPt + 'pt;width:' + wPt + 'pt;border-collapse:collapse;table-layout:fixed;font-family:DejaVu Sans,Arial,sans-serif;font-size:8pt;">';
    html += '<colgroup>';
    colW.forEach(function (w) { html += '<col style="width:' + pt(w) + 'pt">'; });
    html += '</colgroup>';

    rows.forEach(function (row, ri) {
        var rH = pt(rowH[ri] || 20);
        var isMergedRow = row[0] && row[0].isMerged;
        var bg = ri === 0 ? hdrColor : (ri % 2 === 1 ? '#ffffff' : strColor);
        html += '<tr>';
        if (isMergedRow) {
            html += '<td colspan="' + totalCols + '" style="background:' + hdrColor + ';color:#ffffff;font-weight:bold;text-align:center;padding:2pt 3pt;border:0.5pt solid ' + bdColor + ';min-height:' + rH + 'pt;height:auto;">' + escapeCellContent(row[0].text || '') + '</td>';
        } else {
            for (var ci = 0; ci < totalCols; ci++) {
                var cell     = row[ci] || { text: '', align: 'left' };
                var cW       = pt(colW[ci] || 60);
                var isHeader = (ri === 0);
                var cellBg   = isHeader ? hdrColor : bg;
                var color    = isHeader ? '#ffffff' : '#212529';
                var fw       = isHeader ? 'bold' : 'normal';
                var align    = cell.align || 'left';
                html += '<td style="width:' + cW + 'pt;background:' + cellBg + ';border:0.5pt solid ' + bdColor + ';font-weight:' + fw + ';color:' + color + ';padding:2pt 3pt;vertical-align:top;text-align:' + align + ';word-wrap:break-word;min-height:' + rH + 'pt;height:auto;">' + escapeCellContent(cell.text || '') + '</td>';
            }
        }
        html += '</tr>';
    });
    html += '</table>';
    return html;
}

function buildTTDHtml(td, objLeft, objTop) {
    var lPt = pt(objLeft), tPt = pt(objTop), wPt = pt(td.totalWidth), colWPt = pt(td.colW), ttdHPt = pt(td.ttdH);
    var html = '<table style="position:absolute;left:' + lPt + 'pt;top:' + tPt + 'pt;width:' + wPt + 'pt;border-collapse:collapse;font-family:DejaVu Sans,Arial,sans-serif;font-size:9pt;">';
    html += '<tr>'; td.ttdData.forEach(function (col) { html += '<td style="width:' + colWPt + 'pt;text-align:center;vertical-align:top;padding:2pt;border:none;">' + escapeContent(col.label) + '</td>'; }); html += '</tr>';
    html += '<tr>'; td.ttdData.forEach(function () { html += '<td style="width:' + colWPt + 'pt;height:' + ttdHPt + 'pt;border:none;"></td>'; }); html += '</tr>';
    html += '<tr>'; td.ttdData.forEach(function (col) { html += '<td style="width:' + colWPt + 'pt;text-align:center;font-weight:bold;text-decoration:underline;border:none;padding:1pt 2pt;">' + escapeContent(col.nama) + '</td>'; }); html += '</tr>';
    html += '<tr>'; td.ttdData.forEach(function (col) { html += '<td style="width:' + colWPt + 'pt;text-align:center;border:none;padding:0pt 2pt;font-size:8pt;">' + escapeContent(col.jabatan) + '</td>'; }); html += '</tr>';
    html += '</table>';
    return html;
}

function generateHTMLForPage(pgData) {
    var canvas = pgData.canvas, tableStore = pgData.tableStore;
    var html = '<div class="page" style="position:relative;width:' + A4_W + 'pt;height:' + A4_H + 'pt;">';

    canvas.getObjects().forEach(function (obj) {
        if (obj.excludeFromExport) return;
        if (obj.name && (obj.name.startsWith('__') || obj.name === 'kop_logo_label')) return;
        var pos = realTopLeft(obj), lPt = pt(pos.x), tPt = pt(pos.y), wPt = pt(pos.w), hPt = pt(pos.h);
        var posStyle = 'position:absolute;left:' + lPt + 'pt;top:' + tPt + 'pt;';

        if (obj.name && tableStore[obj.name]) {
            html += buildTableHtml(obj.name, pos.x, pos.y, tableStore); return;
        }
        if (obj.type === 'textbox' || obj.type === 'i-text') {
            html += '<div style="' + posStyle + textStyle(obj, pos.w) + '">' + escapeContent(obj.text) + '</div>';
        } else if (obj.type === 'image') {
            var dimS = 'width:' + wPt + 'pt;height:' + hPt + 'pt;';
            if (obj.name === 'logo' || obj.name === 'kop_logo') {
                html += '<div style="' + posStyle + dimS + '">{{logo}}</div>';
            } else {
                var src = obj.toDataURL ? obj.toDataURL({ format: 'png' }) : '';
                if (src) html += '<img src="' + src + '" style="' + posStyle + dimS + 'display:block;" />';
            }
        } else if (obj.type === 'group' && obj.name === 'barcode') {
            html += '<div style="' + posStyle + 'width:' + wPt + 'pt;height:' + hPt + 'pt;">{{barcode_signature}}</div>';
        } else if (obj.type === 'line') {
            var br = obj.getBoundingRect(true), sw = pt(obj.strokeWidth || 1);
            if (sw < 0.75) sw = 0.75;
            html += '<div style="position:absolute;left:' + pt(br.left) + 'pt;top:' + pt(br.top) + 'pt;width:' + pt(Math.max(br.width, 1)) + 'pt;height:' + sw + 'pt;background:' + (obj.stroke || '#000') + '"></div>';
        } else if (obj.type === 'rect') {
            if (obj.name === 'kop_logo') { html += '<div style="' + posStyle + 'width:' + wPt + 'pt;height:' + hPt + 'pt;">{{logo}}</div>'; return; }
            var rs = posStyle + 'width:' + wPt + 'pt;height:' + hPt + 'pt;background:' + (obj.fill || 'transparent') + ';';
            if (obj.stroke) rs += 'border:' + pt(obj.strokeWidth || 1) + 'pt solid ' + obj.stroke + ';';
            html += '<div style="' + rs + '"></div>';
        } else if (obj.type === 'group') {
            if (tableStore[obj.name]) { html += buildTableHtml(obj.name, pos.x, pos.y, tableStore); return; }
            var gScaleX = obj.scaleX || 1, gScaleY = obj.scaleY || 1;
            var gHalfW  = (obj.width  || 0) / 2 * gScaleX;
            var gHalfH  = (obj.height || 0) / 2 * gScaleY;
            obj.getObjects().forEach(function (child) {
                if (child.type === 'textbox' || child.type === 'i-text') {
                    var cx2 = (obj.left || 0) + gHalfW + (child.left || 0) * gScaleX;
                    var cy2 = (obj.top  || 0) + gHalfH + (child.top  || 0) * gScaleY;
                    var cw2 = (child.width || 100) * (child.scaleX || 1) * gScaleX;
                    html += '<div style="position:absolute;left:' + pt(cx2) + 'pt;top:' + pt(cy2) + 'pt;' + textStyle(child, cw2) + '">' + escapeContent(child.text) + '</div>';
                }
            });
        }
    });
    html += '</div>';
    return html;
}

function generateHTML() {
    var fullHtml = pages.map(function (pg) { return generateHTMLForPage(pg); }).join('\n');
    document.getElementById('html_template').value = fullHtml;
    var allPagesData = pages.map(function (pg) {
        var json = pg.canvas.toJSON(['name', 'excludeFromExport']);
        json._tableStore = pg.tableStore;
        return json;
    });
    document.getElementById('canvas_json').value = JSON.stringify({ pages: allPagesData, version: 2 });
    return true;
}

// ============================================================
// RESTORE EXISTING CANVAS
// ============================================================
function friendlyVarLabel(varName) {
    // Hapus suffix angka acak: z3ded, 3ded, 2abc, dll
    var clean = varName.replace(/[a-z]?\d+[a-z]{0,5}$/i, '');
    // Hapus suffix huruf acak pendek di akhir (4-6 karakter, tidak bermakna)
    clean = clean.replace(/_?[a-z]{3,6}(?=[A-Z_]|$)/i, function(m) {
        // Pertahankan kata bermakna umum
        var keep = ['nama','nilai','kelas','tanggal','nomor','bulan','tahun',
                    'sekolah','siswa','mapel','capaian','akhir','awal'];
        return keep.some(function(k){ return m.toLowerCase().includes(k); }) ? m : '';
    });
    clean = clean.replace(/_{2,}/g, '_').replace(/^_|_$/g, '');
    if (!clean) clean = varName; // fallback
    return clean.split('_').map(function(w){
        return w.charAt(0).toUpperCase() + w.slice(1).toLowerCase();
    }).join(' ');
}

function restoreCanvas() {
    var data = window.EXISTING_CANVAS_JSON;
    if (!data) return;
    var parsed = (typeof data === 'string') ? JSON.parse(data) : data;

   function restoreVars(canvas) {
        canvas.getObjects().forEach(function (obj) {
            if (obj.type === 'textbox' || obj.type === 'i-text') {
                var m = (obj.text || '').match(/\{\{([^}]+)\}\}/g);
                if (m) m.forEach(function (v) {
                    var n = v.replace(/[{}]/g, '').trim();
                    if (['logo', 'barcode_signature'].indexOf(n) === -1) {
                        registerVariable(n, friendlyVarLabel(n)); // ← pakai helper
                    }
                });
            }
        });
    }

    if (parsed.version === 2 && parsed.pages && parsed.pages.length) {
        while (pages.length < parsed.pages.length) createPage();
        parsed.pages.forEach(function (pageJson, idx) {
            var pg = pages[idx]; if (!pg) return;
            var store = pageJson._tableStore || {};
            delete pageJson._tableStore;
            pg.canvas.loadFromJSON(pageJson, function () {
                pg.tableStore = store; pg.canvas.requestRenderAll();
                restoreVars(pg.canvas); drawMarginGuidesForPage(pg, marginVisible);
                saveStateForPage(pg); renderPageThumbnails();
            });
        });
        return;
    }
    var pg = pages[0]; if (!pg) return;
    var store = parsed._tableStore || {};
    delete parsed._tableStore;
    pg.canvas.loadFromJSON(parsed, function () {
        pg.tableStore = store; pg.canvas.requestRenderAll();
        restoreVars(pg.canvas); drawMarginGuidesForPage(pg, marginVisible);
        saveStateForPage(pg); renderPageThumbnails();
    });
}

// ============================================================
// FORM SUBMIT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('templateForm');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        var hasContent = pages.some(function (pg) {
            return pg.canvas.getObjects().filter(function (o) { return !o.excludeFromExport; }).length > 0;
        });
        if (!hasContent) { e.preventDefault(); alert('Template masih kosong!'); return false; }
        generateHTML();
    });
});

// ============================================================
// KEYBOARD SHORTCUTS
// ============================================================
document.addEventListener('keydown', function (e) {
    var tag     = document.activeElement.tagName;
    var inInput = ['INPUT', 'TEXTAREA', 'SELECT'].indexOf(tag) !== -1;
    if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'c' && !inInput) { e.preventDefault(); copySelected(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'v' && !inInput) { e.preventDefault(); pasteClipboard(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'd' && !inInput) { e.preventDefault(); copySelected(); pasteClipboard(); }
    if ((e.key === 'Delete' || e.key === 'Backspace') && !inInput) { removeSelected(); }
    if (e.key === '+' && e.ctrlKey) { e.preventDefault(); zoomIn(); }
    if (e.key === '-' && e.ctrlKey) { e.preventDefault(); zoomOut(); }
    if (e.key === '0' && e.ctrlKey) { e.preventDefault(); zoomReset(); }
    if (!inInput) {
        var canvas = getCanvas();
        if (canvas) {
            var obj = canvas.getActiveObject(), step = e.shiftKey ? 10 : 1;
            if (obj) {
                if (e.key === 'ArrowLeft')  { obj.set('left', (obj.left || 0) - step); obj.setCoords(); canvas.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowRight') { obj.set('left', (obj.left || 0) + step); obj.setCoords(); canvas.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowUp')    { obj.set('top',  (obj.top  || 0) - step); obj.setCoords(); canvas.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowDown')  { obj.set('top',  (obj.top  || 0) + step); obj.setCoords(); canvas.requestRenderAll(); e.preventDefault(); saveState(); }
            }
        }
    }
});

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    createPage();
    switchPage(0);
    drawRulersBase();
    restoreCanvas();
    updatePageIndicator();

    if (!window.EDITOR_KELAS_LIST || !window.EDITOR_KELAS_LIST.length) {
        fetch('/dashboard/surat/templates/api/kelas-list', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (data) { window.EDITOR_KELAS_LIST = data || []; })
            .catch(function () { window.EDITOR_KELAS_LIST = []; });
    }
    if (!window.EDITOR_MAPEL_LIST || !window.EDITOR_MAPEL_LIST.length) {
        fetch('/dashboard/surat/templates/api/mapel-list', { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (data) { window.EDITOR_MAPEL_LIST = data || []; })
            .catch(function () { window.EDITOR_MAPEL_LIST = []; });
    }
});

// ============================================================
// GLOBAL EXPORTS
// ============================================================
window.toggleGrid          = toggleGrid;
window.toggleMarginGuides  = toggleMarginGuides;
window.toggleRulerVis      = toggleRulerVis;
window.setZoom             = setZoom;
window.zoomIn              = zoomIn;
window.zoomOut             = zoomOut;
window.zoomReset           = zoomReset;
window.addText             = addText;
window.triggerImageUpload  = triggerImageUpload;
window.addImage            = addImage;
window.triggerLogoUpload   = triggerLogoUpload;
window.addLogoImage        = addLogoImage;
window.addBarcode          = addBarcode;
window.removeSelected      = removeSelected;
window.alignObj            = alignObj;
window.distributeObjects   = distributeObjects;
window.bringForward        = bringForward;
window.sendBackward        = sendBackward;
window.copySelected        = copySelected;
window.pasteClipboard      = pasteClipboard;
window.toggleLock          = toggleLock;
window.undo                = undo;
window.redo                = redo;
window.addNewPage          = addNewPage;
window.removeCurrentPage   = removeCurrentPage;
window.switchPage          = switchPage;
window.generateHTML        = generateHTML;
window.applyFormat         = applyFormat;
window.applyWidth          = applyWidth;
window.applyHeight         = applyHeight;
window.applyOpacity        = applyOpacity;
window.applyRotation       = applyRotation;
window.applyPosition       = applyPosition;
window.toggleBold          = toggleBold;
window.toggleItalic        = toggleItalic;
window.toggleUnderline     = toggleUnderline;
window.toggleStrikethrough = toggleStrikethrough;
window.snapEnabled         = snapEnabled;
window.setSnapEnabled      = function (v) { snapEnabled = v; };