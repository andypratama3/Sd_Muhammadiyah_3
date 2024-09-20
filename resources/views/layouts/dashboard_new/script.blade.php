<!-- Core JS -->
<script src="{{ asset('asset_dashboard_new/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('asset_dashboard_new/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('asset_dashboard_new/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('asset_dashboard_new/vendor/js/menu.js') }}"></script>

<!-- Vendors JS -->
<script src="{{ asset('asset_dashboard_new/vendor/libs/apex-charts/apexcharts.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('asset_dashboard_new/js/main.js') }}"></script>

<!-- Load SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Page JS -->
<script src="{{ asset('asset_dashboard_new/js/dashboards-analytics.js') }}"></script>
<script src="https://kit.fontawesome.com/2feee0b69e.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

<!-- GitHub buttons -->
<script async defer src="https://buttons.github.io/buttons.js"></script>

@stack('js')

<script>
    function reloadTable(id) {
        var table = $(id).DataTable();
        table.cleanData;
        table.ajax.reload();
    }


    $(document).ready(function () {
        $('.navbar-nav-right').on('click', '.swal-logout', function (e) {
            // slug = e.target.dataset.id;
            Swal.fire({
                title: 'Yakin ingin keluar?',
                text: 'Anda akan dialihkan ke beranda.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, log out!',
                cancelButtonText: 'No, cancel!',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#logout-form').submit();
                }

            });
        });

        setTimeout(function () {
            $('.alert').remove();
        }, 4000);

        $(".table").on('click', '.delete', function (e) {
            slug = e.target.dataset.id;
            Swal.fire({
                title: 'Anda yakin?',
                text: 'Data yang sudah dihapus tidak dapat dikembalikan!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'Hapus !',
                cancelButtonText: 'Batal !',
                dangerMode: true,
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#delete-${slug}`).submit();
                }
            });
        });
    });

    getActivity();

    function getActivity() {
        const url = "{{ route('dashboard.datamaster.get.activitys') }}";
        $.ajax({
            type: "GET",
            url: url,
            success: function (response) {
                const activitys = response.activitys;
                if (response.activitys_count > 9) {
                    $('#activity_count').text(9 + '+');
                } else {
                    $('#activity_count').text(response.activitys_count);
                }

                const activity_icon = $('#activity_icon');
                $.each(activitys, function (indexInArray, valueOfElement) {
                    if (valueOfElement === 'Menambahkan') {
                        activity_icon.addClass('fas fa-plus text-white');
                    }
                    if (valueOfElement === 'Mengubah') {
                        activity_icon.addClass('fas fa-edit text-white');
                    }
                    if (valueOfElement === 'Menghapus') {
                        activity_icon.addClass('fas fa-trash text-white');
                    }
                    if (valueOfElement === 'Mengaktifkan') {
                        activity_icon.addClass('fas fa-toggle-on text-white');
                    }
                    if (valueOfElement === 'Menonaktifkan') {
                        activity_icon.addClass('fas fa-toggle-off text-white');
                    }
                });
            }
        });
    }
</script>
