{{-- <script src="{{ asset('asset_dashboard/vendor/jquery/jquery.min.js') }}"></script> --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="{{ asset('asset_dashboard/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('asset_dashboard/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/ruang-admin.min.js') }}"></script>
<script src="{{ asset('asset_dashboard/js/SwetAlert/index.js') }}" nonce="{{ csp_nonce() }}"></script>
<script src="https://kit.fontawesome.com/2feee0b69e.js" crossorigin="anonymous"></script>

@stack('js')

<script>
    function reloadTable(id){
    var table = $(id).DataTable();
    table.cleanData;
    table.ajax.reload();
    }
    $(document).ready(function () {
        $('.navbar').on('click','.swal-logout',function (e) {
        slug = e.target.dataset.id;
        swal({
                title: 'Yakin ingin keluar?',
                text: 'Anda akan dialihkan ke beranda.',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            })
            .then((willLogout) => {
                if (willLogout) {
                    $(`#logout-form`).submit();
                } else {
                    // Do Nothing
                }
            });
    });
    setTimeout(function () {
        $('.alert').remove();
    }, 4000);

        $(".delete").click(function (e) {
            slug = e.target.dataset.id;
            swal({
                    title: 'Anda yakin?',
                    text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        $(`#delete-${slug}`).submit();
                    } else {
                        // Do Nothing
                    }
                });
        });
    });
    getActivity();
    function getActivity(){
        const url = "{{ route('dashboard.datamaster.get.activitys') }}";
        $.ajax({
            type: "GET",
            url: url,

            success: function (response) {
                const activitys = response.activitys;

                if(response.activitys_count > 9){
                    $('#activity_count').text(9 + '+');
                }else{
                    $('#activity_count').text(response.activitys_count);
                }
                //each data
                const activity_link = $('#activity_link');
                const activity_icon = $('#activity_icon');
                const activity_date = $('#activity_date');
                const activity_data = $('#activity_data');
                $.each(activitys, function (indexInArray, valueOfElement) {
                    if(activitys === 'Menambahkan'){
                        activity_icon.className = 'fas fa-plus text-white';
                    }
                });

            }
        });
    }
</script>
