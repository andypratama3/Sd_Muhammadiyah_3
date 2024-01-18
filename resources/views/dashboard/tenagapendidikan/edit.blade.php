@extends('layouts.dashboard')
@section('title', 'Edit guru')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
    <div class="card mb-4">
        @include('layouts.flashmessage')
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Edit guru {{ $guru->name }}</h6>
        </div>
        <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.guru.update',$guru->slug) }}" method="POST" enctype="multipart/form-data">
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
                <label for="">Pelajaran</label>
                <select name="pelajarans[]" id="" multiple class="form-control select2">
                    @foreach ($guru->pelajarans as $item)
                    <option value="{{ $item->id }}" selected>{{ $item->name }}</option>
                    @endforeach
                    @foreach ($pelajarans as $pelajaran)
                    <option value="{{ $pelajaran->id }}">{{ $pelajaran->name }}</option>
                    @endforeach
                </select>
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
            <a href="{{ route('dashboard.datasekolah.guru.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Simpan</button>
        </form>
        </div>
    </div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script type="text/javascript">
    $(document).ready(function (e) {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
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
