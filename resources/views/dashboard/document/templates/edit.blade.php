@extends('layouts.dashboard')

@push('css')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --premium-blue: #0d6efd;
        --sidebar-bg: rgba(255, 255, 255, 0.8);
        --glass-border: rgba(255, 255, 255, 0.3);
        --glass-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }
    #editorRoot {
        font-family: 'Inter', sans-serif;
        background: #f8f9fa;
    }
    .card.shadow-sm {
        border: 1px solid var(--glass-border);
        background: var(--sidebar-bg);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        box-shadow: var(--glass-shadow) !important;
        border-radius: 12px;
    }
    .card-header {
        background: transparent !important;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        font-family: 'Outfit', sans-serif;
    }
    .btn-sm {
        border-radius: 8px;
        padding: 0.4rem 0.8rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .btn-sm:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    #sidebarLeft, #sidebarRight {
        height: max-content !important;
        overflow-y: auto;
        scrollbar-width: thin;
    }
    /* Modern Scrollbar */
    #sidebarLeft::-webkit-scrollbar, #sidebarRight::-webkit-scrollbar {
        width: 4px;
    }
    #sidebarLeft::-webkit-scrollbar-thumb, #sidebarRight::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 10px;
    }
</style>
@endpush

@section('title','Edit Template Surat')

@section('content')

