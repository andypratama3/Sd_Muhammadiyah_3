@extends('layouts.dashboard')
@section('title', 'Edit Artikel')
@section('content')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet"
    type="text/css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Artikel {{ $artikel->name }}</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.news.artikel.update', $artikel->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $artikel->slug }}">
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" class="form-control" name="name" value="{{ $artikel->name }}" id="name" aria-describedby="name" placeholder="Masukan Nama" >
            </div>
            <div class="form-group">
                <label>Kategori Artikel</label>
                <select class="form-control select2" multiple="multiple" name="categorys"
                    data-placeholder="Pilih Kategori artikel">
                    @foreach ($artikel->categorys as $category)
                    <option selected value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                    @foreach ($categorys as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="">Foto</label>
                <input type="file" class="form-control" name="image" value="{{ $artikel->image }}" placeholder="{{ $artikel->image }}">
            </div>
            <div class="form-group text-center">
                <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="" srcset="" width="200px">
            </div>
            <div class="form-group">
                <div id="editor">{!! $artikel->artikel !!}</div>
                <textarea name="artikel" id="content-editor" style="display: none;">{{ $artikel->artikel }}</textarea>
            </div>

            <a href="{{ route('dashboard.news.artikel.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
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
        var toolbarOptions = [
            ['bold', 'italic', 'underline', 'strike'],
            ['blockquote', 'code-block'],

            [{
                'header': 1
            }, {
                'header': 2
            }], // custom button values
            [{
                'list': 'ordered'
            }, {
                'list': 'bullet'
            }],
            [{
                'script': 'sub'
            }, {
                'script': 'super'
            }], // superscript/subscript
            [{
                'indent': '-1'
            }, {
                'indent': '+1'
            }], // outdent/indent
            [{
                'direction': 'rtl'
            }], // text direction

            [{
                'size': ['small', false, 'large', 'huge']
            }], // custom dropdown
            [{
                'header': [1, 2, 3, 4, 5, 6, false]
            }],

            [{
                'color': []
            }, {
                'background': []
            }], // dropdown with defaults from theme
            [{
                'font': []
            }],
            [{
                'align': []
            }],

            ['clean']
        ];

        var quill = new Quill('#editor', {
            modules: {
                toolbar: toolbarOptions
            },
            theme: 'snow'
        });
        quill.on('text-change', function (delta, oldDelta, source){
            $('#content-editor').text($('.ql-editor').html());

        });

    });
</script>
@endpush
@endsection
