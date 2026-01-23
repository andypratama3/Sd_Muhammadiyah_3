@extends('layouts.dashboard')

@section('title', 'Edit Jam Kerja')

@section('content')
<div class="mb-4 card">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-primary">Edit Jam Kerja</h6>
    </div>

    <div class="card-body">
        <form action="{{ route('dashboard.jam.absen.update', $jamKerja->id) }}"
              method="POST">
            @csrf
            @method('PUT')

            @include('dashboard.absensis.jam_kerja.form', ['jamKerja' => $jamKerja])

            <div class="mt-4">
                <a href="{{ route('dashboard.jam.absen.index') }}"
                   class="btn btn-danger">
                    Kembali
                </a>
                <button class="btn btn-primary float-end">Update</button>
            </div>
        </form>
    </div>
</div>
@endsection
