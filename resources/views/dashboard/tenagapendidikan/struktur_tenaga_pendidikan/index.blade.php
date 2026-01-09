@extends('layouts.dashboard')
@section('title','Struktur Tenaga Pendidikan')
@section('content')
<div class="mb-4 col-lg-12">
    <div class="card">
        @include('layouts.flashmessage')
        <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
            <h4 class="m-0 text-center font-weight-bold">Struktur Tenaga Pendidikan</h5>
                <a href="{{ route('dashboard.datasekolah.struktur.tenaga.pendidikan.create') }}" class="float-right btn btn-success btn-sm">Tambah <i class="fas fa-plus"></i></a>
        </div>
        <div class="table-responsive">
            <table class="table text-center align-items-center table-flush">
                <thead class="thead-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Parent</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($strukturTenagaPendidikan as $struktur)
                    <tr>
                        <td>{{ ++$no }}</td>
                        <td>{{ $struktur->name }}</td>
                        <td>{{ $struktur->struktur_tenaga_pendidikan->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('dashboard.datasekolah.struktur.tenaga.pendidikan.edit', $struktur->slug) }}"
                                class="btn btn-primary btn-sm">
                                <i class="fas fa-pen"></i>
                            </a>

                            <a href="#" data-id="{{ $struktur->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                <form action="{{ route('dashboard.datasekolah.struktur.tenaga.pendidikan.destroy', $struktur->slug) }}"
                                    id="delete-{{ $struktur->slug }}" method="POST" enctype="multipart/form-data">
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
        <div class="clearfix card-footer">
            <ul class="float-left m-0">
                {{-- <span class="badge badge-primary">Total : {{ $count }} Data</span> --}}
            </ul>
            <ul class="float-right m-0 pagination">
                {{ $strukturTenagaPendidikan    ->onEachSide(1)->links() }}
            </ul>
        </div>
        <div class="card-footer"></div>
    </div>
</div>
@endsection
