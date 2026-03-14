@extends('layouts.dashboard')

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('asset_dashboard/js/document/style.css') }}">
{{-- SweetAlert2 --}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                <label>Kategori</label>
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
                <label>Nama Template</label>
                <input type="text" name="name"
                    placeholder="Contoh: Surat Keterangan Aktif" required>
            </div>
        </div>

        <div class="tb-spacer"></div>

        {{-- Undo/Redo di topbar --}}
        <button type="button" class="tb-btn" onclick="undo()" title="Undo (Ctrl+Z)">
            <i class="bi bi-arrow-counterclockwise"></i>
        </button>
        <button type="button" class="tb-btn" onclick="redo()" title="Redo (Ctrl+Y)">
            <i class="bi bi-arrow-clockwise"></i>
        </button>

        <div class="sep"></div>

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
        </div>
    </div>

    {{-- Shapes --}}
    <div class="sb-section">
        <div class="sb-section-head">
            <i class="bi bi-shapes"></i> Shapes
        </div>
        <div class="sb-section-body">
            <div class="sb-row">
                <button class="sb-btn" onclick="addShape('rect')"  title="Kotak"><i class="bi bi-square"></i> Kotak</button>
                <button class="sb-btn" onclick="addShape('circle')" title="Lingkaran"><i class="bi bi-circle"></i> Ellipse</button>
            </div>
            <div class="sb-row">
                <button class="sb-btn" onclick="addShape('triangle')" title="Segitiga"><i class="bi bi-triangle"></i> Segitiga</button>
                <button class="sb-btn" onclick="addShape('hline')" title="Garis"><i class="bi bi-dash-lg"></i> Garis</button>
            </div>
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
                <i class="bi bi-cursor"></i> Deselect <span class="ctx-shortcut" style="margin-left:auto">Esc</span>
            </button>
            <div class="sb-row">
                <button class="sb-btn" onclick="undo()"><i class="bi bi-arrow-counterclockwise"></i> Undo <span class="ctx-shortcut" style="margin-left:auto">Ctrl+Z</span></button>
                <button class="sb-btn" onclick="redo()"><i class="bi bi-arrow-clockwise"></i> Redo <span class="ctx-shortcut" style="margin-left:auto">Ctrl+Y</span></button>
            </div>
        </div>
    </div>

    {{-- Perataan --}}
    <div class="sb-section">
        <div class="sb-section-head"><i class="bi bi-align-center"></i> Perataan</div>
        <div class="sb-section-body">
            <div class="sb-row" style="flex-wrap:wrap;gap:3px">
                <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('left')"    title="Rata Kiri"><i class="bi bi-align-start"></i></button>
                <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('hcenter')" title="Tengah H"><i class="bi bi-align-center"></i></button>
                <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('right')"   title="Rata Kanan"><i class="bi bi-align-end"></i></button>
                <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('top')"     title="Rata Atas"><i class="bi bi-align-top"></i></button>
                <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('vcenter')" title="Tengah V"><i class="bi bi-align-middle"></i></button>
                <button class="sb-btn" style="flex:0 0 auto;padding:5px 8px" onclick="alignObj('bottom')"  title="Rata Bawah"><i class="bi bi-align-bottom"></i></button>
            </div>
            <div class="sb-row">
                <button class="sb-btn" onclick="distributeObjects('h')"><i class="bi bi-distribute-horizontal"></i> Dist. H</button>
                <button class="sb-btn" onclick="distributeObjects('v')"><i class="bi bi-distribute-vertical"></i> Dist. V</button>
            </div>
        </div>
    </div>

    {{-- Format Objek --}}
    <div class="sb-section" id="formatToolbar">
        <div class="sb-section-head"><i class="bi bi-sliders2"></i> Format Objek</div>
        <div class="sb-section-body">
            {{-- Font --}}
            <div class="fmt-grid-2">
                <div>
                    <span class="fmt-label">Font</span>
                    <select id="fontFamily" class="fmt-select" onchange="applyFormat('fontFamily', this.value)">
                        <option value="Arial">Arial</option>
                        <option value="Times New Roman">Times New Roman</option>
                        <option value="Courier New">Courier New</option>
                        <option value="Georgia">Georgia</option>
                        <option value="Verdana">Verdana</option>
                        <option value="Tahoma">Tahoma</option>
                        <option value="Trebuchet MS">Trebuchet MS</option>
                        <option value="Impact">Impact</option>
                        <option value="Comic Sans MS">Comic Sans MS</option>
                    </select>
                </div>
                <div>
                    <span class="fmt-label">Size</span>
                    <input type="number" id="fontSize" class="fmt-input" value="14" min="6" max="200"
                        onchange="applyFormat('fontSize', parseInt(this.value))">
                </div>
            </div>
            {{-- Warna Teks --}}
            <div>
                <span class="fmt-label">Warna Teks</span>
                <input type="color" id="fontColor" class="fmt-color"
                    value="#000000" onchange="applyFormat('fill', this.value)">
            </div>
            {{-- Style buttons --}}
            <div class="fmt-btn-row">
                <button id="btnBold"         class="fmt-btn" onclick="toggleBold()"          title="Bold (Ctrl+B)"><strong>B</strong></button>
                <button id="btnItalic"        class="fmt-btn" onclick="toggleItalic()"         title="Italic (Ctrl+I)"><em>I</em></button>
                <button id="btnUnderline"     class="fmt-btn" onclick="toggleUnderline()"      title="Underline (Ctrl+U)"><u>U</u></button>
                <button id="btnStrike"        class="fmt-btn" onclick="toggleStrikethrough()"  title="Strikethrough"><s>S</s></button>
            </div>
            {{-- Align buttons --}}
            <div class="fmt-btn-row">
                <button id="btnAlignLeft"    class="fmt-btn" onclick="applyFormat('textAlign','left')"    title="Rata Kiri"><i class="bi bi-text-left"></i></button>
                <button id="btnAlignCenter"  class="fmt-btn" onclick="applyFormat('textAlign','center')"  title="Tengah"><i class="bi bi-text-center"></i></button>
                <button id="btnAlignRight"   class="fmt-btn" onclick="applyFormat('textAlign','right')"   title="Rata Kanan"><i class="bi bi-text-right"></i></button>
                <button id="btnAlignJustify" class="fmt-btn" onclick="applyFormat('textAlign','justify')" title="Justify"><i class="bi bi-justify"></i></button>
            </div>
            {{-- Line Height --}}
            <div>
                <div class="fmt-range-row">
                    <span class="fmt-label" style="flex:1;margin:0">Line Height</span>
                    <span class="fmt-range-val" id="lineHeightVal">1.4</span>
                </div>
                <input type="range" class="fmt-range" id="lineHeightSlider" min="10" max="30" value="14"
                    oninput="applyFormat('lineHeight',this.value/10); document.getElementById('lineHeightVal').textContent=(this.value/10).toFixed(1)">
            </div>
            <div class="fmt-sep"></div>
            {{-- Posisi & Ukuran --}}
            <div class="fmt-grid-2">
                <div>
                    <span class="fmt-label">X (px)</span>
                    <input type="number" id="objX" class="fmt-input" value="0"
                        onchange="applyPosition('x', parseInt(this.value))">
                </div>
                <div>
                    <span class="fmt-label">Y (px)</span>
                    <input type="number" id="objY" class="fmt-input" value="0"
                        onchange="applyPosition('y', parseInt(this.value))">
                </div>
                <div>
                    <span class="fmt-label">Lebar (px)</span>
                    <input type="number" id="objWidth" class="fmt-input" value="200"
                        onchange="applyWidth(parseInt(this.value))">
                </div>
                <div>
                    <span class="fmt-label">Tinggi (px)</span>
                    <input type="number" id="objHeight" class="fmt-input" value="50"
                        onchange="applyHeight(parseInt(this.value))">
                </div>
            </div>
            {{-- Opacity --}}
            <div>
                <div class="fmt-range-row">
                    <span class="fmt-label" style="flex:1;margin:0">Opacity</span>
                    <span class="fmt-range-val" id="opacityVal">100</span><span style="font-size:.68rem;color:var(--text-muted)">%</span>
                </div>
                <input type="range" class="fmt-range" id="objOpacity" min="10" max="100" value="100"
                    oninput="document.getElementById('opacityVal').textContent=this.value; applyOpacity(parseInt(this.value))">
            </div>
            {{-- Rotasi --}}
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
                <button class="zoom-btn" onclick="addNewPage()" title="Tambah Halaman"><i class="bi bi-plus"></i></button>
                <button class="zoom-btn" onclick="removeCurrentPage()" title="Hapus Halaman"><i class="bi bi-x"></i></button>
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
                <button class="zoom-btn" onclick="zoomOut()" title="Zoom Out (Ctrl+-)"><i class="bi bi-zoom-out"></i></button>
                <span id="zoomLabel">100%</span>
                <button class="zoom-btn" onclick="zoomIn()"  title="Zoom In (Ctrl++)"><i class="bi bi-zoom-in"></i></button>
                <button class="zoom-btn" onclick="zoomReset()" title="Reset Zoom (Ctrl+0)" style="font-size:.7rem;font-weight:700;width:auto;padding:0 6px">1:1</button>
            </div>

            <div class="ct-divider"></div>

            <label class="ct-switch" title="Toggle Grid">
                <input type="checkbox" id="toggleGrid" onchange="toggleGrid(this.checked)">
                <i class="bi bi-grid-3x3"></i> Grid
            </label>
            <label class="ct-switch" title="Toggle Snap (seperti Figma)">
                <input type="checkbox" id="toggleSnap" checked onchange="setSnapEnabled(this.checked)">
                <i class="bi bi-magnet"></i> Snap
            </label>
            <label class="ct-switch" title="Toggle Margin Guides">
                <input type="checkbox" id="toggleMargin" onchange="toggleMarginGuides(this.checked)">
                <i class="bi bi-border-outer"></i> Margin
            </label>
            <label class="ct-switch" title="Toggle Ruler">
                <input type="checkbox" id="toggleRuler" checked onchange="toggleRulerVis(this.checked)">
                <i class="bi bi-rulers"></i> Ruler
            </label>

            <div class="ct-divider"></div>

            <div class="coord-bar">
                <span class="coord-item"><i class="bi bi-file-earmark" style="font-size:.65rem"></i> <span id="pageIndicator" class="coord-val">1/1</span></span>
                <span class="coord-item">X <span id="coordX" class="coord-val">—</span></span>
                <span class="coord-item">Y <span id="coordY" class="coord-val">—</span></span>
                <span class="coord-item">W <span id="coordW" class="coord-val">—</span></span>
                <span class="coord-item">H <span id="coordH" class="coord-val">—</span></span>
            </div>

            {{-- Shortcut hints --}}
            <div style="margin-left:auto;display:flex;gap:4px;align-items:center;opacity:0.6;font-size:0.68rem;color:var(--text-muted)">
                <span>Klik kanan untuk menu</span>
                <span class="shortcut-hint">Del</span>hapus
                <span class="shortcut-hint">Ctrl+D</span>duplikat
            </div>
        </div>

        {{-- Variable Panel --}}
        <div id="variablePanel">
            <div class="var-panel-wrap">
                <div class="var-panel-head">
                    <i class="bi bi-braces text-primary"></i> Variabel Tersedia
                    <small style="font-weight:400;color:var(--text-muted);margin-left:4px">— klik untuk taruh ke canvas</small>
                </div>
                <div id="variableChips"></div>
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

{{-- Context menu --}}
<div id="canvasContextMenu"></div>

{{-- ═══════════════════ MODAL KOP SURAT ═══════════════════ --}}
@include('dashboard.document.templates._modal_kop')

{{-- ═══════════════════ MODAL VARIABEL ════════════════════ --}}
@include('dashboard.document.templates._modal_variable', ['variableGroups' => $variableGroups])

{{-- ═══════════════════ MODAL TABEL ═══════════════════════ --}}
@include('dashboard.document.templates._modal_table', ['kelasList' => $kelasList])

@endsection

@push('js')
{{-- SweetAlert2 HARUS dimuat sebelum JS editor --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fabric@5.3.0/dist/fabric.min.js"></script>
<script src="{{ asset('asset_dashboard/js/document/constants.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/utils.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/ruler.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/variable-registry.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/table-style-panel.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/table-handles.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/table-renderer.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/raport-api.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/page-manager.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/canvas-events.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/elements.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/document/html-export.js') }}"></script>
<script>
/* Fix textBaseline 'alphabetical' → 'alphabetic' */
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
@endpush
