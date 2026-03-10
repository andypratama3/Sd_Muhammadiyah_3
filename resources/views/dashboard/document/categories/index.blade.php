@extends('layouts.dashboard')

@section('title', 'Kategori Surat')

@section('content')
    <div class="row">
        <div class="col-12">

            {{-- Breadcrumb --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kategori Surat</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="mb-0">Kategori Surat / Dokumen</h5>
                    <a href="{{ route('dashboard.documents.categories.create') }}" class="btn btn-primary btn-sm">
                        <i class="bx bx-plus me-1"></i> Tambah Kategori
                    </a>
                </div>

                <div class="card-body">

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                            <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row g-3">
                        @forelse($categories as $cat)
                            <div class="col-sm-6 col-xl-4">
                                <div class="card border shadow-none h-100 mb-0">
                                    <div class="card-body">
                                        <div class="d-flex align-items-start gap-3 mb-3">
                                            @if ($cat->logo_path)
                                                <img src="{{ Storage::url($cat->logo_path) }}"
                                                    class="rounded border bg-light p-1 flex-shrink-0"
                                                    style="width:56px;height:56px;object-fit:contain;"
                                                    alt="{{ $cat->name }}">
                                            @else
                                                <div class="avatar avatar-md flex-shrink-0">
                                                    <span class="avatar-initial rounded bg-label-primary">
                                                        <i class="bx bx-folder"></i>
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="overflow-hidden">
                                                <h6 class="mb-1 text-truncate">{{ $cat->name }}</h6>
                                                <p class="text-muted small mb-0"
                                                    style="display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                                                    {{ $cat->description ?: 'Tidak ada deskripsi' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="badge bg-label-info rounded-pill">
                                                {{ $cat->templates_count }} template
                                            </span>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('dashboard.documents.categories.edit', $cat) }}"
                                                    class="btn btn-sm btn-icon btn-outline-warning" data-bs-toggle="tooltip"
                                                    title="Edit">
                                                    <i class="bx bx-edit-alt"></i>
                                                </a>
                                                <form action="{{ route('dashboard.documents.categories.destroy', $cat) }}"
                                                    method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus kategori ini?')">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger"
                                                        data-bs-toggle="tooltip" title="Hapus">
                                                        <i class="bx bx-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="text-center py-5">
                                    <i class="bx bx-folder-open bx-lg text-muted mb-2 d-block"></i>
                                    <p class="text-muted mb-3">Belum ada kategori dokumen.</p>
                                    <a href="{{ route('dashboard.documents.categories.create') }}"
                                        class="btn btn-primary btn-sm">
                                        Buat Kategori Pertama
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($categories->hasPages())
                        <div class="d-flex justify-content-end mt-4">
                            {{ $categories->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
