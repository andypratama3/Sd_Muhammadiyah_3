@extends('layouts.dashboard')
@section('title', 'Edit Prestasi')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
@endpush
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Edit Prestasi</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.prestasi.update', $prestasi->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $prestasi->slug }}">
            <div class="mt-2 form-group">
                <label class="form-label" for="name">Nama</label>
                <input type="text" class="form-control" name="name" id="name" aria-describedby="name"
                    placeholder="Masukan Prestasi" value="{{ $prestasi->name }}">
            </div>
            <div class="mt-2 form-group">
                <label class="form-label" for="">Status</label>
                <select name="status" id="" class="form-control">
                    <option selected value="{{ $prestasi->status }}">{{ ($prestasi->status == 1 ) ? 'Prestasi Siswa' : 'Prestasi Sekolah'  }}</option>
                    <option value="1">Prestasi Siswa</option>
                    <option value="2">Prestasi Sekolah</option>
                </select>
            </div>
             <div class="mt-2 form-group">
                <label class="form-label" for="prestasi_kategori" class="form-label">Kategori Prestasi</label>
                <select name="prestasi_kategori[]" class="form-control select2" multiple>
                    @foreach ($kategoriPrestasi as $item)
                        <option value="{{ $item->id }}"
                            {{ $prestasi->prestasi_kategori->contains('id', $item->id) ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mt-2 form-group">
                <label class="form-label" for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" value="{{ $prestasi->foto }}">
                </div>
                <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto) }}" alt="" srcset="" style="width: 100%; margin-top: 2rem;">
            </div>
            <div class="mt-2 form-group">
                <label class="form-label" for="">Deskripsi</label>
                 <div id="editor">{!! $prestasi->description !!}</div>
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
