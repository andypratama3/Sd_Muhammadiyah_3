document.addEventListener('DOMContentLoaded', function () {
    const collapses = document.querySelectorAll('.accordion-collapse');

    collapses.forEach(function (collapse) {
        // Listen for when the collapse is shown
        collapse.addEventListener('show.bs.collapse', function () {
            const id = collapse.getAttribute('id');
            if (!id) return;

            // Add opened ID to localStorage
            let openAccordions = JSON.parse(localStorage.getItem('openAccordions')) || [];
            if (!openAccordions.includes(id)) {
                openAccordions.push(id);
                localStorage.setItem('openAccordions', JSON.stringify(openAccordions));
            }

            // Toggle icons
            const button = collapse.previousElementSibling.querySelector('.accordion-button');
            const folderIcon = button.querySelector('.bi-folder');
            const folderOpenIcon = button.querySelector('.bi-folder2-open');
            if (folderIcon) folderIcon.classList.add('d-none');
            if (folderOpenIcon) folderOpenIcon.classList.remove('d-none');
        });

        collapse.addEventListener('hide.bs.collapse', function () {
            const id = collapse.getAttribute('id');
            if (!id) return;

            // Remove ID from localStorage
            let openAccordions = JSON.parse(localStorage.getItem('openAccordions')) || [];
            openAccordions = openAccordions.filter(item => item !== id);
            localStorage.setItem('openAccordions', JSON.stringify(openAccordions));

            // Toggle icons
            const button = collapse.previousElementSibling.querySelector('.accordion-button');
            const folderIcon = button.querySelector('.bi-folder');
            const folderOpenIcon = button.querySelector('.bi-folder2-open');
            if (folderIcon) folderIcon.classList.remove('d-none');
            if (folderOpenIcon) folderOpenIcon.classList.add('d-none');
        });
    });
});

// Reopen accordions from localStorage on page load
const openAccordions = JSON.parse(localStorage.getItem('openAccordions')) || [];
openAccordions.forEach(id => {
    const target = document.getElementById(id);
    if (target && target.classList.contains('accordion-collapse')) {
        new bootstrap.Collapse(target, {
            toggle: true
        });
    }
});

