@extends('layouts.dashboard')

@section('title','Edit Template Surat')

@section('content')

<div class="row">

    {{-- SIDEBAR KOMPONEN --}}
    <div class="col-lg-3">

        <div class="card mb-3">
            <div class="card-header fw-bold">Komponen</div>
            <div class="card-body d-grid gap-2">

                {{-- KOP SURAT --}}
                <button type="button" class="btn btn-primary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalKop">
                    <i class="bi bi-bank me-1"></i> Kop Surat Sekolah
                </button>

                <hr class="my-1">

                <button type="button" class="btn btn-outline-primary btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalVariable">
                    <i class="bi bi-braces"></i> Tambah Variabel
                </button>

                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="addText()">
                    <i class="bi bi-fonts"></i> Tambah Text
                </button>

                <button type="button" class="btn btn-outline-warning btn-sm" onclick="triggerImageUpload()">
                    <i class="bi bi-image"></i> Upload Gambar
                </button>
                <input type="file" id="imageUpload" accept="image/*" style="display:none" onchange="addImage(event)">

                <button type="button" class="btn btn-outline-warning btn-sm" onclick="triggerLogoUpload()">
                    <i class="bi bi-building"></i> Logo Sekolah
                </button>
                <input type="file" id="logoUpload" accept="image/*" style="display:none" onchange="addLogoImage(event)">

                <button type="button" class="btn btn-outline-dark btn-sm" onclick="addBarcode()">
                    <i class="bi bi-upc-scan"></i> Barcode Signature
                </button>

                <hr class="my-1">

                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill"
                        onclick="bringForward()" title="Pindah ke depan">
                        <i class="bi bi-layers-fill"></i> Depan
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill"
                        onclick="sendBackward()" title="Pindah ke belakang">
                        <i class="bi bi-layers"></i> Belakang
                    </button>
                </div>

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
                        value="16" min="6" max="72"
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
                            onclick="applyFormat('textAlign','left')"><i class="bi bi-text-left"></i></button>
                        <button class="btn btn-outline-secondary btn-sm"
                            onclick="applyFormat('textAlign','center')"><i class="bi bi-text-center"></i></button>
                        <button class="btn btn-outline-secondary btn-sm"
                            onclick="applyFormat('textAlign','right')"><i class="bi bi-text-right"></i></button>
                    </div>
                </div>

                <div class="d-flex gap-2 mb-2">
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleBold()"><strong>B</strong></button>
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleItalic()"><em>I</em></button>
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleUnderline()"><u>U</u></button>
                </div>

                <hr class="my-2">

                <div class="mb-2">
                    <label class="form-label small mb-1">Lebar (px)</label>
                    <input type="number" id="objWidth" class="form-control form-control-sm"
                        value="200" min="50" max="794"
                        onchange="applyWidth(parseInt(this.value))">
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-1">Opacity: <span id="opacityVal">100</span>%</label>
                    <input type="range" id="objOpacity" class="form-range"
                        min="10" max="100" value="100"
                        oninput="document.getElementById('opacityVal').textContent=this.value; applyOpacity(parseInt(this.value))">
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

        <form action="{{ route('dashboard.documents.templates.update', $template) }}" method="POST" id="templateForm">
            @csrf
            @method('PUT')

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ $template->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Template</label>
                            <input type="text" name="name" class="form-control"
                                value="{{ $template->name }}"
                                placeholder="Contoh: Surat Keterangan Aktif" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- VARIABEL PANEL --}}
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
                    <i class="bi bi-save"></i> Simpan Perubahan
                </button>
                <a href="{{ route('dashboard.documents.templates.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>

        </form>

    </div>

</div>

