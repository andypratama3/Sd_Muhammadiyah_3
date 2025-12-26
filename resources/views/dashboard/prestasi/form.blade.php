@extends('layouts.dashboard')
@section('title', isset($prestasi) ? 'Edit Prestasi' : 'Tambah Prestasi')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">{{ isset($prestasi) ? 'Edit Prestasi' : 'Tambah Prestasi' }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ isset($prestasi) ? route('dashboard.datasekolah.prestasi.update', $prestasi->slug) : route('dashboard.datasekolah.prestasi.store') }}"
              method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($prestasi))
                @method('PUT')
                @if($prestasi->slug)
                    <input type="hidden" name="slug" value="{{ $prestasi->slug }}">
                @endif
            @endif

            <!-- Nama -->
            <div class="mt-2 form-group">
                <label class="form-label" for="name">Nama</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror"
                       name="name" id="name" aria-describedby="name"
                       placeholder="Masukan Prestasi" value="{{ old('name', $prestasi->name ?? '') }}">
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Status -->
            <div class="mt-2 form-group">
                <label class="form-label" for="status">Status</label>
                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                    <option value="">Pilih Status Prestasi</option>
                    <option value="1" {{ old('status', $prestasi->status ?? '') == '1' ? 'selected' : '' }}>Prestasi Siswa</option>
                    <option value="2" {{ old('status', $prestasi->status ?? '') == '2' ? 'selected' : '' }}>Prestasi Sekolah</option>
                </select>
                @error('status')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Tanggal -->
            <div class="mt-2 form-group">
                <label class="form-label" for="tanggal">Tanggal</label>
                <input type="date" class="form-control @error('tanggal') is-invalid @enderror"
                       name="tanggal" id="tanggal" value="{{ old('tanggal', $prestasi->tanggal ?? '') }}">
                @error('tanggal')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Prestasi Siswa Fields (Hidden by default) -->
            <div class="mt-2 form-group d-none" id="prestasi_siswa">
                <!-- Tingkat -->
                <div class="mt-2 form-group">
                    <label class="form-label" for="tingkat">Tingkat Prestasi</label>
                    <select name="tingkat" id="tingkat" class="form-control @error('tingkat') is-invalid @enderror"
                            data-placeholder="Pilih Tingkat">
                        <option value="">Pilih Tingkat</option>
                        <option value="Sekolah" {{ old('tingkat', $prestasi->tingkat ?? '') == 'Sekolah' ? 'selected' : '' }}>Sekolah</option>
                        <option value="Kota" {{ old('tingkat', $prestasi->tingkat ?? '') == 'Kota' ? 'selected' : '' }}>Kota</option>
                        <option value="Provinsi" {{ old('tingkat', $prestasi->tingkat ?? '') == 'Provinsi' ? 'selected' : '' }}>Provinsi</option>
                        <option value="Nasional" {{ old('tingkat', $prestasi->tingkat ?? '') == 'Nasional' ? 'selected' : '' }}>Nasional</option>
                        <option value="Internasional" {{ old('tingkat', $prestasi->tingkat ?? '') == 'Internasional' ? 'selected' : '' }}>Internasional</option>
                    </select>
                    @error('tingkat')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Juara -->
                <div class="mt-2 form-group">
                    <label class="form-label" for="juara">Juara</label>
                    <input type="text" class="form-control @error('juara') is-invalid @enderror"
                           name="juara" id="juara" value="{{ old('juara', $prestasi->juara ?? '') }}">
                    @error('juara')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- penyelenggara -->
            <div class="mt-2 form-group">
                <label class="form-label" for="penyelenggara">Penyelenggara</label>
                <input type="text" class="form-control @error('penyelenggara') is-invalid @enderror"
                    name="penyelenggara" id="penyelenggara" value="{{ old('penyelenggara', $prestasi->penyelenggara ?? '') }}">
                @error('peyelenggara')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Kategori Prestasi -->
            <div class="mt-2 form-group">
                <label class="form-label" for="select2">Kategori Prestasi</label>
                <select name="prestasi_kategori[]" id="select2" class="form-control select2 @error('prestasi_kategori') is-invalid @enderror"
                        multiple data-placeholder="Pilih Kategori Prestasi">
                    <option value="">Pilih Kategori Prestasi</option>
                    @foreach ($kategoriPrestasi as $item)
                        <option value="{{ $item->id }}"
                                {{ (in_array($item->id, old('prestasi_kategori', isset($prestasi) ? $prestasi->prestasi_kategori->pluck('id')->toArray() : []))) ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
                @error('prestasi_kategori')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Foto -->
            <div class="mt-2 form-group">
                <label class="form-label" for="foto">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control @error('foto') is-invalid @enderror"
                           id="foto" name="foto" accept="image/*">
                </div>
                @error('foto')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @if(isset($prestasi) && $prestasi->foto)
                    <div class="mt-2">
                        <small class="text-muted">Foto saat ini:</small>
                        <div class="mt-1">
                            <img src="{{ asset('storage/img/prestasi/' . $prestasi->foto) }}"
                                 alt="Foto Prestasi" class="img-thumbnail" style="max-width: 200px;">
                        </div>
                    </div>
                @endif
            </div>

            <!-- Deskripsi -->
            <div class="mt-2 form-group">
                <label class="form-label" for="editor">Deskripsi</label>
                <div id="editor">{!! old('description', $prestasi->description ?? '') !!}</div>
                <textarea name="description" id="content-editor" style="display: none;"></textarea>
                @error('description')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Buttons -->
            <div class="mt-2 mb-2 form-group">
                <a href="{{ route('dashboard.datasekolah.prestasi.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
                <button type="submit" class="btn btn-primary btn-sm float-lg-end">
                    {{ isset($prestasi) ? 'Update' : 'Submit' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

<script>
    $(function () {
        // Select2 initialization
        $('.select2').select2({
            theme: 'bootstrap4',
            width: "100%"
        });

        // Quill Editor
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],
            [{'header': 1}, {'header': 2}],
            [{'list': 'ordered'}, {'list': 'bullet'}],
            [{'script': 'sub'}, {'script': 'super'}],
            [{'indent': '-1'}, {'indent': '+1'}],
            [{'direction': 'rtl'}],
            [{'size': ['small', false, 'large', 'huge']}],
            [{'header': [1, 2, 3, 4, 5, 6, false]}],
            [{'color': []}, {'background': []}],
            [{'font': []}],
            [{'align': []}],
            ['clean']
        ];

        var quill = new Quill('#editor', {
            modules: {
                toolbar: toolbarOptions
            },
            theme: 'snow'
        });

        quill.on('text-change', function (delta, oldDelta, source) {
            $('#content-editor').text($('.ql-editor').html());
        });

        // Toggle prestasi_siswa section based on status
        function togglePrestasiSiswa() {
            const status = $('#status').val();
            if (status === '1') {
                $('#prestasi_siswa').removeClass('d-none');
            } else {
                $('#prestasi_siswa').addClass('d-none');
            }
        }

        // Initial check on page load
        togglePrestasiSiswa();

        // Listen to status change
        $('#status').on('change', function () {
            togglePrestasiSiswa();
        });
    });
</script>
@endpush
@endsection
