/**
 * canvas-events.js — event listener per halaman (snap, guides, format toolbar,
 *                    koordinat live, keyboard shortcuts)
 *
 * Depends: semua modul lainnya
 */

// ─────────────────────────────────────────────────────────────
// ATTACH PAGE EVENTS
// ─────────────────────────────────────────────────────────────

function attachPageEvents(pgData) {
    var fc = pgData.canvas;

    // Pindah halaman aktif saat klik canvas lain
    fc.on('mouse:down', function () {
        var idx = pages.indexOf(pgData);
        if (idx !== -1 && idx !== currentPage) {
            setTimeout(function () { switchPage(idx); }, 0);
        }
    });

    // Update ruler cursor
    fc.on('mouse:move', function (opt) {
        if (pgData !== pages[currentPage]) return;
        var p = opt.absolutePointer || opt.pointer;
        if (!p || _rulerRafId) return;
        _rulerRafId = requestAnimationFrame(function () {
            _rulerRafId = null;
            drawRulerCursor(p.x, p.y);
        });
    });

    // Siapkan snap points saat mulai drag
    fc.on('mouse:down', function (e) {
        if (pgData !== pages[currentPage]) return;

        pgData._pageSnapPoints = [];
        pgData._objGuidePoints = [];
        pgData._activeSnapX    = null;
        pgData._activeSnapY    = null;
        pgData._prevSnapX      = null;
        pgData._prevSnapY      = null;

        if (!e.target) return;
        var obj = e.target;

        // Canvas snap points (garis tengah, margin, tepi)
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

        // Snap points dari objek lain
        fc.getObjects().forEach(function (o) {
            if (o === obj || !o.selectable) return;
            if (o.name && (o.name.startsWith('__') || o.name.startsWith('kop_line'))) return;
            var b = o.getBoundingRect(true);
            pgData._objGuidePoints.push(
                { ref: b.left,               type: 'x', origin: o },
                { ref: b.left + b.width / 2, type: 'x', origin: o },
                { ref: b.left + b.width,     type: 'x', origin: o },
                { ref: b.top,                type: 'y', origin: o },
                { ref: b.top + b.height / 2, type: 'y', origin: o },
                { ref: b.top + b.height,     type: 'y', origin: o }
            );
        });
    });

    // Snap + guide saat drag
    fc.on('object:moving', function (e) {
        if (!snapEnabled) return;

        var obj     = e.target;
        var pointer = e.pointer;
        var zoom    = fc.getZoom();
        var T_IN    = SNAP_THRESHOLD / zoom;
        var T_OUT   = SNAP_RELEASE   / zoom;

        var oBox = obj.getBoundingRect(true);
        var oCX  = oBox.left + oBox.width  / 2;
        var oCY  = oBox.top  + oBox.height / 2;

        var newSnapX = null;
        var newSnapY = null;

        pgData._pageSnapPoints.forEach(function (sp) {
            if (sp.type === 'x' && !newSnapX) {
                var wasSnapped = pgData._activeSnapX &&
                    Math.abs(pgData._activeSnapX.ref - sp.ref) < 0.5;
                if (wasSnapped) {
                    if (Math.abs(pointer.x - sp.ref) < T_OUT) newSnapX = sp;
                } else {
                    var edgesX = [oBox.left, oCX, oBox.left + oBox.width];
                    for (var i = 0; i < 3; i++) {
                        if (Math.abs(edgesX[i] - sp.ref) < T_IN) { newSnapX = sp; break; }
                    }
                }
            }
            if (sp.type === 'y' && !newSnapY) {
                var wasSnappedY = pgData._activeSnapY &&
                    Math.abs(pgData._activeSnapY.ref - sp.ref) < 0.5;
                if (wasSnappedY) {
                    if (Math.abs(pointer.y - sp.ref) < T_OUT) newSnapY = sp;
                } else {
                    var edgesY = [oBox.top, oCY, oBox.top + oBox.height];
                    for (var j = 0; j < 3; j++) {
                        if (Math.abs(edgesY[j] - sp.ref) < T_IN) { newSnapY = sp; break; }
                    }
                }
            }
        });

        // Terapkan snap
        if (newSnapX) {
            var dL = Math.abs(oBox.left - newSnapX.ref);
            var dC = Math.abs(oCX - newSnapX.ref);
            var dR = Math.abs(oBox.left + oBox.width - newSnapX.ref);
            var mD = Math.min(dL, dC, dR);
            if      (mD === dL) obj.set('left', newSnapX.ref);
            else if (mD === dC) obj.set('left', newSnapX.ref - oBox.width / 2);
            else                obj.set('left', newSnapX.ref - oBox.width);
            obj.setCoords();
        }
        if (newSnapY) {
            var dT  = Math.abs(oBox.top - newSnapY.ref);
            var dCY = Math.abs(oCY - newSnapY.ref);
            var dB  = Math.abs(oBox.top + oBox.height - newSnapY.ref);
            var mDY = Math.min(dT, dCY, dB);
            if      (mDY === dT)   obj.set('top', newSnapY.ref);
            else if (mDY === dCY)  obj.set('top', newSnapY.ref - oBox.height / 2);
            else                   obj.set('top', newSnapY.ref - oBox.height);
            obj.setCoords();
        }

        pgData._activeSnapX = newSnapX;
        pgData._activeSnapY = newSnapY;

        // Object-to-object alignment guides
        oBox = obj.getBoundingRect(true);
        oCX  = oBox.left + oBox.width  / 2;
        oCY  = oBox.top  + oBox.height / 2;

        var VISUAL_T         = 4 / zoom;
        var visibleObjGuides = [];
        var seenX = {}, seenY = {};

        pgData._objGuidePoints.forEach(function (g) {
            var key = Math.round(g.ref);
            if (g.type === 'x' && !seenX[key]) {
                var edgesX = [oBox.left, oCX, oBox.left + oBox.width];
                for (var i = 0; i < 3; i++) {
                    if (Math.abs(edgesX[i] - g.ref) < VISUAL_T) {
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

        var hasSnap = !!(newSnapX || newSnapY);
        var hasAny  = hasSnap || visibleObjGuides.length > 0;

        if (hasAny) {
            var hadSnap = !!(pgData._prevSnapX || pgData._prevSnapY);
            if (!hadSnap && pgData._guideAlpha < 0.9) {
                fadeGuides(pgData, 1, obj, newSnapX, newSnapY, visibleObjGuides);
            } else {
                if (pgData._guideAlphaRafId) {
                    cancelAnimationFrame(pgData._guideAlphaRafId);
                    pgData._guideAlphaRafId = null;
                }
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
        pgData._activeSnapX     = null; pgData._activeSnapY     = null;
        pgData._prevSnapX       = null; pgData._prevSnapY       = null;
        pgData._pageSnapPoints  = [];   pgData._objGuidePoints  = [];
        if (pgData._guideAlpha > 0) fadeGuides(pgData, 0, null, null, null, null);
    });

    fc.on('object:modified', function () {
        pgData._prevSnapX = null; pgData._prevSnapY = null;
        if (pgData._guideAlpha > 0) fadeGuides(pgData, 0, null, null, null, null);
        saveStateForPage(pgData);
        renderPageThumbnails();
    });

    // Format toolbar
    fc.on('selection:created', function () { updateFormatToolbar(); });
    fc.on('selection:updated', updateFormatToolbar);
    fc.on('selection:cleared', function () {
        document.getElementById('formatToolbar').style.display = 'none';
        updateCoords(null);
    });

    fc.on('object:scaling',  function (e) { scheduleCoordUpdate(e.target); });
    fc.on('object:rotating', function (e) { scheduleCoordUpdate(e.target); });
    fc.on('object:added',    function ()  { saveStateForPage(pgData); });
    fc.on('object:removed',  function ()  { saveStateForPage(pgData); });

    // Table handles + style panel
    attachTableHandles(pgData);
    attachStylePanel(pgData);
}

// ─────────────────────────────────────────────────────────────
// FORMAT TOOLBAR
// ─────────────────────────────────────────────────────────────

var _coordRafId = null;

function scheduleCoordUpdate(obj) {
    if (_coordRafId) return;
    _coordRafId = requestAnimationFrame(function () {
        _coordRafId = null;
        updateCoords(obj);
    });
}

function updateCoords(obj) {
    var x = '—', y = '—', w = '—', h = '—';

    if (obj) {
        var xPx = Math.round(obj.left || 0);
        var yPx = Math.round(obj.top  || 0);
        var wPx = Math.round((obj.width  || 0) * (obj.scaleX || 1));
        var hPx = Math.round((obj.height || 0) * (obj.scaleY || 1));

        x = xPx + ' (' + pxToMm(xPx).toFixed(1) + 'mm)';
        y = yPx + ' (' + pxToMm(yPx).toFixed(1) + 'mm)';
        w = wPx + ' (' + pxToMm(wPx).toFixed(1) + 'mm)';
        h = hPx + ' (' + pxToMm(hPx).toFixed(1) + 'mm)';
    }

    var elX = document.getElementById('coordX'); if (elX) elX.textContent = x;
    var elY = document.getElementById('coordY'); if (elY) elY.textContent = y;
    var elW = document.getElementById('coordW'); if (elW) elW.textContent = w;
    var elH = document.getElementById('coordH'); if (elH) elH.textContent = h;
}

function updateFormatToolbar() {
    var canvas = getCanvas();
    if (!canvas) return;
    var obj = canvas.getActiveObject();
    if (!obj) return;

    var toolbar = document.getElementById('formatToolbar');
    if (!toolbar) return;
    toolbar.style.display = 'block';

    var opacity = Math.round((obj.opacity || 1) * 100);
    document.getElementById('objOpacity').value    = opacity;
    document.getElementById('opacityVal').textContent = opacity;
    document.getElementById('objX').value           = Math.round(obj.left  || 0);
    document.getElementById('objY').value           = Math.round(obj.top   || 0);
    document.getElementById('objWidth').value       = Math.round((obj.width  || 0) * (obj.scaleX || 1));
    document.getElementById('objHeight').value      = Math.round((obj.height || 0) * (obj.scaleY || 1));
    document.getElementById('objRotate').value      = Math.round(obj.angle || 0);
    document.getElementById('rotateVal').textContent = Math.round(obj.angle || 0);

    if (obj.type === 'textbox' || obj.type === 'i-text') {
        document.getElementById('fontSize').value    = obj.fontSize   || 16;
        document.getElementById('fontFamily').value  = obj.fontFamily || 'Arial';
        document.getElementById('fontColor').value   = obj.fill       || '#000000';
        var lineH = obj.lineHeight || 1.4;
        document.getElementById('lineHeightSlider').value  = Math.round(lineH * 10);
        document.getElementById('lineHeightVal').textContent = lineH.toFixed(1);
    }

    var lockIcon  = document.getElementById('lockIcon');
    var lockLabel = document.getElementById('lockLabel');
    if (lockIcon)  lockIcon.className  = obj.lockMovementX ? 'bi bi-lock-fill text-warning' : 'bi bi-lock';
    if (lockLabel) lockLabel.textContent = obj.lockMovementX ? 'Buka' : 'Kunci';

    updateCoords(obj);
}

// ── Format apply helpers ──────────────────────────────────────

function applyFormat(prop, value) {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o) return;
    o.set(prop, value);
    getCanvas().requestRenderAll();
    saveState();
}

function applyWidth(value) {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o) return;
    if (o.type === 'textbox') o.set('width', value);
    else o.set('scaleX', value / (o.width || 1));
    getCanvas().requestRenderAll();
    saveState();
}

function applyHeight(value) {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o) return;
    o.set('scaleY', value / (o.height || 1));
    getCanvas().requestRenderAll();
    saveState();
}

function applyOpacity(value) {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o) return;
    o.set('opacity', value / 100);
    getCanvas().requestRenderAll();
    saveState();
}

function applyRotation(value) {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o) return;
    o.set('angle', value);
    getCanvas().requestRenderAll();
    saveState();
}

function applyPosition(axis, value) {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o) return;
    axis === 'x' ? o.set('left', value) : o.set('top', value);
    o.setCoords();
    getCanvas().requestRenderAll();
    saveState();
}

function toggleBold() {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o || o.type !== 'textbox') return;
    o.set('fontWeight', o.fontWeight === 'bold' ? 'normal' : 'bold');
    getCanvas().requestRenderAll();
    saveState();
}