<div class="row g-0" id="editorRoot">

    {{-- ====================================================== --}}
    {{-- SIDEBAR KIRI                                           --}}
    {{-- ====================================================== --}}
    <div class="col-lg-3 pe-2" id="sidebarLeft" style="min-width:240px;max-width:260px;">

        {{-- KOMPONEN --}}
        <div class="card mb-2 shadow-sm">
            <div class="card-header fw-bold py-2 d-flex align-items-center gap-2">
                <i class="bi bi-grid-1x2 text-primary"></i> Komponen
            </div>
            <div class="card-body d-grid gap-1 py-2 px-2">

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
                    <i class="bi bi-fonts"></i> Tambah Teks
                </button>

                <button type="button" class="btn btn-outline-success btn-sm"
                    data-bs-toggle="modal" data-bs-target="#modalTable">
                    <i class="bi bi-table"></i> Sisipkan Tabel
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

                <button type="button" class="btn btn-outline-info btn-sm" onclick="addNewPage()">
                    <i class="bi bi-file-earmark-plus"></i> Tambah Halaman
                </button>

                <hr class="my-1">

                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="bringForward()" title="Pindah ke depan">
                        <i class="bi bi-layers-fill"></i> Depan
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="sendBackward()" title="Pindah ke belakang">
                        <i class="bi bi-layers"></i> Belakang
                    </button>
                </div>

                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-info btn-sm flex-fill" onclick="copySelected()" title="Salin (Ctrl+C)">
                        <i class="bi bi-copy"></i> Salin
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm flex-fill" onclick="pasteClipboard()" title="Tempel (Ctrl+V)">
                        <i class="bi bi-clipboard"></i> Tempel
                    </button>
                </div>

                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleLock()">
                        <i class="bi bi-lock" id="lockIcon"></i> <span id="lockLabel">Kunci</span>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm flex-fill" onclick="removeSelected()">
                        <i class="bi bi-trash"></i> Hapus
                    </button>
                </div>

                <button type="button" class="btn btn-outline-info btn-sm" id="btnDeselect">
                    <i class="bi bi-cursor"></i> Deselect
                </button>

            </div>
        </div>

        {{-- PAGE NAVIGATOR --}}
        <div class="card mb-2 shadow-sm">
            <div class="card-header fw-bold py-2 d-flex align-items-center justify-content-between">
                <span><i class="bi bi-files text-warning"></i> Halaman</span>
                <div class="d-flex gap-1">
                    <button class="btn btn-outline-secondary btn-sm px-1 py-0" onclick="addNewPage()" title="Tambah halaman"><i class="bi bi-plus"></i></button>
                    <button class="btn btn-outline-danger btn-sm px-1 py-0" onclick="removeCurrentPage()" title="Hapus halaman ini"><i class="bi bi-x"></i></button>
                </div>
            </div>
            <div class="card-body py-2 px-2" id="pageThumbnails" style="max-height:220px;overflow-y:auto;">
                {{-- filled by JS --}}
            </div>
        </div>

        {{-- ALIGNMENT --}}
        <div class="card mb-2 shadow-sm">
            <div class="card-header fw-bold py-2 d-flex align-items-center gap-2">
                <i class="bi bi-align-center text-success"></i> Perataan & Distribusi
            </div>
            <div class="card-body py-2 px-2">
                <div class="mb-1 small text-muted">Sejajarkan ke Halaman</div>
                <div class="d-flex gap-1 mb-2 flex-wrap">
                    <button class="btn btn-outline-secondary btn-sm" onclick="alignObj('left')"    title="Kiri"><i class="bi bi-align-start"></i></button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="alignObj('hcenter')" title="Tengah H"><i class="bi bi-align-center"></i></button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="alignObj('right')"   title="Kanan"><i class="bi bi-align-end"></i></button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="alignObj('top')"     title="Atas"><i class="bi bi-align-top"></i></button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="alignObj('vcenter')" title="Tengah V"><i class="bi bi-align-middle"></i></button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="alignObj('bottom')"  title="Bawah"><i class="bi bi-align-bottom"></i></button>
                </div>
                <div class="mb-1 small text-muted">Distribusi (multi-select)</div>
                <div class="d-flex gap-1">
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="distributeObjects('h')">
                        <i class="bi bi-distribute-horizontal"></i> H
                    </button>
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="distributeObjects('v')">
                        <i class="bi bi-distribute-vertical"></i> V
                    </button>
                </div>
            </div>
        </div>

        {{-- FORMAT TEKS --}}
        <div class="card mb-2 shadow-sm" id="formatToolbar" style="display:none">
            <div class="card-header fw-bold py-2 d-flex align-items-center gap-2">
                <i class="bi bi-type text-primary"></i> Format Teks
            </div>
            <div class="card-body py-2 px-2">

                <div class="row g-1 mb-2">
                    <div class="col-5">
                        <label class="form-label small mb-0">Ukuran</label>
                        <input type="number" id="fontSize" class="form-control form-control-sm"
                            value="16" min="6" max="200"
                            onchange="applyFormat('fontSize', parseInt(this.value))">
                    </div>
                    <div class="col-7">
                        <label class="form-label small mb-0">Font</label>
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
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-0">Warna Teks</label>
                    <input type="color" id="fontColor" class="form-control form-control-color form-control-sm w-100"
                        value="#000000" onchange="applyFormat('fill', this.value)">
                </div>

                <div class="mb-2">
                    <div class="btn-group w-100" role="group">
                        <button class="btn btn-outline-secondary btn-sm" onclick="applyFormat('textAlign','left')"><i class="bi bi-text-left"></i></button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="applyFormat('textAlign','center')"><i class="bi bi-text-center"></i></button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="applyFormat('textAlign','right')"><i class="bi bi-text-right"></i></button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="applyFormat('textAlign','justify')"><i class="bi bi-justify"></i></button>
                    </div>
                </div>

                <div class="d-flex gap-1 mb-2">
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleBold()"><strong>B</strong></button>
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleItalic()"><em>I</em></button>
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleUnderline()"><u>U</u></button>
                    <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="toggleStrikethrough()"><s>S</s></button>
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-0">Line Height: <span id="lineHeightVal">1.4</span></label>
                    <input type="range" id="lineHeightSlider" class="form-range" min="10" max="30" value="14"
                        oninput="applyFormat('lineHeight', this.value/10); document.getElementById('lineHeightVal').textContent=(this.value/10).toFixed(1)">
                </div>

                <hr class="my-1">

                <div class="row g-1 mb-2">
                    <div class="col-6">
                        <label class="form-label small mb-0">X (px)</label>
                        <input type="number" id="objX" class="form-control form-control-sm" value="0"
                            onchange="applyPosition('x', parseInt(this.value))">
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">Y (px)</label>
                        <input type="number" id="objY" class="form-control form-control-sm" value="0"
                            onchange="applyPosition('y', parseInt(this.value))">
                    </div>
                </div>

                <div class="row g-1 mb-2">
                    <div class="col-6">
                        <label class="form-label small mb-0">Lebar (px)</label>
                        <input type="number" id="objWidth" class="form-control form-control-sm" value="200"
                            onchange="applyWidth(parseInt(this.value))">
                    </div>
                    <div class="col-6">
                        <label class="form-label small mb-0">Tinggi (px)</label>
                        <input type="number" id="objHeight" class="form-control form-control-sm" value="50"
                            onchange="applyHeight(parseInt(this.value))">
                    </div>
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-0">Opacity: <span id="opacityVal">100</span>%</label>
                    <input type="range" id="objOpacity" class="form-range" min="10" max="100" value="100"
                        oninput="document.getElementById('opacityVal').textContent=this.value; applyOpacity(parseInt(this.value))">
                </div>

                <div class="mb-2">
                    <label class="form-label small mb-0">Rotasi: <span id="rotateVal">0</span>&deg;</label>
                    <input type="range" id="objRotate" class="form-range" min="-180" max="180" value="0"
                        oninput="document.getElementById('rotateVal').textContent=this.value; applyRotation(parseInt(this.value))">
                </div>

            </div>
        </div>

        {{-- UNDO / REDO --}}
        <div class="card mb-2 shadow-sm">
            <div class="card-body d-flex gap-2 py-2">
                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="undo()">
                    <i class="bi bi-arrow-counterclockwise"></i> Undo
                </button>
                <button class="btn btn-outline-secondary btn-sm flex-fill" onclick="redo()">
                    <i class="bi bi-arrow-clockwise"></i> Redo
                </button>
            </div>
        </div>

    </div>

    {{-- ====================================================== --}}
    {{-- CANVAS AREA                                            --}}
    {{-- ====================================================== --}}
    <div class="col" style="min-width:0;">

        <form action="{{ route('dashboard.documents.templates.update', $template) }}" method="POST" id="templateForm">
            @csrf
            @method('PUT')

            {{-- Info Template --}}
            <div class="card mb-2 shadow-sm">
                <div class="card-body py-2">
                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold small mb-1">Kategori</label>
                            <select name="category_id" class="form-select form-select-sm" required>
                                <option value="">Pilih Kategori</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $template->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small mb-1">Nama Template</label>
                            <input type="text" name="name" class="form-control form-control-sm"
                                value="{{ $template->name }}" placeholder="Contoh: Surat Keterangan Aktif" required>
                        </div>
                        <div class="col-md-3 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('dashboard.documents.templates.index') }}" class="btn btn-outline-secondary btn-sm">Batal</a>
                        </div>
                    </div>
                    <div class="row g-2 mt-1">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small mb-1">Kelas <span class="text-muted fw-normal">(opsional, multi-select)</span></label>
                            <select name="kelas_ids[]" class="form-select form-select-sm" multiple style="min-height:60px">
                                @foreach($kelasList ?? [] as $kls)
                                <option value="{{ $kls->id }}" {{ $template->kelasList->contains($kls->id) ? 'selected' : '' }}>{{ $kls->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small mb-1">Mata Pelajaran <span class="text-muted fw-normal">(opsional, multi-select)</span></label>
                            <select name="mapel_ids[]" class="form-select form-select-sm" multiple style="min-height:60px">
                                @foreach($mapelList ?? [] as $mp)
                                <option value="{{ $mp->id }}" {{ $template->pelajarans->contains($mp->id) ? 'selected' : '' }}>{{ $mp->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CANVAS TOOLBAR --}}
            <div class="card mb-2 shadow-sm">
                <div class="card-body py-1 px-2 d-flex align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomOut()" title="Zoom Out"><i class="bi bi-zoom-out"></i></button>
                        <span id="zoomLabel" class="small fw-semibold" style="min-width:38px;text-align:center">100%</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomIn()" title="Zoom In"><i class="bi bi-zoom-in"></i></button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="zoomReset()" title="Reset Zoom">1:1</button>
                    </div>
                    <div class="vr"></div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="toggleGrid" onchange="toggleGrid(this.checked)">
                        <label class="form-check-label small" for="toggleGrid"><i class="bi bi-grid"></i> Grid</label>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="toggleSnap" checked onchange="snapEnabled=this.checked">
                        <label class="form-check-label small" for="toggleSnap"><i class="bi bi-magnet"></i> Snap</label>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="toggleMargin" checked onchange="toggleMarginGuides(this.checked)">
                        <label class="form-check-label small" for="toggleMargin"><i class="bi bi-border-outer"></i> Margin</label>
                    </div>
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="toggleRuler" checked onchange="toggleRulerVis(this.checked)">
                        <label class="form-check-label small" for="toggleRuler"><i class="bi bi-rulers"></i> Penggaris</label>
                    </div>
                    <div class="vr"></div>
                    <small class="text-muted">
                        Hal: <span id="pageIndicator" class="fw-semibold text-dark">1/1</span>
                        &nbsp;|&nbsp;
                        X:<span id="coordX" class="fw-semibold text-dark">-</span>
                        Y:<span id="coordY" class="fw-semibold text-dark">-</span>
                        &nbsp;W:<span id="coordW" class="fw-semibold text-dark">-</span>
                        H:<span id="coordH" class="fw-semibold text-dark">-</span>
                    </small>
                </div>
            </div>

            {{-- VARIABEL PANEL --}}
            <div class="card mb-2 shadow-sm" id="variablePanel" style="display:none">
                <div class="card-header d-flex justify-content-between align-items-center py-1">
                    <span class="fw-semibold small"><i class="bi bi-braces text-primary me-1"></i>Variabel Tersedia</span>
                    <small class="text-muted">Klik chip untuk menaruh ke canvas</small>
                </div>
                <div class="card-body py-2 px-3">
                    <div id="variableChips" class="d-flex flex-wrap gap-2"></div>
                </div>
            </div>

            {{-- CANVAS WRAPPER --}}
            <div id="editorContainer" style="overflow:auto; background:#e9ecef; border-radius:4px; position:relative; height:calc(100vh - 250px); display:flex; flex-direction:column; align-items:center;">
                <div id="rulerLayout" style="display:grid; grid-template-columns:20px minmax(794px, 1fr); grid-template-rows:20px 1fr; width:fit-content; margin:0 auto;">
                    {{-- corner --}}
                    <div id="rulerCorner" style="background:#dee2e6;border-right:1px solid #adb5bd;border-bottom:1px solid #adb5bd;z-index:30;position:sticky;top:0;left:0;"></div>
                    {{-- horizontal ruler --}}
                    <div style="position:sticky;top:0;z-index:29;overflow:hidden;height:20px;">
                        <canvas id="rulerH" height="20" style="display:block;background:#dee2e6;border-bottom:1px solid #adb5bd;"></canvas>
                    </div>
                    {{-- vertical ruler --}}
                    <div style="position:sticky;left:0;z-index:28;overflow:hidden;width:20px;">
                        <canvas id="rulerV" width="20" style="display:block;background:#dee2e6;border-right:1px solid #adb5bd;"></canvas>
                    </div>
                    {{-- canvas pages --}}
                    <div style="padding:40px; display:flex; flex-direction:column; align-items:center; gap:30px;" id="canvasPagesContainer">
                        {{-- Pages added dynamically by JS --}}
                    </div>
                </div>
            </div>

            <input type="hidden" name="html_template" id="html_template">
            <input type="hidden" name="canvas_json" id="canvas_json">

        </form>

    </div>

</div>

{{-- ========================= --}}
{{--   MODAL KOP SURAT         --}}
{{-- ========================= --}}
<div class="modal fade" id="modalKop" tabindex="-1">
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
                        <div class="form-text">Kosongkan &rarr; pakai placeholder <code>@{{logo}}</code></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ukuran Logo (px)</label>
                        <input type="number" id="kopLogoSize" class="form-control form-control-sm" value="90" min="40" max="200">
                    </div>
                    <div class="col-12"><label class="form-label fw-semibold">Nama Yayasan / Majelis</label>
                        <input type="text" id="kopLine1" class="form-control form-control-sm" value="MAJELIS DIKDASMEN MUHAMMADIYAH SAMARINDA"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Nama Sekolah (Besar)</label>
                        <input type="text" id="kopLine2" class="form-control form-control-sm" value="Sekolah Kreatif"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Sub-nama / Jenjang</label>
                        <input type="text" id="kopLine3" class="form-control form-control-sm" value="SD MUHAMMADIYAH 3 SAMARINDA SEBERANG"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Alamat</label>
                        <input type="text" id="kopLine4" class="form-control form-control-sm" value="Jalan Dato Iba Telp. (0541) 260066 Kel. Sungai Keledang - Samarinda Seberang 75131"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">Email / Website</label>
                        <input type="text" id="kopLine5" class="form-control form-control-sm" value="E-mail : sdmuhammadiyahtiga@ymail.com"></div>
                    <div class="col-md-6"><label class="form-label fw-semibold">NPSN / Akreditasi</label>
                        <input type="text" id="kopLine6" class="form-control form-control-sm" value="NPSN : 30404112"></div>
                    <div class="col-12"><label class="form-label fw-semibold">Garis Bawah Kop</label>
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
                <button type="button" class="btn btn-primary" id="btnAddKop"><i class="bi bi-plus-circle me-1"></i>Tambahkan ke Canvas</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================= --}}
{{--   MODAL TAMBAH VARIABEL   --}}
{{-- ========================= --}}
<div class="modal fade" id="modalVariable" tabindex="-1">
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
                    <label class="form-label fw-semibold small d-block mb-2">Variabel Umum (klik untuk pilih)</label>
                    <div class="d-flex flex-wrap gap-2" id="presetButtons">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nama_siswa" data-label="Nama Siswa">nama_siswa</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nisn" data-label="NISN">nisn</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nis" data-label="NIS">nis</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="kelas" data-label="Kelas">kelas</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="fase" data-label="Fase">fase</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="semester" data-label="Semester">semester</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="tahun_ajaran" data-label="Tahun Pelajaran">tahun_ajaran</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="tanggal" data-label="Tanggal">tanggal</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nomor_surat" data-label="Nomor Surat">nomor_surat</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nama_sekolah" data-label="Nama Sekolah">nama_sekolah</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="alamat_siswa" data-label="Alamat Siswa">alamat_siswa</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="kepala_sekolah" data-label="Kepala Sekolah">kepala_sekolah</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nip" data-label="NIP/NBM">nip</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="wali_kelas" data-label="Wali Kelas">wali_kelas</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nbm_wali" data-label="NBM Wali Kelas">nbm_wali</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nilai_rata" data-label="Nilai Rata-rata">nilai_rata</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="peringkat" data-label="Peringkat">peringkat</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="naik_kelas" data-label="Naik Kelas">naik_kelas</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="nama_kelas" data-label="Nama Kelas">nama_kelas</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-name="mata_pelajaran" data-label="Mata Pelajaran">mata_pelajaran</button>
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

