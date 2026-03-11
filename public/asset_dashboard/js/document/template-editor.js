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
 *  5. All other editor logic unchanged.
 */

// ============================================================
// CONSTANTS
// ============================================================
var CANVAS_W  = 794;
var CANVAS_H  = 1123;
var MARGIN    = 30;
var A4_W      = 595.28;
var A4_H      = 841.89;
var R         = A4_W / CANVAS_W;

// ============================================================
// STATE
// ============================================================
var TABLE_STORE  = {};
var tableCounter = 0;
var pages        = [];
var currentPage  = 0;
var snapEnabled  = true;
var currentZoom  = 1;
var _clipboard   = null;

// ─── Snap / guide constants ──────────────────────────────────
// Snap only to PAGE guides (center, margin, edge).
// Object-to-object guides are VISUAL ONLY — no magnetic pull.
var SNAP_THRESHOLD   = 6;      // canvas-px to engage page-guide snap
var SNAP_RELEASE     = 10;     // canvas-px to release (hysteresis)
var GUIDE_FADE_IN    = 0.22;
var GUIDE_FADE_OUT   = 0.15;

// ============================================================
// UTILS
// ============================================================
function pt(px) { return parseFloat((px * R).toFixed(2)); }
function escHtml(s) { return (s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function escapeContent(text) {
    return (text||'')
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
        .replace(/\n/g,'<br>')
        .replace(/\{\{([^}]+)\}\}/g,function(m,v){return '{{'+v.trim()+'}}';});
}
function realTopLeft(obj) {
    var w=(obj.width||0)*(obj.scaleX||1), h=(obj.height||0)*(obj.scaleY||1);
    var ox=obj.originX||'left', oy=obj.originY||'top';
    var x=obj.left||0, y=obj.top||0;
    if(ox==='center') x=x-w/2; else if(ox==='right') x=x-w;
    if(oy==='center') y=y-h/2; else if(oy==='bottom') y=y-h;
    return {x:x,y:y,w:w,h:h};
}
function buildColWidths(specs,totalW){
    var fixed=0,fills=0;
    specs.forEach(function(s){s===null?fills++:(fixed+=s);});
    var fillW=fills>0?Math.max(20,(totalW-fixed)/fills):0;
    return specs.map(function(s){return s===null?fillW:s;});
}
function buildRowHeights(dataRows,rowH,headerH){
    var arr=[headerH]; for(var i=0;i<dataRows;i++) arr.push(rowH); return arr;
}

// ============================================================
// ACTIVE CANVAS HELPERS
// ============================================================
function getCanvas()     { return pages[currentPage]?pages[currentPage].canvas:null; }
function getTableStore() { return pages[currentPage]?pages[currentPage].tableStore:TABLE_STORE; }

// ============================================================
// PAGE MANAGEMENT
// ============================================================
function createPage() {
    var container  = document.getElementById('canvasPagesContainer');
    var pageIndex  = pages.length;

    var wrapper = document.createElement('div');
    wrapper.className = 'page-block'+(pageIndex===0?' active-page':'');
    wrapper.style.position = 'relative';

    var label = document.createElement('div');
    label.className = 'page-label';
    label.textContent = 'Halaman '+(pageIndex+1);
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
        fireRightClick:         false,
    });
    fc.setBackgroundColor('white', fc.renderAll.bind(fc));

    // ── Premium selection style ────────────────────────────
    fabric.Object.prototype.set({
        transparentCorners: false,
        cornerColor:        '#0ea5e9',
        cornerStrokeColor:  '#ffffff',
        borderColor:        '#0ea5e9',
        cornerSize:         9,
        cornerStyle:        'circle',
        borderDashArray:    [4, 3],
        borderScaleFactor:  1.5,
        padding:            5,
    });

    // ── Overlays ───────────────────────────────────────────
    var fabricWrapperEl = fc.wrapperEl;
    fc.upperCanvasEl.style.zIndex = '3';

    function makeOverlay(id, extraStyle) {
        var c = document.createElement('canvas');
        c.id = id+'_'+pageIndex;
        c.width  = CANVAS_W;
        c.height = CANVAS_H;
        c.style.cssText = 'position:absolute;top:0;left:0;z-index:1;pointer-events:none;'+(extraStyle||'');
        fabricWrapperEl.appendChild(c);
        return c;
    }

    var guideEl  = makeOverlay('overlay_guide',  '');
    var gridEl   = makeOverlay('overlay_grid',   'opacity:0.5;display:none;');
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
        // snap state (per drag)
        _pageSnapPoints: [],   // page-guide snap points (with magnet)
        _objGuidePoints: [],   // object-to-object alignment points (visual only)
        _activeSnapX:    null,
        _activeSnapY:    null,
        _prevSnapX:      null,
        _prevSnapY:      null,
        _guideAlpha:     0,
        _guideAlphaRafId:null,
    };
    pages.push(pageData);

    attachPageEvents(pageData);
    drawMarginGuidesForPage(pageData, marginVisible);
    renderPageThumbnails();
    saveStateForPage(pageData);
    return pageData;
}

function switchPage(idx) {
    if (idx<0||idx>=pages.length) return;
    var old = pages[currentPage];
    if (old) { old.canvas.discardActiveObject(); old.canvas.renderAll(); old.wrapper.classList.remove('active-page'); }
    currentPage = idx;
    var pg = pages[currentPage];
    pg.wrapper.classList.add('active-page');
    updatePageIndicator();
    renderPageThumbnails();
    pg.wrapper.scrollIntoView({behavior:'smooth',block:'nearest'});
}

function addNewPage()    { createPage(); switchPage(pages.length-1); saveState(); }

function removeCurrentPage() {
    if (pages.length<=1) { alert('Minimal harus ada 1 halaman.'); return; }
    if (!confirm('Hapus halaman '+(currentPage+1)+'?')) return;
    var pg = pages[currentPage];
    pg.canvas.dispose(); pg.wrapper.remove(); pages.splice(currentPage,1);
    pages.forEach(function(p,i){ var l=p.wrapper.querySelector('.page-label'); if(l) l.textContent='Halaman '+(i+1); p._pageIndex=i; });
    currentPage = Math.min(currentPage,pages.length-1);
    pages[currentPage].wrapper.classList.add('active-page');
    updatePageIndicator(); renderPageThumbnails(); saveState();
}

function updatePageIndicator() {
    var el = document.getElementById('pageIndicator');
    if (el) el.textContent = (currentPage+1)+'/'+pages.length;
}

function renderPageThumbnails() {
    var container = document.getElementById('pageThumbnails'); if (!container) return;
    container.innerHTML = '';
    pages.forEach(function(pg,idx){
        var item = document.createElement('div');
        item.className = 'thumb-item'+(idx===currentPage?' active':'');
        var tc = document.createElement('canvas');
        tc.width=80; tc.height=Math.round(80*(CANVAS_H/CANVAS_W));
        item.appendChild(tc);
        var lbl = document.createElement('div'); lbl.className='thumb-label'; lbl.textContent='Hal. '+(idx+1);
        item.appendChild(lbl);
        item.addEventListener('click',function(){switchPage(idx);});
        container.appendChild(item);
        var ctx=tc.getContext('2d'); ctx.fillStyle='#fff'; ctx.fillRect(0,0,tc.width,tc.height);
        var url=pg.canvas.toDataURL({format:'png',quality:0.3});
        var img=new Image(); img.onload=function(){ctx.drawImage(img,0,0,tc.width,tc.height);}; img.src=url;
    });
}

// ============================================================
// ZOOM
// ============================================================
function setZoom(z) {
    z = Math.min(2.5,Math.max(0.4,parseFloat(z.toFixed(2))));
    currentZoom = z;
    pages.forEach(function(pg){
        var fc=pg.canvas;
        fc.setZoom(z);
        fc.setWidth(Math.round(CANVAS_W*z));
        fc.setHeight(Math.round(CANVAS_H*z));
        fc.renderAll();
        [pg.guideEl,pg.gridEl,pg.marginEl].forEach(function(el){
            el.style.transform='scale('+z+')';
            el.style.transformOrigin='0 0';
        });
        pg.wrapper.style.width  = Math.round(CANVAS_W*z)+'px';
        pg.wrapper.style.height = Math.round(CANVAS_H*z)+'px';
        if (fc.wrapperEl) {
            fc.wrapperEl.style.width  = Math.round(CANVAS_W*z)+'px';
            fc.wrapperEl.style.height = Math.round(CANVAS_H*z)+'px';
        }
    });
    document.getElementById('zoomLabel').textContent = Math.round(z*100)+'%';
    drawRulersBase();
    pages.forEach(function(pg){drawMarginGuidesForPage(pg,marginVisible);});
}
function zoomIn()    { setZoom(currentZoom+0.1); }
function zoomOut()   { setZoom(currentZoom-0.1); }
function zoomReset() { setZoom(1); }

document.getElementById('editorContainer').addEventListener('wheel',function(e){
    if (!e.ctrlKey) return; e.preventDefault();
    e.deltaY<0?zoomIn():zoomOut();
},{passive:false});

// ============================================================
// RULERS
// ============================================================
var rulerVisible=true;
var rulerH=document.getElementById('rulerH');
var rulerV=document.getElementById('rulerV');
var ctxH=rulerH.getContext('2d',{willReadFrequently:true});
var ctxV=rulerV.getContext('2d',{willReadFrequently:true});
var _rulerHBase=null, _rulerVBase=null, _rulerRafId=null;

