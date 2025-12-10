@extends('layouts.dashboard')

@section('title','Whatsaap Create')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Buat Pengumuman</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('dashboard.monitoring.whatsapp.store') }}" method="POST">
                        @csrf
                        <div class="mb-2 form-group">
                            <label for="webhook_url">Isi Pesan</label>
                            <textarea name="isi" id="isi" cols="30" rows="10" class="form-control"></textarea>
                        </div>
                        <a href="{{ route('whatsapp.webhook') }}" class="btn btn-danger btn-sm">Kembali</a>
                        <button type="submit" class="btn btn-primary btn-sm float-end">Kirim Pengumuman</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
