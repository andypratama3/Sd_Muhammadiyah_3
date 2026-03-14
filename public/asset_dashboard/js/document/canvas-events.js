/**
 * canvas-events.js — event listener per halaman
 *
 * IMPROVEMENTS:
 *  - Drag/resize stabil: scaleX/scaleY dipertahankan setelah resize (tidak direset)
 *  - SweetAlert2 untuk semua dialog
 *  - Context menu klik kanan (modern, seperti Figma)
 *  - checkTableOverflow dipanggil setelah object:modified
 *  - Format toolbar muncul/hilang otomatis dan update state tombol B/I/U
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

        // Canvas snap points
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

    // Snap + guide saat drag — Figma-like stability
    fc.on('object:moving', function (e) {
        if (!snapEnabled) return;

        var obj  = e.target;
        var zoom = fc.getZoom();
        var T_IN  = SNAP_THRESHOLD / zoom;
        // FIX: release distance jauh lebih besar dari pull-in,
        // dan dihitung dari jarak TEPI OBJECT ke snap point (bukan pointer)
        // Ini mencegah snap "terjebak" saat drag tabel di dekat margin
        var T_OUT = (SNAP_RELEASE * 3) / zoom;

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
                    // FIX: jarak tepi object dari snap point, bukan pointer
                    var ex = [oBox.left, oCX, oBox.left + oBox.width];
                    var minDX = Math.min(Math.abs(ex[0]-sp.ref), Math.abs(ex[1]-sp.ref), Math.abs(ex[2]-sp.ref));
                    if (minDX < T_OUT) newSnapX = sp;
                    // jika minDX >= T_OUT → snap dilepas, object bebas bergerak
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
                    // FIX: jarak tepi object dari snap point
                    var ey = [oBox.top, oCY, oBox.top + oBox.height];
                    var minDY = Math.min(Math.abs(ey[0]-sp.ref), Math.abs(ey[1]-sp.ref), Math.abs(ey[2]-sp.ref));
                    if (minDY < T_OUT) newSnapY = sp;
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

    // Setelah resize: SIMPAN scale yang baru, jangan reset ke 1
    fc.on('object:scaling', function (e) {
        scheduleCoordUpdate(e.target);
    });

    fc.on('object:scaled', function (e) {
        var obj = e.target;
        // Update panel format dengan dimensi terbaru
        if (obj) {
            updateFormatToolbar();
            saveState();
        }
    });

    fc.on('mouse:up', function () {
        // FIX: reset snap state pada mouse:up agar snap tidak "terjebak" antar drag
        pgData._activeSnapX     = null;
        pgData._activeSnapY     = null;
        pgData._prevSnapX       = null;
        pgData._prevSnapY       = null;
        if (pgData._guideAlpha > 0) {
            fadeGuides(pgData, 0, null, null, null, null);
        }
    });

    // Setelah object dimodifikasi (moved, scaled, rotated)
    fc.on('object:modified', function (e) {
        var obj = e.target;
        if (!obj) return;

        // FIX: cek overflow setelah drag selesai — pakai setTimeout agar
        // fabric sudah selesai update scaleX/scaleY sebelum kita baca
        if (obj._isTable || (obj.name && pgData.tableStore[obj.name])) {
            var _obj = obj;
            var _pg  = pgData;
            setTimeout(function() {
                if (typeof checkTableOverflow === 'function') {
                    checkTableOverflow(_pg, _obj);
                }
            }, 50);
        }

        updateFormatToolbar();
        saveState();
        renderPageThumbnails();
    });

    // Saat objek dipilih — tampilkan format toolbar
    fc.on('selection:created', function () {
        updateFormatToolbar();
        hideContextMenu();
    });

    fc.on('selection:updated', function () {
        updateFormatToolbar();
    });

    fc.on('selection:cleared', function () {
        var toolbar = document.getElementById('formatToolbar');
        if (toolbar) toolbar.style.display = 'none';
        scheduleCoordUpdate(null);
        hideContextMenu();
    });

    // Teks sedang diedit
    fc.on('text:changed', function () {
        saveState();
    });

    // Right-click context menu
    fc.on('mouse:down', function (opt) {
        if (opt.e && opt.e.button === 2) {
            opt.e.preventDefault();
            opt.e.stopPropagation();
            var target = fc.findTarget(opt.e, false);
            if (target) {
                fc.setActiveObject(target);
                fc.requestRenderAll();
            }
            showContextMenu(opt.e, target, pgData);
        } else {
            hideContextMenu();
        }
    });
}

// ─────────────────────────────────────────────────────────────
// CONTEXT MENU (klik kanan seperti Figma)
// ─────────────────────────────────────────────────────────────

function _ensureContextMenu() {
    var menu = document.getElementById('canvasContextMenu');
    if (!menu) {
        menu = document.createElement('div');
        menu.id = 'canvasContextMenu';
        document.body.appendChild(menu);
    }
    return menu;
}

function showContextMenu(e, target, pgData) {
    var menu = _ensureContextMenu();

    var hasTarget = !!target;
    var isTable   = hasTarget && target._isTable;
    var isText    = hasTarget && (target.type === 'textbox' || target.type === 'i-text');
    var isLocked  = hasTarget && target.lockMovementX;

    menu.innerHTML = '';

    var items = [];

    if (hasTarget) {
        items.push({ icon: 'bi-copy',     label: 'Salin',              shortcut: 'Ctrl+C', fn: function () { copySelected(); } });
        items.push({ icon: 'bi-clipboard', label: 'Tempel',            shortcut: 'Ctrl+V', fn: function () { pasteClipboard(); } });
        items.push({ icon: 'bi-front',     label: 'Duplikasi',         shortcut: 'Ctrl+D', fn: function () { copySelected(); pasteClipboard(); } });
        items.push({ sep: true });
        items.push({ icon: 'bi-layers-fill', label: 'Bawa ke Depan',   fn: function () { bringForward(); } });
        items.push({ icon: 'bi-layers',    label: 'Kirim ke Belakang', fn: function () { sendBackward(); } });
        items.push({ sep: true });

        if (isLocked) {
            items.push({ icon: 'bi-unlock', label: 'Buka Kunci',       fn: function () { toggleLock(); } });
        } else {
            items.push({ icon: 'bi-lock',   label: 'Kunci Posisi',     fn: function () { toggleLock(); } });
        }

        if (isText) {
            items.push({ sep: true });
            items.push({ icon: 'bi-type-bold', label: 'Toggle Bold',   fn: function () { toggleBold(); } });
            items.push({ icon: 'bi-type-italic', label: 'Toggle Italic', fn: function () { toggleItalic(); } });
        }

        if (isTable) {
            items.push({ sep: true });
            items.push({
                icon: 'bi-arrow-down-right-square',
                label: 'Pindah ke Halaman Baru',
                fn: function () {
                    var canvas = pgData.canvas;
                    var obj = canvas.getActiveObject();
                    if (!obj) return;
                    var td = pgData.tableStore[obj.name];
                    if (!td) return;
                    var oldName = obj.name;
                    canvas.remove(obj);
                    delete pgData.tableStore[oldName];
                    canvas.requestRenderAll();
                    _placeTableOnNextPage(pgData, td);
                    saveState();
                }
            });
        }

        items.push({ sep: true });
        items.push({ icon: 'bi-trash', label: 'Hapus', danger: true, shortcut: 'Del', fn: function () { removeSelected(); } });

    } else {
        items.push({ icon: 'bi-clipboard', label: 'Tempel', shortcut: 'Ctrl+V', fn: function () { pasteClipboard(); } });
        items.push({ sep: true });
        items.push({ icon: 'bi-file-earmark-plus', label: 'Tambah Halaman', fn: function () { addNewPage(); } });
        items.push({ icon: 'bi-arrows-fullscreen',  label: 'Zoom 100%',     fn: function () { zoomReset(); } });
    }

    items.forEach(function (item) {
        if (item.sep) {
            var sep = document.createElement('div');
            sep.className = 'ctx-menu-sep';
            menu.appendChild(sep);
            return;
        }
        var btn = document.createElement('button');
        btn.className = 'ctx-menu-item' + (item.danger ? ' danger' : '');
        btn.innerHTML =
            '<i class="bi ' + item.icon + '"></i>' +
            '<span>' + item.label + '</span>' +
            (item.shortcut ? '<span class="ctx-shortcut">' + item.shortcut + '</span>' : '');
        btn.addEventListener('mousedown', function (ev) {
            ev.preventDefault();
            hideContextMenu();
            setTimeout(item.fn, 0);
        });
        menu.appendChild(btn);
    });

    // Posisi menu — jaga agar tidak keluar viewport
    var x = e.clientX;
    var y = e.clientY;
    menu.style.display = 'block';
    var mw = menu.offsetWidth;
    var mh = menu.offsetHeight;
    if (x + mw > window.innerWidth  - 8) x = window.innerWidth  - mw - 8;
    if (y + mh > window.innerHeight - 8) y = window.innerHeight - mh - 8;
    menu.style.left = x + 'px';
    menu.style.top  = y + 'px';
}

function hideContextMenu() {
    var menu = document.getElementById('canvasContextMenu');
    if (menu) menu.style.display = 'none';
}

document.addEventListener('click', function (e) {
    var menu = document.getElementById('canvasContextMenu');
    if (menu && !menu.contains(e.target)) hideContextMenu();
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') hideContextMenu();
});

// ─────────────────────────────────────────────────────────────
// COORD UPDATE
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
        var xPx = Math.round(obj.left  || 0);
        var yPx = Math.round(obj.top   || 0);
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

// ─────────────────────────────────────────────────────────────
// FORMAT TOOLBAR
// ─────────────────────────────────────────────────────────────

function updateFormatToolbar() {
    var canvas = getCanvas();
    if (!canvas) return;
    var obj = canvas.getActiveObject();
    if (!obj) {
        var tb = document.getElementById('formatToolbar');
        if (tb) tb.style.display = 'none';
        return;
    }

    var toolbar = document.getElementById('formatToolbar');
    if (toolbar) toolbar.style.display = 'block';

    // Posisi & dimensi — gunakan scaleX/scaleY asli (TIDAK di-reset)
    var opacity = Math.round((obj.opacity || 1) * 100);
    _setVal('objOpacity', opacity);
    _setVal('opacityVal', opacity);
    _setVal('objX',       Math.round(obj.left  || 0));
    _setVal('objY',       Math.round(obj.top   || 0));
    _setVal('objWidth',   Math.round((obj.width  || 0) * (obj.scaleX || 1)));
    _setVal('objHeight',  Math.round((obj.height || 0) * (obj.scaleY || 1)));
    _setVal('objRotate',  Math.round(obj.angle || 0));
    _setVal('rotateVal',  Math.round(obj.angle || 0));

    if (obj.type === 'textbox' || obj.type === 'i-text') {
        _setVal('fontSize',   obj.fontSize   || 16);
        _setVal('fontFamily', obj.fontFamily || 'Arial');
        _setVal('fontColor',  obj.fill       || '#000000');
        var lineH = obj.lineHeight || 1.4;
        _setVal('lineHeightSlider', Math.round(lineH * 10));
        _setVal('lineHeightVal',    lineH.toFixed(1));

        // Toggle state button format
        _toggleFmtBtn('btnBold',        obj.fontWeight  === 'bold');
        _toggleFmtBtn('btnItalic',      obj.fontStyle   === 'italic');
        _toggleFmtBtn('btnUnderline',   !!obj.underline);
        _toggleFmtBtn('btnStrike',      !!obj.linethrough);
        _toggleFmtBtn('btnAlignLeft',   (obj.textAlign || 'left') === 'left');
        _toggleFmtBtn('btnAlignCenter', obj.textAlign === 'center');
        _toggleFmtBtn('btnAlignRight',  obj.textAlign === 'right');
        _toggleFmtBtn('btnAlignJustify',obj.textAlign === 'justify');
    }

    var lockIcon  = document.getElementById('lockIcon');
    var lockLabel = document.getElementById('lockLabel');
    if (lockIcon)  lockIcon.className  = obj.lockMovementX ? 'bi bi-lock-fill text-warning' : 'bi bi-lock';
    if (lockLabel) lockLabel.textContent = obj.lockMovementX ? 'Buka' : 'Kunci';

    updateCoords(obj);
}

function _setVal(id, val) {
    var el = document.getElementById(id);
    if (!el) return;
    if (el.tagName === 'SPAN' || el.tagName === 'CODE') el.textContent = val;
    else el.value = val;
}

function _toggleFmtBtn(id, active) {
    var el = document.getElementById(id);
    if (!el) return;
    if (active) el.classList.add('active');
    else        el.classList.remove('active');
}

// ── Format apply helpers ──────────────────────────────────────

function applyFormat(prop, value) {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o) return;
    o.set(prop, value);
    canvas.requestRenderAll();
    updateFormatToolbar();
    saveState();
}

/**
 * applyWidth — KRITIS: gunakan scaleX bukan set width langsung
 * agar konsisten dengan apa yang user lihat saat resize manual
 */
