    let currentStep = 0;
    const steps = document.querySelectorAll('.step');
    const progressbar = document.querySelectorAll('.progressbar li');


    // show
    const ortu = document.getElementById('ortu');
    const wali = document.getElementById('wali');

    document.getElementById('selected_data').addEventListener('change', function() {
      if (this.value === 'orang_tua') {
        ortu.classList.remove('d-none');
        document.querySelectorAll('#ortu input').forEach(input => input.setAttribute('required', true));
        wali.classList.add('d-none');
        document.querySelectorAll('#wali input').forEach(input => input.removeAttribute('required'));
      } else if (this.value === 'wali') {
        ortu.classList.add('d-none');
        document.querySelectorAll('#ortu input').forEach(input => input.removeAttribute('required'));

        wali.classList.remove('d-none');
        document.querySelectorAll('#wali input').forEach(input => input.setAttribute('required', true));
      }
    });

    function showStep(index) {
      steps.forEach((step, i) => {
        step.classList.toggle('active', i === index);
        progressbar[i].classList.toggle('active', i <= index);
      });
      currentStep = index;
    }


    function nextStep() {
    const currentInputs = steps[currentStep].querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;

    currentInputs.forEach(input => {
        if (!input.checkValidity()) {
        isValid = false;
        input.classList.add('is-invalid'); // Optional: tambahkan efek visual
        } else {
        input.classList.remove('is-invalid');
        }
    });

    // Jika semua input valid, lanjut ke step berikutnya
    if (isValid && currentStep < steps.length - 1) {
        showStep(currentStep + 1);
    }
    }


    function prevStep() {
      if (currentStep > 0) {
        showStep(currentStep - 1);
      }
    }

    document.getElementById('ppdbForm').addEventListener('submit', function(e) {
      e.preventDefault();
      alert('Formulir berhasil dikirim! 🎉');
    });
