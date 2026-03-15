/**
 * table-style-panel.js — floating panel warna header/stripe saat tabel dipilih
 *
 * PATCH:
 *  - Tombol "Terapkan" tidak bisa diklik: panel perlu mousedown stopPropagation
 *    agar canvas upperCanvasEl tidak intercept event
 *  - z-index panel dinaikkan ke 10000 agar di atas semua overlay
 *  - pointer-events: all diset eksplisit pada panel dan semua child-nya
 *
 * Depends: constants.js, table-handles.js (_liveRerenderTable),
 *          page-manager.js (saveStateForPage, renderPageThumbnails)
 */

function attachStylePanel(pgData) {
    var fc = pgData.canvas;

    fc.on('selection:created', function (e) {
        var obj = e.selected && e.selected[0];
        _maybeShowStylePanel(pgData, obj);
    });

    fc.on('selection:updated', function (e) {
        _removeStylePanel();
        var obj = e.selected && e.selected[0];
        _maybeShowStylePanel(pgData, obj);
    });

    fc.on('selection:cleared', function () { _removeStylePanel(); });

    fc.on('object:moving', function (e) {
        if (_rowHandle.fabricObj === e.target) {
            _updateStylePanelPos(pgData, e.target);
        }
    });
}

function _maybeShowStylePanel(pgData, obj) {
    if (!obj || !obj.name) return;
    var td = pgData.tableStore[obj.name];
    if (!td || td.type === 'ttd') return;
    _showStylePanel(pgData, obj, td);
}

