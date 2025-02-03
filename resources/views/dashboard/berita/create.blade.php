@extends('layouts.dashboard')
@section('title', 'Tambah Berita')
@push('css')
        <link href="https://cdn.bootcdn.net/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet" />
@endpush
@section('content')
<div class="card mb-4">
    <div class="row">
        @include('layouts.flashmessage')
    </div>
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Berita</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.news.berita.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group mt-2">
                <label for="judul">Judul</label>
                <input type="text" class="form-control" name="judul" id="judul" value="{{ old('judul') }}" placeholder="Masukan Judul">
            </div>
            <div class="form-group mt-2 ">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/jpeg,image/png,application/pdf,image" value="{{ old('foto') }}" onchange="document.getElementById('output').src = window.URL.createObjectURL(this.files[0])">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="" id="output" alt="" style="width: 200px; height: 50%;">
                </div>
            </div>

            <div class="form-group mt-2 mb-2">
                <label for="">Deskripsi</label>
                <div id="editor"></div>
                <textarea name="desc" id="content-editor" style="display: none;"></textarea>

            </div>

            <a  href="{{ route('dashboard.news.berita.index') }}" class="btn btn-danger btn-sm">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-end btn-sm">Submit</button>
        </form>
    </div>
</div>
@push('js')
<script defer src="https://cdn.bootcdn.net/ajax/libs/quill/1.3.7/quill.min.js"></script>
<script defer src="https://unpkg.com/quill-resize-image@1.0.5/dist/quill-resize-image.min.js"></script>
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


    $(document).ready(function () {
            Quill.register("modules/resize", window.QuillResizeImage);

            const toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': 1 }, { 'header': 2 }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'font': [] }],
                [{ 'align': [] }],
                ['link', 'image', 'video'],
                ['clean']
            ];

            var quill = new Quill('#editor', {
                modules: {
                    toolbar: {
                        container: toolbarOptions,
                        handlers: {
                            image: function () {
                                selectLocalImage();
                            }
                        }
                    },
                    resize: {
                        locale: {},
                    },

                },
                theme: 'snow'
            });


            quill.on('text-change', function (delta, oldDelta, source) {
                $('#content-editor').text($('.ql-editor').html());
            });

            function selectLocalImage() {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();

                input.onchange = async () => {
                    const file = input.files[0];
                    if (file) {
                        const formData = new FormData();
                        formData.append('gambar_upload', file);

                        try {
                            const response = await fetch('{{ route('dashboard.news.berita.uploadImage') }}', {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                },
                            });

                            if (!response.ok) {
                                throw new Error(`HTTP error! status: ${response.status}`);
                            }

                            const data = await response.json();
                            if (data.status === 'success') {
                                const range = quill.getSelection();
                                quill.insertEmbed(range.index, 'image', data.url);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil',
                                    text: data.message,
                                    showConfirmButton: false,
                                    timer: 1500,
                                });
                            } else {
                                alert(data.message || 'Gagal mengunggah gambar');
                            }
                        } catch (error) {
                            console.error('Error uploading image:', error);
                            alert('Terjadi kesalahan saat mengunggah gambar.');
                        }
                    }
                };
            }

        });
</script>
@endpush
@endsection
