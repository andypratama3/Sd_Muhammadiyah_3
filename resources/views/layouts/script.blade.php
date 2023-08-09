<script>
    $(document).ready(function () {
        setTimeout( function () {
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
  </script>
