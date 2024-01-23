@extends('layouts.dashboard')
@section('title', 'Detail Berita')
@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('dashboard.datasekolah.guru.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label for="name">Nama</label>
                <input type="text" class="form-control" id="name" value="{{ $guru->name }}" readonly>
            </div>
            <div class="form-group">
                <label for="">Deskripsi</label>
                <input type="text" class="form-control" id="" value="{{ $guru->description }}" readonly>
            </div>
            <div class="form-group">
                <label for="">Pelajaran</label>
                @foreach ($guru->pelajarans as $pelajaran)
                <table class="table table-bordered">
                    <tr>
                        <td>{{ $pelajaran->name }}</td>
                    </tr>
                </table>
                @endforeach
            </div>

            <div class="form-group">
                <img src="{{ asset('storage/img/berita/'.$guru->foto) }}" alt="" srcset=""
                    style="width: 100%; height:">
            </div>
            <a href="{{ route('dashboard.datasekolah.guru.index') }}" class="btn btn-danger float-end">Kembali</a>
        </form>
    </div>
</div>
@endsection