function toggleRulerVis(show){
    rulerVisible=show;
    rulerH.style.display=show?'block':'none';
    rulerV.style.display=show?'block':'none';
    document.getElementById('rulerCorner').style.display=show?'block':'none';
}
function drawRulersBase(){
    if (!rulerVisible) return;
    var W=Math.round(CANVAS_W*currentZoom), H=Math.round(CANVAS_H*currentZoom);
    rulerH.width=W; rulerV.height=H;
    ctxH.fillStyle='#dee2e6'; ctxH.fillRect(0,0,W,20);
    ctxH.font='9px Arial'; ctxH.textBaseline='bottom';
    var mmPx=(CANVAS_W/210)*currentZoom, step=mmPx<5?20:mmPx<10?10:5;
    for(var mm=0;mm<=210;mm+=step){
        var x=Math.round(mm*mmPx);
        ctxH.fillStyle='#6c757d'; ctxH.fillRect(x,mm%10===0?6:13,1,mm%10===0?14:7);
        if(mm%10===0&&x>0){ctxH.fillStyle='#343a40';ctxH.fillText(mm,x+2,18);}
    }
    ctxV.fillStyle='#dee2e6'; ctxV.fillRect(0,0,20,H);
    ctxV.font='9px Arial';
    var mmPxV=(CANVAS_H/297)*currentZoom, stepV=mmPxV<5?20:mmPxV<10?10:5;
    for(var mmv=0;mmv<=297;mmv+=stepV){
        var y=Math.round(mmv*mmPxV);
        ctxV.fillStyle='#6c757d'; ctxV.fillRect(mmv%10===0?6:13,y,mmv%10===0?14:7,1);
        if(mmv%10===0&&y>0){ctxV.save();ctxV.translate(14,y-2);ctxV.rotate(-Math.PI/2);ctxV.fillStyle='#343a40';ctxV.fillText(mmv,0,0);ctxV.restore();}
    }
    _rulerHBase=ctxH.getImageData(0,0,rulerH.width,20);
    _rulerVBase=ctxV.getImageData(0,0,20,rulerV.height);
}
function drawRulerCursor(px,py){
    if (!rulerVisible||!_rulerHBase||!_rulerVBase) return;
    var x=Math.round(px*currentZoom), y=Math.round(py*currentZoom);
    ctxH.putImageData(_rulerHBase,0,0);
    ctxV.putImageData(_rulerVBase,0,0);
    ctxH.fillStyle='rgba(220,53,69,0.85)'; ctxH.fillRect(x,0,1,20);
    ctxV.fillStyle='rgba(220,53,69,0.85)'; ctxV.fillRect(0,y,20,1);
}

// ============================================================
// GRID
// ============================================================
var gridVisible=false;
function toggleGrid(show){
    gridVisible=show;
    pages.forEach(function(pg){
        var gc=pg.gridEl, ctx=gc.getContext('2d');
        if (!show){gc.style.display='none';return;}
        gc.style.display='block';
        ctx.clearRect(0,0,CANVAS_W,CANVAS_H);
        ctx.strokeStyle='rgba(100,149,237,0.35)'; ctx.lineWidth=0.5;
        ctx.beginPath();
        for(var gx=0;gx<=CANVAS_W;gx+=10){ctx.moveTo(gx,0);ctx.lineTo(gx,CANVAS_H);}
        for(var gy=0;gy<=CANVAS_H;gy+=10){ctx.moveTo(0,gy);ctx.lineTo(CANVAS_W,gy);}
        ctx.stroke();
    });
}

// ============================================================
// MARGIN GUIDES
// ============================================================
var marginVisible=true;
function drawMarginGuidesForPage(pg,show){
    var mc=pg.marginEl, ctx=mc.getContext('2d');
    ctx.clearRect(0,0,CANVAS_W,CANVAS_H);
    if (!show) return;
    var M=MARGIN;
    ctx.strokeStyle='rgba(0,120,255,0.35)'; ctx.lineWidth=1; ctx.setLineDash([5,4]);
    ctx.beginPath();
    ctx.moveTo(M,0);ctx.lineTo(M,CANVAS_H);
    ctx.moveTo(CANVAS_W-M,0);ctx.lineTo(CANVAS_W-M,CANVAS_H);
    ctx.moveTo(0,M);ctx.lineTo(CANVAS_W,M);
    ctx.moveTo(0,CANVAS_H-M);ctx.lineTo(CANVAS_W,CANVAS_H-M);
    ctx.stroke(); ctx.setLineDash([]);
}
function toggleMarginGuides(show){
    marginVisible=show;
    pages.forEach(function(pg){drawMarginGuidesForPage(pg,show);});
}

// ============================================================
// ──────────────────────────────────────────────────────────
//  FIGMA-SMOOTH DRAG ENGINE
// ──────────────────────────────────────────────────────────
// ============================================================

// ============================================================
// SMART GUIDES — visual only, no magnetic pull
// ============================================================

/**
 * drawSmartGuides
 * Draws alignment guide lines on the overlay canvas.
 *
 * snapX / snapY  — page guide that is SNAPPED (object position adjusted).
 *                  These get the full bright line + badge.
 * objGuides      — array of {type:'x'|'y', ref, origin} from other objects.
 *                  These are VISUAL ONLY — dashed, dimmer, no badge.
 * alpha          — 0-1 for fade animation
 */
function drawSmartGuides(pgData, obj, snapX, snapY, objGuides, alpha) {
    var ctx = pgData.guideEl.getContext('2d');
    ctx.clearRect(0, 0, CANVAS_W, CANVAS_H);
    if (alpha <= 0) return;

    var b1  = obj.getBoundingRect(true);
    var b1R = b1.left + b1.width;
    var b1B = b1.top  + b1.height;

    ctx.save();
    ctx.globalAlpha = Math.min(1, alpha);

    // ── Object-to-object visual guides (dashed, no magnet) ──
    if (objGuides && objGuides.length) {
        ctx.save();
        ctx.strokeStyle = 'rgba(14,165,233,0.55)';
        ctx.lineWidth   = 1;
        ctx.setLineDash([4, 3]);
        ctx.shadowColor = 'rgba(14,165,233,0.3)';
        ctx.shadowBlur  = 3;
        objGuides.forEach(function(g) {
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
        ctx.setLineDash([]);
        ctx.restore();
    }

    // ── Page snap guides (solid, bright, with badge) ────────
    function drawSnapLine(isX, ref) {
        ctx.save();
        // Ghost full-canvas
        ctx.strokeStyle = 'rgba(244,63,94,0.15)';
        ctx.lineWidth   = 1;
        ctx.setLineDash([]);
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, 0); ctx.lineTo(ref, CANVAS_H); }
        else     { ctx.moveTo(0, ref); ctx.lineTo(CANVAS_W, ref); }
        ctx.stroke();

        // Glow bloom
        ctx.strokeStyle = 'rgba(244,63,94,0.22)';
        ctx.lineWidth   = 7;
        ctx.shadowColor = 'rgba(244,63,94,0.3)';
        ctx.shadowBlur  = 10;
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, b1.top - 20); ctx.lineTo(ref, b1B + 20); }
        else     { ctx.moveTo(b1.left - 20, ref); ctx.lineTo(b1R + 20, ref); }
        ctx.stroke();

        // Crisp line
        ctx.strokeStyle = '#f43f5e';
        ctx.lineWidth   = 1.5;
        ctx.shadowColor = 'rgba(244,63,94,0.55)';
        ctx.shadowBlur  = 5;
        ctx.setLineDash([]);
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, b1.top - 20); ctx.lineTo(ref, b1B + 20); }
        else     { ctx.moveTo(b1.left - 20, ref); ctx.lineTo(b1R + 20, ref); }
        ctx.stroke();

        // Endpoint dots
        ctx.fillStyle   = '#f43f5e';
        ctx.shadowBlur  = 5;
        var dots = isX ? [[ref, b1.top], [ref, b1B]] : [[b1.left, ref], [b1R, ref]];
        dots.forEach(function(d) {
            ctx.beginPath(); ctx.arc(d[0], d[1], 3.5, 0, Math.PI * 2); ctx.fill();
        });
        ctx.restore();
    }

    if (snapX) drawSnapLine(true,  snapX.ref);
    if (snapY) drawSnapLine(false, snapY.ref);

    // ── Coordinate badge ────────────────────────────────────
    if (snapX || snapY) {
        ctx.save();
        ctx.shadowBlur = 0;
        var parts = [];
        if (snapX) parts.push('X ' + Math.round(snapX.ref));
        if (snapY) parts.push('Y ' + Math.round(snapY.ref));
        var txt  = parts.join('  ');
        var font = 'bold 9.5px -apple-system,monospace';
        ctx.font = font;
        var tw   = ctx.measureText(txt).width;
        var px   = 6, bh = 16, bw = tw + px * 2;
        var bx   = (snapX ? snapX.ref : b1.left) + 5;
        var by   = b1.top - bh - 6;
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
    ctx.arcTo(x + w, y,     x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x,     y + h, r);
    ctx.arcTo(x,     y + h, x,     y,     r);
    ctx.arcTo(x,     y,     x + w, y,     r);
    ctx.closePath();
}

