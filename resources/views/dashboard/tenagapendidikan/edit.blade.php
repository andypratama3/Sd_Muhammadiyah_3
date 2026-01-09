@extends('layouts.dashboard')
@section('title', 'Edit Tenaga Pendidikan')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
    <div class="mb-4 card">
        @include('layouts.flashmessage')
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Edit tenagapendidikan {{ $tenagapendidikan->name }}</h6>
        </div>
        <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.tenagapendidikan.update', $tenagapendidikan->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $tenagapendidikan->slug }}">
            <div class="mt-2 form-group">
                <label for="judul">Nama</label>
                <input type="text" class="form-control" name="name" id="name"  value="{{ $tenagapendidikan->name }}"
                    placeholder="Masukan nama">
            </div>
            <div class="mt-2 form-group">
                <label for="">Jabatan</label>
                <input type="text" class="form-control" id="jabatan" name="jabatan" value="{{ $tenagapendidikan->jabatan }}">
            </div>
            <div class="mt-2 form-group">
                <label for="">Struktur Tenaga Pendidikan</label>
                <select name="struktur_tenaga_pendidikan_id" id="struktur_tenaga_pendidikan_id" class="form-control select2">
                    <option value="" selected>Pilih Struktur</option>
                    @foreach ($strukturTenagaPendidikan as $item)
                        <option value="{{ $item->id }}" {{ $tenagapendidikan->struktur_tenaga_pendidikan_id == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-2 mb-2 form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*" onchange="loadPreview(this)">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="{{ asset('storage/img/tenagapendidikan/'.$tenagapendidikan->foto) }}" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>
            <a href="{{ route('dashboard.datasekolah.tenagapendidikan.index') }}" class="btn btn-danger float-lg-start btn-sm">Kembali</a>
            <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
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