function toggleItalic() {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o || o.type !== 'textbox') return;
    o.set('fontStyle', o.fontStyle === 'italic' ? 'normal' : 'italic');
    getCanvas().requestRenderAll();
    saveState();
}

function toggleUnderline() {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o || o.type !== 'textbox') return;
    o.set('underline', !o.underline);
    getCanvas().requestRenderAll();
    saveState();
}

function toggleStrikethrough() {
    var o = getCanvas() && getCanvas().getActiveObject();
    if (!o || o.type !== 'textbox') return;
    o.set('linethrough', !o.linethrough);
    getCanvas().requestRenderAll();
    saveState();
}

// ─────────────────────────────────────────────────────────────
// ALIGNMENT & DISTRIBUTE
// ─────────────────────────────────────────────────────────────

function alignObj(dir) {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o) return;
    var b = o.getBoundingRect(true);

    if      (dir === 'left')    o.set('left', 0);
    else if (dir === 'hcenter') o.set('left', (CANVAS_W - b.width)  / 2);
    else if (dir === 'right')   o.set('left',  CANVAS_W - b.width);
    else if (dir === 'top')     o.set('top',  0);
    else if (dir === 'vcenter') o.set('top',  (CANVAS_H - b.height) / 2);
    else if (dir === 'bottom')  o.set('top',   CANVAS_H - b.height);

    o.setCoords();
    canvas.requestRenderAll();
    saveState();
}