{{-- ========================= --}}
{{--   MODAL KOP SURAT         --}}
{{-- ========================= --}}
<div class="modal fade" id="modalKop" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-bank text-primary me-2"></i>Buat Kop Surat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">

                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Logo Sekolah</label>
                        <input type="file" id="kopLogoFile" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text">Kosongkan → pakai placeholder <code>{logo}</code> (diganti otomatis saat generate)</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ukuran Logo (px)</label>
                        <input type="number" id="kopLogoSize" class="form-control form-control-sm" value="90" min="40" max="200">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Nama Yayasan / Majelis</label>
                        <input type="text" id="kopLine1" class="form-control form-control-sm"
                            value="MAJELIS DIKDASMEN MUHAMMADIYAH SAMARINDA">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Nama Sekolah</label>
                        <input type="text" id="kopLine2" class="form-control form-control-sm"
                            value="Sekolah Kreatif">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Sub-nama / Jenjang</label>
                        <input type="text" id="kopLine3" class="form-control form-control-sm"
                            value="SD MUHAMMADIYAH 3 SAMARINDA SEBERANG">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Alamat</label>
                        <input type="text" id="kopLine4" class="form-control form-control-sm"
                            value="Jalan Dato Iba Telp. (0541) 260066 Kel. Sungai Keledang – Samarinda Seberang 75131">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email / Website</label>
                        <input type="text" id="kopLine5" class="form-control form-control-sm"
                            value="E-mail : sdmuhammadiyahtiga@ymail.com">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">NPSN / Akreditasi (opsional)</label>
                        <input type="text" id="kopLine6" class="form-control form-control-sm"
                            value="NPSN : 30404112">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold">Garis Bawah Kop</label>
                        <select id="kopBorderStyle" class="form-select form-select-sm">
                            <option value="double">Garis Double (tebal + tipis)</option>
                            <option value="single">Garis Single</option>
                            <option value="none">Tanpa Garis</option>
                        </select>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="btnAddKop">
                    <i class="bi bi-plus-circle me-1"></i>Tambahkan ke Canvas
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ========================= --}}
{{--   MODAL TAMBAH VARIABEL   --}}
{{-- ========================= --}}
<div class="modal fade" id="modalVariable" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-braces text-primary me-2"></i>Tambah Variabel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Variabel</label>
                    <div class="input-group">
                        <span class="input-group-text text-primary fw-bold">@{{</span>
                        <input type="text" id="varNameInput" class="form-control" placeholder="contoh: nama_siswa">
                        <span class="input-group-text text-primary fw-bold">@}}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Label <span class="text-muted fw-normal">(opsional)</span></label>
                    <input type="text" id="varLabelInput" class="form-control" placeholder="contoh: Nama Siswa">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small d-block mb-2">Variabel Umum</label>
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
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nilai_rata" data-label="Nilai Rata-rata">nilai_rata</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="peringkat" data-label="Peringkat">peringkat</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="semester" data-label="Semester">semester</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="wali_kelas" data-label="Wali Kelas">wali_kelas</button>
                    </div>
                </div>
                <div class="p-2 bg-light rounded small text-muted border">
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
    #canvasWrapper canvas { border:none !important; box-shadow:none !important; display:block !important; }
</style>
@endpush

@push('js')
<script>
    (function() {
        var proto = CanvasRenderingContext2D.prototype;
        var d = Object.getOwnPropertyDescriptor(proto, 'textBaseline');
        if (d && d.set) {
            var orig = d.set;
            Object.defineProperty(proto, 'textBaseline', {
                get: d.get,
                set: function(v) { orig.call(this, v === 'alphabetical' ? 'alphabetic' : v); },
                configurable: true, enumerable: d.enumerable,
            });
        }
    })();
</script>
<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<script>
    var EXISTING_CANVAS_JSON = @json($template->canvas_json);
</script>

@verbatim
<script>
// =============================================================
// CANVAS INIT
// =============================================================
var canvas = new fabric.Canvas('templateCanvas', { preserveObjectStacking: true });
canvas.setBackgroundColor('white', canvas.renderAll.bind(canvas));

// =============================================================
// RESTORE
// =============================================================
if (EXISTING_CANVAS_JSON) {
    canvas.loadFromJSON(EXISTING_CANVAS_JSON, function() {
        canvas.renderAll();
        canvas.getObjects().forEach(function(obj) {
            if (obj.type === 'textbox' || obj.type === 'i-text') {
                var m = (obj.text || '').match(/\{\{([^}]+)\}\}/g);
                if (m) m.forEach(function(x) {
                    var n = x.replace(/[{}]/g,'').trim();
                    if (!['logo','barcode_signature'].includes(n)) registerVariable(n, n);
                });
            }
        });
        saveState();
    });
}

