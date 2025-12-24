@extends('layouts.dashboard')
@section('title', 'Tambah Prestasi')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Prestasi</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.prestasi.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-2 form-group">
                <label for="name">Nama</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="name"
                    placeholder="Masukan Prestasi">
            </div>
            <div class="mt-2 form-group">
                <label for="">Status</label>
                <select name="status" id="" class="form-control">
                    <option selected disabled>Pilih Kategori Prestasi</option>
                    <option value="1">Prestasi Siswa</option>
                    <option value="2">Prestasi Sekolah</option>
                </select>
            </div>
            <div class="mt-2 form-group">
                <label for="prestasi_kategori" class="form-label">Kategori Prestasi</label>
                <select name="prestasi_kategori[]" id="select2" class="form-control select2"  multiple data-placeholder="Pilih Kategori Prestasi">
                    <option value="">Pilih Kategori Prestasi</option>
                    @foreach ($kategoriPrestasi as $item)
                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mt-2 form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto">
                </div>
            </div>
            <div class="mt-2 form-group">
                <label for="">Deskripsi</label>
                 <div id="editor"></div>
                <textarea name="description" id="content-editor" style="display: none;"></textarea>
            </div>

            <div class="mt-2 mb-2 form-group">
                <a href="{{ route('dashboard.datasekolah.prestasi.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
                <button type="submit" class="btn btn-primary btn-sm float-lg-end">Submit</button>
            </div>
        </form>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>


<script>
    $(function () {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: "100%"
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
            // console.log($(".ql-editor").html());
        });


    });
</script>
@endpush
@endsection