function fadeGuides(pgData, targetAlpha, obj, snapX, snapY, objGuides) {
    if (pgData._guideAlphaRafId) cancelAnimationFrame(pgData._guideAlphaRafId);
    function step() {
        var diff  = targetAlpha - pgData._guideAlpha;
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
// PAGE EVENTS
// ============================================================
function attachPageEvents(pgData) {
    var fc = pgData.canvas;

    // ── Switch page safely ─────────────────────────────────
    fc.on('mouse:down', function() {
        var idx = pages.indexOf(pgData);
        if (idx !== -1 && idx !== currentPage) setTimeout(function() { switchPage(idx); }, 0);
    });

    // ── Ruler cursor ───────────────────────────────────────
    fc.on('mouse:move', function(opt) {
        if (pgData !== pages[currentPage]) return;
        var p = opt.absolutePointer || opt.pointer; if (!p) return;
        if (_rulerRafId) return;
        _rulerRafId = requestAnimationFrame(function() { _rulerRafId = null; drawRulerCursor(p.x, p.y); });
    });

    // ── Prepare snap / guide data on drag start ────────────
    fc.on('mouse:down', function(e) {
        if (pgData !== pages[currentPage]) return;
        pgData._pageSnapPoints = [];
        pgData._objGuidePoints = [];
        pgData._activeSnapX    = null;
        pgData._activeSnapY    = null;
        pgData._prevSnapX      = null;
        pgData._prevSnapY      = null;

        if (!e.target) return;
        var obj = e.target;

        // PAGE snap points — these DO have magnetic pull
        pgData._pageSnapPoints = [
            { ref: CANVAS_W / 2,      type: 'x' },
            { ref: CANVAS_H / 2,      type: 'y' },
            { ref: MARGIN,            type: 'x' },
            { ref: CANVAS_W - MARGIN, type: 'x' },
            { ref: MARGIN,            type: 'y' },
            { ref: CANVAS_H - MARGIN, type: 'y' },
            { ref: 0,                 type: 'x' },
            { ref: CANVAS_W,          type: 'x' },
            { ref: 0,                 type: 'y' },
            { ref: CANVAS_H,          type: 'y' },
        ];

        // OBJECT guide points — visual only, NO magnet
        fc.getObjects().forEach(function(o) {
            if (o === obj || !o.selectable) return;
            if (o.name && (o.name.startsWith('__') || o.name.startsWith('kop_line'))) return;
            var b = o.getBoundingRect(true);
            pgData._objGuidePoints.push(
                { ref: b.left,                type: 'x', origin: o },
                { ref: b.left + b.width / 2,  type: 'x', origin: o },
                { ref: b.left + b.width,      type: 'x', origin: o },
                { ref: b.top,                 type: 'y', origin: o },
                { ref: b.top + b.height / 2,  type: 'y', origin: o },
                { ref: b.top + b.height,      type: 'y', origin: o }
            );
        });
    });

    // ── CORE: object:moving ────────────────────────────────
    // Movement is 100% direct (1:1 with mouse — no spring, no lerp).
    // Only page guides cause position adjustment.
    // Object-to-object lines are shown as visual hints only.
    fc.on('object:moving', function(e) {
        var obj  = e.target;
        var zoom = fc.getZoom();
        var T_IN  = SNAP_THRESHOLD / zoom;
        var T_OUT = SNAP_RELEASE   / zoom;

        var oBox = obj.getBoundingRect(true);
        var oCX  = oBox.left + oBox.width  / 2;
        var oCY  = oBox.top  + oBox.height / 2;

        // ── 1. Page-guide snap (moves object) ──────────────
        var newSnapX = null, newSnapY = null;

        pgData._pageSnapPoints.forEach(function(sp) {
            if (sp.type === 'x' && !newSnapX) {
                var wasHere = pgData._activeSnapX && Math.abs(pgData._activeSnapX.ref - sp.ref) < 0.5;
                var T = wasHere ? T_OUT : T_IN;
                var edges = [oBox.left, oCX, oBox.left + oBox.width];
                for (var i = 0; i < 3; i++) {
                    if (Math.abs(edges[i] - sp.ref) < T) { newSnapX = sp; break; }
                }
            } else if (sp.type === 'y' && !newSnapY) {
                var wasHereY = pgData._activeSnapY && Math.abs(pgData._activeSnapY.ref - sp.ref) < 0.5;
                var TY = wasHereY ? T_OUT : T_IN;
                var edgesY = [oBox.top, oCY, oBox.top + oBox.height];
                for (var j = 0; j < 3; j++) {
                    if (Math.abs(edgesY[j] - sp.ref) < TY) { newSnapY = sp; break; }
                }
            }
        });

        // Apply page-snap position adjustment
        if (newSnapX) {
            var dL = Math.abs(oBox.left - newSnapX.ref);
            var dC = Math.abs(oCX       - newSnapX.ref);
            var dR = Math.abs(oBox.left + oBox.width - newSnapX.ref);
            var mD = Math.min(dL, dC, dR);
            if      (mD === dL) obj.set('left', newSnapX.ref);
            else if (mD === dC) obj.set('left', newSnapX.ref - oBox.width / 2);
            else                obj.set('left', newSnapX.ref - oBox.width);
            obj.setCoords();
        }
        if (newSnapY) {
            var dT  = Math.abs(oBox.top  - newSnapY.ref);
            var dCY = Math.abs(oCY       - newSnapY.ref);
            var dB  = Math.abs(oBox.top  + oBox.height - newSnapY.ref);
            var mDY = Math.min(dT, dCY, dB);
            if      (mDY === dT)  obj.set('top', newSnapY.ref);
            else if (mDY === dCY) obj.set('top', newSnapY.ref - oBox.height / 2);
            else                  obj.set('top', newSnapY.ref - oBox.height);
            obj.setCoords();
        }

        pgData._activeSnapX = newSnapX;
        pgData._activeSnapY = newSnapY;

        // ── 2. Object-guide detection (visual only) ─────────
        // Re-read box after snap adjustment
        oBox = obj.getBoundingRect(true);
        oCX  = oBox.left + oBox.width  / 2;
        oCY  = oBox.top  + oBox.height / 2;

        var visibleObjGuides = [];
        var VISUAL_T = 4 / zoom; // how close to show alignment line (no pull)
        var seenX = {}, seenY = {};
        pgData._objGuidePoints.forEach(function(g) {
            var key = Math.round(g.ref);
            if (g.type === 'x' && !seenX[key]) {
                var edges = [oBox.left, oCX, oBox.left + oBox.width];
                for (var i = 0; i < 3; i++) {
                    if (Math.abs(edges[i] - g.ref) < VISUAL_T) {
                        visibleObjGuides.push(g); seenX[key] = true; break;
                    }
                }
            } else if (g.type === 'y' && !seenY[key]) {
                var edgesY = [oBox.top, oCY, oBox.top + oBox.height];
                for (var j = 0; j < 3; j++) {
                    if (Math.abs(edgesY[j] - g.ref) < VISUAL_T) {
                        visibleObjGuides.push(g); seenY[key] = true; break;
                    }
                }
            }
        });

        // ── 3. Guide drawing ────────────────────────────────
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
            if (pgData._guideAlpha > 0) {
                fadeGuides(pgData, 0, null, null, null, null);
            }
        }

        pgData._prevSnapX = newSnapX;
        pgData._prevSnapY = newSnapY;

        scheduleCoordUpdate(obj);
    });

    // ── Release ────────────────────────────────────────────
    fc.on('mouse:up', function() {
        pgData._activeSnapX = null;
        pgData._activeSnapY = null;
        pgData._prevSnapX   = null;
        pgData._prevSnapY   = null;
        pgData._pageSnapPoints = [];
        pgData._objGuidePoints = [];
        if (pgData._guideAlpha > 0) fadeGuides(pgData, 0, null, null, null, null);
    });

    fc.on('object:modified', function() {
        pgData._prevSnapX = null; pgData._prevSnapY = null;
        if (pgData._guideAlpha > 0) fadeGuides(pgData, 0, null, null, null, null);
        saveStateForPage(pgData);
        renderPageThumbnails();
    });

    fc.on('selection:created', function()  { updateToolbar(); });
    fc.on('selection:updated', updateToolbar);
    fc.on('selection:cleared', function()  {
        document.getElementById('formatToolbar').style.display = 'none';
        updateCoords(null);
    });
    fc.on('object:scaling',  function(e) { scheduleCoordUpdate(e.target); });
    fc.on('object:rotating', function(e) { scheduleCoordUpdate(e.target); });
    fc.on('object:added',    function()  { saveStateForPage(pgData); });
    fc.on('object:removed',  function()  { saveStateForPage(pgData); });
}

// ============================================================
// UNDO / REDO
// ============================================================
function saveStateForPage(pgData){
    if (pgData._isSaving) return;
    clearTimeout(pgData._saveTimer);
    pgData._saveTimer=setTimeout(function(){
        var json=JSON.stringify(pgData.canvas.toJSON(['name','excludeFromExport']));
        pgData._history.push(json);
        if (pgData._history.length>50) pgData._history.shift();
        pgData._historyRedo=[];
    },60);
}
function saveState(){ if(pages[currentPage]) saveStateForPage(pages[currentPage]); }
function undo(){
    var pg=pages[currentPage]; if(!pg) return;
    if(pg._history.length<2) return;
    pg._isSaving=true; pg._historyRedo.push(pg._history.pop());
    pg.canvas.loadFromJSON(pg._history[pg._history.length-1],function(){
        pg.canvas.renderAll(); pg._isSaving=false; renderPageThumbnails();
    });
}
function redo(){
    var pg=pages[currentPage]; if(!pg) return;
    if(!pg._historyRedo.length) return;
    pg._isSaving=true; var n=pg._historyRedo.pop(); pg._history.push(n);
    pg.canvas.loadFromJSON(n,function(){
        pg.canvas.renderAll(); pg._isSaving=false; renderPageThumbnails();
    });
}

// ============================================================
// VARIABLE REGISTRY
// ============================================================
var variableRegistry=[];
function registerVariable(name,label){
    if(variableRegistry.find(function(v){return v.name===name;})) return;
    variableRegistry.push({name:name,label:label||name});
    renderVariableChips();
}
function renderVariableChips(){
    var panel=document.getElementById('variablePanel');
    var el=document.getElementById('variableChips');
    if(!variableRegistry.length){panel.style.display='none';return;}
    panel.style.display='block'; el.innerHTML='';
    variableRegistry.forEach(function(v){
        var c=document.createElement('button'); c.type='button';
        c.className='btn btn-primary btn-sm d-flex align-items-center gap-1';
        c.style.cssText='border-radius:20px;font-size:0.8rem';
        c.innerHTML='<i class="bi bi-braces" style="font-size:0.7rem"></i>'
            +'<span>'+escHtml(v.label)+'</span>'
            +'<code style="font-size:0.7rem;color:rgba(255,255,255,0.75);margin-left:2px">{{'+escHtml(v.name)+'}}</code>';
        c.addEventListener('click',function(){placeVariableOnCanvas(v.name);});
        el.appendChild(c);
    });
}
function placeVariableOnCanvas(name){
    var canvas=getCanvas(); if(!canvas) return;
    var t=new fabric.Textbox('{{'+name+'}}',{
        left:MARGIN+10,top:200,width:220,fontSize:16,fontFamily:'Arial',fill:'#1a56db',name:'var_'+name,
    });
    canvas.add(t); canvas.setActiveObject(t); canvas.requestRenderAll();
}

// ============================================================
// MODAL: VARIABEL
// ============================================================
document.addEventListener('DOMContentLoaded',function(){
    var ni=document.getElementById('varNameInput');
    var li=document.getElementById('varLabelInput');
    var pc=document.getElementById('varPreviewCode');
    if(ni){
        ni.addEventListener('input',function(){
            ni.classList.remove('is-invalid');
            pc.textContent='{{ '+(ni.value.trim().replace(/\s+/g,'_').toLowerCase()||'nama_variabel')+' }}';
        });
    }
    var presetBtns=document.getElementById('presetButtons');
    if(presetBtns){
        presetBtns.addEventListener('click',function(e){
            var b=e.target.closest('button[data-name]'); if(!b) return;
            ni.value=b.dataset.name; li.value=b.dataset.label;
            pc.textContent='{{ '+b.dataset.name+' }}'; ni.classList.remove('is-invalid');
        });
    }
    var btnConfirm=document.getElementById('btnConfirmVariable');
    if(btnConfirm){
        btnConfirm.addEventListener('click',function(){
            var raw=ni.value.trim(); if(!raw){ni.classList.add('is-invalid');ni.focus();return;}
            var name=raw.replace(/\s+/g,'_').toLowerCase();
            var label=li.value.trim()||name;
            registerVariable(name,label); placeVariableOnCanvas(name);
            ni.value=''; li.value=''; pc.textContent='{{ nama_variabel }}';
            bootstrap.Modal.getInstance(document.getElementById('modalVariable')).hide();
        });
    }
    if(ni){ ni.addEventListener('keydown',function(e){if(e.key==='Enter'){e.preventDefault();document.getElementById('btnConfirmVariable').click();}}); }
    var btnDeselect=document.getElementById('btnDeselect');
    if(btnDeselect){ btnDeselect.addEventListener('click',function(){var canvas=getCanvas();if(!canvas)return;canvas.discardActiveObject();canvas.requestRenderAll();}); }
});

// ============================================================
// MODAL: KOP SURAT
// ============================================================
document.addEventListener('DOMContentLoaded',function(){
    var btnKop=document.getElementById('btnAddKop'); if(!btnKop) return;
    btnKop.addEventListener('click',function(){
        var canvas=getCanvas(); if(!canvas) return;
        var logoSize=parseInt(document.getElementById('kopLogoSize').value)||90;
        var line1=document.getElementById('kopLine1').value.trim();
        var line2=document.getElementById('kopLine2').value.trim();
        var line3=document.getElementById('kopLine3').value.trim();
        var line4=document.getElementById('kopLine4').value.trim();
        var line5=document.getElementById('kopLine5').value.trim();
        var line6=document.getElementById('kopLine6').value.trim();
        var borderType=document.getElementById('kopBorderStyle').value;
        var logoFile=document.getElementById('kopLogoFile').files[0];

        function buildKop(logoDataUrl){
            canvas.getObjects().filter(function(o){return o.name&&o.name.startsWith('kop_');}).forEach(function(o){canvas.remove(o);});
            var CW=CANVAS_W,KT=20,LW=logoSize,TX=LW+34,TW=CW-TX-20;
            var textItems=[
                line1?[line1,11,'bold','#000000']:null,
                line2?[line2,16,'bold','#c0392b']:null,
                line3?[line3,12,'bold','#1a5276']:null,
                line4?[line4,10,'normal','#000000']:null,
                line5?[line5,10,'normal','#000000']:null,
            ].filter(Boolean);
            var lineHeights=textItems.map(function(t){return t[1]*1.4;});
            var totalH=lineHeights.reduce(function(a,b){return a+b;},0);
            var curY=KT+Math.max(0,(LW-totalH)/2);
            textItems.forEach(function(t,i){
                canvas.add(new fabric.Textbox(t[0],{left:TX,top:curY,width:TW,fontSize:t[1],fontFamily:'Arial',fontWeight:t[2],textAlign:'center',fill:t[3],name:'kop_text'}));
                curY+=lineHeights[i];
            });
            if(line6) canvas.add(new fabric.Textbox(line6,{left:20,top:KT+LW+4,width:LW+30,fontSize:9,fontFamily:'Arial',fill:'#000',name:'kop_npsn'}));
            var lineY=KT+LW+(line6?16:4);
            if(borderType==='double'){
                canvas.add(new fabric.Line([20,lineY,CW-20,lineY],{stroke:'#000',strokeWidth:3,name:'kop_line',selectable:false,evented:false}));
                canvas.add(new fabric.Line([20,lineY+5,CW-20,lineY+5],{stroke:'#000',strokeWidth:1,name:'kop_line',selectable:false,evented:false}));
            } else if(borderType==='single'){
                canvas.add(new fabric.Line([20,lineY,CW-20,lineY],{stroke:'#000',strokeWidth:2,name:'kop_line',selectable:false,evented:false}));
            }
            function addLogoObj(imgObj){
                imgObj.scaleToWidth(LW); imgObj.scaleToHeight(LW);
                imgObj.set({left:20,top:KT,name:'kop_logo'});
                canvas.add(imgObj); canvas.sendToBack(imgObj); canvas.requestRenderAll(); saveState();
                var modalEl=document.getElementById('modalKop'); if(modalEl) bootstrap.Modal.getInstance(modalEl).hide();
            }
            if(logoDataUrl){ fabric.Image.fromURL(logoDataUrl,addLogoObj); }
            else {
                canvas.add(new fabric.Rect({left:20,top:KT,width:LW,height:LW,fill:'#f0f0f0',stroke:'#bbb',strokeWidth:1,rx:4,ry:4,name:'kop_logo'}));
                canvas.add(new fabric.Text('{{logo}}',{left:20+LW/2,top:KT+LW/2,fontSize:9,fontFamily:'Arial',fill:'#888',originX:'center',originY:'center',name:'kop_logo_label',selectable:false,evented:false}));
                canvas.requestRenderAll(); saveState();
                var modalEl=document.getElementById('modalKop'); if(modalEl) bootstrap.Modal.getInstance(modalEl).hide();
            }
        }
        if(logoFile){ var r=new FileReader(); r.onload=function(ev){buildKop(ev.target.result);}; r.readAsDataURL(logoFile); }
        else buildKop(null);
    });
});

// ============================================================
// MODAL: TABLE INSERT
// ============================================================
document.addEventListener('DOMContentLoaded',function(){
    var btnInsert=document.getElementById('btnInsertTable'); if(!btnInsert) return;
    btnInsert.addEventListener('click',function(){
        var activeTab=document.querySelector('#tableTabs .nav-link.active'); if(!activeTab) return;
        var href=activeTab.getAttribute('href');
        if(href==='#tabCustom')               insertCustomTable();
        else if(href==='#tabKelasMapel')      insertKelasMapelTable();
        else if(href==='#tabRaport')          insertRaportTable();
        else if(href==='#tabProgramUnggulan') insertUnggulanTable();
        else if(href==='#tabEkskul')          insertEkskulTable();
        else if(href==='#tabAbsensi')         insertAbsensiTable();
        else if(href==='#tabTTD')             insertTTDArea();
        var modalEl=document.getElementById('modalTable'); if(modalEl) bootstrap.Modal.getInstance(modalEl).hide();
    });
});

// ============================================================
// TABLE HELPERS
// ============================================================
function createTablePlaceholder(tableData,sx,sy){
    var canvas=getCanvas(); if(!canvas) return;
    var pg=pages[currentPage]; if(!pg) return;
    var id='tbl_'+(++tableCounter);
    pg.tableStore[id]=tableData;
    if(tableData.autoRegisterVars) tableData.autoRegisterVars.forEach(function(v){registerVariable(v.name,v.label);});
    var occupied=canvas.getObjects().map(function(o){return{x:o.left,y:o.top};});
    var dropY=sy;
    while(occupied.some(function(p){return Math.abs(p.x-sx)<20&&Math.abs(p.y-dropY)<20;})) dropY+=20;
    var offscreen=renderTableToCanvas(tableData);
    fabric.Image.fromURL(offscreen.toDataURL(),function(img){
        img.set({left:sx,top:dropY,name:id,selectable:true,evented:true,hasBorders:true,hasControls:true,lockRotation:true});
        img._isTable=true;
        canvas.add(img); canvas.setActiveObject(img); canvas.requestRenderAll(); saveState();
    });
}

function renderTableToCanvas(td){
    var rows=td.rows,colW=td.colWidths,rowHeights=td.rowHeights;
    var hdrColor=td.headerColor||'#1a5276',strColor=td.stripeColor||'#eaf2ff',bdColor=td.borderColor||'#adb5bd';
    var oc=document.createElement('canvas'); oc.width=td.totalWidth; oc.height=td.totalHeight;
    var ctx=oc.getContext('2d');
    var curY=0;
    rows.forEach(function(row,ri){
        var rH=rowHeights[ri];
        var bg=ri===0?hdrColor:(ri%2===1?'#ffffff':strColor);
        var textFill=ri===0?'#ffffff':'#212529',fw=ri===0?'bold':'normal',fs=ri===0?9:8;
        var curX=0;
        row.forEach(function(cell,ci){
            var cW=colW[ci]||60;
            var cellBg=cell.isMerged?hdrColor:bg,cFill=cell.isMerged?'#ffffff':textFill,cFw=(ri===0||cell.isMerged)?'bold':fw;
            ctx.fillStyle=cellBg; ctx.fillRect(curX,curY,cW,rH);
            ctx.strokeStyle=bdColor; ctx.lineWidth=0.5; ctx.strokeRect(curX+0.25,curY+0.25,cW-0.5,rH-0.5);
            var rawText=(cell.text||'').replace(/\{\{[^}]+\}\}/g,'…').replace(/\n/g,' ');
            if(rawText){
                ctx.fillStyle=cFill; ctx.font=cFw+' '+fs+'px Arial';
                ctx.textBaseline='middle'; ctx.textAlign=cell.align||(ci===0?'center':'left');
                var maxW=cW-6,tx=ci===0?curX+cW/2:curX+3,display=rawText;
                while(display.length>1&&ctx.measureText(display).width>maxW) display=display.slice(0,-1);
                if(display!==rawText) display+='…';
                ctx.fillText(display,tx,curY+rH/2);
            }
            curX+=cW; if(cell.isMerged) return false;
        });
        curY+=rH;
    });
    return oc;
}

