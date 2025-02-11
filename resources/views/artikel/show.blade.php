@extends('layouts.user')
@section('title','Artikel')
@push('meta_user')
    <meta name="description" content="{!! Str::limit($artikel->deskripsi, 160) !!}">
    <meta name="keywords" content="{!! $artikel->deskripsi !!}">
    <meta name="author" content="{{ $artikel->user->name }}">
    <meta name="copyright" content="{{ $artikel->user->name }}">
    @foreach ($artikel->categorys as $category)
        <meta name="category" content="{{ $category->name }}">
    @endforeach
    <meta property="og:title" content="{{ $artikel->title }}">
    <meta property="og:description" content="{!! Str::limit($artikel->deskripsi, 160) !!}">
    <meta property="og:image" content="{{ asset('storage/img/artikels/'. $artikel->foto) }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $artikel->title }}">
    <meta name="twitter:description" content="{!! Str::limit($artikel->deskripsi, 160) !!}">
    <meta name="twitter:image" content="{{ asset('storage/img/artikels/'. $artikel->foto) }}">
@endpush
@push('css_user')
<style>
    .like {
        color: #da0000;
    }

    .unlike {
        color: #c2aeae;
    }

    img {
        border-radius: 10px;
    }
    .nav-link {
        color: black !important;
    }

    .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
        background-color: #5ce70b !important;
        color: #fff !important;
    }

    .comment {
        .post-entry-1 {
            margin-bottom: 30px;
        }

        .post-entry-1 img {
            margin-bottom: 30px;
        }

        .post-entry-1 h2 {
            margin-bottom: 20px;
            font-size: 20px;
            font-weight: 500;
            line-height: 1.2;
            font-weight: 500;
        }

        .post-entry-1 h2 a {
            color: var(--color-black);
        }

        .post-entry-1.lg h2 {
            font-size: 40px;
            line-height: 1;
        }

        .post-meta {
            font-size: 11px;
            letter-spacing: 0.07rem;
            text-transform: uppercase;
            font-weight: 600;
            font-family: var(--font-secondary);
            color: rgba(var(--color-black-rgb), 0.4);
            margin-bottom: 10px;
        }

        /* Font not working in <textarea> for this version of bs */
    }

    .comment .avatar {
        position: relative;
        display: inline-block;
        width: 3rem;
        height: 3rem;
    }

    .comment .avatar-img,
    .comment .avatar-initials,
    .comment .avatar-placeholder {
        width: 100%;
        height: 100%;
        border-radius: inherit;
    }

    .comment .avatar-img {
        display: block;
        -o-object-fit: cover;
        object-fit: cover;
    }

    .comment .avatar-initials {
        position: absolute;
        top: 0;
        left: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--color-white);
        line-height: 0;
        background-color: rgba(var(--color-black-rgba), 0.1);
    }

    .comment .avatar-placeholder {
        position: absolute;
        top: 0;
        left: 0;
        background: rgba(var(--color-black-rgba), 0.1) url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='%23fff' d='M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z'/%3e%3c/svg%3e") no-repeat center/1.75rem;
    }

    .comment .avatar-indicator {
        position: absolute;
        right: 0;
        bottom: 0;
        width: 20%;
        height: 20%;
        display: block;
        background-color: rgba(var(--color-black-rgba), 0.1);
        border-radius: 50%;
    }

    .comment .avatar-group {
        display: inline-flex;
    }

    .comment .avatar-group .avatar+.avatar {
        margin-left: -0.75rem;
    }

    .comment .avatar-group .avatar:hover {
        z-index: 1;
    }

    .comment .avatar-sm,
    .comment .avatar-group-sm>.avatar {
        width: 2.125rem;
        height: 2.125rem;
        font-size: 1rem;
    }

    .comment .avatar-sm .avatar-placeholder,
    .comment .avatar-group-sm>.avatar .avatar-placeholder {
        background-size: 1.25rem;
    }

    .comment .avatar-group-sm>.avatar+.avatar {
        margin-left: -0.53125rem;
    }

    .comment .avatar-lg,
    .comment .avatar-group-lg>.avatar {
        width: 4rem;
        height: 4rem;
        font-size: 1.5rem;
    }

    .comment .avatar-lg .avatar-placeholder,
    .comment .avatar-group-lg>.avatar .avatar-placeholder {
        background-size: 2.25rem;
    }

    .comment .avatar-group-lg>.avatar+.avatar {
        margin-left: -1rem;
    }

    .comment .avatar-light .avatar-indicator {
        box-shadow: 0 0 0 2px rgba(var(--color-white-rgba), 0.75);
    }

    .comment .avatar-group-light>.avatar {
        box-shadow: 0 0 0 2px rgba(var(--color-white-rgba), 0.75);
    }

    .comment .avatar-dark .avatar-indicator {
        box-shadow: 0 0 0 2px rgba(var(--color-black-rgba), 0.25);
    }

    .comment .avatar-group-dark>.avatar {
        box-shadow: 0 0 0 2px rgba(var(--color-black-rgba), 0.25);
    }

    .comment textarea {
        font-family: inherit;
    }

    .comment .comment-replies-title,
    .comment .comment-title {
        text-transform: uppercase;
        color: var(--color-black) !important;
        letter-spacing: 0.1rem;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 30px;
    }

    .comment .comment-meta .text-muted,
    .comment .reply-meta .text-muted {
        font-family: var(--font-secondary);
        font-size: 12px;
    }
