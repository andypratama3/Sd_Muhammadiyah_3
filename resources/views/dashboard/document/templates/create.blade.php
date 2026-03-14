@extends('layouts.dashboard')

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('asset_dashboard/js/document/style.css') }}">
@endpush

@section('title', 'Buat Template Surat')

@section('content')

{{-- ── TOP BAR ──────────────────────────────────────────────────────────── --}}
<div class="editor-topbar">
    <div class="brand">
        <i class="bi bi-file-earmark-richtext"></i>
        Template Baru
    </div>
    <div class="sep"></div>

    <form action="{{ route('dashboard.documents.templates.store') }}"
          method="POST" id="templateForm"
          style="display:contents">
        @csrf
        <input type="hidden" name="kelas_id" id="kelas_id">

        <div class="col-md-2">
            <div class="tb-field">
                <label class="form-label text-white">Kategori</label>
                <select name="category_id" required class="form-control select2">
                    <option value="">Pilih…</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-2">
            <div class="tb-field">
                <label class="form-label text-white">Nama Template</label>
                <input type="text" name="name"
                    placeholder="Contoh: Surat Keterangan Aktif" required class="form-control">
            </div>
        </div>

        <div class="tb-spacer"></div>

        <a href="{{ route('dashboard.documents.templates.index') }}"
           class="tb-btn">
            <i class="bi bi-x-lg"></i> Batal
        </a>

        <button type="submit" class="tb-btn save">
            <i class="bi bi-save"></i> Simpan Template
        </button>

        <input type="hidden" name="html_template" id="html_template">
        <input type="hidden" name="canvas_json"   id="canvas_json">
    </form>
</div>

