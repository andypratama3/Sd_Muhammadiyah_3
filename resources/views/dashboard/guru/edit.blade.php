@extends('layouts.dashboard')
@section('title', 'Edit guru')
@push('css')
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
<link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@endpush
@section('content')
    <div class="mb-4 card">
        @include('layouts.flashmessage')
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Edit guru {{ $guru->name }}</h6>
        </div>
        <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.guru.update', $guru->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $guru->slug }}">

            <div class="mt-2 form-group">
                <label for="karyawan_id">Guru</label>
                <select name="karyawan_id" id="karyawan_id" class="form-control select2 @error('karyawan_id') is-invalid @enderror">
                    @if ($guru->karyawan)
                        <option selected value="{{ $guru->karyawan_id }}">{{ $guru->karyawan->name }}</option>
                    @else
                        <option value="" selected>Pilih Guru</option>
                    @endif
                    @foreach ($karyawans as $karyawanOption)
                        @if ($guru->karyawan_id !== $karyawanOption->id)
                            <option value="{{ $karyawanOption->id }}">{{ $karyawanOption->name }}</option>
                        @endif
                    @endforeach
                </select>
                @error('karyawan_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-2 form-group">
                <label for="description">Deskripsi</label>
                <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" placeholder="Deskripsi" value="{{ old('description', $guru->description) }}">
                @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-2 form-group">
                <label for="lulusan">Lulusan</label>
                <input type="text" class="form-control @error('lulusan') is-invalid @enderror" id="lulusan" name="lulusan" placeholder="lulusan" value="{{ old('lulusan', $guru->lulusan) }}">
                @error('lulusan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-2 form-group">
                <label for="pelajarans">Pelajaran</label>
                <select name="pelajarans[]" id="pelajarans" multiple class="form-control select2 @error('pelajarans') is-invalid @enderror">
                    @php
                        $selectedPelajaranIds = $guru->pelajarans->pluck('id')->toArray();
                        $oldPelajaranIds = old('pelajarans', $selectedPelajaranIds);
                    @endphp
                    @foreach ($pelajarans as $pelajaran)
                        <option value="{{ $pelajaran->id }}" {{ in_array($pelajaran->id, $oldPelajaranIds) ? 'selected' : '' }}>{{ $pelajaran->name }}</option>
                    @endforeach
                </select>
                @error('pelajarans')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-2 mb-2 form-group">
                <label for="foto">Foto</label>
                <div class="custom-file">
                    <input type="file" class="form-control @error('foto') is-invalid @enderror" id="foto" name="foto" accept="image/*">
                </div>
                @error('foto')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="mt-3 text-center">
                    <h6 class="">Foto yang dipilih</h6>
                    <img src="{{ asset('storage/img/guru/'.$guru->foto) }}" id="output" alt="{{ $guru->name }}" style="width: 200px; height: 50%;">
                </div>
            </div>

            <a href="{{ route('dashboard.datasekolah.guru.index') }}" class="btn btn-danger btn-sm float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary btn-sm float-lg-end">Simpan</button>
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