{{-- ========================= --}}
{{--   MODAL SISIPKAN TABEL    --}}
{{-- ========================= --}}
<div class="modal fade" id="modalTable" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-table text-success me-2"></i>Sisipkan Tabel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs mb-3" id="tableTabs">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabCustom">Tabel Kustom</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabKelasMapel">Kelas &amp; Mapel</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabRaport">Raport Akademik</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabProgramUnggulan">Program Unggulan</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabEkskul">Ekstrakulikuler</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabAbsensi">Absensi</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabTTD">Area TTD</a></li>
                </ul>
                <div class="tab-content">

                    {{-- TAB: TABEL KUSTOM --}}
                    <div class="tab-pane fade show active" id="tabCustom">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Jumlah Baris</label>
                                <input type="number" id="tableRows" class="form-control" value="5" min="1" max="50">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Jumlah Kolom</label>
                                <input type="number" id="tableCols" class="form-control" value="4" min="1" max="12">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Lebar Tabel (px)</label>
                                <input type="number" id="tableWidth" class="form-control" value="750" min="200" max="794">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Tinggi Baris (px)</label>
                                <input type="number" id="tableRowHeight" class="form-control" value="24" min="14" max="80">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Warna Header</label>
                                <input type="color" id="tableHeaderColor" class="form-control form-control-color" value="#1a5276">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Warna Baris Genap</label>
                                <input type="color" id="tableStripeColor" class="form-control form-control-color" value="#eaf2ff">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Warna Border</label>
                                <input type="color" id="tableBorderColor" class="form-control form-control-color" value="#adb5bd">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Header Kolom <small class="text-muted">(pisahkan dengan koma)</small></label>
                                <input type="text" id="tableHeaders" class="form-control" placeholder="No, Nama Siswa, Nilai, Keterangan">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="tableHasNo" checked>
                                    <label class="form-check-label">Otomatis isi nomor urut di kolom pertama</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB: KELAS & MATA PELAJARAN DINAMIS --}}
                    <div class="tab-pane fade" id="tabKelasMapel">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Tabel dinamis berisi daftar kelas dan mata pelajaran dari database. Variabel akan digenerate otomatis.
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipe Tabel</label>
                                <select id="kelasMapelType" class="form-select">
                                    <option value="daftar_kelas">Daftar Kelas</option>
                                    <option value="daftar_mapel">Daftar Mata Pelajaran</option>
                                    <option value="jadwal_kelas">Jadwal Per Kelas</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pilih Kelas (opsional)</label>
                                <select id="kelasMapelKelas" class="form-select">
                                    <option value="">— Semua Kelas —</option>
                                    @foreach($kelasList ?? [] as $kls)
                                    <option value="{{ $kls->id }}">{{ $kls->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Lebar Tabel (px)</label>
                                <input type="number" id="kelasMapelWidth" class="form-control" value="754">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tinggi Baris (px)</label>
                                <input type="number" id="kelasMapelRowH" class="form-control" value="24">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Warna Header</label>
                                <input type="color" id="kelasMapelHeaderColor" class="form-control form-control-color" value="#1a5276">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Kolom Tambahan <small class="text-muted">(pisahkan koma)</small></label>
                                <input type="text" id="kelasMapelKolom" class="form-control" value="Keterangan">
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="kelasMapelAutoVar" checked>
                                    <label class="form-check-label small">Auto-generate variabel untuk setiap baris</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB: RAPORT AKADEMIK --}}
                    <div class="tab-pane fade" id="tabRaport">
                        <div class="row g-3">
                            <div class="col-lg-7">
                                <div class="d-flex gap-2 mb-3 align-items-end">
                                    <div class="flex-fill">
                                        <label class="form-label fw-semibold mb-1">Pilih Tingkat Kelas</label>
                                        <select id="raportTingkatKelas" class="form-select">
                                            <option value="">— Pilih Kelas —</option>
                                        </select>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="btnLoadMapel" disabled>
                                        <i class="bi bi-arrow-clockwise"></i> Muat Mapel
                                    </button>
                                    <a href="/dashboard/kurikulum-mapel" target="_blank"
                                       class="btn btn-outline-secondary btn-sm" title="Kelola kurikulum mapel">
                                        <i class="bi bi-gear"></i>
                                    </a>
                                </div>
                                <div id="raportMapelPreview">
                                    <div class="text-center text-muted py-4 small border rounded bg-light">
                                        <i class="bi bi-table fs-3 d-block mb-2"></i>
                                        Pilih kelas untuk melihat daftar mapel
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <label class="form-label fw-semibold">Lebar Tabel (px)</label>
                                <input type="number" id="raportWidth" class="form-control form-control-sm mb-2" value="754" min="400" max="794">
                                <label class="form-label fw-semibold">Warna Header</label>
                                <input type="color" id="raportHeaderColor" class="form-control form-control-color form-control-sm mb-2" value="#1a5276">
                                <label class="form-label fw-semibold">Tinggi Baris Data (px)</label>
                                <input type="number" id="raportRowHeight" class="form-control form-control-sm mb-2" value="40" min="20" max="100">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="raportAutoVar" checked>
                                    <label class="form-check-label small">Auto-generate variabel <code>@{{nilai_xxx}}</code>, <code>@{{capaian_xxx}}</code> per mapel</label>
                                </div>
                                <hr class="my-2">
                                <div class="small fw-semibold text-muted mb-1">Kelompok yang disisipkan:</div>
                                <div id="raportKelompokToggle" class="d-flex flex-wrap gap-1"></div>
                                <div class="alert alert-warning py-2 small mt-3 mb-0" id="raportNoKelasAlert" style="display:none">
                                    <i class="bi bi-exclamation-triangle me-1"></i>
                                    Pilih kelas terlebih dahulu sebelum menyisipkan tabel.
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB: PROGRAM UNGGULAN --}}
                    <div class="tab-pane fade" id="tabProgramUnggulan">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Nama Program</label>
                                <input type="text" id="unggulanNama" class="form-control mb-2" value="TAHFIZ">
                                <label class="form-label fw-semibold">Item Program <small class="text-muted">(satu per baris)</small></label>
                                <textarea id="unggulanItems" class="form-control font-monospace" rows="10" style="font-size:0.85rem">a. Al-Fatihah
