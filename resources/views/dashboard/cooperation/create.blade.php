@extends('layouts.dashboard')
@section('title', 'Tambah Kerja Sama')
@push('css')
        <link href="https://cdn.bootcdn.net/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet" />
@endpush
@section('content')
<div class="card mb-4">
    <div class="row">
        @include('layouts.flashmessage')
    </div>
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Kerja Sama</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.cooperation.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mt-2">
                <label for="name">Nama</label>
                <input type="text" class="form-control" name="name" id="name" value="{{ old('name') }}" placeholder="Masukan Nama">
            </div>
            <div class="form-group mt-2 mb-2">
                <label for="">Order</label>
                <input type="number" class="form-control" name="order" id="order" value="{{ old('order') }}" placeholder="Masukan Posisi">
            </div>
            <div class="form-group mt-4">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,application/pdf,image" value="{{ old('foto') }}" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="" id="output" alt="" style="border-radius: 10px;" class="img-fluid">
                </div>
            </div>

            <div class="form-group mt-2">
                <a  href="{{ route('dashboard.datasekolah.cooperation.index') }}" class="btn btn-danger btn-sm">Kembali</a>
                <button type="submit" class="btn btn-primary float-lg-end btn-sm">Submit</button>
            </div>
        </form>
    </div>
</div>
@push('js')
<script type="text/javascript">
    function previewImage(event) {
        const output = document.getElementById('output');
        const file = event.target.files[0];

        if (file) {
            const reader = new FileReader();

            reader.onload = function(e) {
                output.src = e.target.result; // Set the Base64 encoded image data
                output.style.display = 'block'; // Display the image
            };

            reader.readAsDataURL(file); // Read the file as a Data URL
        } else {
            output.src = ''; // Clear src if no file
            output.style.display = 'none'; // Hide the image
        }
    }
   </script>
@endpush
@endsection