// =============================================================
// VARIABEL
// =============================================================
var variableRegistry = [];

function registerVariable(name, label) {
    if (variableRegistry.find(function(v){ return v.name===name; })) return;
    variableRegistry.push({ name: name, label: label||name });
    renderVariableChips();
}

function renderVariableChips() {
    var panel = document.getElementById('variablePanel');
    var el    = document.getElementById('variableChips');
    if (!variableRegistry.length) { panel.style.display='none'; return; }
    panel.style.display = 'block';
    el.innerHTML = '';
    variableRegistry.forEach(function(v) {
        var c = document.createElement('button');
        c.type = 'button';
        c.className = 'btn btn-primary btn-sm d-flex align-items-center gap-1';
        c.style.cssText = 'border-radius:20px;font-size:0.8rem';
        c.innerHTML = '<i class="bi bi-braces" style="font-size:0.7rem"></i><span>'+v.label+'</span>'
            + '<code style="font-size:0.7rem;color:rgba(255,255,255,0.75);margin-left:2px">{{'+v.name+'}}</code>';
        c.addEventListener('click', function(){ placeVariableOnCanvas(v.name); });
        el.appendChild(c);
    });
}

function placeVariableOnCanvas(name) {
    canvas.add(new fabric.Textbox('{{'+name+'}}', {
        left:100, top:180, width:250, fontSize:16, fontFamily:'Arial', fill:'#1a56db', name:'var_'+name,
    }));
    canvas.setActiveObject(canvas.getObjects()[canvas.getObjects().length-1]);
    canvas.renderAll();
}

// =============================================================
// MODAL VARIABEL
// =============================================================
document.addEventListener('DOMContentLoaded', function() {
    var ni = document.getElementById('varNameInput');
    var li = document.getElementById('varLabelInput');
    var pc = document.getElementById('varPreviewCode');

    ni.addEventListener('input', function(){
        ni.classList.remove('is-invalid');
        pc.textContent = '{{ '+(ni.value.trim().replace(/\s+/g,'_').toLowerCase()||'nama_variabel')+' }}';
    });

    document.getElementById('presetButtons').addEventListener('click', function(e){
        var b = e.target.closest('button[data-name]'); if (!b) return;
        ni.value=b.dataset.name; li.value=b.dataset.label;
        pc.textContent='{{ '+b.dataset.name+' }}'; ni.classList.remove('is-invalid');
    });

    document.getElementById('btnConfirmVariable').addEventListener('click', function(){
        var raw = ni.value.trim();
        if (!raw){ ni.classList.add('is-invalid'); ni.focus(); return; }
        var name = raw.replace(/\s+/g,'_').toLowerCase();
        var label = li.value.trim()||name;
        registerVariable(name,label); placeVariableOnCanvas(name);
        ni.value=''; li.value=''; pc.textContent='{{ nama_variabel }}';
        bootstrap.Modal.getInstance(document.getElementById('modalVariable')).hide();
    });

    ni.addEventListener('keydown', function(e){ if(e.key==='Enter'){ e.preventDefault(); document.getElementById('btnConfirmVariable').click(); } });
});

