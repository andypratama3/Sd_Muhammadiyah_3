@extends('layouts.user')

@section('title', 'Siswa Aktif')
@push('css_user')
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
@endpush
@section('content')

<style>
    .student-card-wrapper {
        margin-bottom: 30px;
        display: flex;
        justify-content: center;
    }

    .book {
        position: relative;
        border-radius: 12px;
        width: 100%;
        max-width: 240px;
        height: 320px;
        background-color: #f9fdfb;
        box-shadow: 0 4px 16px rgba(32, 173, 87, 0.15);
        transform-style: preserve-3d;
        perspective: 2000px;
        transition: transform 0.3s ease;
        overflow: hidden;
        border: 2px solid transparent;
    }

    .book:hover {
        border-color: #20AD57;
        box-shadow: 0 0 20px rgba(32, 173, 87, 0.4);
    }

    @keyframes bgPattern {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }

    .cover {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.6s ease;
        transform-origin: left;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 2;
        padding: 15px;
        animation: bgPattern 10s ease infinite;
        background: #B2B3BD;
        background-size: 400% 400%;
        color: #fff;
        box-shadow: inset 0 0 0 2000px rgba(255, 255, 255, 0.03);
    }

    .cover img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 12px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.2);
    }

    .cover p {
        font-size: 15px;
        font-weight: bold;
        margin: 0;
        text-align: center;
        color: #fff;
        text-shadow: 1px 1px 2px #ffffff55;
    }

    .book:hover .cover {
        transform: rotateY(-85deg);
    }

    .card-content {
        position: relative;
        z-index: 1;
        height: 100%;
        width: 100%;
        padding: 20px 15px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        background: white;
        border-radius: 12px;
        text-align: center;
    }

    .card-content h6 {
        font-size: 17px;
        margin-bottom: 10px;
        color: #20AD57;
        font-weight: 700;
    }

    .card-content p {
        font-size: 14px;
        margin: 4px 0;
        color: #555;
    }

    .shine {
        content: '';
        position: absolute;
        top: 0;
        left: -75%;
        width: 50%;
        height: 100%;
        background: linear-gradient(
            120deg,
            rgba(32, 173, 87, 0.1) 0%,
            rgba(32, 173, 87, 0.5) 50%,
            rgba(32, 173, 87, 0.1) 100%
        );
        transform: skewX(-20deg);
        transition: none;
        z-index: 3;
        pointer-events: none;
    }

    .book:hover .shine {
        animation: shineEffect 1.2s ease forwards;
    }

    @keyframes shineEffect {
        0% { left: -75%; }
        100% { left: 125%; }
    }

    @media (max-width: 767px) {
        .book {
            height: 300px;
        }
        .cover img {
            height: 140px;
        }
        .book.active-mobile .cover {
            transform: rotateY(-85deg);
        }
    }

</style>

<div class="row mx-2">
    <div class="col-md-12 mt-2 mb-2">
        <div class="section-title text-start mx-2">
            <div class="d-flex justify-content-between flex-wrap">
                <h2 class="ms-3" style="color: #20AD57;">Siswa Aktif</h2>
                    <select name="tahun" id="tahun" class="form-control w-auto text-center">
                        <option value="">--Pilih Tahun--</option>
                        @for($i = 2019; $i <= date('Y'); $i++)
                            <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>Tahun {{ $i }}</option>
                        @endfor
                    </select>
            </div>
        </div>
    </div>

    <div class="col-md-12">
         @if($siswas->isEmpty())
            <div class="text-center mt-5 mb-5">
                <button type="button" class="btn btn-primary">Belum ada data alumni untuk tahun yang dipilih.</button>
            </div>
        @else
        <div class="row justify-content-start g-4 px-3 mb-4">
            @foreach ($siswas as $year => $siswa)
                <div class="col-md-12 mt-3 mb-2">
                    <h5 class="text-center text-success fw-bold">Angkatan Tahun {{ $year }}</h5>
                    <hr class="mx-auto" style="width: 60px; border-color: #20AD57;">
                </div>
                @foreach ($siswa as $s)
                    <div class="col-lg-3 col-md-4 col-sm-6 col-6 student-card-wrapper" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="book mx-auto">
                            <div class="shine"></div>

                            <div class="cover">
                                <img loading="lazy" src="{{ $s->foto ? asset('storage/img/siswa/' . $s->foto) : asset('asset_dashboard/img/default.jpg') }}" alt="Foto Siswa" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=2940&auto=format&fit=crop';" class="img-fluid" alt="Foto Siswa Alumni {{ $s->name }} Tahun {{ $s->tahun_lulus ?? '2019' }}">
                                <p>{{ $s->name }}</p>
                            </div>

                            <div class="card-content">
                                <h6>{{ $s->name }}</h6>
                                <p>NISN: {{ $s->nisn ?? '-' }}</p>
                                <p>Tahun: {{ $s->tahun_lulus ?? '2019' }}</p>
                                @if($s->foto)
                                    <a href="{{ asset('storage/img/siswa/' . $s->foto) }}" class="btn btn-outline-success btn-sm mt-2" target="_blank">
                                        <i class="bi bi-download me-1"></i> Unduh Foto
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            @endforeach
        </div>
        @endif
        </div>
    </div>
</div>
@push('js_user')
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init();
        $(document).ready(function () {
            $('#tahun').on('change', function () {
                let tahun = $(this).val();
                if (tahun) {
                    window.location.href = `{{ route('siswa-lulus.index') }}?tahun=${tahun}`;
                }
            });
        });
    </script>
@endpush

@endsection