b. Al-Fajr
c. Al-Ghasyiyah
d. Al-A'Ala
e. Al-Thoriq
f. Al-Buruj
g. Al-Insyiqaq
h. Al Mutafifin</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kolom Penilaian <small class="text-muted">(pisahkan koma)</small></label>
                                <input type="text" id="unggulanKolom" class="form-control mb-2" value="Predikat,Keterangan">
                                <label class="form-label fw-semibold">Lebar Tabel (px)</label>
                                <input type="number" id="unggulanWidth" class="form-control mb-2" value="754">
                                <label class="form-label fw-semibold">Warna Header</label>
                                <input type="color" id="unggulanHeaderColor" class="form-control form-control-color mb-2" value="#1a5276">
                                <div class="form-check mb-1">
                                    <input class="form-check-input" type="checkbox" id="unggulanMergeHeader" checked>
                                    <label class="form-check-label">Tampilkan nama program sebagai header merged row</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TAB: EKSTRAKULIKULER --}}
                    <div class="tab-pane fade" id="tabEkskul">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Daftar Ekskul <small class="text-muted">(satu per baris)</small></label>
                                <textarea id="ekskulItems" class="form-control font-monospace" rows="12" style="font-size:0.85rem">Tapak Suci
Futsal
Karate
Panahan
Tilawah
Tahfidz
Bahasa Arab
Kaligrafi
English Fun
Sains Club
Math Club
Mewarnai
Tari
Catur
Teater
Dokcil
TIK</textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Kolom Penilaian <small class="text-muted">(pisahkan koma)</small></label>
                                <input type="text" id="ekskulKolom" class="form-control mb-2" value="Predikat,Keterangan">
                                <label class="form-label fw-semibold">Lebar Tabel (px)</label>
                                <input type="number" id="ekskulWidth" class="form-control mb-2" value="754">
                                <label class="form-label fw-semibold">Warna Header</label>
                                <input type="color" id="ekskulHeaderColor" class="form-control form-control-color mb-2" value="#1a5276">
                            </div>
                        </div>
                    </div>

                    {{-- TAB: ABSENSI --}}
                    <div class="tab-pane fade" id="tabAbsensi">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Jumlah Siswa (baris)</label>
                                <input type="number" id="absensiRows" class="form-control" value="30" min="5" max="50">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Lebar Tabel (px)</label>
                                <input type="number" id="absensiWidth" class="form-control" value="754">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Warna Header</label>
                                <input type="color" id="absensiHeaderColor" class="form-control form-control-color" value="#1a5276">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Kolom Kehadiran <small class="text-muted">(pisahkan koma)</small></label>
                                <input type="text" id="absensiKolom" class="form-control" value="S,I,A,Keterangan">
                            </div>
                        </div>
                    </div>

                    {{-- TAB: AREA TTD --}}
                    <div class="tab-pane fade" id="tabTTD">
                        <div class="alert alert-info py-2 small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Area tanda tangan akan disisipkan sebagai blok HTML dengan kolom-kolom TTD.
                        </div>
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label fw-semibold">Kolom TTD <small class="text-muted">(format: Label,Nama,Jabatan — satu per baris)</small></label>
                                <textarea id="ttdKolom" class="form-control font-monospace" rows="5" style="font-size:0.85rem">Orang Tua,@{{nama_ortu}},