// =============================================================
// MODAL KOP SURAT
// =============================================================
document.getElementById('btnAddKop').addEventListener('click', function() {
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
              .filter(function(o){ return o.name && o.name.startsWith('kop_'); })
              .forEach(function(o){ canvas.remove(o); });

        var CW   = canvas.width;   // 794
        var KT   = 20;             // kopTop
        var LW   = logoSize;
        var TX   = LW + 34;        // teks mulai dari kanan logo
        var TW   = CW - TX - 20;   // lebar teks

        // ── Teks kop ──
        var textItems = [
            line1 ? [line1,  11, 'bold',   '#000000'] : null,
            line2 ? [line2,  16, 'bold',   '#c0392b'] : null,
            line3 ? [line3,  12, 'bold',   '#1a5276'] : null,
            line4 ? [line4,  10, 'normal', '#000000'] : null,
            line5 ? [line5,  10, 'normal', '#000000'] : null,
        ].filter(Boolean);

        var lineHeights = textItems.map(function(t){ return t[1] * 1.4; });
        var totalH = lineHeights.reduce(function(a,b){ return a+b; }, 0);
        var startY = KT + Math.max(0, (LW - totalH) / 2);
        var curY   = startY;

        textItems.forEach(function(t, i) {
            var tb = new fabric.Textbox(t[0], {
                left: TX, top: curY,
                width: TW, fontSize: t[1],
                fontFamily: 'Arial', fontWeight: t[2],
                textAlign: 'center', fill: t[3],
                name: 'kop_text', selectable: true, evented: true,
            });
            canvas.add(tb);
            curY += lineHeights[i];
        });

        // NPSN di bawah logo (kiri)
        if (line6) {
            canvas.add(new fabric.Textbox(line6, {
                left: 20, top: KT + LW + 4,
                width: LW + 30, fontSize: 9,
                fontFamily: 'Arial', fill: '#000',
                name: 'kop_npsn',
            }));
        }

        // ── Garis ──
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

        // ── Logo ──
        function addLogoObj(imgObj) {
            imgObj.scaleToWidth(LW);
            imgObj.scaleToHeight(LW);
            imgObj.set({ left: 20, top: KT, name: 'kop_logo' });
            canvas.add(imgObj);
            canvas.sendToBack(imgObj);
            canvas.renderAll();
            saveState();
            bootstrap.Modal.getInstance(document.getElementById('modalKop')).hide();
        }

        if (logoDataUrl) {
            fabric.Image.fromURL(logoDataUrl, addLogoObj);
        } else {
            // Placeholder kotak abu2 dengan tulisan {{logo}}
            var ph = new fabric.Rect({
                left: 20, top: KT, width: LW, height: LW,
                fill: '#f0f0f0', stroke: '#bbb', strokeWidth: 1, rx: 4, ry: 4,
                name: 'kop_logo', selectable: true, evented: true,
            });
            var phLabel = new fabric.Text('{{logo}}', {
                left: 20 + LW / 2, top: KT + LW / 2,
                fontSize: 9, fontFamily: 'Arial', fill: '#888',
                originX: 'center', originY: 'center',
                name: 'kop_logo_label', selectable: false, evented: false,
            });
            canvas.add(ph);
            canvas.add(phLabel);
            canvas.renderAll();
            saveState();
            bootstrap.Modal.getInstance(document.getElementById('modalKop')).hide();
        }
    }

    if (logoFile) {
        var reader = new FileReader();
        reader.onload = function(e){ buildKop(e.target.result); };
        reader.readAsDataURL(logoFile);
    } else {
        buildKop(null);
    }
});

// =============================================================
// UNDO / REDO
// =============================================================
var _history=[], _historyRedo=[], _isSaving=false;

function saveState() {
    if (_isSaving) return;
    _history.push(JSON.stringify(canvas.toJSON(['name'])));
    _historyRedo=[];
}
function undo() {
    if (_history.length<2) return;
    _isSaving=true; _historyRedo.push(_history.pop());
    canvas.loadFromJSON(_history[_history.length-1], function(){ canvas.renderAll(); _isSaving=false; });
}
function redo() {
    if (!_historyRedo.length) return;
    _isSaving=true; var n=_historyRedo.pop(); _history.push(n);
    canvas.loadFromJSON(n, function(){ canvas.renderAll(); _isSaving=false; });
}
canvas.on('object:added',    saveState);
canvas.on('object:modified', saveState);
canvas.on('object:removed',  saveState);
if (!EXISTING_CANVAS_JSON) saveState();

// =============================================================
// FORMAT TOOLBAR
// =============================================================
canvas.on('selection:created', updateToolbar);
canvas.on('selection:updated', updateToolbar);
canvas.on('selection:cleared', function(){ document.getElementById('formatToolbar').style.display='none'; });

function updateToolbar() {
    var obj = canvas.getActiveObject(); if (!obj) return;
    document.getElementById('formatToolbar').style.display='block';
    var op = Math.round((obj.opacity||1)*100);
    document.getElementById('objOpacity').value = op;
    document.getElementById('opacityVal').textContent = op;
    if (obj.type==='textbox'||obj.type==='i-text') {
        document.getElementById('fontSize').value   = obj.fontSize   || 16;
        document.getElementById('fontFamily').value = obj.fontFamily || 'Arial';
        document.getElementById('fontColor').value  = obj.fill       || '#000000';
        document.getElementById('objWidth').value   = Math.round(obj.width) || 200;
    } else {
        document.getElementById('objWidth').value = Math.round((obj.width||0)*(obj.scaleX||1));
    }
}

