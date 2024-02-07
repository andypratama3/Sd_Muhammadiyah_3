@extends('layouts.dashboard')
@section('title','Siswa')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.css"/>
@endpush
@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        @include('layouts.flashmessage')
        <div class="card">
            <div class="card-body">
                <h4 class="card-title text-primary mb-4"> Siswa
                <a href="{{ route('dashboard.datamaster.siswa.create') }}" class="btn btn-success btn-sm float-right">Tambah <i class="fas fa-plus"></i></a>
                </h4>
                <div class="table-responsive">
                    <table class="table mt-4" id="siswa_table">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Nisn</th>
                                <!-- <th>Kelas</th> -->
                                <!-- <th>Ketagori Kelas</th>
                                <th>Tanggal Masuk</th> -->
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
{{-- <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script> --}}
{{-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script> --}}
{{-- <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script> --}}
{{-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/pdfmake.min.js"></script> --}}
{{-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.32/vfs_fonts.js"></script> --}}
{{-- <script type="text/javascript" src="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.js"></script> --}}
<script>
$(document).ready(function () {
    $('#siswa_table').DataTable({
        dom: 'Bfrtip',
        buttons: [
            { extend: 'copyHtml5', text: '<i class="fa-solid fa-copy mr-1"></i> Copy', className: 'btn btn-danger' },
            { extend: 'excelHtml5', text: '<i class="fa-solid fa-file-excel mr-1"></i> Excel', className: 'btn btn-success'},
            // { extend: 'csvHtml5', text: 'CSV', className: 'btn btn-'},
            { extend: 'pdfHtml5', text: '<i class="fa-solid fa-file-pdf mr-1"></i> PDF', className: 'btn btn-warning'},
        ],
        initComplete: function() {
            var btns = $('.dt-button');
            btns.removeClass('dt-button');
        },
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
