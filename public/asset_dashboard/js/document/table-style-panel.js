/**
 * table-style-panel.js — floating panel warna header/stripe saat tabel dipilih
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
        'position:fixed;z-index:9999;' +
        'background:rgba(255,255,255,0.97);' +
        'border:1px solid #e2e8f0;border-radius:12px;' +
        'padding:8px 12px;' +
        'box-shadow:0 4px 24px rgba(0,0,0,0.13),0 1px 4px rgba(0,0,0,0.07);' +
        'display:flex;align-items:center;gap:10px;' +
        'font-size:0.8rem;font-family:inherit;' +
        'top:' + pos.top + 'px;left:' + pos.left + 'px;' +
        'animation:_panelIn .18s cubic-bezier(.34,1.56,.64,1) both;';

    if (!document.getElementById('__panelKeyframes')) {
        var styleEl = document.createElement('style');
        styleEl.id  = '__panelKeyframes';
        styleEl.textContent =
            '@keyframes _panelIn{' +
            'from{opacity:0;transform:translateY(6px) scale(.97)}' +
            'to{opacity:1;transform:none}}';
        document.head.appendChild(styleEl);
    }

    var headerColor = td.headerColor || '#1a5276';
    var stripeColor = td.stripeColor || '#eaf2ff';

    panel.innerHTML =
        // Judul
        '<span style="color:#64748b;font-weight:600;white-space:nowrap;display:flex;align-items:center;gap:4px;">' +
        '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">' +
        '<circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 0 20"/><path d="M2 12h20"/></svg>' +
        'Warna Tabel</span>' +

        // Warna Header
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;margin:0">' +
        '<span style="color:#94a3b8;font-size:.75rem;white-space:nowrap;">Header</span>' +
        '<div style="position:relative;width:28px;height:28px;">' +
        '<input type="color" id="__spHeader" value="' + headerColor + '" ' +
        'style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;border:none;">' +
        '<div id="__spHeaderSwatch" style="width:28px;height:28px;border-radius:7px;' +
        'border:2px solid #e2e8f0;background:' + headerColor + ';pointer-events:none;"></div>' +
        '</div></label>' +

        // Warna Stripe
        '<label style="display:flex;align-items:center;gap:5px;cursor:pointer;margin:0">' +
        '<span style="color:#94a3b8;font-size:.75rem;white-space:nowrap;">Stripe</span>' +
        '<div style="position:relative;width:28px;height:28px;">' +
        '<input type="color" id="__spStripe" value="' + stripeColor + '" ' +
        'style="opacity:0;position:absolute;inset:0;width:100%;height:100%;cursor:pointer;border:none;">' +
        '<div id="__spStripeSwatch" style="width:28px;height:28px;border-radius:7px;' +
        'border:2px solid #e2e8f0;background:' + stripeColor + ';pointer-events:none;"></div>' +
        '</div></label>' +

        '<div style="width:1px;height:22px;background:#e2e8f0;"></div>' +

        // Tombol Terapkan
        '<button id="__spApply" type="button" ' +
        'style="background:#0ea5e9;color:#fff;border:none;border-radius:8px;' +
        'padding:5px 11px;font-size:.78rem;font-weight:600;cursor:pointer;' +
        'display:flex;align-items:center;gap:4px;white-space:nowrap;transition:background .15s;">' +
        '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">' +
        '<polyline points="20 6 9 17 4 12"/></svg>Terapkan</button>' +

        // Tombol tutup
        '<button id="__spClose" type="button" ' +
        'style="background:none;border:none;color:#94a3b8;cursor:pointer;' +
        'padding:2px;display:flex;align-items:center;border-radius:5px;transition:color .15s;">' +
        '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">' +
        '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>';

    document.body.appendChild(panel);

    // Live preview swatch
    document.getElementById('__spHeader').addEventListener('input', function () {
        document.getElementById('__spHeaderSwatch').style.background = this.value;
    });
    document.getElementById('__spStripe').addEventListener('input', function () {
        document.getElementById('__spStripeSwatch').style.background = this.value;
    });

    // Terapkan
    document.getElementById('__spApply').addEventListener('click', function () {
        td.headerColor = document.getElementById('__spHeader').value;
        td.stripeColor = document.getElementById('__spStripe').value;
        _liveRerenderTable({ td: td, fabricObj: obj, pgData: pgData });
        saveStateForPage(pgData);
        renderPageThumbnails();
    });

    // Hover effect
    var applyBtn = document.getElementById('__spApply');
    applyBtn.addEventListener('mouseenter', function () { this.style.background = '#0284c7'; });
    applyBtn.addEventListener('mouseleave', function () { this.style.background = '#0ea5e9'; });

    document.getElementById('__spClose').addEventListener('click', _removeStylePanel);
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

    panelTop  = Math.min(panelTop,  window.innerHeight - 80);
    panelLeft = Math.min(panelLeft, window.innerWidth  - 360);
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
