
    <style>
        .loading-data {
            display: flex;
            justify-content: center;
            align-content: center;
            margin-top: 50px;
        }
        .loading-data .loading-image-data {
            animation: bounce 2s infinite ease-in-out;
        }
        @keyframes bounce {
        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-40px);
        }
        }
    </style>
<div class="loading-data">
    <img class="loading-image-data" src="{{asset('assets/img/SD3_logo.png')}}" alt="Loading">
</div>
