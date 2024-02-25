@extends('layouts.dashboard')
@section('title','Siswa')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
<link href="{{ asset('asset_dashboard/vendor/select2/dist/css/select2.css') }}" rel="stylesheet" type="text/css">

@endpush
@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        @include('layouts.flashmessage')
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-4">Siswa<a href="{{ route('dashboard.datamaster.siswa.create') }}" class="btn btn-success btn-sm float-right">Tambah <i class="fas fa-plus"></i></a></h4>
                <div class="row">
                    <div class="col-md-12 mx-3">
                        <div class="form-group row">
                            <div class="gap-4">
                                {{-- <a href="{{ route('siswa.export_excel') }}" class="btn btn-warning btn-sm"><i class="fas fa-file-pdf"></i> Export PDF</a> --}}
                                <a href="{{ route('siswa.export_excel') }}" class="btn btn-success btn-sm"><i class="fas fa-file-excel"></i> Export Excel</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
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
        swal({
            title: 'Anda yakin?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
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
                            swal('Berhasil', data.message, 'success').then(() => {
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
});
</script>
@endpush
@endsection