function applyFormat(p,v){ var o=canvas.getActiveObject(); if(!o) return; o.set(p,v); canvas.renderAll(); saveState(); }
function applyWidth(v)   { var o=canvas.getActiveObject(); if(!o) return; o.type==='textbox'?o.set('width',v):o.set('scaleX',v/o.width); canvas.renderAll(); saveState(); }
function applyOpacity(v) { var o=canvas.getActiveObject(); if(!o) return; o.set('opacity',v/100); canvas.renderAll(); saveState(); }
function toggleBold()      { var o=canvas.getActiveObject(); if(!o||o.type!=='textbox') return; o.set('fontWeight',o.fontWeight==='bold'?'normal':'bold'); canvas.renderAll(); saveState(); }
function toggleItalic()    { var o=canvas.getActiveObject(); if(!o||o.type!=='textbox') return; o.set('fontStyle',o.fontStyle==='italic'?'normal':'italic'); canvas.renderAll(); saveState(); }
function toggleUnderline() { var o=canvas.getActiveObject(); if(!o||o.type!=='textbox') return; o.set('underline',!o.underline); canvas.renderAll(); saveState(); }

// =============================================================
// LAYER CONTROL
// =============================================================
function bringForward()  { var o=canvas.getActiveObject(); if(!o) return; canvas.bringForward(o);  canvas.renderAll(); saveState(); }
function sendBackward()  { var o=canvas.getActiveObject(); if(!o) return; canvas.sendBackwards(o); canvas.renderAll(); saveState(); }

// =============================================================
// ADD TEXT / IMAGE / LOGO / BARCODE
// =============================================================
function addText() {
    canvas.add(new fabric.Textbox('Tulis teks di sini', {
        left:100, top:150, width:300, fontSize:16, fontFamily:'Arial', fill:'#000',
    }));
}
function triggerImageUpload(){ document.getElementById('imageUpload').click(); }
function addImage(e) {
    var f=e.target.files[0]; if(!f) return;
    var r=new FileReader();
    r.onload=function(ev){ fabric.Image.fromURL(ev.target.result, function(img){ img.scaleToWidth(200); img.set({left:100,top:100}); canvas.add(img); canvas.renderAll(); }); };
    r.readAsDataURL(f); e.target.value='';
}
function triggerLogoUpload(){ document.getElementById('logoUpload').click(); }
function addLogoImage(e) {
    var f=e.target.files[0]; if(!f) return;
    var r=new FileReader();
    r.onload=function(ev){ fabric.Image.fromURL(ev.target.result, function(img){ img.scaleToWidth(100); img.set({left:40,top:30,name:'logo'}); canvas.add(img); canvas.renderAll(); }); };
    r.readAsDataURL(f); e.target.value='';
}
function addBarcode() {
    var g=new fabric.Group([
        new fabric.Rect({width:120,height:120,fill:'#fff',stroke:'#333',strokeWidth:1,rx:4,ry:4}),
        new fabric.Text('{{barcode_signature}}',{fontSize:9,fontFamily:'Courier New',fill:'#333',textAlign:'center',originX:'center',originY:'center',left:60,top:60}),
    ],{left:600,top:860,name:'barcode'});
    canvas.add(g);
}

function removeSelected() {
    var obj = canvas.getActiveObject(); if(!obj){ alert('Pilih elemen terlebih dahulu.'); return; }
    if (obj.name && obj.name.startsWith('kop_')) {
        if (!confirm('Hapus seluruh kop surat?')) return;
        canvas.getObjects().filter(function(o){ return o.name&&o.name.startsWith('kop_'); })
              .forEach(function(o){ canvas.remove(o); });
    } else {
        canvas.remove(obj);
    }
    canvas.discardActiveObject(); canvas.renderAll();
}

