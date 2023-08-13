@extends('layouts.dashboard')
@section('title', 'Edit guru')
@section('content')
    <div class="card mb-4">
        @include('layouts.flashmessage')
        <div class="card-body">
        <form action="{{ route('dashboard.guru.update',$guru->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="judul">Nama</label>
                <input type="text" class="form-control" name="name" id="name"  value="{{ $guru->name }}"
                    placeholder="Masukan nama">
            </div>
            <div class="form-group">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" name="description" placeholder="Deskripsi" value="{{ $guru->description }}">
            </div>
            <div class="form-group">
                <label for="">Lulusan</label>
                <input type="text" class="form-control" id="" name="lulusan" placeholder="lulusan" value="{{ $guru->lulusan }}">
            </div>
            <div class="form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="loadPreview(this)">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="{{ asset('storage/img/guru/'.$guru->foto) }}" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>
            <a href="{{ route('dashboard.guru.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Simpan</button>
        </form>
        </div>
    </div>
@push('js')
<script type="text/javascript">
    $(document).ready(function (e) {
        $('#foto').change(function () {
            let reader = new FileReader();
            reader.onload = (e) => {
                $('#output').attr('src', e.target.result);
            }
            reader.readAsDataURL(this.files[0]);
        });
    });
</script>
@endpush
@endsection