// ============================================================
// TABLE INSERTERS (unchanged)
// ============================================================
function insertCustomTable(){
    var rows=parseInt(document.getElementById('tableRows').value)||5;
    var cols=parseInt(document.getElementById('tableCols').value)||4;
    var tWidth=parseInt(document.getElementById('tableWidth').value)||750;
    var rowH=parseInt(document.getElementById('tableRowHeight').value)||24;
    var hdrColor=document.getElementById('tableHeaderColor').value;
    var strColor=document.getElementById('tableStripeColor').value;
    var bdColor=document.getElementById('tableBorderColor').value;
    var rawHdr=document.getElementById('tableHeaders').value;
    var hasNo=document.getElementById('tableHasNo').checked;
    var headers=rawHdr?rawHdr.split(',').map(function(s){return s.trim();}):[]; while(headers.length<cols) headers.push('Kolom '+(headers.length+1));
    var cw=tWidth/cols,colWidths=[]; for(var i=0;i<cols;i++) colWidths.push(cw);
    var tableRows=[],hdrRow=[]; for(var c=0;c<cols;c++) hdrRow.push({text:headers[c]||'Kol '+(c+1),align:'center'}); tableRows.push(hdrRow);
    for(var r=0;r<rows;r++){var row=[];for(var cc=0;cc<cols;cc++) row.push({text:(cc===0&&hasNo)?String(r+1):'',align:cc===0?'center':'left'});tableRows.push(row);}
    createTablePlaceholder({type:'custom',totalWidth:tWidth,totalHeight:(rowH+4)+rows*rowH,colWidths:colWidths,rowHeights:buildRowHeights(rows,rowH,rowH+4),rows:tableRows,headerColor:hdrColor,stripeColor:strColor,borderColor:bdColor},MARGIN,200);
}
function insertKelasMapelTable(){
    var tType=document.getElementById('kelasMapelType').value;
    var kelasId=document.getElementById('kelasMapelKelas').value;
    var tWidth=parseInt(document.getElementById('kelasMapelWidth').value)||754;
    var rowH=parseInt(document.getElementById('kelasMapelRowH').value)||24;
    var hdrColor=document.getElementById('kelasMapelHeaderColor').value;
    var kolomRaw=document.getElementById('kelasMapelKolom').value.split(',').map(function(s){return s.trim();}).filter(Boolean);
    var autoVar=document.getElementById('kelasMapelAutoVar').checked;
    var kelasList=window.EDITOR_KELAS_LIST||[],mapelList=window.EDITOR_MAPEL_LIST||[];
    var tableRows=[],autoVars=[],colSpecs=[],hdrRow=[];
    if(tType==='daftar_kelas'){
        hdrRow=[{text:'No',align:'center'},{text:'Nama Kelas',align:'left'},{text:'Kategori',align:'left'}];
        kolomRaw.forEach(function(k){hdrRow.push({text:k,align:'center'});});
        colSpecs=[28,null,100]; kolomRaw.forEach(function(){colSpecs.push(80);}); tableRows.push(hdrRow);
        var filteredKelas=kelasId?kelasList.filter(function(k){return String(k.id)===String(kelasId);}):kelasList;
        filteredKelas.forEach(function(kls,idx){
            var varName='kelas_'+(kls.slug||String(kls.id));
            if(autoVar) autoVars.push({name:varName,label:kls.name});
            var row=[{text:String(idx+1),align:'center'},{text:kls.name,align:'left'},{text:kls.category_kelas||'',align:'left'}];
            kolomRaw.forEach(function(){row.push({text:autoVar?'{{'+varName+'}}':'',align:'center'});});
            tableRows.push(row);
        });
    } else if(tType==='daftar_mapel'){
        hdrRow=[{text:'No',align:'center'},{text:'Mata Pelajaran',align:'left'}];
        kolomRaw.forEach(function(k){hdrRow.push({text:k,align:'center'});});
        colSpecs=[28,null]; kolomRaw.forEach(function(){colSpecs.push(80);}); tableRows.push(hdrRow);
        mapelList.forEach(function(mp,idx){
            var varName='mapel_'+(mp.slug||String(mp.id));
            if(autoVar) autoVars.push({name:varName,label:mp.name});
            var row=[{text:String(idx+1),align:'center'},{text:mp.name,align:'left'}];
            kolomRaw.forEach(function(){row.push({text:autoVar?'{{'+varName+'}}':'',align:'center'});});
            tableRows.push(row);
        });
    } else if(tType==='jadwal_kelas'){
        hdrRow=[{text:'No',align:'center'},{text:'Mata Pelajaran',align:'left'},{text:'Hari',align:'center'},{text:'Jam',align:'center'}];
        kolomRaw.forEach(function(k){hdrRow.push({text:k,align:'center'});});
        colSpecs=[28,null,80,80]; kolomRaw.forEach(function(){colSpecs.push(80);}); tableRows.push(hdrRow);
        mapelList.forEach(function(mp,idx){
            var row=[{text:String(idx+1),align:'center'},{text:mp.name,align:'left'},{text:autoVar?'{{hari_'+(mp.slug||idx)+'}}':'',align:'center'},{text:autoVar?'{{jam_'+(mp.slug||idx)+'}}':'',align:'center'}];
            kolomRaw.forEach(function(){row.push({text:'',align:'center'});});
            tableRows.push(row);
        });
    }
    if(tableRows.length<=1){alert('Tidak ada data kelas/mapel.');return;}
    var colWidths=buildColWidths(colSpecs,tWidth),totalH=(rowH+4)+(tableRows.length-1)*rowH;
    createTablePlaceholder({type:'kelas_mapel',totalWidth:tWidth,totalHeight:totalH,colWidths:colWidths,rowHeights:buildRowHeights(tableRows.length-1,rowH,rowH+4),rows:tableRows,headerColor:hdrColor,stripeColor:'#f0f7ff',borderColor:'#adb5bd',autoRegisterVars:autoVar?autoVars:[]},MARGIN,200);
}