function distributeObjects(axis) {
    var canvas = getCanvas();
    if (!canvas) return;
    var sel = canvas.getActiveObject();
    if (!sel || sel.type !== 'activeSelection') {
        alert('Pilih 2+ objek.'); return;
    }
    var objs = sel.getObjects();
    if (objs.length < 3) { alert('Butuh minimal 3 objek.'); return; }

    if (axis === 'h') {
        objs.sort(function (a, b) { return a.left - b.left; });
        var gap = (objs[objs.length - 1].left - objs[0].left) / (objs.length - 1);
        objs.forEach(function (o, i) {
            o.set('left', objs[0].left + i * gap);
            o.setCoords();
        });
    } else {
        objs.sort(function (a, b) { return a.top - b.top; });
        var gapV = (objs[objs.length - 1].top - objs[0].top) / (objs.length - 1);
        objs.forEach(function (o, i) {
            o.set('top', objs[0].top + i * gapV);
            o.setCoords();
        });
    }

    canvas.requestRenderAll();
    saveState();
}

// ─────────────────────────────────────────────────────────────
// LAYER / COPY / PASTE / LOCK
// ─────────────────────────────────────────────────────────────

function bringForward() {
    var c = getCanvas(); if (!c) return;
    var o = c.getActiveObject(); if (!o) return;
    c.bringForward(o); c.requestRenderAll(); saveState();
}

