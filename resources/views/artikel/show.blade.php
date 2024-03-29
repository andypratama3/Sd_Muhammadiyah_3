@extends('layouts.user')
@section('title','Artikel')
@push('css_user')
    <style>
        .like{
            color: #da0000;
        }
        .unlike{
            color: #c2aeae;
        }
    </style>
@endpush
@section('content')
<section class="single-post-content" style="margin-top: 95px;">
    <div class="container">
        <div class="row row-not-refresh" data-aos="fade-up">
            <div class="col-md-9 post-content" >
                <div class="single-post">
                    <div class="post-meta"><span class="date">
                            @foreach ($artikel->categorys as $category)
                            {{ $category->name }}
                            @endforeach
                        </span>
                        <span class="mx-1">&bullet;</span>
                        <span>{{ $artikel->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                    <h1 class="mb-5">{{ $artikel->name }}</h1>
                    <figure class="my-4">
                        {{-- <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="" class="img-fluid"> --}}
                        <img src="{{ asset('storage/img/artikel/Berita_a_20240210170052.jpg') }}" alt="" class="img-fluid">
                    </figure>
                    <p><span class="firstcharacter">{{ $firstCharacter }}</span>{{ $contentWithoutFirstCharacter }}</p>
                </div>
                <!-- ======= Comments ======= -->
                <div class="comments my-5" id="reset_form">
                    <h5 class="comment-title py-4">{{ $count }} Comments</h5>
                    @foreach ($comments as $comment)
                    @if ($count >= 0)
                        <div class="comment d-flex mb-4">
                            @foreach ($comment->users as $user)
                            <div class="flex-shrink-0">
                                <div class="avatar avatar-sm rounded-circle">
                                    <img class="avatar-img" src="{{ asset('storgae/app/public/img/user', $user->avatar) }}" alt="" class="img-fluid">
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-2 ms-sm-3">
                                <div class="comment-meta d-flex align-items-baseline">
                                    <h6 class="me-2">{{ $user->name }}</h6>
                                    <span class="text-muted">{{ $comment->created_at->diffForHumans() }}</span>
                                </div>
                                @endforeach
                                <div class="form-group comments-group">
                                    <div class="comment-body" id="comment-body">
                                        {{ $comment->comment }}

                                        <form id="comment-form-update" action="{{ route('comment.update', $comment->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="slug" value="{{ $comment->slug }}">
                                        <input type="hidden" name="artikel" value="{{ $artikel->id }}" id="artikel">
                                        <input type="hidden" name="user" value="{{ Auth::id() }}" id="user">
                                        <div class="col-12 mb-3" id="comment-form-{{ $comment->id }}" style="display: none;">
                                        <div class="form-group">
                                            <label for="comment-message">Edit Komentar</label>
                                            <textarea class="form-control" name="comment"  id="comment-message-show-{{ $comment->id }}" placeholder="Masukan Teks" cols="30" rows="1">{{ $comment->comment }}</textarea>
                                            <div class="col-12 mt-2">
                                                <input type="submit" class="btn btn-primary" value="Posting">
                                            </div>
                                        </div>
                                        </div>
                                    </form>
                                    </div>
                                    <div class="comment-meta">
                                        @foreach ($comment->users as $item)
                                            @auth
                                                @if ($item->id === Auth::user()->id)
                                                    <!-- Edit and Delete Buttons for Comment Owner -->
                                                    <div class="form-group float-end" id="button_comment">
                                                        <button class="btn btn-primary btn-sm commentar-edit" data-id="<?=$comment->id ?>" data-comment="<?=$comment->comment ?>" ><i class="bi bi-pen"></i></button>
                                                        <button class="btn btn-danger btn-sm commentar-delete"  data-id="<?=$comment->slug ?>"><i class="bi bi-trash"></i></button>
                                                    </div>
                                                @else
                                                    <!-- Reply Button for Non-Comment Owner Users -->
                                                    <div class="form-group float-end">
                                                        <button class="btn btn-primary btn-sm">Reply</button>
                                                    </div>
                                                @endif
                                            @endauth
                                            <!-- Like Button for All Users -->
                                            <div class="form-group">
                                                @if (Auth::check())
                                                    @if ($comment->likes)
                                                        <i class="bi bi-heart-fill like" id="like" data-id="<?=$comment->id ?>"></i>
                                                        <span id="like">{{ $comment->countLike() }} Like</span>
                                                    @else
                                                        <i class="bi bi-heart-fill unlike" id="like" data-id="<?=$comment->id ?>"></i>
                                                        <span id="like">{{ $comment->countLike() }} Like</span>
                                                    @endif
                                                @else
                                                    <i class="bi bi-heart-fill unlike" id="like" data-id="<?=$comment->id ?>"></i>
                                                    <span id="like">{{ $comment->countLike() }} Like</span>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>

                                </div>
                            </div>
                        </div>
                        @else
                        <h2 class="text-muted">Tidak Ada Komentar</h2>
                    @endif
                    @endforeach
                </div><!-- End Comments -->
                <!-- ======= Comments Form ======= -->
                <div class="row justify-content-center mt-5">
                    <div class="col-lg-12" id="reset_komen">
                        <h5 class="comment-title">Komentar</h5>
                        @include('layouts.flashmessage')
                        <div class="row" >
                            <form id="comment-form" action="{{ route('comment.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="artikel" value="{{ $artikel->id }}" id="artikel">
                            <input type="hidden" name="user" value="{{ Auth::id() }}" id="user">
                                {{-- @auth --}}
                                <div class="col-12 mb-3" id="comment-form">
                                    <label for="comment-message">Tambahkan Komentar</label>
                                    <textarea class="form-control" name="comment" id="comment-message" placeholder="Masukan Teks" cols="30" rows="10"></textarea>
                                </div>
                                <div class="col-12">
                                    <input type="submit" class="btn btn-primary" value="Posting">
                                </div>
                                {{-- @else
                                <div class="col-12">
                                    <a href="{{ route('register') }}" class="btn btn-primary">Daftar</a>
                                    <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
                                </div>
                                @endauth --}}
                            </form>
                        </div>
                    </div>
                </div><!-- End Comments Form -->
            </div>
            <div class="col-md-3">
                <!-- ======= Sidebar ======= -->
                <div class="aside-block">
                    <ul class="nav nav-pills custom-tab-nav mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-latest-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-latest" type="button" role="tab" aria-controls="pills-latest"
                                aria-selected="true">Latest</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-trending-tab" data-bs-toggle="pill"
                                data-bs-target="#pills-trending" type="button" role="tab" aria-controls="pills-trending"
                                aria-selected="false">Trending</button>
                        </li>

                    </ul>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-latest" role="tabpanel" aria-labelledby="pills-latest-tab">
                            @foreach ($latest_artikel as $latest)
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta">
                                    <span class="date"></span>
                                <span class="mx-1">&bullet;</span> <span>{{ $latest->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                                <h2 class="mb-2"><a href="{{ route('artikel.show', $latest->slug) }}">{{ $latest->name }}</a></h2>
                                @foreach ($latest->categorys as $category)
                                <span class="author mb-3 d-block">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            @endforeach
                        </div>

                        <!-- Trending -->
                        <div class="tab-pane fade" id="pills-trending" role="tabpanel" aria-labelledby="pills-trending-tab">
                            @foreach ($artikel_trending_list as $trending)
                            <div class="post-entry-1 border-bottom">
                                <div class="post-meta">
                                    <span class="date"></span>
                                <span class="mx-1">&bullet;</span> <span>{{ $trending->created_at->formatLocalized('%A %d %B %Y') }}</span></div>
                                <h2 class="mb-2"><a href="{{ route('artikel.show', $trending->slug) }}">{{ $trending->name }}</a></h2>
                                @foreach ($trending->categorys as $category)
                                <span class="author mb-3 d-block">{{ $category->name }}</span>
                                @endforeach
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@push('js_user')
<script src="{{ asset('asset_dashboard/js/SwetAlert/index.js') }}"></script>
<script>
    $(document).ready(function() {
        $('.row-not-refresh').on('click', '#like', function (e) {
            const comment_id = $(this).data('id');
            $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            $.ajax({
                type: "POST",
                url: "{{ route('like.comment') }}",
                data: {
                    'comment_id': comment_id,
                },
                success: function (response) {
                    $('.comments').load(location.href + " .comments");
                    $('.comment-meta').load(location.href + " .comment-meta");
                },
                error: function (response) {
                    window.location.href = "{{ route('login') }}";
                },
            });
        });

        $('.row-not-refresh').on('click','.commentar-edit', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var commentForm = $('#comment-form-' + id);
            commentForm.toggle();
                $('#comment-message-show-' + id).on('input', function() {
                        var updatedComment = $(this).val();
                        var updatedComment = $('#comment-message-show-' + id).val();
                    });
        });
        $('.row-not-refresh').on('click', '.commentar-delete',function () {
            var slug = $(this).data('id');
            var url = '{{ route("comment.destroy", ":slug") }}';
            url = url.replace(':slug', slug);
            swal({
                title: 'Anda yakin?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    // Send a DELETE request
                    $.ajax({
                        url: url,
                        type: 'DELETE', // Use the DELETE method
                        success: function (data) {
                            $('.comments').load(location.href + " .comments");
                            $('.comment-meta').load(location.href + " .comment-meta");
                        },
                        error: function(data){

                        }
                    });
                } else {
                    // If the user cancels the deletion, do nothing
                }
            });
    });

    $('.row-not-refresh').on('submit','#comment-form',function(e) {
    e.preventDefault();
    $.ajax({
        type: 'POST',
        url: $(this).attr('action'),
        data: $(this).serialize(),
        success: function(response) {
            $('.comments').load(location.href + " .comments");
            $('#reset_komen').load(location.href + " #reset_komen");
            $('.comment-meta').load(location.href + " .comment-meta");

        },
        error: function(error) {
            if(error.status = '401'){
                window.location.href = "{{ route('login') }}";
            }else{

            }
        }
        });
    });

    $('.row-not-refresh').on('submit','#comment-form-update',function(e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(response) {
                $('.comments').load(location.href + " .comments");
                $('#reset_komen').load(location.href + " #reset_komen");
                $('.comment-meta').load(location.href + " .comment-meta");
            },
            error: function(error) {

            }
        });
    });
});
</script>
@endpush
@endsection
