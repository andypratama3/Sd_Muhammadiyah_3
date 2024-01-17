@extends('layouts.dashboard')
@section('title', 'Detail Prestasi')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="form-group">
            <label for="name">Nama Pretasi</label>
            <input type="text" class="form-control" id="name" aria-describedby="emailHelp" value="{{ $prestasi->name }}"
                readonly>
        </div>
        <div class="form-group">
            <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto) }}" alt="" srcset="" style="width: 100%;">
        </div>
        <div class="form-group">
            <div id="editor">{!! $prestasi->description !!}</div>
            <textarea name="prestasi" id="content-editor" style="display: none;"></textarea>
        </div>
        <div class="form-group">
            <label for="">Status</label>
            <input type="text" class="form-control" readonly value="{{ ($prestasi->status == 1 ) ? 'Prestasi Siswa' : 'Prestasi Sekolah'  }}">
        </div>
        <a href="{{ route('dashboard.datasekolah.prestasi.index') }}" class="btn btn-danger float-end">Kembali</a>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    $(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        var quill = new Quill('#editor', {
            theme: 'snow'
            readOnly: true,
        });


    });
</script>
@endpush
@endsection