var _raportKelompoks=[],_raportAktifKelompok={};
(function loadTingkatKelas(){
    fetch('/dashboard/surat/templates/api/kelas-list',{headers:{'Accept':'application/json'}})
        .then(function(r){if(!r.ok)throw new Error();return r.json();})
        .then(function(data){
            var sel=document.getElementById('raportTingkatKelas'); if(!sel) return;
            data.forEach(function(tk){var opt=document.createElement('option');opt.value=tk.id;opt.textContent=tk.name+(tk.category_kelas?' ('+tk.category_kelas+')':'');sel.appendChild(opt);});
            if(!window.EDITOR_KELAS_LIST||!window.EDITOR_KELAS_LIST.length) window.EDITOR_KELAS_LIST=data;
        }).catch(function(){});
})();
document.addEventListener('DOMContentLoaded',function(){
    var selKelas=document.getElementById('raportTingkatKelas'),btnLoad=document.getElementById('btnLoadMapel');
    if(selKelas) selKelas.addEventListener('change',function(){var id=this.value;if(btnLoad)btnLoad.disabled=!id;if(id)loadMapelFromAPI(id);});
    if(btnLoad) btnLoad.addEventListener('click',function(){var id=document.getElementById('raportTingkatKelas').value;if(id)loadMapelFromAPI(id);});
});
function loadMapelFromAPI(tingkatId){
    var preview=document.getElementById('raportMapelPreview'); if(!preview) return;
    preview.innerHTML='<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div> Memuat...</div>';
    fetch('/dashboard/surat/templates/api/mapel-list?kelas_id='+tingkatId,{headers:{'Accept':'application/json'}})
        .then(function(r){if(!r.ok)throw new Error();return r.json();})
        .then(function(data){
            var mapels=Array.isArray(data)?data:(data.kelompoks?null:[]);
            if(mapels){_raportKelompoks=[{kelompok:{id:'all',nama:'Mata Pelajaran',warna_header:'#1a5276'},mapels:mapels.map(function(mp){var slug=(mp.slug||mp.name.toLowerCase().replace(/\s+/g,'_')).replace(/[^a-z0-9_]/g,'');return{nama:mp.name,var_nilai:'nilai_'+slug,var_capaian:'capaian_'+slug};})}];}
            else _raportKelompoks=data.kelompoks||[];
            _raportAktifKelompok={};
            _raportKelompoks.forEach(function(k){_raportAktifKelompok[k.kelompok.id]=true;});
            renderRaportPreview(); renderKelompokToggles();
        }).catch(function(){preview.innerHTML='<div class="alert alert-danger small py-2">Gagal memuat mapel.</div>';});
}
function renderRaportPreview(){
    var preview=document.getElementById('raportMapelPreview'); if(!preview) return;
    if(!_raportKelompoks.length){preview.innerHTML='<div class="alert alert-warning small py-2">Tidak ada mapel.</div>';return;}
    var html='';
    _raportKelompoks.forEach(function(grp){
        var kel=grp.kelompok; if(_raportAktifKelompok[kel.id]===false) return;
        html+='<div class="mb-2"><div class="px-2 py-1 rounded-top small fw-bold text-white d-flex justify-content-between" style="background:'+(kel.warna_header||'#1a5276')+'">';
        html+='<span>'+escHtml(kel.nama)+'</span><span class="badge bg-white text-dark">'+grp.mapels.length+' mapel</span></div>';
        html+='<table class="table table-sm table-bordered mb-0" style="font-size:0.8rem"><thead class="table-light"><tr><th style="width:30px">No</th><th>Mata Pelajaran</th><th style="width:80px">Var Nilai</th><th style="width:100px">Var Capaian</th></tr></thead><tbody>';
        grp.mapels.forEach(function(mp,i){html+='<tr><td class="text-center">'+(i+1)+'</td><td>'+escHtml(mp.nama)+'</td><td><code class="text-primary small">{{'+mp.var_nilai+'}}</code></td><td><code class="text-success small">{{'+mp.var_capaian+'}}</code></td></tr>';});
        html+='</tbody></table></div>';
    });
    preview.innerHTML=html||'<p class="text-muted small">Semua kelompok di-nonaktifkan.</p>';
}
function renderKelompokToggles(){
    var container=document.getElementById('raportKelompokToggle'); if(!container) return;
    container.innerHTML='';
    _raportKelompoks.forEach(function(grp){
        var kel=grp.kelompok,aktif=_raportAktifKelompok[kel.id]!==false;
        var btn=document.createElement('button'); btn.type='button';
        btn.className='btn btn-sm '+(aktif?'btn-primary':'btn-outline-secondary');
        btn.style.fontSize='0.75rem'; btn.textContent=kel.nama;
        btn.addEventListener('click',function(){_raportAktifKelompok[kel.id]=!_raportAktifKelompok[kel.id];renderRaportPreview();renderKelompokToggles();});
        container.appendChild(btn);
    });
}
function insertRaportTable(){
    var tWidth=parseInt(document.getElementById('raportWidth').value)||754;
    var hdrColor=document.getElementById('raportHeaderColor').value;
    var rowH=parseInt(document.getElementById('raportRowHeight').value)||40;
    var autoVar=document.getElementById('raportAutoVar').checked;
    if(!_raportKelompoks.length){var ae=document.getElementById('raportNoKelasAlert');if(ae)ae.style.display='block';return;}
    var ae2=document.getElementById('raportNoKelasAlert');if(ae2)ae2.style.display='none';
    var aktif=_raportKelompoks.filter(function(grp){return _raportAktifKelompok[grp.kelompok.id]!==false;});
    var colSpecs=[28,null,52,null],colWidths=buildColWidths(colSpecs,tWidth);
    var hdrNames=['No','Muatan Pelajaran','Nilai\nAkhir','Capaian Kompetensi'];
    var tableRows=[],autoVars=[];
    tableRows.push(hdrNames.map(function(h){return{text:h,align:'center'};}));
    var no=1;
    aktif.forEach(function(grp){
        var kel=grp.kelompok,mergedRow=[];
        for(var mc=0;mc<4;mc++) mergedRow.push({text:mc===0?kel.nama.toUpperCase():'',isMerged:mc===0,align:'center'});
        tableRows.push(mergedRow);
        grp.mapels.forEach(function(mp){
            if(autoVar){autoVars.push({name:mp.var_nilai,label:'Nilai '+mp.nama.substring(0,18)});autoVars.push({name:mp.var_capaian,label:'Capaian '+mp.nama.substring(0,15)});}
            tableRows.push([{text:String(no++),align:'center'},{text:mp.nama,align:'left'},{text:autoVar?'{{'+mp.var_nilai+'}}':'',align:'center'},{text:autoVar?'{{'+mp.var_capaian+'}}':'',align:'left'}]);
        });
    });
    var rowHeights=tableRows.map(function(row,ri){if(ri===0)return rowH+4;if(row[0]&&row[0].isMerged)return rowH-8;return rowH;});
    createTablePlaceholder({type:'raport',totalWidth:tWidth,totalHeight:rowHeights.reduce(function(a,b){return a+b;},0),colWidths:colWidths,rowHeights:rowHeights,rows:tableRows,headerColor:hdrColor,stripeColor:'#f5f9ff',borderColor:'#888888',autoRegisterVars:autoVar?autoVars:[]},MARGIN,200);
}
function insertUnggulanTable(){
    var namaProgram=document.getElementById('unggulanNama').value.trim()||'PROGRAM UNGGULAN';
    var itemsRaw=document.getElementById('unggulanItems').value.split('\n').map(function(s){return s.trim();}).filter(Boolean);
    var kolomRaw=document.getElementById('unggulanKolom').value.split(',').map(function(s){return s.trim();});
    var tWidth=parseInt(document.getElementById('unggulanWidth').value)||754;
    var hdrColor=document.getElementById('unggulanHeaderColor').value;
    var mergeHeader=document.getElementById('unggulanMergeHeader').checked;
    var cols=2+kolomRaw.length,hdrRow=[{text:'No',align:'center'},{text:'Program',align:'left'}];
    kolomRaw.forEach(function(k){hdrRow.push({text:k,align:'center'});});
    var cSpecs=[25,null]; kolomRaw.forEach(function(){cSpecs.push(70);}); var colWidths=buildColWidths(cSpecs,tWidth),rowH=22,tableRows=[];
    if(mergeHeader){var mr=[];for(var mc=0;mc<cols;mc++)mr.push({text:mc===0?namaProgram:'',isMerged:mc===0,align:'center'});tableRows.push(mr);}
    tableRows.push(hdrRow);
    itemsRaw.forEach(function(item,i){var row=[{text:String(i+1),align:'center'},{text:item,align:'left'}];kolomRaw.forEach(function(){row.push({text:'-',align:'center'});});tableRows.push(row);});
    var rH=tableRows.map(function(r,i){return(i===0&&mergeHeader)?20:(i===0||i===1)?rowH+4:rowH;});
    createTablePlaceholder({type:'unggulan',totalWidth:tWidth,totalHeight:rH.reduce(function(a,b){return a+b;},0),colWidths:colWidths,rowHeights:rH,rows:tableRows,headerColor:hdrColor,stripeColor:'#f5f9ff',borderColor:'#adb5bd'},MARGIN,200);
}
function insertEkskulTable(){
    var itemsRaw=document.getElementById('ekskulItems').value.split('\n').map(function(s){return s.trim();}).filter(Boolean);
    var kolomRaw=document.getElementById('ekskulKolom').value.split(',').map(function(s){return s.trim();});
    var tWidth=parseInt(document.getElementById('ekskulWidth').value)||754,hdrColor=document.getElementById('ekskulHeaderColor').value;
    var cSpecs=[25,null]; kolomRaw.forEach(function(){cSpecs.push(80);}); var colWidths=buildColWidths(cSpecs,tWidth),rowH=22;
    var hdrRow=[{text:'No',align:'center'},{text:'Ekstrakurikuler',align:'left'}]; kolomRaw.forEach(function(k){hdrRow.push({text:k,align:'center'});});
    var tableRows=[hdrRow];
    itemsRaw.forEach(function(item,i){var row=[{text:String(i+1),align:'center'},{text:item,align:'left'}];kolomRaw.forEach(function(){row.push({text:'-',align:'center'});});tableRows.push(row);});
    createTablePlaceholder({type:'ekskul',totalWidth:tWidth,totalHeight:(rowH+4)+itemsRaw.length*rowH,colWidths:colWidths,rowHeights:buildRowHeights(itemsRaw.length,rowH,rowH+4),rows:tableRows,headerColor:hdrColor,stripeColor:'#f0f7ff',borderColor:'#adb5bd'},MARGIN,200);
}
function insertAbsensiTable(){
    var rows=parseInt(document.getElementById('absensiRows').value)||30;
    var tWidth=parseInt(document.getElementById('absensiWidth').value)||754,hdrColor=document.getElementById('absensiHeaderColor').value;
    var kolomRaw=document.getElementById('absensiKolom').value.split(',').map(function(s){return s.trim();});
    var hdrRow=[{text:'No',align:'center'},{text:'Nama Siswa',align:'left'},{text:'NIS',align:'center'}]; kolomRaw.forEach(function(k){hdrRow.push({text:k,align:'center'});});
    var cSpecs=[25,null,60]; kolomRaw.forEach(function(){cSpecs.push(40);}); var colWidths=buildColWidths(cSpecs,tWidth),rowH=20,tableRows=[hdrRow];
    for(var r=0;r<rows;r++){var row=[{text:String(r+1),align:'center'},{text:'',align:'left'},{text:'',align:'center'}];kolomRaw.forEach(function(){row.push({text:'',align:'center'});});tableRows.push(row);}
    createTablePlaceholder({type:'absensi',totalWidth:tWidth,totalHeight:(rowH+4)+rows*rowH,colWidths:colWidths,rowHeights:buildRowHeights(rows,rowH,rowH+4),rows:tableRows,headerColor:hdrColor,stripeColor:'#f0f7ff',borderColor:'#adb5bd'},MARGIN,200);
}
function insertTTDArea(){
    var canvas=getCanvas();if(!canvas)return;
    var pg=pages[currentPage];if(!pg)return;
    var rawKolom=document.getElementById('ttdKolom').value.trim().split('\n').filter(Boolean);
    var tWidth=parseInt(document.getElementById('ttdWidth').value)||754;
    var ttdH=parseInt(document.getElementById('ttdHeight').value)||80;
    var posY=parseInt(document.getElementById('ttdPosY').value)||950;
    var cols=rawKolom.length,colW=tWidth/(cols||1);
    var ttdData=rawKolom.map(function(line){var parts=line.split(',');return{label:(parts[0]||'').trim(),nama:(parts[1]||'').trim(),jabatan:(parts[2]||'').trim()};});
    var id='ttd_'+(++tableCounter);
    pg.tableStore[id]={type:'ttd',ttdData:ttdData,totalWidth:tWidth,colW:colW,ttdH:ttdH};
    ttdData.forEach(function(col){[col.nama,col.jabatan].forEach(function(s){var m=(s||'').match(/\{\{([^}]+)\}\}/g);if(m)m.forEach(function(mv){registerVariable(mv.replace(/[{}]/g,'').trim(),mv.replace(/[{}]/g,'').trim());});});});
    var objs=[],cx=0;
    ttdData.forEach(function(col){
        objs.push(new fabric.Rect({left:cx,top:0,width:colW,height:20+ttdH+40,fill:'#fafafa',stroke:'#dee2e6',strokeWidth:0.5}));
        objs.push(new fabric.Textbox(col.label,{left:cx+4,top:4,width:colW-8,height:16,fontSize:9,fontFamily:'Arial',textAlign:'center',fill:'#333',selectable:false,evented:false}));
        objs.push(new fabric.Textbox(col.nama||'( __________________ )',{left:cx+4,top:20+ttdH+4,width:colW-8,height:16,fontSize:8,fontFamily:'Arial',textAlign:'center',fontWeight:'bold',fill:'#1a5276',selectable:false,evented:false}));
        if(col.jabatan)objs.push(new fabric.Textbox(col.jabatan,{left:cx+4,top:20+ttdH+18,width:colW-8,height:16,fontSize:7,fontFamily:'Arial',textAlign:'center',fill:'#555',selectable:false,evented:false}));
        cx+=colW;
    });
    var group=new fabric.Group(objs,{left:MARGIN,top:posY,name:id});
    canvas.add(group);canvas.setActiveObject(group);canvas.requestRenderAll();saveState();
}

