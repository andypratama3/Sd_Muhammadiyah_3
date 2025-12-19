@extends('layouts.dashboard')
@section('title', 'Tambah Jadwal')
@push('css')
    <link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('asset_dashboard/vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}" rel="stylesheet">
@endpush
@section('content')
<div class="mb-4 card">
    @include('layouts.flashmessage')
    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Jadwal</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.jadwal.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mt-2 form-group">
                <label for="kelas">Kelas</label>
                <select name="kelas" id="kelas" class="form-control select2" data-placeholder="Pilih Kelas">
                    <option selected disabled>Pilih Kelas</option>
                    @foreach ($kelass as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mt-2 form-group">
                <label for="category_kelas">Kategori Kelas</label>
                <select name="category_kelas" id="category_kelas" class="form-control select2" data-placeholder="Pilih Kategori Kelas">
                    <option selected disabled>Pilih Kategori Kelas</option>
                    <option value="" class="option_category"></option>
                </select>
            </div>
            <div class="mt-2 form-group">
                <label for="tahun_ajaran">Tahun Ajaran</label>
                <select name="tahun_ajaran" id="tahun_ajaran" class="form-control select2">
                    <option selected disabled>Pilih Tahun Ajaran</option>
                    <?php $years = range(2010, strftime("%Y", time())); ?>
                    <?php foreach($years as $year) : ?>
                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                  <?php endforeach; ?>a>
                </select>
            </div>
            <div class="mt-2 mb-4 form-group">
                <label for="jadwal">Jadwal</label>
                <input type="file" class="form-control" name="jadwal" id="jadwal">
            </div>

            <div class="mt-2 mb-2 form-group">
                <label for="" class="text-center form-label">Jadwal</label>
                <table class="table table-bordered" id="dynamicJadwal">
                    <thead class="text-center">
                        <tr>
                            <th>Hari</th>
                            <th>Waktu</th>
                            <th>Mata Pelajaran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <select name="jadwal[0][hari]" class="form-control">
                                    <option value="">Pilih Hari</option>
                                    <option value="Senin">Senin</option>
                                    <option value="Selasa">Selasa</option>
                                    <option value="Rabu">Rabu</option>
                                    <option value="Kamis">Kamis</option>
                                    <option value="Jumat">Jumat</option>
                                    <option value="Sabtu">Sabtu</option>
                                </select>
                            </td>
                            <td>
                                <input type="time" name="jadwal[0][mulai]" class="mb-1 form-control">
                                <input type="time" name="jadwal[0][selesai]" class="form-control">
                            </td>
                            <td>
                                <input type="text" name="jadwal[0][mapel]" class="form-control"
                                    placeholder="Mata Pelajaran">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary" id="addJadwal">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <a  href="{{ route('dashboard.datasekolah.jadwal.index') }}" class="btn btn-sm btn-danger float-lg-start">Kembali</a>
            <button type="submit" class="btn btn-sm btn-primary float-lg-end">Submit</button>
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


        let i = 0;

        $('#addJadwal').click(function () {
            i++;
            $('#dynamicJadwal tbody').append(`
                <tr>
                    <td>
                        <select name="jadwal[`+i+`][hari]" class="form-control">
                            <option value="">Pilih Hari</option>
                            <option value="Senin">Senin</option>
                            <option value="Selasa">Selasa</option>
                            <option value="Rabu">Rabu</option>
                            <option value="Kamis">Kamis</option>
                            <option value="Jumat">Jumat</option>
                            <option value="Sabtu">Sabtu</option>
                        </select>
                    </td>
                    <td>
                        <input type="time" name="jadwal[`+i+`][mulai]" class="mb-1 form-control">
                        <input type="time" name="jadwal[`+i+`][selesai]" class="form-control">
                    </td>
                    <td>
                        <input type="text" name="jadwal[`+i+`][mapel]" class="form-control"
                            placeholder="Mata Pelajaran">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger removeJadwal">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });

        $(document).on('click', '.removeJadwal', function () {
            $(this).closest('tr').remove();
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