$(document).ready(function () {
    $('.accordion').on('click', '.button_copy_va,input', function () {
            const va_number = $(this).closest('.input-group').find('input').val(); // Ambil input value
            navigator.clipboard.writeText(va_number).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Nomor Virtual Account berhasil disalin',
                });
                // change button text
                $(this).text('Disalin');
            }).catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Terdapat kesalahan saat menyalin nomor Virtual Account',
                });
            });
        });

    $('.accordion').on('click', '.btn-show-qr', function () {
        let charge_id = $(this).data('id');
        const url_action = $(this).data('url');

        // Show loading
        $(`.spinner-qr-${charge_id}`).removeClass('d-none');

        // Load image and then show it
        const img = new Image();
        img.onload = function () {
            $(`.img-qr-${charge_id}`).attr('src', url_action).removeClass('d-none');
            $(`.spinner-qr-${charge_id}`).addClass('d-none');
            $(`.p-${charge_id}`).addClass('d-none');
            $(`.btn-qr-download-${charge_id}`).removeClass('d-none');
            $(`.btn-qr-show-${charge_id}`).addClass('d-none');
            $(`.btn-qr-hide-${charge_id}`).removeClass('d-none');
            $(`.btn-qr-hide-${charge_id}`).on('click', function () {
                $(`.img-qr-${charge_id}`).addClass('d-none');
                $(`.p-${charge_id}`).removeClass('d-none');
                $(`.btn-qr-download-${charge_id}`).addClass('d-none');
                $(`.btn-qr-show-${charge_id}`).removeClass('d-none');
                $(`.btn-qr-hide-${charge_id}`).addClass('d-none');
            });
        };
        img.src = url_action;
    });



    $('#modal_how_pay_button').click(function () {
        $('#modal_how_pay').modal('show');
    });

    // Close the modal when needed (optional)
    $('#closeModalButton').click(function () {
        $('#modal_how_pay').modal('hide');
    });

    // button pay
    $('.accordion-body').on('click', '#payButton', function () {
        let charge_id = $(this).data('id');

        if (!charge_id) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'ID transaksi tidak ditemukan!',
            });
            return;
        }

        // Cegah klik berulang
        $(this).prop('disabled', true);

        // console.log("Mengambil snap_token untuk charge_id:", charge_id);

        $.ajax({
            type: "GET",
            url: "{{ route('pembayaran.searchOrder') }}",
            data: { charge_id: charge_id },
            cache: false,
            success: function (response) {
                if (response.status === 'success' && response.snap_token) {
                    let snapToken = response.snap_token;
                    console.log(snapToken);

                    snap.pay(snapToken, {
                        onSuccess: function (result) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: 'Pembayaran Berhasil',
                            });
                            // $('#payButton').prop('disabled', false);
                        },
                        onPending: function (result) {
                            Swal.fire({
                                icon: 'info',
                                title: 'Pembayaran Sedang Dalam Proses',
                                text: 'Silakan lakukan pembayaran pada menu pembayaran.',
                            });
                            $('#payButton').prop('disabled', false);
                        },
                        onError: function (result) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'Pembayaran Gagal. Silakan coba lagi.',
                            });
                            // $('#payButton').prop('disabled', false);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.message || 'Terjadi kesalahan dalam mendapatkan snap_token',
                    });
                    // $('#payButton').prop('disabled', false);
                }
            },
            error: function (xhr) {
                console.error("Kesalahan Ajax:", xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Gagal mengambil data transaksi',
                });
                // $('#payButton').prop('disabled', false);
            }
        });
    });

    $('.accordion-body').on('click', '#detailButton', function () {
        let charge_id = $(this).data('id');

        if (!charge_id) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'ID transaksi tidak ditemukan!',
            });
            return;
        }

        $.ajax({
            type: "GET",
            url: "{{ route('pembayaran.searchOrderDetail') }}",
            data: {
                charge_id: charge_id
            },
            cache: false,
            success: function(response) {
                if (response.status === "success") {
                    showTransactionDetail(response.data);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Data tidak ditemukan!',
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Terjadi kesalahan, coba lagi.',
                });
            }
        });
    });

    function showTransactionDetail(data) {
        // Set nilai umum
        $("#detail_name").text(data.name ?? "-");
        $("#detail_order_id").text(data.order_id ?? "-");
        $("#detail_gross_amount").text(formatCurrency(data.gross_amount ?? 0));
        $("#detail_payment_type").text(data.payment_type ?? "-");
        $("#detail_transaction_status").text(data.transaction_status ?? "-");
        $("#detail_transaction_time").text(data.transaction_time ?? "-");

        // Tampilkan status transaksi dalam bentuk badge
        let statusBadge = "";
        if (data.transaction_status === 'settlement' || data.transaction_status === 'pay_offline' || data.transaction_status === 'capture' ) {
            statusBadge = `<span class="badge bg-success">Lunas</span>`;
        } else if (data.transaction_status === 'pending') {
            statusBadge = `<span class="badge bg-warning">Belum Lunas</span>`;
        } else if(data.transaction_status === 'free') {
            statusBadge = `<span class="badge bg-success">Gratis</span>`;

        } else {
            statusBadge = data.transaction_status;
        }
        $("#detail_transaction_status").html(statusBadge);

        // Reset semua elemen agar tidak ada data yang tertinggal dari transaksi sebelumnya
        $("#detail_bank, #detail_va_number").text("-");

        // Menentukan informasi tambahan berdasarkan payment_type
        switch (data.payment_type) {
            case "bank_transfer":
                $("#detail_bank").text(data.bank ?? "-");
                $("#detail_va_number").text(data.va_number ?? "-");
                break;
            case "credit_card":
                $("#detail_bank").text(data.bank ?? "-");
                $("#detail_va_number").text(data.card_number ? "**** **** **** " + data.card_number.slice(-4) : "-");
                break;
            case "qris":
                $("#detail_bank").text("QRIS");
                $("#detail_va_number").text("Tersedia (Silakan scan)");
                break;
            case "cstore":
                $("#detail_bank").text(data.store ?? "-");
                $("#detail_va_number").text(data.payment_code ?? "-");
                break;
            case "e_wallet":
                $("#detail_bank").text(data.e_wallet ?? "-");
                $("#detail_va_number").text("Tersedia (Lihat aplikasi e-wallet)");
                break;
            default:
                $("#detail_payment_type").text("Tipe pembayaran tidak dikenal");
                break;
        }

        // Tampilkan modal
        $("#modal_detail").modal("show");
    }

    // Fungsi untuk format angka ke mata uang (Rupiah)
    function formatCurrency(amount) {
        return new Intl.NumberFormat("id-ID", {
            style: "currency",
            currency: "IDR",
            minimumFractionDigits: 0
        }).format(amount);
    }
});
