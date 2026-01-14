@extends('layouts.dashboard')
@section('title','Role')
@section('content')

<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-orange">
                @include('layouts.flashmessage')
                <div class="border-0 card-header">
                    <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
                        <div class="form-group">
                            <form class="ml-1 form-inline" action="{{ route('dashboard.pengaturan.role.index') }}" method="GET">
                                <div class="input-group input-group-sm">
                                    <input class="form-control form-control-navbar" type="text" id="search" name="search" placeholder="Nama role" aria-label="Nama role">
                                    <div class="input-group-append">
                                        <button class="btn btn-navbar btn-default" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <a href="{{ route('dashboard.pengaturan.role.create') }}" class="ml-auto btn btn-primary btn-sm "><i class="fa fa-plus"></i> Tambah</a>
                    </div>

                </div>
                <div class="card-body">
                    <div class="p-0 card-body table-responsive">
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
                                        @can('manage-pengaturan')
                                        <a href="{{ route('dashboard.pengaturan.role.edit', $role->id) }}" class="btn btn-primary btn-sm" title="Ubah"><i class="fa fa-edit"></i></a>
                                        @endcan
                                        @can('manage-pengaturan')
                                        <a href="#" data-id="{{ $role->id }}" class="btn btn-danger btn-sm swal-delete" title="Hapus">
                                            <form action="{{ route('dashboard.pengaturan.role.destroy', $role->id) }}" id="delete-{{ $role->id }}" method="POST" enctype="multipart/form-data">
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
                <div class="clearfix card-footer">
                    <ul class="float-left m-0">
                        Jumlah Data: {{ $count }}
                    </ul>
                    <ul class="float-right m-0 pagination pagination-sm">
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
