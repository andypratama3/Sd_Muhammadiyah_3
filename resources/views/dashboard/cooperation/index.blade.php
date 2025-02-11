@extends('layouts.dashboard')
@section('title','Kerja Sama')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />

@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="m-0 font-weight-bold text-center">Kerja Sama</h4>
            <a href="{{ route('dashboard.datasekolah.cooperation.create') }}" class="btn btn-primary btn-sm float-right">Tambah <i
                    class="fas fa-plus"></i></a>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="cooperation_table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Foto</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<!--Row-->
<div id="myModal" class="modal">

    <!-- The Close Button -->
    <span class="closeheader">&times;</span>

    <!-- Modal Content (The Image) -->
    <img class="modal-content" id="foto" src="">

    <!-- Modal Caption (Image Text) -->
    <div id="caption"></div>
</div>
<input type="hidden" id="coopeation_table" value="{{ route('dashboard.datasekolah.cooperation.data') }}">
@push('js')
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script>
$(document).ready(function () {
    $('#cooperation_table').DataTable({
        ordering: true,
        pagination: true,
        deferRender: true,
        serverSide: true,
        responsive: true,
        processing: true,
        pageLength: 100,
        ajax: {
            'url': $('#coopeation_table').val(),
        },
        columns: [
            { data: 'DT_RowIndex',name: 'DT_RowIndex',orderable: false,searchable: false},
            { data: 'name', name: 'name'},
            { data: 'foto', name: 'foto', orderable: false, searchable: false },
            { data: 'options',name: 'options', orderable: false, searchable: false }
        ],
    });

    $('#cooperation_table').on('click', '#btn-delete', function () {
        var slug = $(this).data('id');
        var url = '{{ route("dashboard.datasekolah.cooperation.destroy", ":slug") }}';
        url = url.replace(':slug', slug);
        Swal.fire({
            title: 'Anda yakin?',
            text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus Data",
            denyButtonText: 'Tidak, Batalkan!',
            reverseButtons: true,
            confirmButtonColor: '#d33',
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
                            reloadTable('#cooperation_table');
                            Swal.fire('Berhasil', data.message, 'success');
                        } else {
                            window.location.href = "{{ route('dashboard.datasekolah.cooperation.index') }}";
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