{{-- ── SIDEBAR KIRI ── --}}
<div id="sidebarLeft">

        {{-- Komponen --}}
        <div class="sb-section">
            <div class="sb-section-head">
                <i class="bi bi-grid-1x2"></i> Komponen
            </div>
            <div class="sb-section-body">
                <button type="button" class="sb-btn primary"
                    data-bs-toggle="modal" data-bs-target="#modalKop">
                    <i class="bi bi-bank"></i> Kop Surat Sekolah
                </button>
                <button type="button" class="sb-btn outline-primary"
                    data-bs-toggle="modal" data-bs-target="#modalVariable">
                    <i class="bi bi-braces"></i> Tambah Variabel
                </button>
                <button type="button" class="sb-btn" onclick="addText()">
                    <i class="bi bi-fonts"></i> Tambah Teks
                </button>
                <button type="button" class="sb-btn outline-success"
                    data-bs-toggle="modal" data-bs-target="#modalTable">
                    <i class="bi bi-table"></i> Sisipkan Tabel
                </button>
                <button type="button" class="sb-btn outline-warning" onclick="triggerImageUpload()">
                    <i class="bi bi-image"></i> Upload Gambar
                </button>
                <input type="file" id="imageUpload" accept="image/*" style="display:none" onchange="addImage(event)">
                <button type="button" class="sb-btn outline-warning" onclick="triggerLogoUpload()">
                    <i class="bi bi-building"></i> Logo Sekolah
                </button>
                <input type="file" id="logoUpload" accept="image/*" style="display:none" onchange="addLogoImage(event)">
                <button type="button" class="sb-btn outline-dark" onclick="addBarcode()">
                    <i class="bi bi-upc-scan"></i> Barcode Signature
                </button>
                <button type="button" class="sb-btn outline-info" onclick="addNewPage()">
                    <i class="bi bi-file-earmark-plus"></i> Tambah Halaman
                </button>
            </div>
        </div>

        {{-- Layer & Edit --}}
        <div class="sb-section">
            <div class="sb-section-head">
                <i class="bi bi-stack"></i> Layer &amp; Edit
            </div>
            <div class="sb-section-body">
                <div class="sb-row">
                    <button class="sb-btn" onclick="bringForward()"><i class="bi bi-layers-fill"></i> Depan</button>
                    <button class="sb-btn" onclick="sendBackward()"><i class="bi bi-layers"></i> Belakang</button>
                </div>
                <div class="sb-row">
                    <button class="sb-btn" onclick="copySelected()"><i class="bi bi-copy"></i> Salin</button>
                    <button class="sb-btn" onclick="pasteClipboard()"><i class="bi bi-clipboard"></i> Tempel</button>
                </div>
                <div class="sb-row">
                    <button class="sb-btn" onclick="toggleLock()">
                        <i class="bi bi-lock" id="lockIcon"></i> <span id="lockLabel">Kunci</span>
                    </button>
                    <button class="sb-btn outline-danger" onclick="removeSelected()">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>
                <button class="sb-btn" id="btnDeselect">
                    <i class="bi bi-cursor"></i> Deselect
                </button>
                <div class="sb-row">
                    <button class="sb-btn" onclick="undo()"><i class="bi bi-arrow-counterclockwise"></i> Undo</button>
                    <button class="sb-btn" onclick="redo()"><i class="bi bi-arrow-clockwise"></i> Redo</button>
                </div>
            </div>
        </div>


        {{-- Perataan --}}
        <div class="sb-section">
            <div class="sb-section-head"><i class="bi bi-align-center"></i> Perataan</div>
            <div class="sb-section-body">
                <div class="sb-row" style="flex-wrap:wrap;gap:3px">
                    <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('left')"    title="Kiri"><i class="bi bi-align-start"></i></button>
                    <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('hcenter')" title="Tengah H"><i class="bi bi-align-center"></i></button>
                    <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('right')"   title="Kanan"><i class="bi bi-align-end"></i></button>
                    <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('top')"     title="Atas"><i class="bi bi-align-top"></i></button>
                    <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('vcenter')" title="Tengah V"><i class="bi bi-align-middle"></i></button>
                    <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('bottom')"  title="Bawah"><i class="bi bi-align-bottom"></i></button>
                </div>
                <div class="sb-row">
                    <button class="sb-btn" onclick="distributeObjects('h')"><i class="bi bi-distribute-horizontal"></i> H</button>
                    <button class="sb-btn" onclick="distributeObjects('v')"><i class="bi bi-distribute-vertical"></i> V</button>
                </div>
            </div>
        </div>

         {{-- Format Objek --}}
        <div class="sb-section" id="formatToolbar">
            <div class="sb-section-head"><i class="bi bi-type"></i> Format Objek</div>
            <div class="sb-section-body">
                <div class="fmt-grid-2">
                    <div>
                        <span class="fmt-label">Font Size</span>
                        <input type="number" id="fontSize" class="fmt-input" value="16" min="6" max="200"
                            onchange="applyFormat('fontSize', parseInt(this.value))">
                    </div>
                    <div>
                        <span class="fmt-label">Font</span>
                        <select id="fontFamily" class="fmt-select" onchange="applyFormat('fontFamily', this.value)">
                            <option value="Arial">Arial</option>
                            <option value="Times New Roman">Times New Roman</option>
                            <option value="Courier New">Courier New</option>
                            <option value="Georgia">Georgia</option>
                            <option value="Verdana">Verdana</option>
                            <option value="Tahoma">Tahoma</option>
                        </select>
                    </div>
                </div>
                <div>
                    <span class="fmt-label">Warna Teks</span>
                    <input type="color" id="fontColor" class="fmt-color"
                        value="#000000" onchange="applyFormat('fill', this.value)">
                </div>
                <div style="display:flex;gap:3px;margin:2px 0">
                    <button class="sb-btn" style="flex:1;justify-content:center;padding:4px" onclick="applyFormat('textAlign','left')"><i class="bi bi-text-left"></i></button>
                    <button class="sb-btn" style="flex:1;justify-content:center;padding:4px" onclick="applyFormat('textAlign','center')"><i class="bi bi-text-center"></i></button>
                    <button class="sb-btn" style="flex:1;justify-content:center;padding:4px" onclick="applyFormat('textAlign','right')"><i class="bi bi-text-right"></i></button>
                    <button class="sb-btn" style="flex:1;justify-content:center;padding:4px" onclick="applyFormat('textAlign','justify')"><i class="bi bi-justify"></i></button>
                </div>
                <div style="display:flex;gap:3px;margin:2px 0">
                    <button class="sb-btn" style="flex:1;justify-content:center;padding:4px" onclick="toggleBold()"><strong>B</strong></button>
                    <button class="sb-btn" style="flex:1;justify-content:center;padding:4px" onclick="toggleItalic()"><em>I</em></button>
                    <button class="sb-btn" style="flex:1;justify-content:center;padding:4px" onclick="toggleUnderline()"><u>U</u></button>
                    <button class="sb-btn" style="flex:1;justify-content:center;padding:4px" onclick="toggleStrikethrough()"><s>S</s></button>
                </div>
                <div>
                    <div class="fmt-range-row">
                        <span class="fmt-label" style="flex:1;margin:0">Line Height</span>
                        <span class="fmt-range-val" id="lineHeightVal">1.4</span>
                    </div>
                    <input type="range" class="fmt-range" id="lineHeightSlider" min="10" max="30" value="14"
                        oninput="applyFormat('lineHeight',this.value/10); document.getElementById('lineHeightVal').textContent=(this.value/10).toFixed(1)">
                </div>
                <hr style="margin:4px 0;border-color:var(--border)">
                <div class="fmt-grid-2">
                    <div>
                        <span class="fmt-label">X (px)</span>
                        <input type="number" id="objX" class="fmt-input" value="0" onchange="applyPosition('x', parseInt(this.value))">
                    </div>
                    <div>
                        <span class="fmt-label">Y (px)</span>
                        <input type="number" id="objY" class="fmt-input" value="0" onchange="applyPosition('y', parseInt(this.value))">
                    </div>
                    <div>
                        <span class="fmt-label">W (px)</span>
                        <input type="number" id="objWidth" class="fmt-input" value="200" onchange="applyWidth(parseInt(this.value))">
                    </div>
                    <div>
                        <span class="fmt-label">H (px)</span>
                        <input type="number" id="objHeight" class="fmt-input" value="50" onchange="applyHeight(parseInt(this.value))">
                    </div>
                </div>
                <div>
                    <div class="fmt-range-row">
                        <span class="fmt-label" style="flex:1;margin:0">Opacity</span>
                        <span class="fmt-range-val" id="opacityVal">100</span><span style="font-size:.68rem;color:var(--text-muted)">%</span>
                    </div>
                    <input type="range" class="fmt-range" id="objOpacity" min="10" max="100" value="100"
                        oninput="document.getElementById('opacityVal').textContent=this.value; applyOpacity(parseInt(this.value))">
                </div>
                <div>
                    <div class="fmt-range-row">
                        <span class="fmt-label" style="flex:1;margin:0">Rotasi</span>
                        <span class="fmt-range-val" id="rotateVal">0</span><span style="font-size:.68rem;color:var(--text-muted)">°</span>
                    </div>
                    <input type="range" class="fmt-range" id="objRotate" min="-180" max="180" value="0"
                        oninput="document.getElementById('rotateVal').textContent=this.value; applyRotation(parseInt(this.value))">
                </div>
            </div>
        </div>

        {{-- Halaman --}}
        <div class="sb-section">
            <div class="sb-section-head" style="justify-content:space-between">
                <span><i class="bi bi-files"></i> Halaman</span>
                <div style="display:flex;gap:3px">
                    <button class="zoom-btn" onclick="addNewPage()"><i class="bi bi-plus"></i></button>
                    <button class="zoom-btn" onclick="removeCurrentPage()"><i class="bi bi-x"></i></button>
                </div>
            </div>
            <div class="sb-section-body" id="pageThumbnails"></div>
        </div>

    </div>{{-- /sidebarLeft --}}

