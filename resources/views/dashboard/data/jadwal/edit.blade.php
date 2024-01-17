@extends('layouts.dashboard')
@section('title', 'Tambah Jadwal')
@push('css')
    <link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="card mb-4">
    @include('layouts.flashmessage')
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Jadwal</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.jadwal.update', $jadwal->slug) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="slug" value="{{ $jadwal->slug }}">
            <div class="form-group">
                <label for="kelas">Kelas</label>
                <select name="kelas" id="kelas" class="form-control select2" data-placeholder="Pilih Kelas">
                    <option value="{{ $jadwal->kelas }}">{{ $jadwal->kelas_jadwal->name }}</option>
                    @foreach ($kelass as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="category_kelas">Kategori Kelas</label>
                <select name="category_kelas" id="category_kelas" class="form-control select2" data-placeholder="Pilih Kategori Kelas">
                    <option selected value="{{ $jadwal->category_kelas }}">{{ $jadwal->category_kelas }}</option>
                    <option value="" class="option_category"></option>
                </select>
            </div>
            <div class="form-group">
                <label for="tahun_ajaran">Tahun Ajaran</label>
                <select name="tahun_ajaran" id="tahun_ajaran" class="form-control select2">
                    <option selected value="{{ $jadwal->tahun_ajaran }}">{{ $jadwal->tahun_ajaran }}</option>
                    <?php $years = range(2010, strftime("%Y", time())); ?>
                    <?php foreach($years as $year) : ?>
                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                  <?php endforeach; ?>a>
                </select>
            </div>
            <div class="form-group">
                <label for="jadwal">Jadwal</label>
                <input type="file" class="form-control" name="jadwal" id="jadwal" value="{{ $jadwal->jadwal }}">
            </div>

            <a  href="{{ route('dashboard.datasekolah.jadwal.index') }}" class="btn btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-primary float-lg-right">Submit</button>
        </form>
    </div>
</div>
@push('js')
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<script>
    $(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
        $('#kelas').on('change', function () {
            var selectedKelasId = $('#kelas').val();
            var categoryKelasDropdown = $('#category_kelas');
            //clear area dropdown
            categoryKelasDropdown.empty();
            //add option for category_kelas
            categoryKelasDropdown.append('<option selected disabled>Pilih Kategori Kelas</option>');
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: '{{ route("dashboard.datasekolah.jadwal.kelas_category") }}',
                method: 'POST',
                data: {
                    id: selectedKelasId
                },
                success: function (response) {
                    let data_category = response.categoryKelas
                    $.each(response, function (index, category) {
                        categoryKelasDropdown.append('<option data-id="' + category  +'" value="' + category + '">' + category + '</option>');
                    });
                },
                error: function (error) {
                    console.log(error);
                }
            });
        });
        $('#category_kelas').on('change',function (e) {
            $('select#smester').load(location.href + ' select#smester > option');
            let kelas = $('#kelas').val();
            let category_kelas = $(this).children('option:selected').data('id');

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "POST",
                url: "{{ route('dashboard.datasekolah.jadwal.getSmester') }}",
                data: {
                    category_kelas : category_kelas,
                    kelas   : kelas,
                },
                success: function (response) {
                    let smester = response.smester;
                    if (response.genap) {
                        $('#smester option[value="genap"]').text('Genap (Sudah Terdata)').prop('disabled', true);

                    }
                    if (response.ganjil) {
                        $('#smester option[value="ganjil"]').text('Ganjil (Sudah Terdata)').prop('disabled', true);

                    }
                }

            });
        });

    });

</script>
@endpush
@endsection