Wali Kelas,@{{wali_kelas}},NBM : @{{nbm_wali}}
Kepala Sekolah,@{{kepala_sekolah}},NBM : @{{nip}}</textarea>
                                <div class="form-text">Format tiap baris: <code>Label,Nama,Jabatan</code>. Nama/Jabatan bisa menggunakan variabel.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Lebar Area (px)</label>
                                <input type="number" id="ttdWidth" class="form-control" value="754">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Tinggi Ruang TTD (px)</label>
                                <input type="number" id="ttdHeight" class="form-control" value="80">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Posisi Y di canvas (px)</label>
                                <input type="number" id="ttdPosY" class="form-control" value="950">
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" id="btnInsertTable">
                    <i class="bi bi-table me-1"></i>Sisipkan ke Canvas
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('css')
<style>
#canvasPagesContainer .page-block {
    display: inline-block;
    border: 1px solid #ccc;
    box-shadow: 0 2px 12px rgba(0,0,0,0.15);
    background: white;
    line-height: 0;
    position: relative;
    margin-bottom: 16px;
}
#canvasPagesContainer .page-block.active-page {
    box-shadow: 0 0 0 3px #0d6efd, 0 2px 12px rgba(0,0,0,0.15);
}
#canvasPagesContainer canvas {
    border: none !important;
    box-shadow: none !important;
    display: block !important;
}
#editorContainer { user-select: none; }
.page-label {
    position: absolute;
    top: -20px;
    left: 0;
    font-size: 11px;
    color: #6c757d;
    font-weight: 500;
    white-space: nowrap;
}
#pageThumbnails .thumb-item {
    cursor: pointer;
    border: 2px solid transparent;
    border-radius: 4px;
    padding: 2px;
    margin-bottom: 4px;
    transition: border-color .15s;
    position: relative;
}
#pageThumbnails .thumb-item:hover,
#pageThumbnails .thumb-item.active {
    border-color: #0d6efd;
}
#pageThumbnails .thumb-item canvas {
    display: block;
    width: 100%;
    height: auto;
    pointer-events: none;
}
#pageThumbnails .thumb-label {
    font-size: 10px;
    color: #6c757d;
    text-align: center;
    margin-top: 2px;
}
</style>
@endpush

@push('js')
{{-- textBaseline polyfill --}}
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
window.EXISTING_CANVAS_JSON = @json($template->canvas_json ?? null);
window.EDITOR_KELAS_LIST    = @json($kelasList ?? []);
window.EDITOR_MAPEL_LIST    = @json($mapelList ?? []);
</script>
<script src="{{ asset('asset_dashboard/js/document/template-editor.js') }}"></script>
@endpush