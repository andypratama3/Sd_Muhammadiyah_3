@extends('layouts.dashboard')

@section('title', 'Tambah Jam Kerja')

@section('content')
<div class="mb-4 card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Tambah Jam Kerja</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('dashboard.jam.absen.store') }}" method="POST">
            @csrf

            @include('dashboard.absensis.jam_kerja.form')

            <div class="mt-4">
                <a href="{{ route('dashboard.jam.absen.index') }}"
                   class="btn btn-danger">
                    Kembali
                </a>
                <button class="btn btn-primary float-end">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
