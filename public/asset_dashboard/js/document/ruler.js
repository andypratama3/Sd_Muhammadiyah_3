/**
 * ruler.js — horizontal/vertical ruler, grid overlay, margin guides, smart guides
 *
 * Depends: constants.js, utils.js
 */

// ── Ruler state ───────────────────────────────────────────────
var rulerVisible = true;
var rulerH       = document.getElementById('rulerH');
var rulerV       = document.getElementById('rulerV');
var ctxH         = rulerH.getContext('2d', { willReadFrequently: true });
var ctxV         = rulerV.getContext('2d', { willReadFrequently: true });
var _rulerHBase  = null;
var _rulerVBase  = null;
var _rulerRafId  = null;

// ── Grid state ────────────────────────────────────────────────
var gridVisible  = false;

// ── Margin state ──────────────────────────────────────────────
var marginVisible = true;

// ─────────────────────────────────────────────────────────────
// RULER
// ─────────────────────────────────────────────────────────────

function toggleRulerVis(show) {
    rulerVisible = show;
    rulerH.style.display = show ? 'block' : 'none';
    rulerV.style.display = show ? 'block' : 'none';
    document.getElementById('rulerCorner').style.display = show ? 'block' : 'none';
}

function drawRulersBase() {
    if (!rulerVisible) return;

    var W       = Math.round(CANVAS_W * currentZoom);
    var H       = Math.round(CANVAS_H * currentZoom);
    var pxPerMm = MM_TO_PX * currentZoom;

    rulerH.width  = W;
    rulerV.height = H;

    // Pilih step tick agar jarak antar tick ≥ 10px
    var tickSteps = [1, 2, 5, 10, 20, 50];
    var step = 50;
    for (var si = 0; si < tickSteps.length; si++) {
        if (tickSteps[si] * pxPerMm >= 10) { step = tickSteps[si]; break; }
    }

    // ── Horizontal ruler ─────────────────────────────────────
    ctxH.fillStyle = '#f1f3f5';
    ctxH.fillRect(0, 0, W, 20);
    ctxH.fillStyle = '#ced4da';
    ctxH.fillRect(0, 19, W, 1);
    ctxH.font          = '8.5px "SF Mono",Consolas,monospace';
    ctxH.textBaseline  = 'top';

    for (var mm = 0; mm <= 210; mm += step) {
        var x       = Math.round(mm * pxPerMm);
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

    // ── Vertical ruler ───────────────────────────────────────
    ctxV.fillStyle = '#f1f3f5';
    ctxV.fillRect(0, 0, 20, H);
    ctxV.fillStyle = '#ced4da';
    ctxV.fillRect(19, 0, 1, H);
    ctxV.font = '8.5px "SF Mono",Consolas,monospace';

    for (var mmv = 0; mmv <= 297; mmv += step) {
        var y        = Math.round(mmv * pxPerMm);
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

/** Gambar crosshair cursor di atas ruler (dipanggil tiap mouse:move). */
function drawRulerCursor(px, py) {
    if (!rulerVisible || !_rulerHBase || !_rulerVBase) return;

    var x   = Math.round(px * currentZoom);
    var y   = Math.round(py * currentZoom);
    var xMm = (px / MM_TO_PX).toFixed(1);
    var yMm = (py / MM_TO_PX).toFixed(1);

    ctxH.putImageData(_rulerHBase, 0, 0);
    ctxV.putImageData(_rulerVBase, 0, 0);

    // Garis merah
    ctxH.fillStyle = 'rgba(220,53,69,0.9)'; ctxH.fillRect(x, 0, 1, 20);
    ctxV.fillStyle = 'rgba(220,53,69,0.9)'; ctxV.fillRect(0, y, 20, 1);

    // Bubble horizontal
    var bubW = 36, bubH = 14;
    var bubX = Math.min(x + 3, rulerH.width - bubW - 2);
    ctxH.fillStyle = 'rgba(220,53,69,0.9)';
    ctxH.beginPath();
    ctxH.roundRect
        ? ctxH.roundRect(bubX, 2, bubW, bubH, 3)
        : ctxH.rect(bubX, 2, bubW, bubH);
    ctxH.fill();
    ctxH.fillStyle    = '#fff';
    ctxH.font         = 'bold 8px "SF Mono",Consolas,monospace';
    ctxH.textBaseline = 'middle';
    ctxH.textAlign    = 'center';
    ctxH.fillText(xMm + 'mm', bubX + bubW / 2, 2 + bubH / 2);
    ctxH.textAlign    = 'left';

    // Bubble vertical
    var bvW = 14, bvH = 36;
    var bvY = Math.min(y + 3, rulerV.height - bvH - 2);
    ctxV.fillStyle = 'rgba(220,53,69,0.9)';
    ctxV.beginPath();
    ctxV.roundRect
        ? ctxV.roundRect(2, bvY, bvW, bvH, 3)
        : ctxV.rect(2, bvY, bvW, bvH);
    ctxV.fill();
    ctxV.save();
    ctxV.translate(2 + bvW / 2, bvY + bvH / 2);
    ctxV.rotate(-Math.PI / 2);
    ctxV.fillStyle    = '#fff';
    ctxV.font         = 'bold 8px "SF Mono",Consolas,monospace';
    ctxV.textBaseline = 'middle';
    ctxV.textAlign    = 'center';
    ctxV.fillText(yMm + 'mm', 0, 0);
    ctxV.restore();
}

// ─────────────────────────────────────────────────────────────
// GRID
// ─────────────────────────────────────────────────────────────

function toggleGrid(show) {
    gridVisible = show;

    pages.forEach(function (pg) {
        var gc  = pg.gridEl;
        var ctx = gc.getContext('2d');

        if (!show) { gc.style.display = 'none'; return; }

        gc.style.display = 'block';
        ctx.clearRect(0, 0, CANVAS_W, CANVAS_H);

        // Grid minor: setiap 5mm
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

        // Grid major: setiap 10mm
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

        // Label mm setiap 50mm
        ctx.font         = '8px "SF Mono",Consolas,monospace';
        ctx.fillStyle    = 'rgba(100,149,237,0.7)';
        ctx.textBaseline = 'top';
        for (var lmx = 10; lmx <= 200; lmx += 50) {
            for (var lmy = 10; lmy <= 280; lmy += 50) {
                ctx.fillText(
                    lmx + ',' + lmy,
                    lmx * MM_TO_PX + 2,
                    lmy * MM_TO_PX + 2
                );
            }
        }
    });
}

// ─────────────────────────────────────────────────────────────
// MARGIN GUIDES
// ─────────────────────────────────────────────────────────────

function drawMarginGuidesForPage(pg, show) {
    var mc  = pg.marginEl;
    var ctx = mc.getContext('2d');
    ctx.clearRect(0, 0, CANVAS_W, CANVAS_H);
    if (!show) return;

    var M = MARGIN; // 20mm = 76px

    // Bayangan area luar margin
    ctx.fillStyle = 'rgba(0,0,0,0.04)';
    ctx.fillRect(0, 0, M, CANVAS_H);                        // kiri
    ctx.fillRect(CANVAS_W - M, 0, M, CANVAS_H);             // kanan
    ctx.fillRect(M, 0, CANVAS_W - M * 2, M);                // atas
    ctx.fillRect(M, CANVAS_H - M, CANVAS_W - M * 2, M);     // bawah

    // Garis margin
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

    // Label "20mm"
    ctx.font         = '9px "SF Mono",Consolas,monospace';
    ctx.fillStyle    = 'rgba(13,110,253,0.6)';
    ctx.textBaseline = 'top';
    ctx.fillText('20mm', M + 3, M + 3);
}

function toggleMarginGuides(show) {
    marginVisible = show;
    pages.forEach(function (pg) { drawMarginGuidesForPage(pg, show); });
}

// ─────────────────────────────────────────────────────────────
// SMART GUIDES
// ─────────────────────────────────────────────────────────────

function drawSmartGuides(pgData, obj, snapX, snapY, objGuides, alpha) {
    var ctx = pgData.guideEl.getContext('2d');
    ctx.clearRect(0, 0, CANVAS_W, CANVAS_H);
    if (alpha <= 0) return;

    var b1  = obj.getBoundingRect(true);
    var b1R = b1.left + b1.width;
    var b1B = b1.top  + b1.height;

    ctx.save();
    ctx.globalAlpha = Math.min(1, alpha);

    // Object-to-object alignment guides (biru, dashed)
    if (objGuides && objGuides.length) {
        ctx.save();
        ctx.strokeStyle = 'rgba(14,165,233,0.55)';
        ctx.lineWidth   = 1;
        ctx.setLineDash([4, 3]);
        ctx.shadowColor = 'rgba(14,165,233,0.3)';
        ctx.shadowBlur  = 3;

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

        ctx.setLineDash([]);
        ctx.restore();
    }

    // Canvas snap lines (merah)
    function drawSnapLine(isX, ref) {
        ctx.save();

        // Full-canvas faint line
        ctx.strokeStyle = 'rgba(244,63,94,0.15)';
        ctx.lineWidth   = 1;
        ctx.setLineDash([]);
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, 0);       ctx.lineTo(ref, CANVAS_H); }
        else      { ctx.moveTo(0, ref);       ctx.lineTo(CANVAS_W, ref); }
        ctx.stroke();

        // Glow near object
        ctx.strokeStyle = 'rgba(244,63,94,0.22)';
        ctx.lineWidth   = 7;
        ctx.shadowColor = 'rgba(244,63,94,0.3)';
        ctx.shadowBlur  = 10;
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, b1.top - 20); ctx.lineTo(ref, b1B + 20); }
        else      { ctx.moveTo(b1.left - 20, ref); ctx.lineTo(b1R + 20, ref); }
        ctx.stroke();

        // Sharp center line
        ctx.strokeStyle = '#f43f5e';
        ctx.lineWidth   = 1.5;
        ctx.shadowColor = 'rgba(244,63,94,0.55)';
        ctx.shadowBlur  = 5;
        ctx.beginPath();
        if (isX) { ctx.moveTo(ref, b1.top - 20); ctx.lineTo(ref, b1B + 20); }
        else      { ctx.moveTo(b1.left - 20, ref); ctx.lineTo(b1R + 20, ref); }
        ctx.stroke();

        // Endpoint dots
        ctx.fillStyle  = '#f43f5e';
        ctx.shadowBlur = 5;
        var dots = isX
            ? [[ref, b1.top], [ref, b1B]]
            : [[b1.left, ref], [b1R, ref]];
        dots.forEach(function (d) {
            ctx.beginPath();
            ctx.arc(d[0], d[1], 3.5, 0, Math.PI * 2);
            ctx.fill();
        });

        ctx.restore();
    }

    if (snapX) drawSnapLine(true,  snapX.ref);
    if (snapY) drawSnapLine(false, snapY.ref);

    // Coordinate badge
    if (snapX || snapY) {
        ctx.save();
        ctx.shadowBlur = 0;

        var parts = [];
        if (snapX) parts.push('X ' + Math.round(snapX.ref));
        if (snapY) parts.push('Y ' + Math.round(snapY.ref));
        var txt = parts.join('  ');

        ctx.font = 'bold 9.5px -apple-system,monospace';
        var tw   = ctx.measureText(txt).width;
        var padX = 6, badgeH = 16, badgeW = tw + padX * 2;
        var bx   = (snapX ? snapX.ref : b1.left) + 5;
        var by   = b1.top - badgeH - 6;

        if (bx + badgeW > CANVAS_W - 4) bx = (snapX ? snapX.ref : b1.left) - badgeW - 5;
        if (by < 4) by = b1.top + 5;

        ctx.fillStyle = 'rgba(244,63,94,0.92)';
        _rrect(ctx, bx, by, badgeW, badgeH, 4);
        ctx.fill();

        ctx.fillStyle    = '#fff';
        ctx.textBaseline = 'middle';
        ctx.textAlign    = 'left';
        ctx.fillText(txt, bx + padX, by + badgeH / 2);

        ctx.restore();
    }

    ctx.restore();
}

