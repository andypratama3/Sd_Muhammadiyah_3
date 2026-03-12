@extends('layouts.dashboard')

@section('title', 'Generate Dokumen')

@push('styles')
<style>
    /* Dark template source preview */
    #template-source-preview {
        font-family: 'Courier New', monospace;
        font-size: 12px;
        background: #1e1c1a;
        color: #a8a29e;
        border: 1px solid #3d3935;
        border-radius: 6px;
        padding: 14px;
        max-height: 240px;
        overflow-y: auto;
        line-height: 1.6;
    }
    #template-source-preview .var-highlight {
        color: #fbbf24;
        background: rgba(251,191,36,.12);
        border-radius: 3px;
        padding: 0 3px;
    }

    /* Field card focus glow */
    .field-card { transition: box-shadow .15s, border-color .15s; }
    .field-card:focus-within {
        border-color: #696cff !important;
        box-shadow: 0 0 0 .15rem rgba(105,108,255,.15);
    }

    /* Batch result list */
    #batch-result-list .batch-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12.5px;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    #batch-result-list .batch-item.success { background: #f0fff4; border-color: #b7ebc8; }
    #batch-result-list .batch-item.error   { background: #fff5f5; border-color: #fecaca; }
    #batch-result-list .batch-item.processing { background: #f0f4ff; border-color: #c7d2fe; }
    #batch-result-list .batch-item .row-label {
        font-weight: 600;
        color: #495057;
        min-width: 60px;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('dashboard.documents.templates.index') }}">Template Surat</a></li>
        <li class="breadcrumb-item active">Generate Dokumen</li>
    </ol>
</nav>

