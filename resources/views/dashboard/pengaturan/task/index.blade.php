@extends('layouts.dashboard')
@section('title','Task')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-orange">
                @include('layouts.flashmessage')
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <div class="d-flex justify-content-between">
                            <form class="form-inline ml-1" action="{{ route('dashboard.pengaturan.task.index') }}" method="GET">
                                <div class="input-group input-group-sm">
                                    <input class="form-control form-control-navbar" type="text" id="search" name="search" placeholder="Nama Task" aria-label="Nama Task">
                                    <div class="input-group-append">
                                        <button class="btn btn-navbar btn-default" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                             </form>
                         <!-- SEARCH FORM -->
                    </div>
                    @can('create-task')
                    <a href="{{ route('dashboard.pengaturan.task.create') }}" class="btn btn-primary btn-sm float-end"><i class="fa fa-plus"></i> Tambah</a>
                @endcan
                </div>
                <div class="card-body">
                    <div class="card-body table-responsive p-0">
                        <table class="table table-hover" id="tabel">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($tasks as $task)
                                <tr>
                                    <td>{{ ++$no }}</td>
                                    <td>{{ $task->name }}</td>
                                    <td>
                                        <a href="{{ route('dashboard.pengaturan.task.show', $task->slug) }}" class="btn btn-primary btn-sm" title="Ubah"><i class="fa fa-eye"></i></a>
                                        @can('edit-task')
                                        <a href="{{ route('dashboard.pengaturan.task.edit', $task->slug) }}" class="btn btn-primary btn-sm" title="Ubah"><i class="fa fa-edit"></i></a>
                                        @endcan
                                        @can('delete-task')
                                        <a href="#" data-id="{{ $task->slug }}" class="btn btn-danger btn-sm swal-delete" title="Hapus">
                                            <form action="{{ route('dashboard.pengaturan.task.destroy', $task->slug) }}" id="delete-{{ $task->slug }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('delete')
                                            </form>
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        @endcan
                                    </td>
                                </tr>
                                @empty
                                <td class="text-center" colspan="3">
                                    <strong>0 Data Found</strong>
                                </td>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">
                    <ul class="m-0 float-left">
                        Jumlah Data: {{ $count }}
                    </ul>
                    <ul class="pagination pagination-sm m-0 float-right">
                        {{ $tasks->onEachSide(1)->links() }}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
@can('delete-task')
    <script>
        $(".swal-delete").click(function (e) {
            slug = e.target.dataset.id;
            swal({
                    title: 'Anda yakin?',
                    text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $(`#delete-${slug}`).submit();
                    } else {
                        // Do Nothing
                    }
                });
        });
    </script>
@endcan
@endpush
@endsection
