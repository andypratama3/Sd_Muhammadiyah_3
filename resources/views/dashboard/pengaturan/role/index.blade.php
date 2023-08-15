@extends('layouts.dashboard')
@section('title','Role')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-orange">
                @include('layouts.flashmessage')
                <div class="card-header border-0">
                    <div class="d-flex justify-content-between">
                         <!-- SEARCH FORM -->
                        <form class="form-inline ml-1" action="{{ route('dashboard.pengaturan.role.index') }}" method="GET">
                            <div class="input-group input-group-sm">
                                <input class="form-control form-control-navbar" type="text" id="search" name="search" placeholder="Nama role" aria-label="Nama role">
                                <div class="input-group-append">
                                    <button class="btn btn-navbar btn-default" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <a href="{{ route('dashboard.pengaturan.role.create') }}" class="ml-auto btn btn-primary btn-sm btn-flat text-bold text-light"><i class="fa fa-plus"></i> Tambah</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="card-body table-responsive p-0">
                        {{-- @include('layouts.flash-message') --}}
                        <table class="table table-hover" id="tabel">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Nama</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td>{{ ++$no }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        @can('edit-role')
                                        <a href="{{ route('dashboard.pengaturan.role.edit', $role->slug) }}" class="btn btn-primary btn-sm" title="Ubah"><i class="fa fa-edit"></i></a>
                                        @endcan
                                        @can('delete-role')
                                        <a href="#" data-id="{{ $role->slug }}" class="btn btn-danger btn-sm swal-delete" title="Hapus">
                                            <form action="{{ route('dashboard.pengaturan.role.destroy', $role->slug) }}" id="delete-{{ $role->slug }}" method="POST" enctype="multipart/form-data">
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
                        {{ $roles->onEachSide(1)->links() }}
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
@can('delete-role')
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
