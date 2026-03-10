@extends('layouts.dashboard')

@section('title', 'Dokumen Dihasilkan')

@section('content')
    <div class="row">
        <div class="col-12">

            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Dokumen Dihasilkan</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">Riwayat Dokumen Dihasilkan</h5>
                    <a href="{{ route('dashboard.documents.templates.index') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Generate Dokumen Baru
                    </a>
                </div>

                <div class="card-body p-0">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mx-4 mt-4 mb-0">
                            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($documents->isEmpty())
                        <div class="text-center py-5">
                            <i class="bx bx-file-blank bx-lg text-muted mb-2 d-block"></i>
                            <p class="text-muted mb-3">Belum ada dokumen yang dihasilkan.</p>
                            <a href="{{ route('dashboard.documents.templates.index') }}" class="btn btn-primary btn-sm">
                                Pilih Template & Generate
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">#</th>
                                        <th>Kode Verifikasi</th>
                                        <th>Template</th>
                                        <th>Kategori</th>
                                        <th>Data Isi</th>
                                        <th>Tanggal Buat</th>
                                        <th class="text-end pe-4">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($documents as $i => $doc)
                                        <tr>
                                            <td class="ps-4 text-muted">{{ $documents->firstItem() + $i }}</td>
                                            <td>
                                                <code class="badge bg-label-warning text-warning rounded-pill"
                                                    style="font-size:12px;letter-spacing:.5px;">
                                                    {{ $doc->verification_code }}
                                                </code>
                                            </td>
                                            <td class="fw-semibold">{{ $doc->template->name }}</td>
                                            <td>
                                                <span class="badge bg-label-primary rounded-pill">
                                                    {{ $doc->template->category->name }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    @foreach (array_slice($doc->data_json, 0, 2) as $k => $v)
                                                        <p class="mb-0" style="font-size:12px;">
                                                            <span class="text-muted">{{ ucfirst($k) }}:</span>
                                                            <span class="fw-semibold">{{ Str::limit($v, 25) }}</span>
                                                        </p>
                                                    @endforeach
                                                    @if (count($doc->data_json) > 2)
                                                        <span class="text-muted" style="font-size:11px;">
                                                            +{{ count($doc->data_json) - 2 }} field lainnya
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="text-muted small">
                                                {{ $doc->created_at->format('d/m/Y H:i') }}
                                                <br>
                                                <span class="text-muted"
                                                    style="font-size:11px;">{{ $doc->created_at->diffForHumans() }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <a href="{{ route('verify.show', $doc->verification_code) }}"
                                                        target="_blank" class="btn btn-sm btn-icon btn-outline-info"
                                                        data-bs-toggle="tooltip" title="Verifikasi Dokumen">
                                                        <i class="bx bx-qr-scan"></i>
                                                    </a>
                                                    <a href="{{ route('dashboard.documents.download', $doc) }}"
                                                        class="btn btn-sm btn-icon btn-outline-success"
                                                        data-bs-toggle="tooltip" title="Download PDF">
                                                        <i class="bx bx-download"></i>
                                                    </a>
                                                    <form action="{{ route('dashboard.documents.destroy', $doc) }}"
                                                        method="POST" onsubmit="return confirm('Hapus dokumen ini?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit"
                                                            class="btn btn-sm btn-icon btn-outline-danger"
                                                            data-bs-toggle="tooltip" title="Hapus">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if ($documents->hasPages())
                            <div class="d-flex justify-content-end p-4">
                                {{ $documents->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
