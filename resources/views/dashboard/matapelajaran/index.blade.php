@extends('layouts.dashboard')
@section('title','Mata Pelajaran')
@section('content')
<div class="col-lg-12 mb-4">
    <div class="card">
        @include('layouts.flashmessage')
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h4 class="m-0 font-weight-bold text-center">Mata Pelajaran</h5>
                <a href="{{ route('dashboard.datasekolah.matapelajaran.create') }}" class="btn btn-success btn-sm float-right">Tambah <i class="fas fa-plus"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table align-items-center table-flush text-center">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($matapelajarans as $matapelajaran)
                    <tr>
                        <td>{{ ++$no }}</td>
                        <td>{{ $matapelajaran->name }}</td>
                        <td>
                            <a href="{{ route('dashboard.datasekolah.matapelajaran.edit', $matapelajaran->slug) }}"
                                class="btn btn-primary btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>

                            <a href="#" data-id="{{ $matapelajaran->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                <form action="{{ route('dashboard.datasekolah.matapelajaran.destroy', $matapelajaran->slug) }}"
                                    id="delete-{{ $matapelajaran->slug }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    @method('delete')
                                </form>
                                <i class="fas fa-trash"></i>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer clearfix">
            <ul class="m-0 float-left">
                {{-- <span class="badge badge-primary">Total : {{ $count }} Data</span> --}}
            </ul>
            <ul class="pagination m-0 float-right">
                {{ $matapelajarans->onEachSide(1)->links() }}
            </ul>
        </div>
        <div class="card-footer"></div>
    </div>
</div>
@endsection
