@extends('layouts.dashboard')
@section('title','Artikel')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    @include('layouts.flashmessage')
    <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="card-title mb-4">Artikel</h4>
            <a href="{{ route('dashboard.news.artikel.create') }}" class="btn btn-primary btn-sm float-right ">Tambah <i class="fas fa-plus"></i></a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="artikel_table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama / Judul</th>
                            <th>Kategori</th>
                            <th>View</th>
                            <th>Status</th>
                            @role('superadmin')
                                <th>Aksi Publish</th>
                            @endrole
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="artikel_data" value="{{ route('dashboard.news.artikel.getArtikel') }}">
@push('js')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script>
$(document).ready(function () {
    function reloadTable(id){
        var table = $(id).DataTable();
        table.cleanData;
        table.ajax.reload();
    }
    $('#artikel_table').DataTable({
        ordering: true,
        pagination: true,
        deferRender: true,
        serverSide: true,
        responsive: true,
        processing: true,
        stateSave: true,
        pageLength: 100,
        ajax: {
            'url': $('#artikel_data').val(),
        },
        columns: [
            { data: 'DT_RowIndex',name: 'DT_RowIndex',orderable: false,searchable: false},
            { data: 'name', name: 'name'},
            { data: 'kategori.name', name: 'kategori.name'},
            { data: 'jumlah_klik', name: 'jumlah_klik', orderable: true},
            {
                data: 'status', name: 'status',
                render: function (data) {
                    if (data == 'publish') {
                        return '<button class="btn btn-sm btn-success"><i class="bx bx-check"></i> Publish</button>';
                    } else {
                        return '<button class="btn btn-sm btn-warning"><i class="bx bx-time-five"></i> Pending</button>';
                    }
                }
            },
            @if (Auth::user()->hasRole('superadmin'))
            { data: 'button_action',name: 'button_action'},
            @endif
            { data: 'options',name: 'options', orderable: false, searchable: false }
        ],
    });
    $('#artikel_table').on('click', '#btn-delete', function () {
        var slug = $(this).data('id');
        var url = '{{ route("dashboard.news.artikel.destroy", ":slug") }}'; // Use the correct route name "destroy"
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
                                // window.location.href = "{{ route('dashboard.news.artikel.index') }}";
                                // Reload the page with a success message
                                // reload datatable
                                reloadTable('#artikel_table');

                            });
                        } else {
                            // Reload the page with an error message
                            swal('Error', data.message, 'error');
                            window.location.href = "{{ route('dashboard.news.artikel.index') }}";
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