/** Helper: rounded rect path */
function _rrect(ctx, x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y,     x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x,     y + h, r);
    ctx.arcTo(x,     y + h, x,     y,     r);
    ctx.arcTo(x,     y,     x + w, y,     r);
    ctx.closePath();
}

/** Fade guide alpha in/out via RAF. */
function fadeGuides(pgData, targetAlpha, obj, snapX, snapY, objGuides) {
    if (pgData._guideAlphaRafId) cancelAnimationFrame(pgData._guideAlphaRafId);

    function step() {
        var diff  = targetAlpha - pgData._guideAlpha;
        var speed = targetAlpha > 0 ? GUIDE_FADE_IN : GUIDE_FADE_OUT;

        if (Math.abs(diff) < 0.015) {
            pgData._guideAlpha = targetAlpha;
            if (targetAlpha === 0) {
                pgData.guideEl.getContext('2d').clearRect(0, 0, CANVAS_W, CANVAS_H);
            } else if (obj) {
                drawSmartGuides(pgData, obj, snapX, snapY, objGuides || [], 1);
            }
            pgData._guideAlphaRafId = null;
            return;
        }

        pgData._guideAlpha = Math.max(0, Math.min(1,
            pgData._guideAlpha + (diff > 0 ? speed : -speed)
        ));

        if (obj) drawSmartGuides(pgData, obj, snapX, snapY, objGuides || [], pgData._guideAlpha);
        pgData._guideAlphaRafId = requestAnimationFrame(step);
    }

    step();
}
