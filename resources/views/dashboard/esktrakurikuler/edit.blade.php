@extends('layouts.dashboard')
@section('title', 'Edit Ekstrakurikuler')
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.ekstrakurikuler.update',$ekstrakurikuler->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $ekstrakurikuler->slug }}">
            <div class="mt-2 form-group">
                <label for="name">Nama Fasilitas</label>
                <input type="text" class="form-control" name="name" id="name" value="{{ $ekstrakurikuler->name }}" placeholder="Masukan name">
            </div>
            <div class="mt-2 form-group">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" value="{{ $ekstrakurikuler->desc }}" name="desc"
                    placeholder="Deskripsi">
            </div>
            <div class="mt-2 form-group">
                <label for="kategori">Kategori</label>
                <select name="kategori" id="kategori" class="form-control">
                    <option value="">Pilih Kategori</option>
                    <option value="seni" {{ old('kategori', $ekstrakurikuler->kategori) == 'seni' ? 'selected' : '' }}>Seni</option>
                    <option value="olahraga" {{ old('kategori', $ekstrakurikuler->kategori) == 'olahraga' ? 'selected' : '' }}>Olahraga</option>
                    <option value="sains" {{ old('kategori', $ekstrakurikuler->kategori) == 'sains' ? 'selected' : '' }}>Sains</option>
                    <option value="islami" {{ old('kategori', $ekstrakurikuler->kategori) == 'islami' ? 'selected' : '' }}>Islami</option>
                    <option value="lainnya" {{ old('kategori', $ekstrakurikuler->kategori) == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div class="mt-2 form-group">
                <label for="jam">Jam</label>
                <input type="time" class="form-control" id="jam" name="jam" placeholder="Masukan jam" value="{{ old('jam', $ekstrakurikuler->jam) }}">
            </div>
            <div class="mt-2 form-group">
                <label for="guru">Guru</label>
                <input type="text" class="form-control" id="guru" name="guru" placeholder="Masukan guru" value="{{ old('guru', $ekstrakurikuler->guru) }}">
            </div>
            <div class="mt-2 form-group">
                <label for="kelas">Kelas</label>
                <input type="text" class="form-control" id="kelas" name="kelas" placeholder="Masukan kelas" value="{{ old('kelas', $ekstrakurikuler->kelas) }}">
            </div>
            <div class="mt-2 mb-2 form-group">
                <label for="">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control" id="foto" name="foto" accept="image/*"
                        onchange="loadPreview(this)">
                </div>
                <div class="mt-3 text-center">
                    <h6 class="">Poto yang di pilih</h6>
                    <img src="{{ asset('storage/img/ekstrakurikuler/'.$ekstrakurikuler->foto) }}" id="output" alt=""
                        style="width: 200px; height: 50%;">
                </div>
            </div>
            <a href="{{ route('dashboard.datasekolah.ekstrakurikuler.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
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