<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-0">Generate: {{ $template->name }}</h4>
        <small class="text-muted">Isi kolom berikut untuk membuat dokumen PDF</small>
    </div>
    <a href="{{ route('dashboard.documents.templates.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bx bx-arrow-back me-1"></i> Kembali
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <ul class="mb-0 ps-3">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- ═══════════════ FORM ═══════════════ --}}
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bx bx-edit me-1"></i> Isi Data Dokumen</h6>
            </div>
            <div class="card-body">

                <form id="generate-form"
                      action="{{ route('dashboard.documents.store', $template) }}"
                      method="POST">
                    @csrf

                    @if($variables)
                    <div class="row g-3 mb-4">
                        @foreach($variables as $var)
                        <div class="col-md-6">
                            <div class="card border field-card mb-0 h-100">
                                <div class="card-body py-3 px-3">
                                    <label for="field_{{ $var }}"
                                           class="form-label fw-semibold mb-2"
                                           style="font-size:12px;text-transform:uppercase;letter-spacing:.5px;">
                                        {{ ucwords(str_replace('_', ' ', $var)) }}
                                    </label>

                                    @if(in_array($var, ['isi','content','keterangan','perihal','catatan','deskripsi']))
                                        <textarea id="field_{{ $var }}"
                                                  name="{{ $var }}"
                                                  rows="3"
                                                  class="form-control form-control-sm @error($var) is-invalid @enderror"
                                                  placeholder="{{ ucwords(str_replace('_',' ',$var)) }}…">{{ old($var) }}</textarea>
                                    @else
                                        <input type="text"
                                               id="field_{{ $var }}"
                                               name="{{ $var }}"
                                               value="{{ old($var) }}"
                                               class="form-control form-control-sm @error($var) is-invalid @enderror"
                                               placeholder="{{ ucwords(str_replace('_',' ',$var)) }}…">
                                    @endif

                                    @error($var)
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="alert alert-info mb-4">
                        <i class="bx bx-info-circle me-1"></i>
                        Template ini tidak memiliki variabel isian. Dokumen akan digenerate secara otomatis
                        dengan variabel sistem (logo &amp; QR code).
                    </div>
                    @endif

                    {{-- System vars indicator --}}
                    @if($template->hasLogo() || $template->hasBarcodeSignature())
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        @if($template->hasLogo())
                        <span class="badge bg-label-success px-3 py-2">
                            <i class="bx bx-check me-1"></i> Logo akan disematkan otomatis
                        </span>
                        @endif
                        @if($template->hasBarcodeSignature())
                        <span class="badge bg-label-success px-3 py-2">
                            <i class="bx bx-check me-1"></i> QR Code verifikasi akan digenerate
                        </span>
                        @endif
                    </div>
                    @endif

                    {{-- ── ACTION BUTTONS ────────────────────────────────── --}}
                    <div class="d-flex flex-wrap align-items-center gap-2">

                        {{-- 1. Generate single PDF --}}
                        <button type="submit" id="generate-btn" class="btn btn-warning">
                            <i class="bx bx-download me-1"></i>
                            <span id="btn-label">Generate &amp; Download PDF</span>
                        </button>

                        <span class="text-muted d-none d-sm-inline" style="font-size:12px;">atau</span>

                        {{-- 2. Download Excel template --}}
                        <a href="{{ route('dashboard.documents.excel-template', $template) }}"
                           class="btn btn-outline-success"
                           title="Download template Excel berisi header variabel template ini">
                            <i class="bx bx-table me-1"></i> Template Excel
                        </a>

                        {{-- 3. Import Excel → batch generate --}}
                        <label class="btn btn-outline-primary mb-0" for="excel-import-file"
                               title="Upload Excel yang sudah diisi, PDF akan diunduh per baris">
                            <i class="bx bx-upload me-1"></i>
                            <span id="import-btn-label">Import &amp; Generate</span>
                        </label>
                        <input type="file" id="excel-import-file" accept=".xlsx,.xls" style="display:none">

                    </div>
                    {{-- /ACTION BUTTONS --}}

                </form>

                {{-- ── BATCH PROGRESS (revealed after file picked) ─────── --}}
                <div id="batch-section" style="display:none;" class="mt-4">
                    <hr>
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0">
                            <i class="bx bx-cog me-1 text-primary"></i> Generate Massal
                            <span id="batch-file-badge"
                                  class="badge bg-label-primary ms-1"
                                  style="font-size:11px;font-weight:500;"></span>
                        </h6>
                        <span id="batch-counter" class="text-muted small fw-semibold"></span>
                    </div>

                    <div class="progress mb-3" style="height:6px;">
                        <div id="batch-progress-bar"
                             class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                             style="width:0%"></div>
                    </div>

                    <div id="batch-result-list"
                         class="d-flex flex-column gap-2"
                         style="max-height:320px;overflow-y:auto;"></div>

                    <div id="batch-summary" class="mt-3" style="display:none;"></div>
                </div>
                {{-- /BATCH PROGRESS --}}

            </div>
        </div>
    </div>

    {{-- ═══════════════ SIDEBAR INFO ═══════════════ --}}
    <div class="col-lg-4">

        {{-- Template Info --}}
        <div class="card mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bx bx-info-circle me-1 text-info"></i> Info Template</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    @if($template->category->logo_path)
                        <img src="{{ Storage::url($template->category->logo_path) }}"
                             class="rounded border bg-light p-1 flex-shrink-0"
                             style="width:48px;height:48px;object-fit:contain;"
                             alt="Logo">
                    @else
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-folder"></i>
                            </span>
                        </div>
                    @endif
                    <div>
                        <p class="fw-semibold mb-0">{{ $template->name }}</p>
                        <small class="text-muted">{{ $template->category->name }}</small>
                    </div>
                </div>

                <table class="table table-sm table-borderless mb-0" style="font-size:13px;">
                    <tr>
                        <td class="text-muted ps-0">Field isian</td>
                        <td class="fw-semibold">{{ count($variables) }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">Logo</td>
                        <td>
                            @if($template->hasLogo())
                                <span class="badge bg-label-success">Ya</span>
                            @else
                                <span class="text-muted">Tidak</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted ps-0">QR Signature</td>
                        <td>
                            @if($template->hasBarcodeSignature())
                                <span class="badge bg-label-success">Ya</span>
                            @else
                                <span class="text-muted">Tidak</span>
                            @endif
                        </td>
                    </tr>
                </table>

                @if($variables)
                <hr class="my-3">
                <p class="mb-2 text-muted"
                   style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:600;">
                    Variabel Template
                </p>
                <div class="d-flex flex-wrap gap-1">
                    @foreach($variables as $var)
                    <code class="badge bg-label-secondary" style="font-size:11px;">{{ $var }}</code>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        {{-- Excel guide --}}
        <div class="card mb-4">
            <div class="card-header py-3">
                <h6 class="mb-0">
                    <i class="bx bx-help-circle me-1 text-warning"></i> Cara Pakai Excel
                </h6>
            </div>
            <div class="card-body py-3" style="font-size:12.5px;line-height:1.7;">
                <ol class="ps-3 mb-0 d-flex flex-column gap-1">
                    <li>Klik <strong class="text-success">Template Excel</strong> — download file <code>.xlsx</code> dengan header kolom siap pakai.</li>
                    <li>Buka file, isi data mulai baris ke-2. <strong>Satu baris = satu PDF.</strong></li>
                    <li>Klik <strong class="text-primary">Import &amp; Generate</strong> lalu pilih file Excel yang sudah diisi.</li>
                    <li>PDF akan otomatis terunduh satu per satu.</li>
                </ol>
            </div>
        </div>

        {{-- Template Source --}}
        <div class="card">
            <div class="card-header py-3">
                <h6 class="mb-0"><i class="bx bx-code-alt me-1 text-secondary"></i> Source Template</h6>
            </div>
            <div class="card-body p-3">
                <div id="template-source-preview">
                    {!! preg_replace('/\{\{(.*?)\}\}/', '<span class="var-highlight">{{$1}}</span>', e($template->html_template)) !!}
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

@push('js')
<script>
/* ── Single generate: show spinner on submit ─────────────── */
document.getElementById('generate-form').addEventListener('submit', function () {
    const btn   = document.getElementById('generate-btn');
    const label = document.getElementById('btn-label');
    btn.disabled = true;
    label.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Membuat PDF…';
});

/* ── Batch Excel import ──────────────────────────────────── */
(function () {
    const PARSE_URL  = "{{ route('dashboard.documents.parse-excel', $template) }}";
    const SINGLE_URL = "{{ route('dashboard.documents.store', $template) }}";
    const CSRF       = "{{ csrf_token() }}";

    const fileInput  = document.getElementById('excel-import-file');
    const importLbl  = document.getElementById('import-btn-label');
    const section    = document.getElementById('batch-section');
    const fileBadge  = document.getElementById('batch-file-badge');
    const counter    = document.getElementById('batch-counter');
    const bar        = document.getElementById('batch-progress-bar');
    const list       = document.getElementById('batch-result-list');
    const summaryEl  = document.getElementById('batch-summary');

    fileInput.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        if (!/\.(xlsx|xls)$/i.test(file.name)) {
            alert('Hanya file .xlsx atau .xls yang diperbolehkan.');
            this.value = '';
            return;
        }
        runBatch(file);
    });

    async function runBatch(file) {
        /* ── reset UI ── */
        section.style.display = 'block';
        fileBadge.textContent = file.name;
        list.innerHTML        = '';
        summaryEl.style.display = 'none';
        bar.style.width       = '0%';
        bar.className         = 'progress-bar bg-success progress-bar-striped progress-bar-animated';
        counter.textContent   = 'Membaca file…';
        importLbl.innerHTML   = '<span class="spinner-border spinner-border-sm me-1"></span>';
        section.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        /* ── Step 1: send Excel to server → get parsed rows as JSON ── */
        let rows = [];
        try {
            const fd = new FormData();
            fd.append('_token', CSRF);
            fd.append('excel_file', file);

            const res  = await fetch(PARSE_URL, { method: 'POST', body: fd });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'Server error ' + res.status);

            rows = json.rows || [];
        } catch (e) {
            showSummary(0, 0, 0, e.message);
            counter.textContent = 'Gagal membaca file.';
            bar.className = 'progress-bar bg-danger';
            bar.style.width = '100%';
            importLbl.innerHTML = 'Import &amp; Generate';
            return;
        }

        if (rows.length === 0) {
            counter.textContent = 'Tidak ada baris data ditemukan.';
            bar.style.width = '100%';
            bar.className   = 'progress-bar bg-warning';
            importLbl.innerHTML = 'Import &amp; Generate';
            return;
        }

        const total = rows.length;
        counter.textContent = `0 / ${total}`;
        let done = 0, success = 0, failed = 0;

        /* ── Step 2: generate PDF per row ── */
        for (const row of rows) {
            const item = addItem(row.label, '⏳ Memproses…', 'processing');

            try {
                const pfd = new FormData();
                pfd.append('_token', CSRF);
                Object.entries(row.data).forEach(([k, v]) => pfd.append(k, v ?? ''));

                const pRes = await fetch(SINGLE_URL, { method: 'POST', body: pfd });
                if (!pRes.ok) throw new Error('HTTP ' + pRes.status);

                /* trigger download */
                const blob   = await pRes.blob();
                const dlUrl  = URL.createObjectURL(blob);
                const anchor = document.createElement('a');
                anchor.href     = dlUrl;
                anchor.download = (row.filename || row.label) + '.pdf';
                document.body.appendChild(anchor);
                anchor.click();
                anchor.remove();
                setTimeout(() => URL.revokeObjectURL(dlUrl), 4000);

                updateItem(item, row.label, '✅ Berhasil diunduh', 'success');
                success++;

            } catch (e) {
                updateItem(item, row.label, '❌ Gagal: ' + e.message, 'error');
                failed++;
            }

            done++;
            bar.style.width     = Math.round((done / total) * 100) + '%';
            counter.textContent = `${done} / ${total}`;

            await sleep(350); /* slight delay so browser queues download */
        }

        /* ── Finish ── */
        bar.classList.remove('progress-bar-animated', 'progress-bar-striped');
        if      (failed === 0) bar.classList.replace('bg-success', 'bg-success');
        else if (success === 0) { bar.classList.remove('bg-success'); bar.classList.add('bg-danger'); }
        else                   { bar.classList.remove('bg-success'); bar.classList.add('bg-warning'); }

        showSummary(total, success, failed);
        importLbl.innerHTML = 'Import &amp; Generate';
        fileInput.value = '';
    }

    /* ── DOM helpers ─────────────────────────────────────── */
    function addItem(label, msg, state) {
        const el = document.createElement('div');
        el.className = `batch-item ${state}`;
        el.innerHTML = `<span class="row-label">${esc(label)}</span>
                        <span class="status-msg">${esc(msg)}</span>`;
        list.appendChild(el);
        list.scrollTop = list.scrollHeight;
        return el;
    }

    function updateItem(el, label, msg, state) {
        el.className = `batch-item ${state}`;
        el.querySelector('.status-msg').innerHTML = msg; /* msg may contain emoji */
    }

    function showSummary(total, success, failed, errMsg) {
        summaryEl.style.display = 'block';
        if (errMsg) {
            summaryEl.innerHTML = `<div class="alert alert-danger mb-0 py-2 small">
                <i class="bx bx-error me-1"></i><strong>Error:</strong> ${esc(errMsg)}</div>`;
            return;
        }
        const cls = failed === 0 ? 'alert-success' : (success === 0 ? 'alert-danger' : 'alert-warning');
        summaryEl.innerHTML = `<div class="alert ${cls} mb-0 py-2 small">
            <i class="bx bx-check-circle me-1"></i>
            Selesai — <strong>${success}</strong> berhasil diunduh,
            <strong>${failed}</strong> gagal, dari total <strong>${total}</strong> baris.</div>`;
    }

    function sleep(ms) { return new Promise(r => setTimeout(r, ms)); }
    function esc(s) {
        return String(s).replace(/[&<>"']/g, c =>
            ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[c]));
    }

})();
</script>
@endpush