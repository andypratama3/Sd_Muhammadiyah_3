@extends('layouts.dashboard')
@section('title', 'Webhook')
@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.css" />
@endpush
@section('content')
<div class="col-lg-12 grid-margin stretch-card">
    <div class="card">
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title">Webhook WhatsApp</h4>
        </div>
        <div class="card-body">
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="">Kelas</label>
                    <select name="" id="" class="form-control">
                        <option value="">Pilih Kelas</option>
                        <option value="">1</option>
                        <option value="">2</option>
                        <option value="">3</option>
                        <option value="">4</option>
                    </select>
                </div>
            </div>
            <div class="mt-4 table-responsive">
                <table class="table mt-4 w-100" id="webhookTable" >
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>ID</th>
                            <th>Nomor Pesan</th>
                            <th>Nama</th>
                            <th>Tipe</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for showing WhatsApp message details -->
<div class="modal fade" id="messageDetailModal" tabindex="-1" role="dialog" aria-labelledby="messageDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="messageDetailModalLabel">WhatsApp Message Detail</h5>
        {{-- <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button> --}}
      </div>
      <div class="modal-body">
        <div id="modalLoading" class="text-center">
          <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
          </div>
        </div>
        <div id="modalContent" style="display: none;">
          <div class="mb-3 row">
            <div class="col-md-6">
              <strong>Message ID:</strong>
              <p id="detailMessageId"></p>
            </div>
            <div class="col-md-6">
              <strong>Phone Number:</strong>
              <p id="detailPhone"></p>
            </div>
          </div>
          <div class="mb-3 row">
            <div class="col-md-6">
              <strong>Profile Name:</strong>
              <p id="detailProfileName"></p>
            </div>
            <div class="col-md-6">
              <strong>Type:</strong>
              <p id="detailType"></p>
            </div>
          </div>
          <div class="mb-3 row">
            <div class="col-md-6">
              <strong>Status:</strong>
              <p id="detailStatus"></p>
            </div>
            <div class="col-md-6">
              <strong>Created At:</strong>
              <p id="detailCreatedAt"></p>
            </div>
          </div>
          <div class="mb-3 row">
            <div class="col-12">
              <strong>Message Content:</strong>
              <pre id="detailContent" style="background-color: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 300px; overflow-y: auto;"></pre>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

@push('js')
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
    <script type="text/javascript"
        src="https://cdn.datatables.net/v/dt/jqc-1.12.3/dt-1.10.16/b-1.4.2/b-html5-1.4.2/datatables.min.js"></script>
    <script>
        $(document).ready(function() {
            function reloadTable(id) {
                var table = $(id).DataTable();
                table.ajax.reload();
            }

            $('#webhookTable').DataTable({
                ordering: true,
                pagination: true,
                deferRender: true,
                serverSide: true,
                responsive: true,
                processing: true,
                pageLength: 100,
                ajax: {
                    'url': "{{ route('dashboard.monitoring.whatsapp.data') }}",
                    'data': function(d) {
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        ordering: false
                    },
                    {
                        data: 'message_id',
                        name: 'message_id'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'profile_name',
                        name: 'profile_name'
                    },
                    {
                        data: 'type',
                        name: 'type',
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
            });

            // Handle show data button click
            $('#webhookTable').on('click', '.btn-show-data', function(e) {
                e.preventDefault();
                const dataUrl = $(this).attr('data-url');

                // Show loading state
                $('#modalLoading').show();
                $('#modalContent').hide();
                $('#messageDetailModal').modal('show');

                // Fetch the data
                $.ajax({
                    url: dataUrl,
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {

                        const data = response.data;

                        // Populate modal with data
                        $('#detailMessageId').text(data.message_id || '-');
                        $('#detailPhone').text(data.phone || '-');
                        $('#detailProfileName').text(data.profile_name || '-');
                        $('#detailType').text(data.type ? data.type.charAt(0).toUpperCase() + data.type.slice(1) : '-');
                        $('#detailStatus').text(data.status || '-');
                        $('#detailCreatedAt').text(data.created_at || '-');

                        // Parse and format JSON content
                        try {
                            const content = typeof data.content === 'string' ? JSON.parse(data.content) : data.content;
                            $('#detailContent').text(JSON.stringify(content, null, 2));
                        } catch (e) {
                            $('#detailContent').text(data.content || '-');
                        }

                        // Hide loading and show content
                        $('#modalLoading').hide();
                        $('#modalContent').show();
                    },
                    error: function(error) {
                        console.error('Error loading data:', error);
                        Swal.fire('Error', 'Failed to load message details', 'error');
                        $('#messageDetailModal').modal('hide');
                    }
                });
            });

            // Handle delete button click
        });
    </script>
@endpush
@endsection
