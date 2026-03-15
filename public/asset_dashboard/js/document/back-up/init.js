/**
 * init.js — inisialisasi DOMContentLoaded, modal handlers,
 *            restore canvas, form submit, global exports
 *
 * IMPROVEMENTS:
 *  - SweetAlert2 untuk semua feedback & konfirmasi
 *  - Form submit dengan validasi yang lebih baik
 *  - Loading overlay saat menyimpan
 *  - Toast notification untuk aksi berhasil
 *  - Global exports lengkap termasuk addShape, distributeObjects
 *  - Perbaikan modal tabel: tombol insert lebih robust
 */

// ─────────────────────────────────────────────────────────────
// MODAL: VARIABEL
// ─────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    var nameInput    = document.getElementById('varNameInput');
    var labelInput   = document.getElementById('varLabelInput');
    var previewCode  = document.getElementById('varPreviewCode');

    if (nameInput) {
        nameInput.addEventListener('input', function () {
            nameInput.classList.remove('is-invalid');
            var val = nameInput.value.trim().replace(/\s+/g, '_').toLowerCase() || 'nama_variabel';
            if (previewCode) previewCode.textContent = '{{ ' + val + ' }}';
        });
    }

    // Preset buttons (event delegation)
    var presetContainer = document.getElementById('presetButtons');
    if (presetContainer) {
        presetContainer.addEventListener('click', function (e) {
            var btn = e.target.closest('button[data-name]');
            if (!btn) return;
            if (nameInput)   nameInput.value  = btn.dataset.name;
            if (labelInput)  labelInput.value = btn.dataset.label;
            if (previewCode) previewCode.textContent = '{{ ' + btn.dataset.name + ' }}';
            nameInput && nameInput.classList.remove('is-invalid');
        });
    }

    // Preset dari accordion
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.preset-var-btn');
        if (!btn) return;
        if (nameInput)   nameInput.value  = btn.dataset.name;
        if (labelInput)  labelInput.value = btn.dataset.label;
        if (previewCode) previewCode.textContent = '{{ ' + btn.dataset.name + ' }}';
        nameInput && nameInput.classList.remove('is-invalid');
    });

    // Konfirmasi tambah variabel
    var btnConfirm = document.getElementById('btnConfirmVariable');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function () {
            var raw = nameInput ? nameInput.value.trim() : '';
            if (!raw) {
                if (nameInput) {
                    nameInput.classList.add('is-invalid');
                    nameInput.focus();
                }
                Swal.fire({
                    toast: true, position: 'top', icon: 'warning',
                    title: 'Nama variabel tidak boleh kosong',
                    showConfirmButton: false, timer: 2000,
                });
                return;
            }
            var varName  = raw.replace(/\s+/g, '_').toLowerCase();
            var varLabel = (labelInput && labelInput.value.trim()) || varName;

            registerVariable(varName, varLabel);
            placeVariableOnCanvas(varName);

            if (nameInput)  nameInput.value  = '';
            if (labelInput) labelInput.value = '';
            if (previewCode) previewCode.textContent = '{{ nama_variabel }}';

            var modalEl = document.getElementById('modalVariable');
            if (modalEl) { var m = bootstrap.Modal.getInstance(modalEl); if (m) m.hide(); }

            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'success',
                title: 'Variabel <code>{{' + varName + '}}</code> ditambahkan',
                showConfirmButton: false, timer: 2500,
            });
        });
    }

    if (nameInput) {
        nameInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('btnConfirmVariable') && document.getElementById('btnConfirmVariable').click();
            }
        });
    }

    // Tombol deselect
    var btnDeselect = document.getElementById('btnDeselect');
    if (btnDeselect) {
        btnDeselect.addEventListener('click', function () {
            var canvas = getCanvas();
            if (!canvas) return;
            canvas.discardActiveObject();
            canvas.requestRenderAll();
        });
    }
});

