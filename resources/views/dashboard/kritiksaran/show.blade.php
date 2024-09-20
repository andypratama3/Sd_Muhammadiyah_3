@extends('layouts.dashboard')
@section('title', 'Kritik Saran')
@section('content')
<div class="card mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold text-primary">Kritik Saran</h6>
    </div>
    <div class="card-body">
        <div class="form-group mt-2">
            <label for="name">Nama</label>
            <input type="text" class="form-control" readonly name="name" id="name" value="{{ $kritik->name }}">
        </div>
        <div class="form-group mt-2">
            <label for="name">Subject</label>
            <input type="text" class="form-control" readonly name="subject" id="subject" value="{{ $kritik->subject }}">
        </div>
        <div class="form-group mt-2">
            <label for="name">Subject</label>
            <textarea class="form-control" readonly name="" id="" cols="30" rows="10">{{ $kritik->message }}</textarea>
        </div>
        <div class="form-group mt-2">
            <label for="name">Tanggal Di Kirim</label>
            <input type="text" class="form-control" readonly name="created_at" id="created_at" value="{{ $kritik->created_at }}">
        </div>
        <div class="form-group mt-2">
            <a href="{{ route('dashboard.kritik.saran.index') }}" class="btn btn-sm btn-danger float-lg-start">Kembali</a>
        </div>
    </div>
</div>
@endsection