</style>
@endpush
@section('content')
<section class="single-post-content " style="margin-top: 20px;">
    <div class="container">
        <div class="row row-not-refresh" data-aos="fade-up">
            <div class="col-md-12 post-content ">
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
                        {{-- <img src="{{ asset('storage/img/artikel/'. $artikel->image) }}" alt="" class="img-fluid">
                        --}}
                        <img src="{{ asset('storage/img/berita/Berita_test_20240412172357.jpg') }}" alt=""
                            class="img-fluid">
                    </figure>
                    <p class="text-black"><span class="firstcharacter text-black">{!! substr(strip_tags($artikel->artikel), 0, 1) !!}</span>{!! substr($artikel->artikel, 1) !!}</p>
                </div>
                <!-- ======= Comments ======= -->
                <div class="comments my-5" id="reset_form">
                    <h5 class="comment-title py-4">{{ $count }} Komentar</h5>
                    @foreach ($comments as $comment)
                    @if ($count >= 0)
                    <div class="comment d-flex mb-4">
                        @foreach ($comment->users as $user)
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-sm rounded-circle">
                                <img class="avatar-img" src="{{ asset('storage/img/profile/'. $user->avatar) }}" alt=""
                                    class="img-fluid">
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

                                    <form id="comment-form-update" action="{{ route('comment.update', $comment->id) }}"
                                        method="POST">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="slug" value="{{ $comment->slug }}">
                                        <input type="hidden" name="artikel" value="{{ $artikel->id }}" id="artikel">
                                        <input type="hidden" name="user" value="{{ Auth::id() }}" id="user">
                                        <div class="col-12 mb-3" id="comment-form-{{ $comment->id }}"
                                            style="display: none;">
                                            <div class="form-group">
                                                <label for="comment-message">Edit Komentar</label>
                                                <textarea class="form-control" name="comment"
                                                    id="comment-message-show-{{ $comment->id }}"
                                                    placeholder="Masukan Teks" cols="30"
                                                    rows="1">{{ $comment->comment }}</textarea>
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
                                        <button class="btn btn-primary btn-sm commentar-edit"
                                            data-id="<?=$comment->id ?>" data-comment="<?=$comment->comment ?>"><i
                                                class="fa fa-pen"></i></button>
                                        <button class="btn btn-danger btn-sm commentar-delete"
                                            data-id="<?=$comment->slug ?>"><i class="fa fa-trash"></i></button>
                                    </div>
                                    @else
                                    <!-- Reply Button for Non-Comment Owner Users -->
                                    {{-- <div class="form-group float-end">
                                                        <button class="btn btn-primary btn-sm">Reply</button>
                                                    </div> --}}
                                    @endif
                                    @endauth
                                    <!-- Like Button for All Users -->
                                    <div class="form-group">
                                        @auth
                                        @if ($comment->likes)
                                        <i class="fa-solid fa-heart like" id="like" data-id="<?=$comment->id ?>"></i>
                                        <span id="like">{{ $comment->countLike() }} Like</span>
                                        @else
                                        <i class="fa-solid fa-heart unlike" id="like" data-id="<?=$comment->id ?>"></i>
                                        <span id="like">{{ $comment->countLike() }} Like</span>
                                        @endif
                                        @else
                                        <i class="fa-solid fa-heart unlike" id="like" data-id="<?=$comment->id ?>"></i>
                                        <span id="like">{{ $comment->countLike() }} Like</span>
                                        @endauth
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
                <div class="row justify-content-center mt-5 mb-5">
                    <div class="col-lg-12" id="reset_komen">
                        <h5 class="comment-title">Komentar</h5>
                        @include('layouts.flashmessage')
                        <div class="row">
                            <form id="comment-form" action="{{ route('comment.store') }}" method="post"
                                enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="artikel" value="{{ $artikel->id }}" id="artikel">
                                <input type="hidden" name="user" value="{{ Auth::id() }}" id="user">

                                <div class="col-12 mb-3" id="comment-form">
                                    <label for="comment-message">Tambahkan Komentar</label>
                                    <textarea class="form-control" name="comment" id="comment-message"
                                        placeholder="Masukan Teks" cols="30" rows="1"></textarea>
                                </div>
                                <div class="col-12">
                                    <input type="submit" class="btn" style="background-color: #5ce70b !important; color: white !important;" value="Posting">
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
    </div>
</div>
</section>
@push('js_user')
<script src="{{ asset('asset_dashboard/js/SwetAlert/index.js') }}"></script>
<script>
    $(document).ready(function () {
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
                    Swal.fire(
                        'Error',
                        'Kamu harus login terlebih dahulu',
                        'error'
                    )
                },
            });
        });

        $('.row-not-refresh').on('click', '.commentar-edit', function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var commentForm = $('#comment-form-' + id);
            commentForm.toggle();
            $('#comment-message-show-' + id).on('input', function () {
                var updatedComment = $(this).val();
                var updatedComment = $('#comment-message-show-' + id).val();
            });
        });
        $('.row-not-refresh').on('click', '.commentar-delete', function () {
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
                            $('.comment-meta').load(location.href +
                                " .comment-meta");
                        },
                        error: function (data) {

                        }
                    });
                } else {
                    // If the user cancels the deletion, do nothing
                }
            });
        });

        $('.row-not-refresh').on('submit', '#comment-form', function (e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function (response) {
                    $('.comments').load(location.href + " .comments");
                    $('#reset_komen').load(location.href + " #reset_komen");
                    $('.comment-meta').load(location.href + " .comment-meta");

                },
                error: function (error) {
                    if (error.status = '401') {
                        window.location.href = "{{ route('login') }}";
                    } else {

                    }
                }
            });
        });

        $('.row-not-refresh').on('submit', '#comment-form-update', function (e) {
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: $(this).serialize(),
                success: function (response) {
                    $('.comments').load(location.href + " .comments");
                    $('#reset_komen').load(location.href + " #reset_komen");
                    $('.comment-meta').load(location.href + " .comment-meta");
                },
                error: function (error) {

                }
            });
        });
    });
</script>
@endpush
@endsection
