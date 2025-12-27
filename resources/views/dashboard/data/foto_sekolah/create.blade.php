@extends('layouts.dashboard')
@section('title', 'Tambah Foto Sekolah')

@section('content')
    <div class="mb-4 card">
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Tambah Foto Sekolah</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.datasekolah.foto_sekolah.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mt-2 form-group">
                    <label class="form-label" for="name">Nama</label>
                    <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name" id="name" placeholder="Masukkan Nama Foto" value="{{ old('name') }}">
                    @if ($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                </div>

                <div class="mt-2 form-group">
                    <label class="form-label" for="foto">Foto</label>
                    <div class="custom-file">
                        <input type="file" class="form-control {{ $errors->has('foto') ? 'is-invalid' : '' }}" id="foto" name="foto" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="previewImage(event)">
                    </div>
                    @if ($errors->has('foto'))
                        <div class="invalid-feedback">
                            {{ $errors->first('foto') }}
                        </div>
                    @endif

                    <div class="mt-4 text-center">
                        <img src="" id="output" alt="Preview" style="width: 100%; max-width: 500px; display: none;">
                    </div>
                </div>

                <div class="mt-4 form-group">
                    <a href="{{ route('dashboard.datasekolah.foto_sekolah.index') }}" class="btn btn-danger float-start">Kembali</a>
                    <button type="submit" class="btn btn-primary float-end">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script>
        function previewImage(event) {
            const input = event.target;
            const output = document.getElementById('output');

            if (input.files && input.files[0]) {
                const reader = new FileReader();

                reader.onload = function(e) {
                    output.src = e.target.result;
                    output.style.display = 'block';
                };

                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
@endpush
