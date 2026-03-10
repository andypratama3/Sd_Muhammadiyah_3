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
                        dengan variabel sistem (logo & QR code).
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

                    <button type="submit" id="generate-btn" class="btn btn-warning">
                        <i class="bx bx-download me-1"></i>
                        <span id="btn-label">Generate & Download PDF</span>
                    </button>
                </form>
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

@push('scripts')
<script>
document.getElementById('generate-form').addEventListener('submit', function () {
    const btn   = document.getElementById('generate-btn');
    const label = document.getElementById('btn-label');
    btn.disabled = true;
    label.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Membuat PDF…';
});
</script>
@endpush