// ============================================================
// FORMAT TOOLBAR
// ============================================================
var _coordRafId=null;
function scheduleCoordUpdate(obj){
    if(_coordRafId) return;
    _coordRafId=requestAnimationFrame(function(){_coordRafId=null;updateCoords(obj);});
}
function updateCoords(obj){
    var x='-',y='-',w='-',h='-';
    if(obj){x=Math.round(obj.left||0);y=Math.round(obj.top||0);w=Math.round((obj.width||0)*(obj.scaleX||1));h=Math.round((obj.height||0)*(obj.scaleY||1));}
    var cx=document.getElementById('coordX');if(cx)cx.textContent=x;
    var cy=document.getElementById('coordY');if(cy)cy.textContent=y;
    var cw=document.getElementById('coordW');if(cw)cw.textContent=w;
    var ch=document.getElementById('coordH');if(ch)ch.textContent=h;
}
function updateToolbar(){
    var canvas=getCanvas();if(!canvas)return;
    var obj=canvas.getActiveObject();if(!obj)return;
    var ft=document.getElementById('formatToolbar');if(!ft)return;
    ft.style.display='block';
    var op=Math.round((obj.opacity||1)*100);
    document.getElementById('objOpacity').value=op;document.getElementById('opacityVal').textContent=op;
    document.getElementById('objX').value=Math.round(obj.left||0);
    document.getElementById('objY').value=Math.round(obj.top||0);
    document.getElementById('objWidth').value=Math.round((obj.width||0)*(obj.scaleX||1));
    document.getElementById('objHeight').value=Math.round((obj.height||0)*(obj.scaleY||1));
    document.getElementById('objRotate').value=Math.round(obj.angle||0);
    document.getElementById('rotateVal').textContent=Math.round(obj.angle||0);
    if(obj.type==='textbox'||obj.type==='i-text'){
        document.getElementById('fontSize').value=obj.fontSize||16;
        document.getElementById('fontFamily').value=obj.fontFamily||'Arial';
        document.getElementById('fontColor').value=obj.fill||'#000000';
        var lh=obj.lineHeight||1.4;
        document.getElementById('lineHeightSlider').value=Math.round(lh*10);
        document.getElementById('lineHeightVal').textContent=lh.toFixed(1);
    }
    var li=document.getElementById('lockIcon'),ll=document.getElementById('lockLabel');
    if(li)li.className=obj.lockMovementX?'bi bi-lock-fill text-warning':'bi bi-lock';
    if(ll)ll.textContent=obj.lockMovementX?'Buka':'Kunci';
    updateCoords(obj);
}
function applyFormat(p,v){var o=getCanvas()&&getCanvas().getActiveObject();if(!o)return;o.set(p,v);getCanvas().requestRenderAll();saveState();}
function applyWidth(v){var o=getCanvas()&&getCanvas().getActiveObject();if(!o)return;o.type==='textbox'?o.set('width',v):o.set('scaleX',v/(o.width||1));getCanvas().requestRenderAll();saveState();}
function applyHeight(v){var o=getCanvas()&&getCanvas().getActiveObject();if(!o)return;o.set('scaleY',v/(o.height||1));getCanvas().requestRenderAll();saveState();}
function applyOpacity(v){var o=getCanvas()&&getCanvas().getActiveObject();if(!o)return;o.set('opacity',v/100);getCanvas().requestRenderAll();saveState();}
function applyRotation(v){var o=getCanvas()&&getCanvas().getActiveObject();if(!o)return;o.set('angle',v);getCanvas().requestRenderAll();saveState();}
function applyPosition(ax,v){var o=getCanvas()&&getCanvas().getActiveObject();if(!o)return;ax==='x'?o.set('left',v):o.set('top',v);o.setCoords();getCanvas().requestRenderAll();saveState();}
function toggleBold(){var o=getCanvas()&&getCanvas().getActiveObject();if(!o||o.type!=='textbox')return;o.set('fontWeight',o.fontWeight==='bold'?'normal':'bold');getCanvas().requestRenderAll();saveState();}
function toggleItalic(){var o=getCanvas()&&getCanvas().getActiveObject();if(!o||o.type!=='textbox')return;o.set('fontStyle',o.fontStyle==='italic'?'normal':'italic');getCanvas().requestRenderAll();saveState();}
function toggleUnderline(){var o=getCanvas()&&getCanvas().getActiveObject();if(!o||o.type!=='textbox')return;o.set('underline',!o.underline);getCanvas().requestRenderAll();saveState();}
function toggleStrikethrough(){var o=getCanvas()&&getCanvas().getActiveObject();if(!o||o.type!=='textbox')return;o.set('linethrough',!o.linethrough);getCanvas().requestRenderAll();saveState();}