{{-- ── EDITOR ROOT ──────────────────────────────────────────────────────── --}}
<div id="editorRoot">

    {{-- ── CANVAS AREA ── --}}
    <div id="canvasArea">

        {{-- Canvas Toolbar --}}
        <div class="canvas-toolbar">
            <span class="pill-a4">
                <i class="bi bi-file-earmark"></i>
                A4 · 210×297mm · 794×1123px
            </span>

            <div class="ct-divider"></div>

            <div class="zoom-row">
                <button class="zoom-btn" onclick="zoomOut()" title="Ctrl+−"><i class="bi bi-zoom-out"></i></button>
                <span id="zoomLabel">100%</span>
                <button class="zoom-btn" onclick="zoomIn()"  title="Ctrl++"><i class="bi bi-zoom-in"></i></button>
                <button class="zoom-btn" onclick="zoomReset()" title="Ctrl+0" style="font-size:.72rem;font-weight:700">1:1</button>
            </div>

            <div class="ct-divider"></div>

            <label class="ct-switch">
                <input type="checkbox" id="toggleGrid" onchange="toggleGrid(this.checked)">
                <i class="bi bi-grid-3x3"></i> Grid <small style="opacity:.6">(5/10mm)</small>
            </label>
            <label class="ct-switch">
                <input type="checkbox" id="toggleSnap" checked onchange="setSnapEnabled(this.checked)">
                <i class="bi bi-magnet"></i> Snap
            </label>
            <label class="ct-switch">
                <input type="checkbox" id="toggleMargin" checked onchange="toggleMarginGuides(this.checked)">
                <i class="bi bi-border-outer"></i> Margin
            </label>
            <label class="ct-switch">
                <input type="checkbox" id="toggleRuler" checked onchange="toggleRulerVis(this.checked)">
                <i class="bi bi-rulers"></i> Ruler
            </label>

            <div class="ct-divider"></div>

            <div class="coord-bar">
                <span class="coord-item">Hal <span id="pageIndicator" class="coord-val">1/1</span></span>
                <span class="coord-item">X <span id="coordX" class="coord-val">—</span></span>
                <span class="coord-item">Y <span id="coordY" class="coord-val">—</span></span>
                <span class="coord-item">W <span id="coordW" class="coord-val">—</span></span>
                <span class="coord-item">H <span id="coordH" class="coord-val">—</span></span>
            </div>
        </div>

        {{-- Variable Panel --}}
        <div id="variablePanel">
            <div class="var-panel-wrap">
                <div class="var-panel-head">
                    <i class="bi bi-braces text-primary"></i> Variabel Tersedia
                    <small style="font-weight:400;color:var(--text-muted);margin-left:4px">— klik untuk taruh ke canvas</small>
                </div>
                <div id="variableChips" style="display:flex;flex-wrap:wrap;gap:5px"></div>
            </div>
        </div>

        {{-- Canvas Ruler + Pages --}}
        <div id="editorContainer">
            <div id="rulerLayout">
                <div id="rulerCorner">mm</div>
                <div style="position:sticky;top:0;z-index:29;overflow:hidden;height:var(--ruler-sz);">
                    <canvas id="rulerH" height="22" style="display:block"></canvas>
                </div>
                <div style="position:sticky;left:0;z-index:28;overflow:hidden;width:var(--ruler-sz);">
                    <canvas id="rulerV" width="22" style="display:block"></canvas>
                </div>
                <div id="canvasPagesContainer"></div>
            </div>
        </div>

    </div>{{-- /canvasArea --}}