function _showStylePanel(pgData, obj, td) {
    _removeStylePanel();

    var pos   = _calcStylePanelPos(pgData, obj);
    var panel = document.createElement('div');
    panel.id  = '__tableStylePanel';

    panel.style.cssText =
        'position:fixed;z-index:10000;' +
        'background:rgba(255,255,255,0.98);' +
        'border:1px solid #e2e8f0;border-radius:12px;' +
        'padding:8px 12px;' +
        'box-shadow:0 4px 24px rgba(0,0,0,0.15),0 1px 4px rgba(0,0,0,0.08);' +
        'display:flex;flex-direction:column;gap:8px;' +
        'font-size:0.8rem;font-family:inherit;' +
        'pointer-events:all;' +
        'top:' + pos.top + 'px;left:' + pos.left + 'px;' +
        'animation:_panelIn .18s cubic-bezier(.34,1.56,.64,1) both;' +
        'user-select:none;min-width:360px;';

    if (!document.getElementById('__panelKeyframes')) {
        var styleEl = document.createElement('style');
        styleEl.id  = '__panelKeyframes';
        styleEl.textContent =
            '@keyframes _panelIn{' +
            'from{opacity:0;transform:translateY(6px) scale(.97)}' +
            'to{opacity:1;transform:none}}';
        document.head.appendChild(styleEl);
    }

    var headerColor  = td.headerColor  || '#1a5276';
    var stripeColor  = td.stripeColor  || '#eaf2ff';
    var currentMode  = td.table_mode   || 'perorang';

    // ── Baris 1: Mode toggle + label tabel ──────────────────────────────────
    var modePerorangBg = currentMode === 'perorang'
        ? 'background:#1a5276;color:#fff;'
        : 'background:#f1f5f9;color:#64748b;';
    var modeDaftarBg   = currentMode === 'daftar'
        ? 'background:#0f5132;color:#fff;'
        : 'background:#f1f5f9;color:#64748b;';

    var row1 =
        '<div style="display:flex;align-items:center;gap:8px;">' +
        // Ikon tabel
        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" style="flex-shrink:0">' +
        '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>' +
        '<span style="color:#64748b;font-weight:600;flex:1;white-space:nowrap;">Mode Generate</span>' +

        // Toggle Per Orang
        '<button id="__spModePerorang" type="button" ' +
        'style="' + modePerorangBg + 'border:1px solid rgba(0,0,0,.1);border-radius:6px;' +
        'padding:3px 9px;font-size:.73rem;font-weight:600;cursor:pointer;' +
        'display:flex;align-items:center;gap:4px;pointer-events:all;transition:all .15s;white-space:nowrap;">' +
        '<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.7 0 4.8-2.1 4.8-4.8S14.7 2.4 12 2.4 7.2 4.5 7.2 7.2 9.3 12 12 12zm0 2.4c-3.2 0-9.6 1.6-9.6 4.8v2.4h19.2v-2.4c0-3.2-6.4-4.8-9.6-4.8z"/></svg>' +
        'Per Orang</button>' +

        // Toggle Daftar
        '<button id="__spModeDaftar" type="button" ' +
        'style="' + modeDaftarBg + 'border:1px solid rgba(0,0,0,.1);border-radius:6px;' +
        'padding:3px 9px;font-size:.73rem;font-weight:600;cursor:pointer;' +
        'display:flex;align-items:center;gap:4px;pointer-events:all;transition:all .15s;white-space:nowrap;">' +
        '<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><path d="M3 13h2v-2H3v2zm0 4h2v-2H3v2zm0-8h2V7H3v2zm4 4h14v-2H7v2zm0 4h14v-2H7v2zM7 7v2h14V7H7z"/></svg>' +
        'Daftar</button>' +

        // Tutup
        '<button id="__spClose" type="button" ' +
        'style="background:none;border:none;color:#94a3b8;cursor:pointer;margin-left:4px;' +
        'padding:2px;display:flex;align-items:center;border-radius:5px;pointer-events:all;">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">' +
        '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>' +
        '</div>';

    // ── Baris 2: Warna + Terapkan ────────────────────────────────────────────
    var row2 =
        '<div style="display:flex;align-items:center;gap:10px;">' +
        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2.5" style="flex-shrink:0">' +
        '<circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/><path d="M2 12h20"/></svg>' +
        '<span style="color:#64748b;font-weight:600;white-space:nowrap;">Warna Tabel</span>' +

        // Warna Header
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;margin:0;pointer-events:all">' +
        '<span style="color:#94a3b8;font-size:.75rem;white-space:nowrap;">Header</span>' +
        '<div style="position:relative;width:28px;height:28px;">' +
        '<input type="color" id="__spHeader" value="' + headerColor + '" ' +
        'style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;border:none;pointer-events:all">' +
        '<div id="__spHeaderSwatch" style="width:28px;height:28px;border-radius:7px;' +
        'border:2px solid #e2e8f0;background:' + headerColor + ';pointer-events:none;"></div>' +
        '</div></label>' +

        // Warna Stripe
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;margin:0;pointer-events:all">' +
        '<span style="color:#94a3b8;font-size:.75rem;white-space:nowrap;">Stripe</span>' +
        '<div style="position:relative;width:28px;height:28px;">' +
        '<input type="color" id="__spStripe" value="' + stripeColor + '" ' +
        'style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;border:none;pointer-events:all">' +
        '<div id="__spStripeSwatch" style="width:28px;height:28px;border-radius:7px;' +
        'border:2px solid #e2e8f0;background:' + stripeColor + ';pointer-events:none;"></div>' +
        '</div></label>' +

        '<div style="width:1px;height:22px;background:#e2e8f0;flex-shrink:0;"></div>' +

        '<button id="__spApply" type="button" ' +
        'style="background:#0ea5e9;color:#fff;border:none;border-radius:8px;margin-left:auto;' +
        'padding:5px 11px;font-size:.78rem;font-weight:600;cursor:pointer;' +
        'display:flex;align-items:center;gap:4px;white-space:nowrap;transition:background .15s;' +
        'pointer-events:all;position:relative;z-index:1;">' +
        '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">' +
        '<polyline points="20 6 9 17 4 12"/></svg>Terapkan</button>' +
        '</div>';

    panel.innerHTML = row1 + row2;

    document.body.appendChild(panel);

    // ── CRITICAL PATCH: stopPropagation agar canvas TIDAK intercept klik di panel ──
    panel.addEventListener('mousedown', function (e) { e.stopPropagation(); });
    panel.addEventListener('mouseup',   function (e) { e.stopPropagation(); });
    panel.addEventListener('click',     function (e) { e.stopPropagation(); });
    panel.addEventListener('touchstart',function (e) { e.stopPropagation(); });

    var spHeader        = document.getElementById('__spHeader');
    var spStripe        = document.getElementById('__spStripe');
    var spApply         = document.getElementById('__spApply');
    var spClose         = document.getElementById('__spClose');
    var spModePerorang  = document.getElementById('__spModePerorang');
    var spModeDaftar    = document.getElementById('__spModeDaftar');

    // ── Mode toggle ──────────────────────────────────────────────────────────
    function _applyModeStyle(mode) {
        if (!spModePerorang || !spModeDaftar) return;
        if (mode === 'perorang') {
            spModePerorang.style.cssText = spModePerorang.style.cssText
                .replace(/background:[^;]+/, 'background:#1a5276')
                .replace(/color:[^;]+/, 'color:#fff');
            spModeDaftar.style.cssText = spModeDaftar.style.cssText
                .replace(/background:[^;]+/, 'background:#f1f5f9')
                .replace(/color:[^;]+/, 'color:#64748b');
        } else {
            spModeDaftar.style.cssText = spModeDaftar.style.cssText
                .replace(/background:[^;]+/, 'background:#0f5132')
                .replace(/color:[^;]+/, 'color:#fff');
            spModePerorang.style.cssText = spModePerorang.style.cssText
                .replace(/background:[^;]+/, 'background:#f1f5f9')
                .replace(/color:[^;]+/, 'color:#64748b');
        }
    }

    if (spModePerorang) {
        spModePerorang.addEventListener('mousedown', function (e) { e.stopPropagation(); e.preventDefault(); });
        spModePerorang.addEventListener('click', function (e) {
            e.stopPropagation();
            // Simpan ke tableData langsung (live, tanpa perlu Terapkan)
            td.table_mode = 'perorang';
            _applyModeStyle('perorang');
            saveStateForPage(pgData);
            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'success',
                title: 'Mode: Per Orang — 1 baris Excel → 1 PDF',
                showConfirmButton: false, timer: 1800,
            });
        });
    }

    if (spModeDaftar) {
        spModeDaftar.addEventListener('mousedown', function (e) { e.stopPropagation(); e.preventDefault(); });
        spModeDaftar.addEventListener('click', function (e) {
            e.stopPropagation();
            td.table_mode = 'daftar';
            _applyModeStyle('daftar');
            saveStateForPage(pgData);
            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'success',
                title: 'Mode: Daftar — semua baris Excel → 1 PDF',
                showConfirmButton: false, timer: 1800,
            });
        });
    }

    if (spHeader) {
        spHeader.addEventListener('input', function () {
            var sw = document.getElementById('__spHeaderSwatch');
            if (sw) sw.style.background = this.value;
        });
    }
    if (spStripe) {
        spStripe.addEventListener('input', function () {
            var sw = document.getElementById('__spStripeSwatch');
            if (sw) sw.style.background = this.value;
        });
    }

    // Terapkan — gunakan mousedown bukan click untuk responsivitas lebih baik
    if (spApply) {
        spApply.addEventListener('mousedown', function (e) {
            e.stopPropagation();
            e.preventDefault();
        });
        spApply.addEventListener('click', function (e) {
            e.stopPropagation();
            var hc = document.getElementById('__spHeader');
            var sc = document.getElementById('__spStripe');
            if (!hc || !sc) return;

            td.headerColor = hc.value;
            td.stripeColor = sc.value;

            _liveRerenderTable({ td: td, fabricObj: obj, pgData: pgData });
            saveStateForPage(pgData);
            renderPageThumbnails();

            // Visual feedback
            this.textContent = '✓ Diterapkan!';
            var btn = this;
            setTimeout(function () {
                if (btn && btn.parentNode) {
                    btn.innerHTML =
                        '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">' +
                        '<polyline points="20 6 9 17 4 12"/></svg>Terapkan';
                }
            }, 1500);
        });

        // Hover effect
        spApply.addEventListener('mouseenter', function () { this.style.background = '#0284c7'; });
        spApply.addEventListener('mouseleave', function () { this.style.background = '#0ea5e9'; });
    }

    if (spClose) {
        spClose.addEventListener('click', function (e) {
            e.stopPropagation();
            _removeStylePanel();
        });
        spClose.addEventListener('mouseenter', function () { this.style.color = '#475569'; });
        spClose.addEventListener('mouseleave', function () { this.style.color = '#94a3b8'; });
    }
}

function _removeStylePanel() {
    var p = document.getElementById('__tableStylePanel');
    if (p) p.remove();
}

function _calcStylePanelPos(pgData, obj) {
    var wrapper   = pgData.canvas.wrapperEl;
    var rect      = wrapper.getBoundingClientRect();
    var zoom      = pgData.canvas.getZoom();

    var panelTop  = rect.top  + (obj.top  + (obj.height || 0) * (obj.scaleY || 1)) * zoom + 10;
    var panelLeft = rect.left + obj.left  * zoom;

    // Clamp agar tidak keluar viewport
    panelTop  = Math.min(panelTop,  window.innerHeight - 80);
    panelTop  = Math.max(panelTop,  8);
    panelLeft = Math.min(panelLeft, window.innerWidth  - 380);
    panelLeft = Math.max(panelLeft, 8);

    return { top: panelTop, left: panelLeft };
}

function _updateStylePanelPos(pgData, obj) {
    var p = document.getElementById('__tableStylePanel');
    if (!p) return;
    var pos = _calcStylePanelPos(pgData, obj);
    p.style.top  = pos.top  + 'px';
    p.style.left = pos.left + 'px';
}