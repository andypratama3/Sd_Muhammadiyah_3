@extends('layouts.dashboard')
@section('title','Jadwal')
@section('content')

<div class="row">
    <div class="mb-4 col-lg-12">
        <!-- Simple Tables -->
        <div class="card">
            @include('layouts.flashmessage')
            <div class="flex-row py-3 card-header d-flex align-items-center justify-content-between">
                <h4 class="m-0 text-center font-weight-bold text-primary">Data Jadwal</h5>
                    <a href="{{ route('dashboard.datasekolah.jadwal.create') }}" class="float-right btn btn-success btn-sm">Tambah <i class="fas fa-plus"></i></a>
            </div>
            <div class="table-responsive">
                <table class="table text-center align-items-center table-flush">
                    <thead class="thead-light">
                        <tr>
                            <th>No</th>
                            <th>Tahun</th>
                            <th>Jadwal</th>
                            <th>Kelas</th>
                            <th>Kategori Kelas</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jadwals as $jadwal)
                        <tr>
                            <td>{{ ++$no }}</td>
                            <td> {{ $jadwal->tahun_ajaran }} </td>
                            <td><a href="{{ asset('storage/file/jadwal/'. $jadwal->jadwal) }}">Lihat Jadwal</a></td>
                            <td> {{ $jadwal->kelas_jadwal->name ?? '-' }} </td>
                            <td> {{ $jadwal->category_kelas }} </td>
                            <td>
                                <a href="{{ route('dashboard.datasekolah.jadwal.show', $jadwal->id) }}" class="btn btn-dark btn-sm"><i
                                        class="fas fa-info-circle"></i></a>
                                <a href="{{ route('dashboard.datasekolah.jadwal.edit', $jadwal->id) }}" class="btn btn-primary btn-sm"><i
                                        class="fa fa-pen"></i></a>
                                <a href="#" data-id="{{ $jadwal->slug }}" class="btn btn-danger btn-sm delete" title="Hapus">
                                    <form action="{{ route('dashboard.datasekolah.jadwal.destroy', $jadwal->id) }}"
                                        id="delete-{{ $jadwal->slug }}" method="POST" enctype="multipart/form-data">
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
        </div>
    </div>
</div>

@endsection
