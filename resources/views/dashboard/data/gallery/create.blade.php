@extends('layouts.dashboard')
@section('title', 'Tambah Aktivitas')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
    <div class="mb-4 card">
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Tambah Aktivitas Gallery</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.datasekolah.gallery.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mt-2 form-group">
                    <label class="form-label" for="name">Nama</label>
                    <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name" id="name" placeholder="Masukan Nama" value="{{ old('name') }}">
                    @if ($errors->has('name'))
                        <div class="invalid-feedback">
                            {{ $errors->first('name') }}
                        </div>
                    @endif
                </div>

                <div class="mt-2 form-group">
                    <label for="gallery_kategori" class="form-label">Kategori Gallery</label>
                    <select name="gallery_kategori[]" id="select2" class="form-control select2"  multiple data-placeholder="Pilih Kategori Gallery">
                        <option value="">Pilih Kategori Gallery</option>
                        @foreach ($kategoriGallery as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-2 form-group">
                    <label class="form-label" for="">Cover Gallery</label>
                    <div class="custom-file">
                        <input type="file" class="form-control" id="cover" name="cover" accept="image/jpeg,image/png,image" value="{{ old('foto') }}" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                    </div>

                    <div class="mt-4 text-center">
                        <img src="" id="output" alt="" style="width: 100%;">
                    </div>
                </div>

                <div class="mt-2 form-group">
                    <label class="form-label" for="link">Link</label>
                    <input type="url" class="form-control {{ $errors->has('link') ? 'is-invalid' : '' }}" name="link" id="link" placeholder="Masukan Link" value="{{ old('link') }}">
                    @if ($errors->has('link'))
                        <div class="invalid-feedback">
                            {{ $errors->first('link') }}
                        </div>
                    @endif
                </div>

                <div class="mt-2 form-group">
                    <label class="form-label" for="foto">Foto</label>
                    <input type="file" class="form-control {{ $errors->has('foto') ? 'is-invalid' : '' }}" id="foto" name="foto[]" multiple onchange="previewImage(event)">
                    @if ($errors->has('foto'))
                        <div class="invalid-feedback">
                            {{ $errors->first('foto') }}
                        </div>
                    @endif
                </div>

                <div class="mt-2 form-group">
                    <div class="text-center">
                        <h6>Preview</h6>
                    </div>
                    <div class="row" id="preview"></div>
                </div>

                <div class="mt-2 form-group">
                    <a href="{{ route('dashboard.datasekolah.gallery.index') }}" class="btn btn-danger float-start">Kembali</a>
                    <button type="submit" class="btn btn-primary float-end">Submit</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>

    <script>
        $(document).ready(function () {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: "100%"
        });

        });
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
