@extends('layouts.dashboard')
@section('title', 'Edit Berita')
@section('content')
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-body">
        <form action="{{ route('dashboard.news.berita.update',$berita->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $berita->slug }}">
            <div class="form-group">
                <label for="judul">Judul</label>
                <input type="text" class="form-control" name="judul" id="judul" value="{{ $berita->judul }}" placeholder="Masukan Judul">
            </div>
            <div class="form-group">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" value="{{ $berita->desc }}" name="desc"
                    placeholder="Deskripsi">
            </div>
            <div class="form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*"
                        onchange="loadPreview(this)">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="{{ asset('storage/img/berita/'.$berita->foto) }}" id="output" alt=""
                        style="width: 200px; height: 50%;">
                </div>
            </div>
            <a href="{{ route('dashboard.news.berita.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
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
