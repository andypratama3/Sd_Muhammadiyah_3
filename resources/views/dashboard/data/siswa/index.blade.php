@extends('layouts.dashboard')
@section('title','Siswa')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">

@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    @include('layouts.flashmessage')
    <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="card-title">Siswa</h4>
            <a href="{{ route('dashboard.datamaster.siswa.create') }}" class="btn btn-success btn-sm float-end">Tambah <i class="fas fa-plus"></i></a>
        </div>
        <div class="card-body">
            <div class="row mb-2">
                <div class="col-md-2">
                    <div class="form-group">
                        <div class="gap-4">
                            <a href="{{ route('siswa.export_excel') }}" class="btn btn-success btn-sm "><i class="fas fa-file-excel"></i> Export Excel</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 ">
                    <div class="form-group">
                        <div class="input-group gap-3">
                            <select name="kelas" id="kelas" class="form-control">
                                <option selected disabled>Pilih Kelas</option>
                                @foreach ($kelass as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->name }}</option>
                                @endforeach
                            </select>
                            <select name="category_kelas" id="category_kelas" class="form-control me-2">
                                <option selected disabled>Pilih Kategori Kelas</option>
                            </select>
                            <button class="btn btn-success btn-sm" id="export-data-kelas-excel"><i class="fas fa-file-excel"></i> Export Excel Perkelas</button>
                                <!-- Hidden form for exporting -->
                                <form action="{{ route('siswa.export_excel_kelas') }}" method="POST" id="exportForm" style="display: none;">
                                    @csrf
                                    <input type="hidden" name="kelas_id" id="export_kelas_id">
                                    <input type="hidden" name="category" id="export_category">
                                </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive mt-2">
                <table class="table mt-4" id="siswa_table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Nisn</th>
                            <th>Kelas</th>
                            <th>Ketagori Kelas</th>
                            {{-- <th>Tanggal Masuk</th>  --}}
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="siswa_data" value="{{ route('siswa.get.records') }}">
@push('js')
<script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>

<script>
$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap4',
    });
    $('#siswa_table').DataTable({
        ordering: true,
        pagination: true,
        deferRender: true,
        serverSide: true,
        responsive: true,
        processing: true,
        pageLength: 100,
        ajax: {
            'url': $('#siswa_data').val(),
            'data': function (data) {
                data.kelas = $('#kelas').val();
                data.category_kelas = $('#category_kelas').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex',name: 'DT_RowIndex',orderable: false,searchable: false},
            { data: 'name', name: 'name'},
            { data: 'nisn', name: 'nisn'},
            { data: 'kelas.name', name: 'kelas.name'},
            { data: 'kelas.category', name: 'kelas.category'},
            { data: 'options',name: 'options', orderable: false, searchable: false }
        ],
    });
    $('#siswa_table').on('click', '#btn-delete', function () {
        var slug = $(this).data('id');
        var url = '{{ route("dashboard.datamaster.siswa.destroy", ":slug") }}'; // Use the correct route name "destroy"
        url = url.replace(':slug', slug);
        Swal.fire({
            title: 'Anda yakin?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Tidak, Batalkan!',
            reverseButtons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete.isConfirmed) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                // Send a DELETE request
                $.ajax({
                    url: url,
                    type: 'DELETE', // Use the DELETE method
                    success: function (data) {
                        if (data.status === 'success') {
                            Swal.fire('Berhasil', data.message, 'success').then(() => {
                                // Reload the page
                                // window.location.href = "{{ route('dashboard.datamaster.siswa.index') }}";
                                // Reload the page with a success message
                                // reload datatable
                                reloadTable('#siswa_table');
                            });
                        } else {
                            // Reload the page with an error message
                            swal('Error', data.message, 'error');
                            window.location.href = "{{ route('dashboard.datamaster.siswa.index') }}";
                        }
                    },
                });
            } else {
                // If the user cancels the deletion, do nothing
            }
        });
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
                    categoryKelasDropdown.append('<option data-id="' +
                        category + '" value="' + category + '">' +
                        category + '</option>');
                });
            },
            error: function (error) {
                console.log(error);
            }
        });
    });
    $('#kelas').on('change', function () {
        reloadTable('#siswa_table');
    });
    $('#category_kelas').on('change', function () {
        reloadTable('#siswa_table');
    });
    $('#export-data-kelas-excel').click(function (e) {
        e.preventDefault();
        let kelas = $('#kelas').val();
        let category_kelas = $('#category_kelas').val();
        $('#export_kelas_id').val(kelas);
        $('#export_category').val(category_kelas);

        // Submit the form
        $('#exportForm').submit();
    });
});
</script>
@endpush
@endsection
