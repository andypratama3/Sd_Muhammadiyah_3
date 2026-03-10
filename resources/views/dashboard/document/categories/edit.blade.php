@extends('layouts.dashboard')

@section('title', 'Edit Kategori')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('dashboard.documents.categories.index') }}">Kategori
                            Surat</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Edit Kategori — <span class="text-primary">{{ $category->name }}</span></h5>
                </div>
                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('dashboard.documents.categories.update', $category) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf @method('PUT')

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $category->name) }}"
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Deskripsi</label>
                            <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description', $category->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Logo Kategori</label>

                            @if ($category->logo_path)
                                <div class="mb-2">
                                    <p class="small text-muted mb-1">Logo saat ini:</p>
                                    <img src="{{ Storage::url($category->logo_path) }}" id="current-logo"
                                        class="rounded border p-1 bg-light" style="height:70px;object-fit:contain;"
                                        alt="Logo">
                                </div>
                            @endif

                            <input type="file" name="logo" id="logo-input"
                                class="form-control @error('logo') is-invalid @enderror" accept=".png,.jpg,.jpeg,.svg">
                            <div class="form-text">Kosongkan jika tidak ingin mengubah logo. Format: PNG, JPG, SVG. Maks:
                                2MB</div>
                            @error('logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div id="logo-preview-wrap" class="mt-2 d-none">
                                <p class="small text-muted mb-1">Preview baru:</p>
                                <img id="logo-preview" src="#" alt="Preview" class="rounded border p-1 bg-light"
                                    style="height:70px;object-fit:contain;">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bx bx-save me-1"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('dashboard.documents.categories.index') }}"
                                class="btn btn-outline-secondary">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('logo-input').addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById('logo-preview').src = e.target.result;
                document.getElementById('logo-preview-wrap').classList.remove('d-none');
            };
            reader.readAsDataURL(file);
        });
    </script>
@endpush