function applyWidth(value) {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o) return;
    var naturalW = o.width || 1;
    if (o.type === 'textbox') {
        o.set('width', value);
    } else {
        o.set('scaleX', value / naturalW);
    }
    o.setCoords();
    canvas.requestRenderAll();
    updateFormatToolbar();
    saveState();
}

function applyHeight(value) {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o) return;
    var naturalH = o.height || 1;
    o.set('scaleY', value / naturalH);
    o.setCoords();
    canvas.requestRenderAll();
    updateFormatToolbar();
    saveState();
}

function applyOpacity(value) {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o) return;
    o.set('opacity', value / 100);
    canvas.requestRenderAll();
    saveState();
}

function applyRotation(value) {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o) return;
    o.set('angle', value);
    o.setCoords();
    canvas.requestRenderAll();
    saveState();
}

function applyPosition(axis, value) {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o) return;
    axis === 'x' ? o.set('left', value) : o.set('top', value);
    o.setCoords();
    canvas.requestRenderAll();
    saveState();
}

function toggleBold() {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o || o.type !== 'textbox') return;
    o.set('fontWeight', o.fontWeight === 'bold' ? 'normal' : 'bold');
    canvas.requestRenderAll();
    updateFormatToolbar();
    saveState();
}

function toggleItalic() {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o || o.type !== 'textbox') return;
    o.set('fontStyle', o.fontStyle === 'italic' ? 'normal' : 'italic');
    canvas.requestRenderAll();
    updateFormatToolbar();
    saveState();
}