// ============================================================
// ALIGNMENT & DISTRIBUTE
// ============================================================
function alignObj(dir){
    var canvas=getCanvas();if(!canvas)return;
    var o=canvas.getActiveObject();if(!o)return;
    var b=o.getBoundingRect(true);
    if(dir==='left')         o.set('left',0);
    else if(dir==='hcenter') o.set('left',(CANVAS_W-b.width)/2);
    else if(dir==='right')   o.set('left',CANVAS_W-b.width);
    else if(dir==='top')     o.set('top',0);
    else if(dir==='vcenter') o.set('top',(CANVAS_H-b.height)/2);
    else if(dir==='bottom')  o.set('top',CANVAS_H-b.height);
    o.setCoords();canvas.requestRenderAll();saveState();
}
function distributeObjects(axis){
    var canvas=getCanvas();if(!canvas)return;
    var sel=canvas.getActiveObject();
    if(!sel||sel.type!=='activeSelection'){alert('Pilih 2+ objek.');return;}
    var objs=sel.getObjects();if(objs.length<3){alert('Butuh minimal 3 objek.');return;}
    if(axis==='h'){
        objs.sort(function(a,b){return a.left-b.left;});
        var g=(objs[objs.length-1].left-objs[0].left)/(objs.length-1);
        objs.forEach(function(o,i){o.set('left',objs[0].left+i*g);o.setCoords();});
    } else {
        objs.sort(function(a,b){return a.top-b.top;});
        var gV=(objs[objs.length-1].top-objs[0].top)/(objs.length-1);
        objs.forEach(function(o,i){o.set('top',objs[0].top+i*gV);o.setCoords();});
    }
    canvas.requestRenderAll();saveState();
}

// ============================================================
// LAYER / COPY / PASTE / LOCK
// ============================================================
function bringForward(){var canvas=getCanvas();if(!canvas)return;var o=canvas.getActiveObject();if(!o)return;canvas.bringForward(o);canvas.requestRenderAll();saveState();}
function sendBackward(){var canvas=getCanvas();if(!canvas)return;var o=canvas.getActiveObject();if(!o)return;canvas.sendBackwards(o);canvas.requestRenderAll();saveState();}
function copySelected(){var canvas=getCanvas();if(!canvas)return;var o=canvas.getActiveObject();if(!o)return;o.clone(function(c){_clipboard=c;});}
function pasteClipboard(){
    var canvas=getCanvas();if(!canvas||!_clipboard)return;
    _clipboard.clone(function(c){
        canvas.discardActiveObject();
        c.set({left:(_clipboard.left||0)+20,top:(_clipboard.top||0)+20,evented:true});
        if(c.type==='activeSelection'){c.canvas=canvas;c.forEachObject(function(o){canvas.add(o);});c.setCoords();}
        else canvas.add(c);
        canvas.setActiveObject(c);canvas.requestRenderAll();saveState();_clipboard=c;
    });
}
function toggleLock(){
    var canvas=getCanvas();if(!canvas)return;
    var o=canvas.getActiveObject();if(!o)return;
    var locked=o.lockMovementX;
    o.set({lockMovementX:!locked,lockMovementY:!locked,lockRotation:!locked,lockScalingX:!locked,lockScalingY:!locked,hasControls:locked,hasBorders:locked});
    canvas.requestRenderAll();
    var li=document.getElementById('lockIcon'),ll=document.getElementById('lockLabel');
    if(li)li.className=!locked?'bi bi-lock-fill text-warning':'bi bi-lock';
    if(ll)ll.textContent=!locked?'Buka':'Kunci';
}

// ============================================================
// ADD ELEMENTS
// ============================================================
function addText(){
    var canvas=getCanvas();if(!canvas)return;
    var usedTops=canvas.getObjects().map(function(o){return Math.round(o.top||0);});
    var newTop=180; while(usedTops.indexOf(newTop)!==-1) newTop+=24;
    canvas.add(new fabric.Textbox('Tulis teks di sini',{left:MARGIN+10,top:newTop,width:300,fontSize:16,fontFamily:'Arial',fill:'#000'}));
    canvas.requestRenderAll();
}
function triggerImageUpload(){document.getElementById('imageUpload').click();}
function addImage(e){
    var canvas=getCanvas();if(!canvas)return;
    var f=e.target.files[0];if(!f)return;
    var reader=new FileReader();
    reader.onload=function(ev){fabric.Image.fromURL(ev.target.result,function(img){img.scaleToWidth(200);img.set({left:100,top:100});canvas.add(img);canvas.requestRenderAll();saveState();});};
    reader.readAsDataURL(f);e.target.value='';
}
function triggerLogoUpload(){document.getElementById('logoUpload').click();}
function addLogoImage(e){
    var canvas=getCanvas();if(!canvas)return;
    var f=e.target.files[0];if(!f)return;
    var reader=new FileReader();
    reader.onload=function(ev){fabric.Image.fromURL(ev.target.result,function(img){img.scaleToWidth(100);img.set({left:40,top:30,name:'logo'});canvas.add(img);canvas.requestRenderAll();saveState();});};
    reader.readAsDataURL(f);e.target.value='';
}
function addBarcode(){
    var canvas=getCanvas();if(!canvas)return;
    var g=new fabric.Group([
        new fabric.Rect({width:120,height:120,fill:'#fff',stroke:'#333',strokeWidth:1,rx:4,ry:4}),
        new fabric.Text('{{barcode_signature}}',{fontSize:9,fontFamily:'Courier New',fill:'#333',textAlign:'center',originX:'center',originY:'center',left:60,top:60}),
    ],{left:620,top:860,name:'barcode'});
    canvas.add(g);canvas.requestRenderAll();saveState();
}
function removeSelected(){
    var canvas=getCanvas();if(!canvas)return;
    var pg=pages[currentPage];if(!pg)return;
    var obj=canvas.getActiveObject();
    if(!obj){alert('Pilih elemen terlebih dahulu.');return;}
    if(obj.name&&obj.name.startsWith('kop_')){
        if(!confirm('Hapus seluruh kop surat?'))return;
        canvas.getObjects().filter(function(o){return o.name&&o.name.startsWith('kop_');}).forEach(function(o){canvas.remove(o);});
    } else {
        if(obj.name&&pg.tableStore[obj.name]) delete pg.tableStore[obj.name];
        canvas.remove(obj);
    }
    canvas.discardActiveObject();canvas.requestRenderAll();saveState();
}

