@extends('layouts.dashboard')
@section('title', 'Detail Artikel')
@section('content')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush
<div class="card mb-4">

    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Detail Artikel {{ $artikel->name }}</h6>
    </div>
    <div class="card-body">
            <div class="form-group mt-2">
                <label for="name">Nama</label>
                <input type="text" class="form-control" name="name" value="{{ $artikel->name }}" id="name" aria-describedby="name" placeholder="Masukan Nama" readonly>
            </div>
            <div class="form-group mt-2">
                <label>Kategori Artikel</label>
                <select class="form-control" multiple="multiple" name="categorys"
                    data-placeholder="Pilih Kategori artikel" disabled>
                    @foreach ($artikel->categorys as $category)
                    <option  value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group mt-2">
                <label for="">Foto</label>
                <input type="file" class="form-control" name="image" value="{{ asset('storage/img/artikel/'. $artikel->image) }}">

                <div class="row mt-2">
                    <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="" class="img-fluid" style="border-radius: 10px;">
                </div>
            </div>
            <div class="form-group mt-2">
                <label for="">Artikel</label>
                <div id="editor">{!! $artikel->artikel !!}</div>
                <textarea name="artikel" id="content-editor" style="display: none;"></textarea>
            </div>

            <a href="{{ route('dashboard.news.artikel.index') }}" class="btn btn-danger float-lg-start btn-sm">Kembali</a>
        </form>
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
