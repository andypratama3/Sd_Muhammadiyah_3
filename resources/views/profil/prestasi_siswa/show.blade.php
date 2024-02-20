@extends('layouts.user')
@section('title','Prestasi Siswa')
@section('content')

<section class="single-post-content" style="margin-top: 95px;">
    <div class="container aos-init aos-animate" data-aos="fade-up">
        <div class="row">
            <header class="section-header text-center">
                <h2><a href="{{ route('prestasi.siswa.index') }}" class="btn btn-primary float-start"><i class="fas fa-arrow-left"></i>Kembali</a> Prestasi Sekolah</h2>
                <h4>SD Muhammadiyah 3 Samarinda</h4>
            </header>
            <div class="col-md-12 mt-2 text-center">

                <img src="{{ asset('storage/img/prestasi/'. $prestasi->foto) }}" alt="" class="img-fluid rounded w-50 mb-4">
                <h4>{{ $prestasi->name }}</h4>
                <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Optio ad eligendi dolorem sunt tempora est vel, fugiat accusantium placeat odit at culpa! Praesentium assumenda earum harum nobis repellendus voluptatibus officiis rem quo suscipit, inventore eos odit minus voluptate velit cum. Nostrum voluptatum fuga error dolores sapiente provident ex tempora, cum consectetur aspernatur perspiciatis natus iusto dolorum laudantium, dicta earum odio voluptatem voluptate. Molestiae minus accusantium perspiciatis mollitia? Aperiam impedit eligendi exercitationem veritatis odit ipsam vitae. Amet impedit dolore laborum modi placeat pariatur, dolorum explicabo quibusdam? Hic laborum atque cum ratione, rerum officia magni? Nesciunt debitis minima recusandae molestiae sit in quae architecto natus, ab unde inventore modi dolores eos ratione deserunt labore porro harum aliquid! Esse reiciendis optio id explicabo animi alias a dolore? Quia corrupti quis non! Quibusdam quia veritatis dolorem. Voluptate, saepe natus tempora impedit nemo, doloremque aspernatur a vel cumque culpa recusandae deleniti ad voluptatibus amet unde.</p>
            </div>
        </div>
    </div>
</section>

@endsection