// ─────────────────────────────────────────────────────────────
// MODAL: KOP SURAT
// ─────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    var btnAddKop = document.getElementById('btnAddKop');
    if (!btnAddKop) return;

    btnAddKop.addEventListener('click', function () {
        var canvas = getCanvas();
        if (!canvas) return;

        var logoSize   = parseInt(document.getElementById('kopLogoSize').value) || 90;
        var line1      = document.getElementById('kopLine1').value.trim();
        var line2      = document.getElementById('kopLine2').value.trim();
        var line3      = document.getElementById('kopLine3').value.trim();
        var line4      = document.getElementById('kopLine4').value.trim();
        var line5      = document.getElementById('kopLine5').value.trim();
        var line6      = document.getElementById('kopLine6').value.trim();
        var borderType = document.getElementById('kopBorderStyle').value;
        var logoFile   = document.getElementById('kopLogoFile').files[0];

        function buildKop(logoDataUrl) {
            // Hapus kop lama
            canvas.getObjects()
                .filter(function (o) { return o.name && o.name.startsWith('kop_'); })
                .forEach(function (o) { canvas.remove(o); });

            var CW = CANVAS_W;
            var KT = 20;
            var LW = logoSize;
            var TX = LW + 34;
            var TW = CW - TX - 20;

            var textDefs = [
                line1 ? [line1, 11, 'bold',   '#000000'] : null,
                line2 ? [line2, 16, 'bold',   '#c0392b'] : null,
                line3 ? [line3, 12, 'bold',   '#1a5276'] : null,
                line4 ? [line4, 10, 'normal', '#000000'] : null,
                line5 ? [line5, 10, 'normal', '#000000'] : null,
            ].filter(Boolean);

            var lineHeights = textDefs.map(function (t) { return t[1] * 1.4; });
            var totalTextH  = lineHeights.reduce(function (a, b) { return a + b; }, 0);
            var curY        = KT + Math.max(0, (LW - totalTextH) / 2);

            textDefs.forEach(function (t, i) {
                canvas.add(new fabric.Textbox(t[0], {
                    left: TX, top: curY, width: TW,
                    fontSize: t[1], fontFamily: 'Arial',
                    fontWeight: t[2], textAlign: 'center',
                    fill: t[3], name: 'kop_text',
                }));
                curY += lineHeights[i];
            });

            if (line6) {
                canvas.add(new fabric.Textbox(line6, {
                    left: 20, top: KT + LW + 4,
                    width: LW + 30, fontSize: 9,
                    fontFamily: 'Arial', fill: '#000',
                    name: 'kop_npsn',
                }));
            }

            var lineY = KT + LW + (line6 ? 16 : 4);
            if (borderType === 'double') {
                canvas.add(new fabric.Line([20, lineY, CW - 20, lineY], {
                    stroke: '#000', strokeWidth: 3, name: 'kop_line',
                    selectable: false, evented: false,
                }));
                canvas.add(new fabric.Line([20, lineY + 5, CW - 20, lineY + 5], {
                    stroke: '#000', strokeWidth: 1, name: 'kop_line',
                    selectable: false, evented: false,
                }));
            } else if (borderType === 'single') {
                canvas.add(new fabric.Line([20, lineY, CW - 20, lineY], {
                    stroke: '#000', strokeWidth: 2, name: 'kop_line',
                    selectable: false, evented: false,
                }));
            }

            function addLogoObject(imgObj) {
                imgObj.scaleToWidth(LW);
                imgObj.scaleToHeight(LW);
                imgObj.set({ left: 20, top: KT, name: 'kop_logo' });
                canvas.add(imgObj);
                canvas.sendToBack(imgObj);
                canvas.requestRenderAll();
                saveState();

                var modalEl = document.getElementById('modalKop');
                if (modalEl) { var m = bootstrap.Modal.getInstance(modalEl); if (m) m.hide(); }

                Swal.fire({
                    toast: true, position: 'bottom-end', icon: 'success',
                    title: 'Kop surat berhasil ditambahkan',
                    showConfirmButton: false, timer: 2000,
                });
            }

            if (logoDataUrl) {
                fabric.Image.fromURL(logoDataUrl, addLogoObject);
            } else {
                canvas.add(new fabric.Rect({
                    left: 20, top: KT, width: LW, height: LW,
                    fill: '#f0f4f8', stroke: '#cbd5e1', strokeWidth: 1,
                    rx: 4, ry: 4, name: 'kop_logo',
                }));
                canvas.add(new fabric.Text('{{logo}}', {
                    left: 20 + LW / 2, top: KT + LW / 2,
                    fontSize: 9, fontFamily: 'Arial', fill: '#94a3b8',
                    originX: 'center', originY: 'center',
                    name: 'kop_logo_label', selectable: false, evented: false,
                }));
                canvas.requestRenderAll();
                saveState();

                var modalEl2 = document.getElementById('modalKop');
                if (modalEl2) { var m2 = bootstrap.Modal.getInstance(modalEl2); if (m2) m2.hide(); }

                Swal.fire({
                    toast: true, position: 'bottom-end', icon: 'success',
                    title: 'Kop surat ditambahkan (tanpa logo)',
                    showConfirmButton: false, timer: 2000,
                });
            }
        }

        if (logoFile) {
            var reader = new FileReader();
            reader.onload = function (ev) { buildKop(ev.target.result); };
            reader.readAsDataURL(logoFile);
        } else {
            buildKop(null);
        }
    });
});

