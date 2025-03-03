@extends('layouts.dashboard')
@section('title','Notifikasi')
@push('css')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    @include('layouts.flashmessage')
    <div class="card">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="card-title mb-4">Notifikasi</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="notifikasi_table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Keterangan</th>
                            <th>Deskripsi</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="artikel_data" value="{{ route('dashboard.notifikasi.data') }}">
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
    $('#notifikasi_table').DataTable({
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
            { data: 'causer_type', name: 'causer_type'},
            { data: 'description', name: 'description'},
            { data: 'created_at', name: 'created_at'},
        ],
    });
});
</script>
@endpush
@endsection
