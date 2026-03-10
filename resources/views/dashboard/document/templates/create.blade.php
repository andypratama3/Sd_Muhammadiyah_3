@extends('layouts.dashboard')

@section('title','Buat Template Surat')

@section('content')

<div class="row">

    {{-- SIDEBAR KOMPONEN --}}
    <div class="col-lg-3">

        <div class="card mb-3">
            <div class="card-header fw-bold">Komponen</div>
            <div class="card-body d-grid gap-2">

                <button type="button" class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalVariable">
                    <i class="bi bi-braces"></i> Tambah Variabel
                </button>

                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addText()">
                    <i class="bi bi-fonts"></i> Tambah Text
                </button>

                <button type="button" class="btn btn-outline-warning btn-sm" onclick="triggerLogoUpload()">
                    <i class="bi bi-image"></i> Logo Sekolah
                </button>
                <input type="file" id="logoUpload" accept="image/*" style="display:none" onchange="addLogoImage(event)">

                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addBarcode()">
                    <i class="bi bi-upc-scan"></i> Barcode Signature
                </button>

                <hr>

                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeSelected()">
                    <i class="bi bi-trash"></i> Hapus Element
                </button>

                <button type="button" class="btn btn-outline-info btn-sm"
                    onclick="canvas.discardActiveObject(); canvas.renderAll()">
                    <i class="bi bi-cursor"></i> Deselect
                </button>

            </div>
        </div>

        {{-- FORMATTING TOOLBAR --}}
        <div class="card mb-3" id="formatToolbar" style="display:none">
            <div class="card-header fw-bold">Format Teks</div>
            <div class="card-body">

                <div class="mb-2">
                    <label class="form-label small mb-1">Font Size</label>
                    <input type="number" id="fontSize" class="form-control form-control-sm"
                        value="16" min="8" max="72"
                        onchange="applyFormat('fontSize', parseInt(this.value))">
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-1">Font Family</label>
                    <select id="fontFamily" class="form-select form-select-sm"
                        onchange="applyFormat('fontFamily', this.value)">
                        <option value="Arial">Arial</option>
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Courier New">Courier New</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Verdana">Verdana</option>
                        <option value="Tahoma">Tahoma</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-1">Warna Teks</label>
                    <input type="color" id="fontColor"
                        class="form-control form-control-color form-control-sm w-100" value="#000000"
                        onchange="applyFormat('fill', this.value)">
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-1">Alignment</label>
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-outline-secondary btn-sm"
                            onclick="applyFormat('textAlign','left')">
                            <i class="bi bi-text-left"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm"
                            onclick="applyFormat('textAlign','center')">
                            <i class="bi bi-text-center"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm"
                            onclick="applyFormat('textAlign','right')">
                            <i class="bi bi-text-right"></i>
                        </button>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleBold()">
                        <strong>B</strong>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleItalic()">
                        <em>I</em>
                    </button>
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleUnderline()">
                        <u>U</u>
                    </button>
                </div>

                <hr class="my-2">

                <div class="mb-2">
                    <label class="form-label small mb-1">Lebar (px)</label>
                    <input type="number" id="objWidth" class="form-control form-control-sm"
                        value="200" min="50" max="794"
                        onchange="applyWidth(parseInt(this.value))">
                </div>

            </div>
        </div>

        {{-- UNDO / REDO --}}
        <div class="card mb-3">
            <div class="card-body d-flex gap-2">
                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="undo()">
                    <i class="bi bi-arrow-counterclockwise"></i> Undo
                </button>
                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="redo()">
                    <i class="bi bi-arrow-clockwise"></i> Redo
                </button>
            </div>
        </div>

    </div>

    {{-- CANVAS EDITOR --}}
    <div class="col-lg-9">

        <form action="{{ route('dashboard.documents.templates.store') }}" method="POST" id="templateForm">
            @csrf

            {{-- Info Template --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Template</label>
                            <input type="text" name="name" class="form-control"
                                placeholder="Contoh: Surat Keterangan Aktif" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- VARIABEL PANEL (di atas canvas) --}}
            <div class="card mb-3" id="variablePanel" style="display:none">
                <div class="card-header d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold small">
                        <i class="bi bi-braces text-primary me-1"></i>Variabel Tersedia
                    </span>
                    <small class="text-muted">Klik chip untuk menaruh ke canvas</small>
                </div>
                <div class="card-body py-2 px-3">
                    <div id="variableChips" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>

            {{-- Canvas --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span class="fw-bold">Template Editor</span>
                    <small class="text-muted">Klik elemen untuk memilih & edit</small>
                </div>
                <div class="card-body text-center p-2" style="overflow:auto; background:#f8f9fa;">
                    <div id="canvasWrapper" style="display:inline-block; border:1px solid #ccc; box-shadow:0 2px 8px rgba(0,0,0,0.1); background:white; line-height:0;">
                        <canvas id="templateCanvas" width="794" height="1123"></canvas>
                    </div>
                </div>
            </div>

            <input type="hidden" name="html_template" id="html_template">
            <input type="hidden" name="canvas_json" id="canvas_json">

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Simpan Template
                </button>
                <a href="{{ route('dashboard.documents.templates.index') }}" class="btn btn-outline-secondary">
                    Batal
                </a>
            </div>

        </form>

    </div>

</div>

{{-- ========================= --}}
{{--   MODAL TAMBAH VARIABEL   --}}
{{-- ========================= --}}
<div class="modal fade" id="modalVariable" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-braces text-primary me-2"></i>Tambah Variabel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Variabel</label>
                    <div class="input-group">
                        <span class="input-group-text text-primary fw-bold">@{{</span>
                        <input type="text" id="varNameInput" class="form-control"
                            placeholder="contoh: nama_siswa">
                        <span class="input-group-text text-primary fw-bold">@}}</span>
                    </div>
                    <div class="form-text">Gunakan huruf kecil dan underscore, tanpa spasi.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        Label Tampilan
                        <span class="text-muted fw-normal">(opsional)</span>
                    </label>
                    <input type="text" id="varLabelInput" class="form-control"
                        placeholder="contoh: Nama Siswa">
                    <div class="form-text">Label untuk chip variabel di panel atas canvas.</div>
                </div>

                {{-- Preset variabel umum --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold small d-block mb-2">Variabel Umum (klik untuk pilih)</label>
                    <div class="d-flex flex-wrap gap-2" id="presetButtons">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nama_siswa" data-label="Nama Siswa">nama_siswa</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nis" data-label="NIS">nis</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="kelas" data-label="Kelas">kelas</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="tanggal" data-label="Tanggal">tanggal</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nama_sekolah" data-label="Nama Sekolah">nama_sekolah</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="kepala_sekolah" data-label="Kepala Sekolah">kepala_sekolah</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nip" data-label="NIP">nip</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="tahun_ajaran" data-label="Tahun Ajaran">tahun_ajaran</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nomor_surat" data-label="Nomor Surat">nomor_surat</button>
                    </div>
                </div>

                <div class="p-2 bg-light rounded small text-muted border" id="varPreview">
                    Preview: <code id="varPreviewCode">@{{ nama_variabel }}</code>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnConfirmVariable">
                    <i class="bi bi-plus-circle me-1"></i>Tambah ke Canvas
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('css')
<style>
    /* Fabric membuat 2 canvas (lower + upper). Pastikan keduanya tidak punya border/shadow sendiri */
    #canvasWrapper canvas {
        border: none !important;
        box-shadow: none !important;
        display: block !important;
    }
</style>
@endpush

@push('js')
<script>
    // Patch: fix Fabric.js v5 'alphabetical' -> 'alphabetic' bug
    (function() {
        var proto = CanvasRenderingContext2D.prototype;
        var descriptor = Object.getOwnPropertyDescriptor(proto, 'textBaseline');
        if (descriptor && descriptor.set) {
            var originalSet = descriptor.set;
            Object.defineProperty(proto, 'textBaseline', {
                get: descriptor.get,
                set: function(val) {
                    originalSet.call(this, val === 'alphabetical' ? 'alphabetic' : val);
                },
                configurable: true,
                enumerable: descriptor.enumerable,
            });
        }
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>

@verbatim
<script>
    // =====================
    // INIT CANVAS
    // =====================
    var canvas = new fabric.Canvas('templateCanvas', {
        preserveObjectStacking: true
    });
    canvas.setBackgroundColor('white', canvas.renderAll.bind(canvas));

    // =====================
    // VARIABEL REGISTRY
    // =====================
    var variableRegistry = []; // [{name, label}]

    function registerVariable(name, label) {
        if (variableRegistry.find(function(v) { return v.name === name; })) return;
        variableRegistry.push({ name: name, label: label || name });
        renderVariableChips();
    }

    function renderVariableChips() {
        var panel   = document.getElementById('variablePanel');
        var chipsEl = document.getElementById('variableChips');

        if (variableRegistry.length === 0) {
            panel.style.display = 'none';
            return;
        }

        panel.style.display = 'block';
        chipsEl.innerHTML = '';

        variableRegistry.forEach(function(v) {
            var chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'btn btn-primary btn-sm d-flex align-items-center gap-1';
            chip.style.borderRadius = '20px';
            chip.style.fontSize = '0.8rem';
            chip.title = 'Klik untuk menaruh {{' + v.name + '}} ke canvas';
            chip.innerHTML =
                '<i class="bi bi-braces" style="font-size:0.7rem"></i>' +
                '<span>' + v.label + '</span>' +
                '<code style="font-size:0.7rem;color:rgba(255,255,255,0.75);margin-left:2px">{{' + v.name + '}}</code>';
            chip.addEventListener('click', function() {
                placeVariableOnCanvas(v.name);
            });
            chipsEl.appendChild(chip);
        });
    }

    function placeVariableOnCanvas(name) {
        var text = new fabric.Textbox('{{' + name + '}}', {
            left: 100,
            top: 150,
            width: 250,
            fontSize: 16,
            fontFamily: 'Arial',
            fill: '#1a56db',
        });
        canvas.add(text);
        canvas.setActiveObject(text);
        canvas.renderAll();
    }

    // =====================
    // MODAL LOGIC — semua event via addEventListener, tidak ada onclick di HTML
    // =====================
    document.addEventListener('DOMContentLoaded', function() {

        var varNameInput     = document.getElementById('varNameInput');
        var varLabelInput    = document.getElementById('varLabelInput');
        var varPreviewCode   = document.getElementById('varPreviewCode');
        var btnConfirm       = document.getElementById('btnConfirmVariable');
        var presetContainer  = document.getElementById('presetButtons');

        // Live preview saat mengetik nama variabel
        varNameInput.addEventListener('input', function() {
            this.classList.remove('is-invalid');
            var val = this.value.trim().replace(/\s+/g, '_').toLowerCase();
            varPreviewCode.textContent = '{{ ' + (val || 'nama_variabel') + ' }}';
        });

        // Preset buttons — delegasi event
        presetContainer.addEventListener('click', function(e) {
            var btn = e.target.closest('button[data-name]');
            if (!btn) return;
            varNameInput.value  = btn.dataset.name;
            varLabelInput.value = btn.dataset.label;
            varPreviewCode.textContent = '{{ ' + btn.dataset.name + ' }}';
            varNameInput.classList.remove('is-invalid');
        });

        // Tombol Tambah ke Canvas
        btnConfirm.addEventListener('click', function() {
            var rawName = varNameInput.value.trim();
            if (!rawName) {
                varNameInput.classList.add('is-invalid');
                varNameInput.focus();
                return;
            }
            varNameInput.classList.remove('is-invalid');

            var name  = rawName.replace(/\s+/g, '_').toLowerCase();
            var label = varLabelInput.value.trim() || name;

            registerVariable(name, label);
            placeVariableOnCanvas(name);

            // Reset form
            varNameInput.value  = '';
            varLabelInput.value = '';
            varPreviewCode.textContent = '{{ nama_variabel }}';

            bootstrap.Modal.getInstance(document.getElementById('modalVariable')).hide();
        });

        // Enter key di input nama
        varNameInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); btnConfirm.click(); }
        });

    });

    // =====================
    // UNDO / REDO
    // =====================
    var _history    = [];
    var _historyRedo = [];
    var _isSaving   = false;

    function saveState() {
        if (_isSaving) return;
        _history.push(JSON.stringify(canvas.toJSON(['name'])));
        _historyRedo = [];
    }

    function undo() {
        if (_history.length < 2) return;
        _isSaving = true;
        _historyRedo.push(_history.pop());
        canvas.loadFromJSON(_history[_history.length - 1], function() {
            canvas.renderAll();
            _isSaving = false;
        });
    }

    function redo() {
        if (_historyRedo.length === 0) return;
        _isSaving = true;
        var next = _historyRedo.pop();
        _history.push(next);
        canvas.loadFromJSON(next, function() {
            canvas.renderAll();
            _isSaving = false;
        });
    }

    canvas.on('object:added',   saveState);
    canvas.on('object:modified', saveState);
    canvas.on('object:removed', saveState);
    saveState();

    // =====================
    // FORMAT TOOLBAR
    // =====================
    canvas.on('selection:created', updateToolbar);
    canvas.on('selection:updated', updateToolbar);
    canvas.on('selection:cleared', function() {
        document.getElementById('formatToolbar').style.display = 'none';
    });

    function updateToolbar() {
        var obj = canvas.getActiveObject();
        if (!obj) return;
        document.getElementById('formatToolbar').style.display = 'block';
        if (obj.type === 'textbox' || obj.type === 'i-text') {
            document.getElementById('fontSize').value   = obj.fontSize   || 16;
            document.getElementById('fontFamily').value = obj.fontFamily || 'Arial';
            document.getElementById('fontColor').value  = obj.fill       || '#000000';
            document.getElementById('objWidth').value   = Math.round(obj.width) || 200;
        } else {
            document.getElementById('objWidth').value = Math.round((obj.width || 0) * (obj.scaleX || 1));
        }
    }

    function applyFormat(prop, value) {
        var obj = canvas.getActiveObject();
        if (!obj) return;
        obj.set(prop, value);
        canvas.renderAll();
        saveState();
    }

    function applyWidth(val) {
        var obj = canvas.getActiveObject();
        if (!obj) return;
        obj.type === 'textbox'
            ? obj.set('width', val)
            : obj.set('scaleX', val / obj.width);
        canvas.renderAll();
        saveState();
    }

    function toggleBold() {
        var obj = canvas.getActiveObject();
        if (!obj || obj.type !== 'textbox') return;
        obj.set('fontWeight', obj.fontWeight === 'bold' ? 'normal' : 'bold');
        canvas.renderAll(); saveState();
    }

    function toggleItalic() {
        var obj = canvas.getActiveObject();
        if (!obj || obj.type !== 'textbox') return;
        obj.set('fontStyle', obj.fontStyle === 'italic' ? 'normal' : 'italic');
        canvas.renderAll(); saveState();
    }

    function toggleUnderline() {
        var obj = canvas.getActiveObject();
        if (!obj || obj.type !== 'textbox') return;
        obj.set('underline', !obj.underline);
        canvas.renderAll(); saveState();
    }

    // =====================
    // ADD TEXT
    // =====================
    function addText() {
        var text = new fabric.Textbox('Tulis teks di sini', {
            left: 100, top: 100, width: 300,
            fontSize: 16, fontFamily: 'Arial', fill: '#000000',
        });
        canvas.add(text);
        canvas.setActiveObject(text);
    }

    // =====================
    // LOGO
    // =====================
    function triggerLogoUpload() {
        document.getElementById('logoUpload').click();
    }

    function addLogoImage(event) {
        var file = event.target.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(e) {
            fabric.Image.fromURL(e.target.result, function(img) {
                img.scaleToWidth(100);
                img.set({ left: 40, top: 30, name: 'logo' });
                canvas.add(img);
                canvas.setActiveObject(img);
                canvas.renderAll();
            });
        };
        reader.readAsDataURL(file);
        event.target.value = '';
    }

    // =====================
    // BARCODE
    // =====================
    function addBarcode() {
        var rect = new fabric.Rect({
            width: 120, height: 120,
            fill: '#fff', stroke: '#333', strokeWidth: 1, rx: 4, ry: 4,
        });
        var label = new fabric.Text('{{barcode_signature}}', {
            fontSize: 9, fontFamily: 'Courier New', fill: '#333',
            textAlign: 'center', originX: 'center', originY: 'center',
            left: 60, top: 60,
        });
        var group = new fabric.Group([rect, label], {
            left: 600, top: 940, name: 'barcode',
        });
        canvas.add(group);
        canvas.setActiveObject(group);
    }

    // =====================
    // REMOVE SELECTED
    // =====================
    function removeSelected() {
        var obj = canvas.getActiveObject();
        if (!obj) {
            alert('Pilih elemen yang ingin dihapus terlebih dahulu.');
            return;
        }
        canvas.remove(obj);
        canvas.discardActiveObject();
        canvas.renderAll();
    }

    // =====================
    // GENERATE HTML — DomPDF pixel-perfect
    //
    // PENTING: Fabric.js menyimpan obj.left/top sebagai titik ORIGIN object.
    // Origin bisa 'left'/'center'/'right' untuk X dan 'top'/'center'/'bottom' untuk Y.
    // Kita harus hitung real top-left corner sebelum konversi ke pt.
    //
    // DomPDF bekerja dalam pt. A4 = 595.28pt x 841.89pt.
    // Canvas = 794px x 1123px → rasio = 595.28 / 794 = 0.74975 pt/px
    // =====================
    function generateHTML() {
        var CANVAS_W = canvas.width;    // 794
        var CANVAS_H = canvas.height;   // 1123
        var A4_W     = 595.28;
        var A4_H     = 841.89;
        var R        = A4_W / CANVAS_W; // px → pt

        // Konversi nilai pixel ke pt, 2 desimal
        function pt(px) {
            return parseFloat((px * R).toFixed(2));
        }

        // Hitung real top-left corner dari Fabric object
        // Fabric obj.left/top adalah posisi titik ORIGIN (bisa center, left, right, dll)
        function realTopLeft(obj) {
            var w = (obj.width  || 0) * (obj.scaleX || 1);
            var h = (obj.height || 0) * (obj.scaleY || 1);
            var ox = obj.originX || 'left';
            var oy = obj.originY || 'top';

            var x = obj.left || 0;
            var y = obj.top  || 0;

            // Koreksi X berdasarkan originX
            if      (ox === 'center') x = x - w / 2;
            else if (ox === 'right')  x = x - w;
            // 'left' = tidak perlu koreksi

            // Koreksi Y berdasarkan originY
            if      (oy === 'center') y = y - h / 2;
            else if (oy === 'bottom') y = y - h;
            // 'top' = tidak perlu koreksi

            return { x: x, y: y, w: w, h: h };
        }

        function escapeContent(text) {
            var t = (text || '')
                .replace(/&/g,  '&amp;')
                .replace(/</g,  '&lt;')
                .replace(/>/g,  '&gt;')
                .replace(/\{\{([^}]+)\}\}/g, function(m, v) { return '{!' + v.trim() + '!}'; })
                .replace(/\n/g, '<br>');
            return t.replace(/\{!([^!]+)!\}/g, function(m, v) { return '{{' + v + '}}'; });
        }

        function textStyle(obj, wPx) {
            // fontSize harus dikalikan scale karena Fabric.js scale seluruh object
            var scaledFontSize = (obj.fontSize || 16) * (obj.scaleY || 1);
            var parts = [
                'font-size:'   + pt(scaledFontSize) + 'pt',
                'font-family:' + (obj.fontFamily   || 'DejaVu Sans') + ',sans-serif',
                'color:'       + (obj.fill         || '#000000'),
                'font-weight:' + (obj.fontWeight   || 'normal'),
                'font-style:'  + (obj.fontStyle    || 'normal'),
                'text-align:'  + (obj.textAlign    || 'left'),
                'line-height:' + (obj.lineHeight    || 1.4),
                'width:'       + pt(wPx) + 'pt',
                'overflow:visible',
            ];
            if (obj.underline) parts.push('text-decoration:underline');
            return parts.join(';');
        }

        // Wrapper: ukuran A4 dalam pt, position:relative wajib untuk absolute children
        var html = '<div style="position:relative;width:' + A4_W + 'pt;height:' + A4_H + 'pt;">';

        var objects = canvas.getObjects();
        // Render dari bawah ke atas z-order (background dulu)
        objects.forEach(function(obj) {
            var pos = realTopLeft(obj);
            var lPt = pt(pos.x);
            var tPt = pt(pos.y);
            var wPt = pt(pos.w);
            var hPt = pt(pos.h);

            var posStyle = 'position:absolute;left:' + lPt + 'pt;top:' + tPt + 'pt;';

            if (obj.type === 'textbox' || obj.type === 'i-text') {
                html += '<div style="' + posStyle + textStyle(obj, pos.w) + '">'
                      + escapeContent(obj.text)
                      + '</div>';

            } else if (obj.type === 'image') {
                var dimStyle = 'width:' + wPt + 'pt;height:' + hPt + 'pt;';
                if (obj.name === 'logo') {
                    // Logo placeholder — diganti oleh DocumentGeneratorService
                    // img di dalam div agar bisa dikontrol ukurannya
                    html += '<div style="' + posStyle + dimStyle + '">{{logo}}</div>';
                } else {
                    var src = obj.toDataURL ? obj.toDataURL({ format: 'png' }) : '';
                    html += '<img src="' + src + '" style="' + posStyle + dimStyle + '" />';
                }

            } else if (obj.type === 'group' && obj.name === 'barcode') {
                html += '<div style="' + posStyle + 'width:' + wPt + 'pt;height:' + hPt + 'pt;">{{barcode_signature}}</div>';

            } else if (obj.type === 'rect') {
                var rectStyle = posStyle
                    + 'width:'       + wPt + 'pt;'
                    + 'height:'      + hPt + 'pt;'
                    + 'background:'  + (obj.fill || 'transparent') + ';';
                if (obj.stroke) {
                    rectStyle += 'border:' + pt(obj.strokeWidth || 1) + 'pt solid ' + obj.stroke + ';';
                }
                html += '<div style="' + rectStyle + '"></div>';
            }
        });

        html += '</div>';

        document.getElementById('html_template').value = html;
        document.getElementById('canvas_json').value   = JSON.stringify(canvas.toJSON(['name']));
        return true;
    }




    // =====================
    // FORM SUBMIT
    // =====================
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        if (canvas.getObjects().length === 0) {
            e.preventDefault();
            alert('Template masih kosong! Tambahkan elemen terlebih dahulu.');
            return false;
        }
        generateHTML();
    });

    // =====================
    // KEYBOARD SHORTCUTS
    // =====================
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'z') { e.preventDefault(); undo(); }
        if ((e.ctrlKey || e.metaKey) && e.key === 'y') { e.preventDefault(); redo(); }
        if ((e.key === 'Delete' || e.key === 'Backspace') &&
            !['INPUT','TEXTAREA','SELECT'].includes(document.activeElement.tagName)) {
            removeSelected();
        }
    });
</script>
@endverbatim
@endpush