// ─────────────────────────────────────────────────────────────
// MODAL: TABEL
// ─────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    var btnInsertTable = document.getElementById('btnInsertTable');
    if (!btnInsertTable) return;

    btnInsertTable.addEventListener('click', function () {
        var activeTab = document.querySelector('#tableTabs .nav-link.active');
        if (!activeTab) {
            Swal.fire({ toast: true, position: 'top', icon: 'info', title: 'Pilih jenis tabel terlebih dahulu', showConfirmButton: false, timer: 2000 });
            return;
        }

        var href = activeTab.getAttribute('href') || activeTab.getAttribute('data-bs-target');
        var success = true;

        try {
            if      (href === '#tabCustom')         insertCustomTable();
            else if (href === '#tabKelasMapel')      insertKelasMapelTable();
            else if (href === '#tabRaport')          insertRaportTable();
            else if (href === '#tabProgramUnggulan') insertUnggulanTable();
            else if (href === '#tabEkskul')          insertEkskulTable();
            else if (href === '#tabAbsensi')         insertAbsensiTable();
            else if (href === '#tabTTD')             insertTTDArea();
            else success = false;
        } catch (err) {
            console.error('[insertTable] error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Gagal Menyisipkan Tabel',
                text: err.message || 'Terjadi kesalahan tidak diketahui.',
                confirmButtonColor: '#1a5276',
            });
            return;
        }

        if (success) {
            var modalEl = document.getElementById('modalTable');
            if (modalEl) { var m = bootstrap.Modal.getInstance(modalEl); if (m) m.hide(); }

            Swal.fire({
                toast: true, position: 'bottom-end', icon: 'success',
                title: 'Tabel berhasil disisipkan',
                showConfirmButton: false, timer: 2000,
            });
        }
    });
});

// ─────────────────────────────────────────────────────────────
// RESTORE CANVAS (mode edit)
// ─────────────────────────────────────────────────────────────

