@extends('layouts.dashboard')

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="{{ asset('asset_dashboard/js/document/style.css') }}">
<style>
:root {
    --primary:       #1a5276;
    --primary-mid:   #2980b9;
    --primary-light: #eaf4fb;
    --accent:        #27ae60;
    --warn:          #e67e22;
    --danger:        #e74c3c;
    --text:          #1a252f;
    --text-muted:    #6c7a89;
    --border:        #d5dde4;
    --bg:            #f0f4f8;
    --card:          #ffffff;
    --radius:        10px;
    --shadow:        0 2px 12px rgba(26,82,118,.08);
}
* { box-sizing: border-box; }
body { font-family: 'Plus Jakarta Sans', sans-serif; }

/* ── TOPBAR ── */
.editor-topbar {
    position: sticky; top: 0; z-index: 100;
    background: #154360;  /* sedikit lebih gelap dari create — penanda mode edit */
    padding: 0 18px;
    display: flex; align-items: center;
    height: 52px; gap: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,.18);
}
.editor-topbar .brand {
    font-size: .92rem; font-weight: 700; color: #fff;
    display: flex; align-items: center; gap: 7px;
    white-space: nowrap;
}
.editor-topbar .brand .edit-badge {
    background: var(--warn); color: #fff;
    border-radius: 4px; padding: 1px 7px;
    font-size: .68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .04em;
}
.editor-topbar .sep { width: 1px; height: 24px; background: rgba(255,255,255,.25); }
.tb-field {
    display: flex; flex-direction: column; gap: 1px;
}
.tb-field label { font-size: .67rem; color: rgba(255,255,255,.65); font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.tb-field select,
.tb-field input[type="text"] {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.22);
    border-radius: 6px;
    color: #fff; font-size: .83rem;
    font-family: inherit; padding: 3px 8px;
    outline: none; min-width: 0;
    transition: background .15s;
}
.tb-field select { min-width: 130px; }
.tb-field input[type="text"] { min-width: 220px; }
.tb-field select:focus,
.tb-field input[type="text"]:focus {
    background: rgba(255,255,255,.22);
    border-color: rgba(255,255,255,.5);
}
.tb-field select option { background: #154360; color: #fff; }
.tb-spacer { flex: 1; }
.tb-btn {
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.25);
    color: #fff; border-radius: 7px;
    padding: 6px 14px; font-size: .82rem;
    font-weight: 600; font-family: inherit;
    cursor: pointer; display: flex; align-items: center; gap: 5px;
    transition: background .15s;
    white-space: nowrap;
    text-decoration: none;
}
.tb-btn:hover { background: rgba(255,255,255,.26); color: #fff; }
.tb-btn.save {
    background: var(--accent);
    border-color: #1e8449;
    box-shadow: 0 2px 8px rgba(39,174,96,.3);
}
.tb-btn.save:hover { background: #1e8449; }
.tb-btn.generate {
    background: var(--warn);
    border-color: #c9720c;
}
.tb-btn.generate:hover { background: #c9720c; }

/* ── EDITOR ROOT ── */
#editorRoot {
    display: flex;
    height: calc(100vh - 52px);
    overflow: hidden;
    background: var(--bg);
}

/* ── SIDEBAR ── */
#sidebarLeft {
    width: 248px; min-width: 248px; max-width: 248px;
    height: 100%; overflow-y: auto;
    background: var(--card); border-right: 1px solid var(--border);
    padding: 10px 8px; display: flex; flex-direction: column; gap: 6px;
}
#sidebarLeft::-webkit-scrollbar { width: 4px; }
#sidebarLeft::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

.sb-section { border: 1px solid var(--border); border-radius: var(--radius); overflow: hidden; }
.sb-section-head {
    background: var(--primary-light); padding: 7px 10px;
    font-size: .76rem; font-weight: 700; color: var(--primary);
    display: flex; align-items: center; gap: 6px;
    border-bottom: 1px solid var(--border);
}
.sb-section-body { padding: 8px; display: flex; flex-direction: column; gap: 4px; }

.sb-btn {
    border: 1.5px solid var(--border); background: #fff; border-radius: 7px;
    padding: 6px 10px; font-size: .79rem; font-weight: 600; color: var(--text);
    font-family: inherit; cursor: pointer;
    display: flex; align-items: center; gap: 6px;
    transition: border-color .15s, background .15s, color .15s; text-align: left;
}
.sb-btn:hover { border-color: var(--primary-mid); background: var(--primary-light); color: var(--primary); }
.sb-btn.primary { background: var(--primary); border-color: var(--primary); color: #fff; }
.sb-btn.primary:hover { background: #154360; }
.sb-btn.outline-primary { border-color: var(--primary-mid); color: var(--primary-mid); }
.sb-btn.outline-primary:hover { background: var(--primary-light); }
.sb-btn.outline-success { border-color: var(--accent); color: var(--accent); }
.sb-btn.outline-success:hover { background: #eafaf1; }
.sb-btn.outline-warning { border-color: var(--warn); color: var(--warn); }
.sb-btn.outline-warning:hover { background: #fef6ec; }
.sb-btn.outline-danger  { border-color: var(--danger); color: var(--danger); }
.sb-btn.outline-danger:hover  { background: #fdf2f2; }
.sb-btn.outline-dark  { border-color: #555; color: #333; }
.sb-btn.outline-dark:hover  { background: #f5f5f5; }
.sb-btn.outline-info  { border-color: var(--primary-mid); color: var(--primary-mid); }
.sb-row { display: flex; gap: 4px; }
.sb-row .sb-btn { flex: 1; justify-content: center; }

/* ── FORMAT TOOLBAR ── */
#formatToolbar { display: none; }
.fmt-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; }
.fmt-label { font-size: .7rem; font-weight: 600; color: var(--text-muted); margin-bottom: 2px; display: block; }
.fmt-input {
    width: 100%; border: 1.5px solid var(--border); border-radius: 6px;
    padding: 5px 7px; font-size: .8rem; font-family: inherit;
    outline: none; transition: border-color .15s;
}
.fmt-input:focus { border-color: var(--primary-mid); }
.fmt-select {
    width: 100%; border: 1.5px solid var(--border); border-radius: 6px;
    padding: 5px 7px; font-size: .8rem; font-family: inherit;
    outline: none; background: #fff; transition: border-color .15s;
}
.fmt-select:focus { border-color: var(--primary-mid); }
.fmt-color { width: 100%; height: 32px; border: 1.5px solid var(--border); border-radius: 6px; padding: 2px; cursor: pointer; }
.fmt-range { width: 100%; accent-color: var(--primary-mid); }
.fmt-range-row { display: flex; align-items: center; gap: 6px; font-size: .75rem; }
.fmt-range-val { min-width: 28px; text-align: right; font-weight: 600; color: var(--primary); }

/* ── COORD BAR ── */
.coord-bar { display: flex; gap: 6px; flex-wrap: wrap; padding: 4px 0; }
.coord-item { font-size: .71rem; color: var(--text-muted); display: flex; align-items: center; gap: 3px; }
.coord-val { font-weight: 700; color: var(--primary); }

/* ── CANVAS AREA ── */
#canvasArea { flex: 1; overflow: auto; background: var(--bg); display: flex; flex-direction: column; }

.canvas-toolbar {
    position: sticky; top: 0; z-index: 50;
    background: #fff; border-bottom: 1px solid var(--border);
    padding: 6px 14px;
    display: flex; align-items: center; flex-wrap: wrap; gap: 8px;
}
.pill-a4 {
    background: var(--primary-light); border: 1px solid #c0d8ee; border-radius: 20px;
    padding: 3px 10px; font-size: .74rem; font-weight: 700; color: var(--primary);
    display: flex; align-items: center; gap: 4px;
}
.ct-divider { width: 1px; height: 20px; background: var(--border); }
.ct-switch { display: flex; align-items: center; gap: 5px; font-size: .77rem; color: var(--text-muted); }
.ct-switch input[type="checkbox"] { accent-color: var(--primary-mid); }
.zoom-row { display: flex; align-items: center; gap: 4px; }
.zoom-btn {
    width: 26px; height: 26px; border: 1.5px solid var(--border);
    background: #fff; border-radius: 6px; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; color: var(--text-muted); transition: border-color .15s, color .15s;
}
.zoom-btn:hover { border-color: var(--primary-mid); color: var(--primary); }
#zoomLabel { font-size: .8rem; font-weight: 700; min-width: 38px; text-align: center; color: var(--text); }

#variablePanel { display: none; }
.var-panel-wrap {
    background: #fff; border-bottom: 1px solid var(--border); padding: 8px 14px;
}
.var-panel-head { font-size: .76rem; font-weight: 700; color: var(--primary); margin-bottom: 6px; display: flex; align-items: center; gap: 5px; }

:root { --ruler-sz: 22px; }
#rulerLayout {
    display: grid;
    grid-template-columns: var(--ruler-sz) 1fr;
    grid-template-rows: var(--ruler-sz) 1fr;
}
#rulerCorner {
    grid-column: 1; grid-row: 1;
    background: #f1f3f5; border-right: 1px solid #ced4da; border-bottom: 1px solid #ced4da;
    display: flex; align-items: center; justify-content: center;
    font-size: .58rem; color: #868e96; font-weight: 700;
    position: sticky; top: 0; left: 0; z-index: 30;
}
#rulerH { grid-column: 2; grid-row: 1; }
#rulerV { grid-column: 1; grid-row: 2; }
#canvasPagesContainer {
    grid-column: 2; grid-row: 2;
    display: flex; flex-direction: column; align-items: center;
    gap: 24px; padding: 24px; min-height: 100%;
}
.page-block { box-shadow: 0 4px 24px rgba(0,0,0,.13); border-radius: 3px; position: relative; background: #fff; }
.page-block.active-page { outline: 3px solid var(--primary-mid); }
.page-label { position: absolute; top: -22px; left: 0; font-size: .72rem; font-weight: 700; color: var(--text-muted); }
.thumb-item { border: 2px solid var(--border); border-radius: 6px; cursor: pointer; overflow: hidden; transition: border-color .15s; }
.thumb-item.active { border-color: var(--primary-mid); }
.thumb-item:hover  { border-color: var(--primary-mid); }
.thumb-label { font-size: .68rem; text-align: center; color: var(--text-muted); padding: 3px 0; }
</style>
@endpush

@section('title', 'Edit Template — ' . $template->name)

@section('content')

{{-- ── TOP BAR ──────────────────────────────────────────────────────────── --}}
<div class="editor-topbar">
    <div class="brand">
        <i class="bi bi-file-earmark-richtext"></i>
        {{ Str::limit($template->name, 28) }}
        <span class="edit-badge">Edit</span>
    </div>
    <div class="sep"></div>

    <form action="{{ route('dashboard.documents.templates.update', $template) }}"
          method="POST" id="templateForm"
          style="display:contents">
        @csrf
        @method('PUT')
        <input type="hidden" name="kelas_id" id="kelas_id" value="{{ $template->kelas_id ?? '' }}">

        <div class="tb-field">
            <label>Kategori</label>
            <select name="category_id" required>
                <option value="">Pilih…</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ $template->category_id == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="tb-field">
            <label>Nama Template</label>
            <input type="text" name="name"
                value="{{ $template->name }}"
                placeholder="Nama template" required>
        </div>

        <div class="tb-spacer"></div>

        {{-- Shortcut ke halaman generate --}}
        <a href="{{ route('dashboard.documents.create', $template) }}"
           class="tb-btn generate" target="_blank">
            <i class="bi bi-file-earmark-arrow-down"></i> Generate
        </a>

        <a href="{{ route('dashboard.documents.templates.index') }}"
           class="tb-btn">
            <i class="bi bi-x-lg"></i> Batal
        </a>

        <button type="submit" class="tb-btn save">
            <i class="bi bi-save"></i> Update Template
        </button>

        <input type="hidden" name="html_template" id="html_template">
        <input type="hidden" name="canvas_json"   id="canvas_json">
    </form>
</div>

{{-- ── EDITOR ROOT ──────────────────────────────────────────────────────── --}}
<div id="editorRoot">

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
            <div class="sb-section-head"><i class="bi bi-stack"></i> Layer &amp; Edit</div>
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

    </div>{{-- /sidebarLeft --}}

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
                <i class="bi bi-grid-3x3"></i> Grid
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
                    <i class="bi bi-braces"></i> Variabel Tersedia
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

{{-- ═══════════════════ MODALS ═══════════════════ --}}
@include('dashboard.document.templates._modal_kop')
@include('dashboard.document.templates._modal_variable', ['variableGroups' => $variableGroups])
@include('dashboard.document.templates._modal_table', ['kelasList' => $kelasList])

@endsection

@push('js')

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
    window.EXISTING_CANVAS_JSON = @json($template->canvas_json ?? null);
    window.EDITOR_KELAS_LIST    = @json($kelasList ?? []);
    window.EDITOR_MAPEL_LIST    = @json($mapelList ?? []);
</script>
<script src="{{ asset('asset_dashboard/js/document/template-editor.js') }}"></script>
@endpush