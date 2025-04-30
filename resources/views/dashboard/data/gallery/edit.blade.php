@extends('layouts.dashboard')
@section('title', 'Edit Gallery')
@section('content')
    <div class="card mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Edit Aktivitas Gallery</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('dashboard.datasekolah.gallery.update', $gallery->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" name="slug" value="{{ $gallery->slug }}">
                <div class="form-group mt-2">
                    <label for="name">Nama</label>
                    <input type="text" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" name="name" id="name" placeholder="Masukan Nama" value="{{ old('name', $gallery->name) }}">
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


                <div class="row mt-4" id="preview">
                    <h4 class="text-center">Preview</h4>
                    <div id="old_foto" class="row">
                        @php
                            $filenames = is_array($gallery->foto) ? $gallery->foto : explode(',', $gallery->foto);
                        @endphp
                        @foreach ($filenames as $filename)
                            <div class="col-md-3">
                                <img src="{{ asset('storage/img/gallery/' . trim($filename)) }}" class="img-thumbnail mb-2" style="width: 100%; height: 300px;">
                            </div>
                        @endforeach
                    </div>
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
            const oldFoto = document.getElementById('old_foto');

            // Sembunyikan foto lama
            if (oldFoto) {
                oldFoto.remove();
            }

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