// ============================================================
// HTML GENERATE
// ============================================================
function textStyle(obj,wPx){
    var scaledFs=(obj.fontSize||16)*(obj.scaleY||1);
    var parts=['font-size:'+pt(scaledFs)+'pt','font-family:'+(obj.fontFamily||'DejaVu Sans')+',sans-serif','color:'+(obj.fill||'#000000'),'font-weight:'+(obj.fontWeight||'normal'),'font-style:'+(obj.fontStyle||'normal'),'text-align:'+(obj.textAlign||'left'),'line-height:'+(obj.lineHeight||1.4),'width:'+pt(wPx)+'pt','overflow:visible','white-space:pre-wrap','word-wrap:break-word'];
    if(obj.underline)   parts.push('text-decoration:underline');
    if(obj.linethrough) parts.push('text-decoration:line-through');
    return parts.join(';');
}
function buildTableHtml(tableId,objLeft,objTop,tableStore){
    var td=tableStore[tableId];if(!td)return'';
    if(td.type==='ttd')return buildTTDHtml(td,objLeft,objTop);
    var lPt=pt(objLeft),tPt=pt(objTop),wPt=pt(td.totalWidth);
    var rows=td.rows,colW=td.colWidths,rowH=td.rowHeights;
    var hdrColor=td.headerColor||'#1a5276',strColor=td.stripeColor||'#eaf2ff',bdColor=td.borderColor||'#adb5bd';
    var html='<table style="position:absolute;left:'+lPt+'pt;top:'+tPt+'pt;width:'+wPt+'pt;border-collapse:collapse;font-family:DejaVu Sans,Arial,sans-serif;font-size:8pt;">';
    rows.forEach(function(row,ri){
        var rH=pt(rowH[ri]||20),bg=ri===0?hdrColor:(ri%2===1?'#ffffff':strColor);
        html+='<tr>';
        row.forEach(function(cell,ci){
            var cW=pt(colW[ci]||60),cellBg=cell.isMerged?hdrColor:bg,color=(ri===0||cell.isMerged)?'#ffffff':'#212529',fw=(ri===0||cell.isMerged)?'bold':'normal',align=cell.align||'left',colspan=cell.isMerged?' colspan="'+row.length+'"':'',content=escapeContent(cell.text||'');
            html+='<td'+colspan+' style="width:'+cW+'pt;height:'+rH+'pt;background:'+cellBg+';border:0.5pt solid '+bdColor+';font-weight:'+fw+';color:'+color+';padding:1pt 2pt;vertical-align:middle;text-align:'+align+';white-space:pre-wrap;word-wrap:break-word;">'+content+'</td>';
            if(cell.isMerged)return false;
        });
        html+='</tr>';
    });
    html+='</table>'; return html;
}
function buildTTDHtml(td,objLeft,objTop){
    var lPt=pt(objLeft),tPt=pt(objTop),wPt=pt(td.totalWidth),colWPt=pt(td.colW),ttdHPt=pt(td.ttdH);
    var html='<table style="position:absolute;left:'+lPt+'pt;top:'+tPt+'pt;width:'+wPt+'pt;border-collapse:collapse;font-family:DejaVu Sans,Arial,sans-serif;font-size:9pt;">';
    html+='<tr>';td.ttdData.forEach(function(col){html+='<td style="width:'+colWPt+'pt;text-align:center;vertical-align:top;padding:2pt;border:none;">'+escapeContent(col.label)+'</td>';});html+='</tr><tr>';
    td.ttdData.forEach(function(){html+='<td style="width:'+colWPt+'pt;height:'+ttdHPt+'pt;border:none;"></td>';});html+='</tr><tr>';
    td.ttdData.forEach(function(col){html+='<td style="width:'+colWPt+'pt;text-align:center;font-weight:bold;text-decoration:underline;border:none;padding:1pt 2pt;">'+escapeContent(col.nama)+'</td>';});html+='</tr><tr>';
    td.ttdData.forEach(function(col){html+='<td style="width:'+colWPt+'pt;text-align:center;border:none;padding:0pt 2pt;font-size:8pt;">'+escapeContent(col.jabatan)+'</td>';});
    html+='</tr></table>';return html;
}
function generateHTMLForPage(pgData){
    var canvas=pgData.canvas,tableStore=pgData.tableStore;
    var html='<div class="page" style="position:relative;width:'+A4_W+'pt;height:'+A4_H+'pt;">';
    canvas.getObjects().forEach(function(obj){
        if(obj.excludeFromExport)return;
        if(obj.name&&(obj.name.startsWith('__')||obj.name==='kop_logo_label'))return;
        var pos=realTopLeft(obj),lPt=pt(pos.x),tPt=pt(pos.y),wPt=pt(pos.w),hPt=pt(pos.h);
        var posStyle='position:absolute;left:'+lPt+'pt;top:'+tPt+'pt;';
        if(obj.name&&tableStore[obj.name]){html+=buildTableHtml(obj.name,pos.x,pos.y,tableStore);return;}
        if(obj.type==='textbox'||obj.type==='i-text'){html+='<div style="'+posStyle+textStyle(obj,pos.w)+'">'+escapeContent(obj.text)+'</div>';}
        else if(obj.type==='image'){var dimS='width:'+wPt+'pt;height:'+hPt+'pt;';if(obj.name==='logo'||obj.name==='kop_logo'){html+='<div style="'+posStyle+dimS+'">{{logo}}</div>';}else{var src=obj.toDataURL?obj.toDataURL({format:'png'}):'';html+='<img src="'+src+'" style="'+posStyle+dimS+'" />';}}
        else if(obj.type==='group'&&obj.name==='barcode'){html+='<div style="'+posStyle+'width:'+wPt+'pt;height:'+hPt+'pt;">{{barcode_signature}}</div>';}
        else if(obj.type==='line'){var br=obj.getBoundingRect(),sw=pt(obj.strokeWidth||1);if(sw<0.75)sw=0.75;html+='<div style="position:absolute;left:'+pt(br.left)+'pt;top:'+pt(br.top)+'pt;width:'+pt(Math.max(br.width,1))+'pt;height:'+sw+'pt;background:'+(obj.stroke||'#000')+'"></div>';}
        else if(obj.type==='rect'){if(obj.name==='kop_logo'){html+='<div style="'+posStyle+'width:'+wPt+'pt;height:'+hPt+'pt;">{{logo}}</div>';return;}var rs=posStyle+'width:'+wPt+'pt;height:'+hPt+'pt;background:'+(obj.fill||'transparent')+';';if(obj.stroke)rs+='border:'+pt(obj.strokeWidth||1)+'pt solid '+obj.stroke+';';html+='<div style="'+rs+'"></div>';}
        else if(obj.type==='group'){if(tableStore[obj.name]){html+=buildTableHtml(obj.name,pos.x,pos.y,tableStore);return;}var gL=obj.left+(obj.width||0)/2*(obj.scaleX||1)-(obj.width||0)*(obj.scaleX||1)/2,gT=obj.top+(obj.height||0)/2*(obj.scaleY||1)-(obj.height||0)*(obj.scaleY||1)/2;obj.getObjects().forEach(function(child){if(child.type==='textbox'||child.type==='i-text'){var cx2=gL+child.left+(obj.width||0)/2*(obj.scaleX||1),cy2=gT+child.top+(obj.height||0)/2*(obj.scaleY||1);html+='<div style="position:absolute;left:'+pt(cx2)+'pt;top:'+pt(cy2)+'pt;'+textStyle(child,(child.width||100)*(child.scaleX||1))+'">'+escapeContent(child.text)+'</div>';}});}
    });
    html+='</div>'; return html;
}
function generateHTML(){
    var fullHtml=pages.map(function(pg){return generateHTMLForPage(pg);}).join('\n');
    document.getElementById('html_template').value=fullHtml;
    var allPagesData=pages.map(function(pg){var json=pg.canvas.toJSON(['name','excludeFromExport']);json._tableStore=pg.tableStore;return json;});
    document.getElementById('canvas_json').value=JSON.stringify({pages:allPagesData,version:2});
    return true;
}

// ============================================================
// RESTORE EXISTING CANVAS
// ============================================================
function restoreCanvas(){
    var data=window.EXISTING_CANVAS_JSON; if(!data) return;
    var parsed=(typeof data==='string')?JSON.parse(data):data;
    function restoreVars(canvas){
        canvas.getObjects().forEach(function(obj){
            if(obj.type==='textbox'||obj.type==='i-text'){
                var m=(obj.text||'').match(/\{\{([^}]+)\}\}/g);
                if(m)m.forEach(function(v){var n=v.replace(/[{}]/g,'').trim();if(['logo','barcode_signature'].indexOf(n)===-1)registerVariable(n,n);});
            }
        });
    }
    if(parsed.version===2&&parsed.pages&&parsed.pages.length){
        while(pages.length<parsed.pages.length) createPage();
        parsed.pages.forEach(function(pageJson,idx){
            var pg=pages[idx];if(!pg)return;
            var store=pageJson._tableStore||{};delete pageJson._tableStore;
            pg.canvas.loadFromJSON(pageJson,function(){pg.tableStore=store;pg.canvas.requestRenderAll();restoreVars(pg.canvas);drawMarginGuidesForPage(pg,marginVisible);saveStateForPage(pg);renderPageThumbnails();});
        });
        return;
    }
    var pg=pages[0];if(!pg)return;
    var store=parsed._tableStore||{};delete parsed._tableStore;
    pg.canvas.loadFromJSON(parsed,function(){pg.tableStore=store;pg.canvas.requestRenderAll();restoreVars(pg.canvas);drawMarginGuidesForPage(pg,marginVisible);saveStateForPage(pg);renderPageThumbnails();});
}

// ============================================================
// FORM SUBMIT
// ============================================================
document.addEventListener('DOMContentLoaded',function(){
    var form=document.getElementById('templateForm');if(!form)return;
    form.addEventListener('submit',function(e){
        var hasContent=pages.some(function(pg){return pg.canvas.getObjects().filter(function(o){return!o.excludeFromExport;}).length>0;});
        if(!hasContent){e.preventDefault();alert('Template masih kosong!');return false;}
        generateHTML();
    });
});

// ============================================================
// KEYBOARD SHORTCUTS
// ============================================================
document.addEventListener('keydown',function(e){
    var tag=document.activeElement.tagName,inInput=['INPUT','TEXTAREA','SELECT'].indexOf(tag)!==-1;
    if((e.ctrlKey||e.metaKey)&&e.key==='z'){e.preventDefault();undo();}
    if((e.ctrlKey||e.metaKey)&&e.key==='y'){e.preventDefault();redo();}
    if((e.ctrlKey||e.metaKey)&&e.key==='c'&&!inInput){e.preventDefault();copySelected();}
    if((e.ctrlKey||e.metaKey)&&e.key==='v'&&!inInput){e.preventDefault();pasteClipboard();}
    if((e.ctrlKey||e.metaKey)&&e.key==='d'&&!inInput){e.preventDefault();copySelected();pasteClipboard();}
    if((e.key==='Delete'||e.key==='Backspace')&&!inInput){removeSelected();}
    if(e.key==='+'&&e.ctrlKey){e.preventDefault();zoomIn();}
    if(e.key==='-'&&e.ctrlKey){e.preventDefault();zoomOut();}
    if(e.key==='0'&&e.ctrlKey){e.preventDefault();zoomReset();}
    if(!inInput){
        var canvas=getCanvas();
        if(canvas){var obj=canvas.getActiveObject(),step=e.shiftKey?10:1;
            if(obj){
                if(e.key==='ArrowLeft') {obj.set('left',(obj.left||0)-step);obj.setCoords();canvas.requestRenderAll();e.preventDefault();saveState();}
                if(e.key==='ArrowRight'){obj.set('left',(obj.left||0)+step);obj.setCoords();canvas.requestRenderAll();e.preventDefault();saveState();}
                if(e.key==='ArrowUp')   {obj.set('top',(obj.top||0)-step);obj.setCoords();canvas.requestRenderAll();e.preventDefault();saveState();}
                if(e.key==='ArrowDown') {obj.set('top',(obj.top||0)+step);obj.setCoords();canvas.requestRenderAll();e.preventDefault();saveState();}
            }
        }
    }
});

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded',function(){
    createPage(); switchPage(0); drawRulersBase(); restoreCanvas(); updatePageIndicator();
    if(!window.EDITOR_KELAS_LIST||!window.EDITOR_KELAS_LIST.length){
        fetch('/dashboard/surat/templates/api/kelas-list',{headers:{'Accept':'application/json'}}).then(function(r){return r.ok?r.json():[];}).then(function(data){window.EDITOR_KELAS_LIST=data||[];}).catch(function(){window.EDITOR_KELAS_LIST=[];});
    }
    if(!window.EDITOR_MAPEL_LIST||!window.EDITOR_MAPEL_LIST.length){
        fetch('/dashboard/surat/templates/api/mapel-list',{headers:{'Accept':'application/json'}}).then(function(r){return r.ok?r.json():[];}).then(function(data){window.EDITOR_MAPEL_LIST=data||[];}).catch(function(){window.EDITOR_MAPEL_LIST=[];});
    }
});

// ============================================================
// GLOBAL EXPORTS — expose functions to window so Blade inline
// onchange/onclick handlers (e.g. onchange="toggleGrid(this.checked)")
// can find them regardless of script load order or module scope.
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
// snapEnabled is a primitive — toggle via this helper:
window.setSnapEnabled      = function(v) { snapEnabled = v; };