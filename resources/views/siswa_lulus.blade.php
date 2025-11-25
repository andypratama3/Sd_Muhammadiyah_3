@extends('layouts.user')

@section('title', 'Siswa Lulus')

@push('css_user')
<link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">
<style>
    body {
        background: linear-gradient(160deg, #a1c4fd, #c2e9fb, #fbc2eb);
        overflow-x: hidden;
    }

    /* Efek background dekoratif dalam konten */
    .decorative-background {
        font-size: 80px;
        text-align: center;
        opacity: 0.1;
        animation: floatBackground 20s linear infinite;
        pointer-events: none;
        margin-bottom: -50px;
    }

    @keyframes floatBackground {
        0% { transform: translateY(0); }
        50% { transform: translateY(20px); }
        100% { transform: translateY(0); }
    }

    .student-card-wrapper {
        margin-bottom: 30px;
        display: flex;
        height: max-content;
        justify-content: center;
    }

    .book {
        position: relative;
        border-radius: 20px;
        width: 100%;
        max-width: 250px;
        height: 380px;
        background: linear-gradient(135deg, #ffefba, #ffffff);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        transform-style: preserve-3d;
        perspective: 2000px;
        transition: transform 0.4s ease, box-shadow 0.4s ease;
        overflow: hidden;
        border: 4px solid #fff;
    }

    .book:hover {
        transform: translateY(-12px) rotateY(8deg) scale(1.03);
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.4);
    }

    .cover {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border-radius: 20px;
        cursor: pointer;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: linear-gradient(135deg, #ffefba, #ffffff);
        justify-content: center;
        padding: 15px;
        color: #fff;
        text-align: center;
    }

    .cover img {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border-radius: 50%;
        margin-bottom: 12px;
        border: 4px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }

    .cover p {
        font-size: 17px;
        font-weight: bold;
        color: #fffa;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
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
        border-radius: 20px;
        text-align: center;
        transition: background 0.3s ease;
    }

    .card-content:hover {
        background: #fffae3;
    }
    .card-content .download-btn {
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }

    .book:hover .card-content .download-btn {
        opacity: 1;
        transform: translateY(0);
    }

    .card-content h6 {
        font-size: 18px;
        margin-bottom: 8px;
        color: black;
        font-weight: bold;
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
            rgba(255, 255, 255, 0.2) 0%,
            rgba(255, 255, 255, 0.6) 50%,
            rgba(255, 255, 255, 0.2) 100%
        );
        transform: skewX(-20deg);
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

    .card-content::before {
        content: "🌟";
        font-size: 28px;
        position: absolute;
        top: 8px;
        right: 10px;
        animation: sparkle 2s infinite;
    }

    @keyframes sparkle {
        0% { opacity: 0.5; transform: scale(1); }
        50% { opacity: 1; transform: scale(1.2); }
        100% { opacity: 0.5; transform: scale(1); }
    }

    @media (max-width: 767px) {
        .book {
            height: 320px;
        }
        .cover img {
            height: 90px;
        }
    }
</style>
@endpush

@section('content')
<div class="mx-2 row">
    <div class="mt-2 mb-2 col-md-12 position-relative" style="margin-top: -30px !important;">
        <div class="decorative-background">🎈🎉✨🌈</div>
        <div class="mx-2 section-title text-start">
            <div class="flex-wrap d-flex justify-content-between align-items-center">
                <h4 class="ms-3" style="color: black; font-family: 'Comic Sans MS', cursive;">🎓 Siswa Alumni Sekolah Kreatif SD Muhammadiyah 3 Samarinda 🎓</h4>
                <select name="tahun" id="tahun" class="w-auto text-center form-control">
                    <option value="" disabled selected>-- Semua Angkatan  --</option>
                    @for($i = 2019; $i <= date('Y'); $i++)
                        <option value="{{ $i }}" {{ request('tahun') == $i ? 'selected' : '' }}>Tahun {{ $i }}  </option>
                    @endfor
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-12">
            @if($siswas->isEmpty())
                <div class="mt-5 mb-5 text-center">
                    <button type="button" class="btn btn-primary btn-lg">Belum ada data alumni untuk tahun yang dipilih 🎈</button>
                </div>
            @else
            <div class="px-3 mb-4 row justify-content-start g-4">
                @foreach ($siswas as $year => $siswa)
                    <div class="mt-3 mb-2 col-md-12">
                        <h5 class="text-center text-success fw-bold">
                            Angkatan Tahun {{ request('tahun') ? $year : 'Semua Angkatan' }} 🎉
                        </h5>
                        <hr class="mx-auto" style="width: 60px; border-color: #20AD57;">
                    </div>
                    @foreach ($siswa as $s)
                       <div class="col-lg-3 col-md-4 col-sm-6 col-6 student-card-wrapper"
                            data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                            <div class="mx-auto book">
                                <div class="shine"></div>
                                <div class="card-content">
                                    @if($s->foto && file_exists(public_path('storage/img/siswa/' . $s->foto)))
                                        <img loading="lazy" class="m-2 img-fluid"
                                            src="{{ asset('storage/img/siswa/' . $s->foto) }}"
                                            alt="Foto {{ $s->name }}"
                                            style="border-radius: 10px;"
                                            onerror="this.onerror=null;this.src='{{ asset('asset_dashboard/img/default.jpg') }}';">
                                    @else
                                        <img loading="lazy" class="m-2 img-fluid"
                                            src="{{ asset('asset_dashboard/img/default.jpg') }}"
                                            alt="Foto Default"
                                            style="border-radius: 10px;">
                                    @endif

                                    <h6>{{ $s->name }}</h6>
                                    <p>🆔: {{ $s->nisn ?? '-' }}</p>
                                    <p>🎓: {{ $s->kelas->first()->pivot->category_kelas ?? '-' }}</p>


                                    @if($s->foto)
                                        <a href="{{ asset('storage/img/siswa/' . $s->foto) }}"
                                        class="mt-2 mb-2 btn btn-outline-success btn-sm download-btn" target="_blank">
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
@endsection

@push('js_user')
<script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script>
    AOS.init();

    $(document).ready(function () {
        $('#tahun').on('change', function () {
            let tahun = $(this).val();
            if (tahun) {
                window.location.href = `{{ route('siswa-lulus.index') }}?tahun=${tahun}`;
            } else {
                window.location.href = `{{ route('siswa-lulus.index') }}`;
            }
        });
    });
</script>
@endpush
