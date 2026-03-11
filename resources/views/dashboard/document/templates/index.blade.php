@extends('layouts.dashboard')

@section('title', 'Template Surat')

@section('content')
<div class="row">
    <div class="col-12">

        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Template Surat</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0">Template Surat / Dokumen</h5>
                <a href="{{ route('dashboard.documents.templates.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Buat Template
                </a>
            </div>

            <div class="card-body p-0">

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mx-4 mt-4 mb-0">
                    <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($templates->isEmpty())
                <div class="text-center py-5">
                    <i class="bx bx-file-blank bx-lg text-muted mb-2 d-block"></i>
                    <p class="text-muted mb-3">Belum ada template surat.</p>
                    <a href="{{ route('dashboard.documents.templates.create') }}" class="btn btn-primary btn-sm">
                        Buat Template Pertama
                    </a>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Nama Template</th>
                                <th>Kategori</th>
                                <th>Variabel</th>
                                <th>Dokumen</th>
                                <th>Diperbarui</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $i => $tpl)
                            @php
                                $vars    = $tpl->allVariables();
                                $shown   = array_slice($vars, 0, 3);
                                $moreCount = max(0, count($vars) - 3);
                            @endphp
                            <tr>
                                <td class="ps-4 text-muted">{{ $templates->firstItem() + $i }}</td>

                                <td>
                                    <span class="fw-semibold">{{ $tpl->name }}</span>
                                </td>

                                <td>
                                    <span class="badge bg-label-primary rounded-pill">
                                        {{ $tpl->category->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    @if(empty($vars))
                                        <span class="text-muted small">—</span>
                                    @else
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($shown as $var)
                                                @php
                                                    $isReserved = in_array($var, ['logo', 'barcode_signature']);
                                                @endphp
                                                <code class="badge fw-normal text-black"
                                                      style="font-size:11px;"
                                                      title="{{ $var }}"
                                                      @class([
                                                          'bg-label-warning bg-' => $isReserved,
                                                          'bg-label-secondary' => !$isReserved,
                                                      ])>
                                                    {{ $var }}
                                                </code>
                                            @endforeach
                                            @if($moreCount > 0)
                                                <span class="text-muted small align-self-center">
                                                    +{{ $moreCount }} lagi
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    {{-- Gunakan documents_count dari withCount(), bukan documents->count() --}}
                                    <span class="badge bg-label-success rounded-pill">
                                        {{ $tpl->documents_count }}
                                    </span>
                                </td>

                                <td class="text-muted small">
                                    {{ $tpl->updated_at->diffForHumans() }}
                                </td>

                                <td class="text-end pe-4">
                                    <div class="d-flex gap-1 justify-content-end">

                                        {{-- Generate Single --}}
                                        <a href="{{ route('dashboard.documents.create', $tpl) }}"
                                           class="btn btn-sm btn-warning"
                                           data-bs-toggle="tooltip" title="Generate Dokumen">
                                            <i class="bx bx-file me-1"></i> Generate
                                        </a>

                                        {{-- Bulk Generate --}}
                                        {{-- <a href="{{ route('dashboard.documents.bulk-create', ['template_id' => $tpl->id]) }}"
                                           class="btn btn-sm btn-icon btn-outline-success"
                                           data-bs-toggle="tooltip" title="Generate Massal">
                                            <i class="bx bx-layer"></i>
                                        </a> --}}

                                        {{-- Edit Template --}}
                                        <a href="{{ route('dashboard.documents.templates.edit', $tpl) }}"
                                           class="btn btn-sm btn-icon btn-outline-primary"
                                           data-bs-toggle="tooltip" title="Edit Template">
                                            <i class="bx bx-code-alt"></i>
                                        </a>

                                        {{-- Hapus
                                        <form action="{{ route('dashboard.documents.templates.destroy', $tpl) }}"
                                              method="POST"
                                              onsubmit="return confirm('Hapus template \'{{ addslashes($tpl->name) }}\'?')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-sm btn-icon btn-outline-danger"
                                                    data-bs-toggle="tooltip" title="Hapus">
                                                <i class="bx bx-trash"></i>
                                            </button>
                                        </form> --}}

                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($templates->hasPages())
                <div class="d-flex justify-content-end p-4">
                    {{ $templates->links() }}
                </div>
                @endif

                @endif

            </div>
        </div>

    </div>
</div>
@endsection