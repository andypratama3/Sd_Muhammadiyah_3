<div class="col-md-3 mt-4">
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
                    <h2 class="mb-2"><a class="text-dark" style="font-size: 20px !important;" href="{{ route('artikel.show', $latest->slug) }}">{{ $latest->name }}</a></h2>
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
                    <h2 class="mb-2"><a class="text-dark" style="font-size: 20px !important;"  href="{{ route('artikel.show', $trending->slug) }}">{{ $trending->name }}</a></h2>
                    @foreach ($trending->categorys as $category)
                    <span class="author mb-3 d-block">{{ $category->name }}</span>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
