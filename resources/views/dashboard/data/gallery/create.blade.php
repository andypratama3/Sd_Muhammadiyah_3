@extends('layouts.dashboard')
@section('title', 'Tambah Aktivitas')
@section('content')
    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Tambah Aktivitas Gallery</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.datasekolah.gallery.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="form-group mt-2">
                    <label for="name">Nama</label>
                    <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name" id="name" placeholder="Masukan Nama" value="{{ old('name') }}">
                    @if ($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                </div>

                <div class="form-group mt-2">
                    <label for="foto">Foto</label>
                    <input type="file" class="form-control {{ $errors->has('foto') ? 'is-invalid' : '' }}" id="foto" name="foto[]" multiple onchange="previewImage(event)">
                    @if ($errors->has('foto'))
                        <div class="invalid-feedback">
                            {{ $errors->first('foto') }}
                        </div>
                    @endif
                </div>

                <div class="form-group mt-2">
                    <div class="text-center">
                        <h6>Preview</h6>
                    </div>
                    <div class="row" id="preview"></div>
                </div>

                <div class="form-group mt-2">
                    <a href="{{ route('dashboard.datasekolah.gallery.index') }}" class="btn btn-danger float-start">Kembali</a>
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
            const preview = document.getElementById('preview');
            preview.innerHTML = '';

            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                const reader = new FileReader();

                reader.onload = function(e) {
                    const imgElement = document.createElement('img');
                    imgElement.src = e.target.result;
                    imgElement.style.width = '100%';
                    imgElement.style.height = '300px';
                    imgElement.classList.add('img-thumbnail', 'mb-2');

                    const colDiv = document.createElement('div');
                    colDiv.className = 'col-md-3';
                    colDiv.appendChild(imgElement);

                    preview.appendChild(colDiv);
                };

                reader.readAsDataURL(file);
            }
        }
    </script>
@endpush