function sendBackward() {
    var c = getCanvas(); if (!c) return;
    var o = c.getActiveObject(); if (!o) return;
    c.sendBackwards(o); c.requestRenderAll(); saveState();
}

function copySelected() {
    var canvas = getCanvas(); if (!canvas) return;
    var o = canvas.getActiveObject(); if (!o) return;
    o.clone(function (cloned) { _clipboard = cloned; });
}

function pasteClipboard() {
    var canvas = getCanvas();
    if (!canvas || !_clipboard) return;

    _clipboard.clone(function (cloned) {
        canvas.discardActiveObject();
        cloned.set({
            left:    (_clipboard.left || 0) + 20,
            top:     (_clipboard.top  || 0) + 20,
            evented: true,
        });

        if (cloned.type === 'activeSelection') {
            cloned.canvas = canvas;
            cloned.forEachObject(function (o) { canvas.add(o); });
            cloned.setCoords();
        } else {
            canvas.add(cloned);
        }

        canvas.setActiveObject(cloned);
        canvas.requestRenderAll();
        saveState();
        _clipboard = cloned;
    });
}

function toggleLock() {
    var canvas = getCanvas(); if (!canvas) return;
    var o = canvas.getActiveObject(); if (!o) return;
    var isLocked = o.lockMovementX;

    o.set({
        lockMovementX: !isLocked,
        lockMovementY: !isLocked,
        lockRotation:  !isLocked,
        lockScalingX:  !isLocked,
        lockScalingY:  !isLocked,
        hasControls:   isLocked,
        hasBorders:    isLocked,
    });

    canvas.requestRenderAll();

    var lockIcon  = document.getElementById('lockIcon');
    var lockLabel = document.getElementById('lockLabel');
    if (lockIcon)  lockIcon.className   = !isLocked ? 'bi bi-lock-fill text-warning' : 'bi bi-lock';
    if (lockLabel) lockLabel.textContent = !isLocked ? 'Buka' : 'Kunci';
}

// ─────────────────────────────────────────────────────────────
// KEYBOARD SHORTCUTS
// ─────────────────────────────────────────────────────────────

document.addEventListener('keydown', function (e) {
    var tag     = document.activeElement.tagName;
    var inInput = ['INPUT', 'TEXTAREA', 'SELECT'].indexOf(tag) !== -1;

    if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); }

    if (!inInput) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'c') { e.preventDefault(); copySelected(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'v') { e.preventDefault(); pasteClipboard(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault(); copySelected(); pasteClipboard();
        }
        if (e.key === 'Delete' || e.key === 'Backspace') { removeSelected(); }
    }

    if (e.ctrlKey && e.key === '+') { e.preventDefault(); zoomIn(); }
    if (e.ctrlKey && e.key === '-') { e.preventDefault(); zoomOut(); }
    if (e.ctrlKey && e.key === '0') { e.preventDefault(); zoomReset(); }

    // Arrow nudge (1px normal, 10px + Shift)
    if (!inInput) {
        var canvas = getCanvas();
        if (canvas) {
            var obj  = canvas.getActiveObject();
            var step = e.shiftKey ? 10 : 1;
            if (obj) {
                if (e.key === 'ArrowLeft')  { obj.set('left', (obj.left || 0) - step); obj.setCoords(); canvas.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowRight') { obj.set('left', (obj.left || 0) + step); obj.setCoords(); canvas.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowUp')    { obj.set('top',  (obj.top  || 0) - step); obj.setCoords(); canvas.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowDown')  { obj.set('top',  (obj.top  || 0) + step); obj.setCoords(); canvas.requestRenderAll(); e.preventDefault(); saveState(); }
            }
        }
    }
});