function toggleUnderline() {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o || o.type !== 'textbox') return;
    o.set('underline', !o.underline);
    canvas.requestRenderAll();
    updateFormatToolbar();
    saveState();
}

function toggleStrikethrough() {
    var canvas = getCanvas();
    if (!canvas) return;
    var o = canvas.getActiveObject();
    if (!o || o.type !== 'textbox') return;
    o.set('linethrough', !o.linethrough);
    canvas.requestRenderAll();
    updateFormatToolbar();
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

    // CRITICAL FIX: pastikan lock dari drag handle selalu dilepas setelah align
    // tanpa ini tabel tidak bisa digeser setelah di-center/align
    o.set({
        lockMovementX: false,
        lockMovementY: false,
    });
    o.setCoords();
    canvas.requestRenderAll();
    saveState();

    // Re-trigger handles jika ini tabel (agar handle positions ikut update)
    var pg = pages[currentPage];
    if (pg && o.name && pg.tableStore[o.name]) {
        if (typeof _repositionRowHandles === 'function') _repositionRowHandles();
        if (typeof _repositionColHandles === 'function') _repositionColHandles();
        if (typeof _updateFloatPanelPos  === 'function') _updateFloatPanelPos(pg, o);
    }
}

function distributeObjects(axis) {
    var canvas = getCanvas();
    if (!canvas) return;
    var sel = canvas.getActiveObject();
    if (!sel || sel.type !== 'activeSelection') {
        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'info',
            title: 'Pilih 2+ objek terlebih dahulu',
            showConfirmButton: false, timer: 2000,
        });
        return;
    }
    var objs = sel.getObjects();
    if (objs.length < 3) {
        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'info',
            title: 'Butuh minimal 3 objek untuk distribute',
            showConfirmButton: false, timer: 2000,
        });
        return;
    }

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
    o.clone(function (cloned) {
        _clipboard = cloned;
        Swal.fire({
            toast: true, position: 'bottom-end', icon: 'success',
            title: 'Disalin ke clipboard',
            showConfirmButton: false, timer: 1500,
        });
    });
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
    updateFormatToolbar();

    Swal.fire({
        toast: true, position: 'bottom-end',
        icon: isLocked ? 'success' : 'info',
        title: isLocked ? 'Elemen dibuka kuncinya' : 'Elemen dikunci',
        showConfirmButton: false, timer: 1500,
    });
}