function restoreCanvas() {
    var data = window.EXISTING_CANVAS_JSON;
    if (!data) return;

    var parsed;
    try {
        parsed = (typeof data === 'string') ? JSON.parse(data) : data;
    } catch (err) {
        console.error('[restoreCanvas] JSON parse error:', err);
        return;
    }

    function restoreVariablesFromCanvas(canvas) {
        canvas.getObjects().forEach(function (obj) {
            if (obj.type !== 'textbox' && obj.type !== 'i-text') return;
            var matches = (obj.text || '').match(/\{\{([^}]+)\}\}/g);
            if (!matches) return;
            matches.forEach(function (mv) {
                var varName = mv.replace(/[{}]/g, '').trim();
                if (['logo', 'barcode_signature'].indexOf(varName) !== -1) return;
                registerVariable(varName, friendlyVarLabel(varName));
            });
        });
    }

    // Format v2: multi-halaman
    if (parsed.version === 2 && parsed.pages && parsed.pages.length) {
        while (pages.length < parsed.pages.length) createPage();

        parsed.pages.forEach(function (pageJson, idx) {
            var pg = pages[idx];
            if (!pg) return;

            var store = pageJson._tableStore || {};
            delete pageJson._tableStore;

            pg.canvas.loadFromJSON(pageJson, function () {
                pg.tableStore = store;
                pg.canvas.requestRenderAll();
                restoreVariablesFromCanvas(pg.canvas);
                drawMarginGuidesForPage(pg, marginVisible);
                saveStateForPage(pg);
                renderPageThumbnails();
            });
        });
        return;
    }

    // Format v1: single halaman
    var pg = pages[0];
    if (!pg) return;

    var store = parsed._tableStore || {};
    delete parsed._tableStore;

    pg.canvas.loadFromJSON(parsed, function () {
        pg.tableStore = store;
        pg.canvas.requestRenderAll();
        restoreVariablesFromCanvas(pg.canvas);
        drawMarginGuidesForPage(pg, marginVisible);
        saveStateForPage(pg);
        renderPageThumbnails();
    });
}

// ─────────────────────────────────────────────────────────────
// FORM SUBMIT
// ─────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    var form = document.getElementById('templateForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var hasContent = pages.some(function (pg) {
            return pg.canvas.getObjects().filter(function (o) {
                return !o.excludeFromExport;
            }).length > 0;
        });

        if (!hasContent) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Template Kosong',
                text: 'Tambahkan setidaknya satu elemen ke canvas sebelum menyimpan.',
                confirmButtonText: 'OK, Saya Mengerti',
                confirmButtonColor: '#1a5276',
            });
            return false;
        }

        // Loading indicator
        var loadingDiv = document.createElement('div');
        loadingDiv.id = '__savingOverlay';
        loadingDiv.style.cssText =
            'position:fixed;inset:0;z-index:99999;background:rgba(240,244,248,0.88);' +
            'display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;' +
            'backdrop-filter:blur(4px);';
        loadingDiv.innerHTML =
            '<div style="width:38px;height:38px;border:3px solid #e2e8f0;border-top-color:#1a5276;' +
            'border-radius:50%;animation:spin 0.7s linear infinite;"></div>' +
            '<div style="font-weight:700;color:#1a5276;font-size:0.9rem;">Menyimpan template…</div>';
        document.body.appendChild(loadingDiv);

        generateHTML();
    });
});

// ─────────────────────────────────────────────────────────────
// INIT
// ─────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {
    createPage();
    switchPage(0);
    drawRulersBase();
    restoreCanvas();
    updatePageIndicator();

    // Preload kelas & mapel
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

    // Pastikan Swal tersedia (fallback ringan jika CDN gagal)
    if (typeof Swal === 'undefined') {
        window.Swal = {
            fire: function (opts) {
                if (opts.toast) { console.info('[Swal toast]', opts.title); return Promise.resolve({ isConfirmed: true }); }
                var msg = (opts.title || '') + (opts.text ? '\n' + opts.text : '');
                if (opts.showCancelButton) {
                    return Promise.resolve({ isConfirmed: window.confirm(msg) });
                }
                window.alert(msg);
                return Promise.resolve({ isConfirmed: true });
            }
        };
    }
});

// ─────────────────────────────────────────────────────────────
// GLOBAL EXPORTS
// ─────────────────────────────────────────────────────────────

window.toggleGrid          = toggleGrid;
window.toggleMarginGuides  = toggleMarginGuides;
window.toggleRulerVis      = toggleRulerVis;
window.setZoom             = setZoom;
window.zoomIn              = zoomIn;
window.zoomOut             = zoomOut;
window.zoomReset           = zoomReset;
window.addText             = addText;
window.addShape            = addShape;
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
window.placeVariableOnCanvas = placeVariableOnCanvas;
window.registerVariable    = registerVariable;
window.hideContextMenu     = hideContextMenu;
window.checkTableOverflow  = checkTableOverflow;
