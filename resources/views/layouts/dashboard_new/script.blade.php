<!-- Core JS -->
<script src="{{ asset('asset_dashboard_new/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('asset_dashboard_new/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('asset_dashboard_new/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('asset_dashboard_new/vendor/js/menu.js') }}"></script>

<!-- Vendors JS -->
{{-- <script src="{{ asset('asset_dashboard_new/vendor/libs/apex-charts/apexcharts.js') }}"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<!-- Main JS -->
<script src="{{ asset('asset_dashboard_new/js/main.js') }}"></script>

<!-- Load SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Page JS -->
<script src="{{ asset('asset_dashboard_new/js/dashboards-analytics.js') }}"></script>
<script src="https://kit.fontawesome.com/2feee0b69e.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<!-- GitHub buttons -->
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="{{ asset('asset_dashboard/vendor/select2/dist/js/select2.js') }}"></script>

<script async defer src="https://buttons.github.io/buttons.js"></script>

@stack('js')

<script>
    function reloadTable(id) {
        var table = $(id).DataTable();
        table.cleanData;
        table.ajax.reload();
    }


    if ($('.select2').length) {
        $('.select2').select2();
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
                cancelButtonText: 'No, cancel',
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
                confirmButtonColor: '#d33',
                confirmButtonText: 'Hapus !',
                cancelButtonText: 'Batal',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#delete-${slug}`).submit();
                } else {
                    // do nothing
                }
            });
        });
    });
    // document.addEventListener("DOMContentLoaded", function () {
    //     fetch('{{ route('dashboard.datamaster.get.activitys') }}') // Sesuaikan dengan endpoint API kamu
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.success) {
    //                 const activityCount = document.getElementById('activity_count');
    //                 const activityItems = document.getElementById('activity_items');

    //                 activityCount.textContent = data.activitys_count > 0 ? data.activitys_count : '';

    //                 activityItems.innerHTML = ''; // Kosongkan sebelum menambahkan data baru

    //                 data.activitys.forEach(activity => {
    //                     let activityItem = `
    //                         <a class="dropdown-item d-flex align-items-center" href="#">
    //                             <div class="mr-3">
    //                                 <div class="icon-circle">
    //                                     <i class="fas fa-file-alt"></i>
    //                                 </div>
    //                             </div>
    //                             <div>
    //                                 <div class="text-gray-500 small">${new Date(activity.created_at).toLocaleString()}</div>
    //                                 <span class="font-weight-bold">${activity.description.split(' ').slice(0, 5).join(' ') + (activity.description.split(' ').length > 5 ? '...' : '')}</span>
    //                             </div>
    //                         </a>
    //                     `;
    //                     activityItems.innerHTML += activityItem;
    //                 });
    //             }
    //         })
    //         .catch(error => console.error('Error fetching activity:', error));
    // });

</script>