// =============================================================
// GENERATE HTML — DomPDF pixel-perfect
//
// PENTING: Fabric.js menyimpan obj.left/top sebagai titik ORIGIN object.
// Origin bisa 'left'/'center'/'right' untuk X dan 'top'/'center'/'bottom' untuk Y.
// Kita harus hitung real top-left corner sebelum konversi ke pt.
//
// DomPDF bekerja dalam pt. A4 = 595.28pt x 841.89pt.
// Canvas = 794px x 1123px → rasio = 595.28 / 794 = 0.74975 pt/px
// =============================================================
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
        // ── Lewati label placeholder kop (teks {{logo}} di dalam Rect) ──
        if (obj.name === 'kop_logo_label') return;

        var pos = realTopLeft(obj);
        var lPt = pt(pos.x);
        var tPt = pt(pos.y);
        var wPt = pt(pos.w);
        var hPt = pt(pos.h);

        var posStyle = 'position:absolute;left:' + lPt + 'pt;top:' + tPt + 'pt;';

        // ── TEXTBOX / I-TEXT ────────────────────────────────────────
        if (obj.type === 'textbox' || obj.type === 'i-text') {
            html += '<div style="' + posStyle + textStyle(obj, pos.w) + '">'
                  + escapeContent(obj.text)
                  + '</div>';

        // ── IMAGE ───────────────────────────────────────────────────
        } else if (obj.type === 'image') {
            var dimStyle = 'width:' + wPt + 'pt;height:' + hPt + 'pt;';
            // Semua image bernama kop_logo atau logo → pakai {{logo}} placeholder
            if (obj.name === 'logo' || obj.name === 'kop_logo') {
                html += '<div style="' + posStyle + dimStyle + '">{{logo}}</div>';
            } else {
                var src = obj.toDataURL ? obj.toDataURL({ format: 'png' }) : '';
                html += '<img src="' + src + '" style="' + posStyle + dimStyle + '" />';
            }

        // ── BARCODE GROUP ───────────────────────────────────────────
        } else if (obj.type === 'group' && obj.name === 'barcode') {
            html += '<div style="' + posStyle + 'width:' + wPt + 'pt;height:' + hPt + 'pt;">{{barcode_signature}}</div>';

        // ── LINE (GARIS KOP) ────────────────────────────────────────
        // Gunakan getBoundingRect() untuk koordinat absolut
        } else if (obj.type === 'line') {
            var br  = obj.getBoundingRect();
            var lx  = pt(br.left);
            var lw  = pt(br.width);
            var lt  = pt(br.top);
            var sw  = pt(obj.strokeWidth || 1);
            // Pastikan minimal 0.75pt agar garis tetap terlihat di DomPDF
            if (sw < 0.75) sw = 0.75;
            html += '<div style="position:absolute;'
                  + 'left:'   + lx + 'pt;'
                  + 'top:'    + lt + 'pt;'
                  + 'width:'  + lw + 'pt;'
                  + 'height:' + sw + 'pt;'
                  + 'background:' + (obj.stroke || '#000') + ';'
                  + '"></div>';

        // ── RECT ────────────────────────────────────────────────────
        } else if (obj.type === 'rect') {
            // Placeholder rect kop_logo → {{logo}}
            if (obj.name === 'kop_logo') {
                html += '<div style="' + posStyle + 'width:' + wPt + 'pt;height:' + hPt + 'pt;">{{logo}}</div>';
                return;
            }
            var rectStyle = posStyle
                + 'width:'       + wPt + 'pt;'
                + 'height:'      + hPt + 'pt;'
                + 'background:'  + (obj.fill || 'transparent') + ';';
            if (obj.stroke) {
                rectStyle += 'border:' + pt(obj.strokeWidth || 1) + 'pt solid ' + obj.stroke + ';';
            }
            html += '<div style="' + rectStyle + '"></div>';
        }
        // Tipe lain dilewati
    });

    html += '</div>';

    document.getElementById('html_template').value = html;
    document.getElementById('canvas_json').value   = JSON.stringify(canvas.toJSON(['name']));
    return true;
}

// =============================================================
// FORM SUBMIT
// =============================================================
document.getElementById('templateForm').addEventListener('submit', function(e) {
    if (!canvas.getObjects().length) {
        e.preventDefault();
        alert('Template masih kosong!');
        return false;
    }
    generateHTML();
});

// =============================================================
// KEYBOARD SHORTCUTS
// =============================================================
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