// ─────────────────────────────────────────────────────────────
// KEYBOARD SHORTCUTS
// ─────────────────────────────────────────────────────────────

document.addEventListener('keydown', function (e) {
    var tag     = document.activeElement.tagName;
    var inInput = ['INPUT', 'TEXTAREA', 'SELECT'].indexOf(tag) !== -1;
    var inModal = !!document.querySelector('.modal.show');

    if (inModal) return;

    if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
    if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); redo(); }

    if (!inInput) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'c') { e.preventDefault(); copySelected(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'v') { e.preventDefault(); pasteClipboard(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault(); copySelected();
            setTimeout(pasteClipboard, 10);
        }
        if (e.key === 'Delete' || e.key === 'Backspace') { removeSelected(); }
        if (e.key === 'Escape') {
            var canvas = getCanvas();
            if (canvas) { canvas.discardActiveObject(); canvas.requestRenderAll(); }
            hideContextMenu();
        }
    }

    // Zoom shortcuts
    if ((e.ctrlKey || e.metaKey) && (e.key === '=' || e.key === '+')) { e.preventDefault(); zoomIn(); }
    if ((e.ctrlKey || e.metaKey) && e.key === '-') { e.preventDefault(); zoomOut(); }
    if ((e.ctrlKey || e.metaKey) && e.key === '0') { e.preventDefault(); zoomReset(); }

    // Arrow nudge (1px normal, 10px Shift, 0.5px Alt)
    if (!inInput) {
        var canvas2 = getCanvas();
        if (canvas2) {
            var obj  = canvas2.getActiveObject();
            var step = e.shiftKey ? 10 : (e.altKey ? 0.5 : 1);
            if (obj) {
                if (e.key === 'ArrowLeft')  { obj.set('left', (obj.left || 0) - step); obj.setCoords(); canvas2.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowRight') { obj.set('left', (obj.left || 0) + step); obj.setCoords(); canvas2.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowUp')    { obj.set('top',  (obj.top  || 0) - step); obj.setCoords(); canvas2.requestRenderAll(); e.preventDefault(); saveState(); }
                if (e.key === 'ArrowDown')  { obj.set('top',  (obj.top  || 0) + step); obj.setCoords(); canvas2.requestRenderAll(); e.preventDefault(); saveState(); }
                scheduleCoordUpdate(obj);
            }
        }
    }
});