</div>{{-- /editorRoot --}}

{{-- ═══════════════════ MODAL KOP SURAT ═══════════════════ --}}
@include('dashboard.document.templates._modal_kop')

{{-- ═══════════════════ MODAL VARIABEL ════════════════════ --}}
@include('dashboard.document.templates._modal_variable', ['variableGroups' => $variableGroups])

{{-- ═══════════════════ MODAL TABEL ═══════════════════════ --}}
@include('dashboard.document.templates._modal_table', ['kelasList' => $kelasList])

@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<script src="{{ asset('asset_dashboard/js/document/constants.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/utils.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/ruler.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/page-manager.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/variable-registry.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/table-style-panel.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/table-handles.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/table-renderer.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/raport-api.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/canvas-events.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/elements.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/html-export.js') }}"></script>
<script>
(function () {
    var proto = CanvasRenderingContext2D.prototype;
    var d = Object.getOwnPropertyDescriptor(proto, 'textBaseline');
    if (d && d.set) {
        var orig = d.set;
        Object.defineProperty(proto, 'textBaseline', {
            get: d.get,
            set: function (v) { orig.call(this, v === 'alphabetical' ? 'alphabetic' : v); },
            configurable: true, enumerable: d.enumerable,
        });
    }
})();
</script>
<script src="{{ asset('asset_dashboard/js/document/init.js') }}"></script>
<script>
    window.EXISTING_CANVAS_JSON = null;
    window.EDITOR_KELAS_LIST    = @json($kelasList ?? []);
    window.EDITOR_MAPEL_LIST    = @json($mapelList ?? []);
</script>
{{-- <script src="{{ asset('asset_dashboard/js/document/template-editor.js') }}"></script> --}}
@endpush