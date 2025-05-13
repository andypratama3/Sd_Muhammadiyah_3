<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Formulir PPDB - SD Kreatif</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background: linear-gradient(to right, #ffffff, #bcdc20);
      font-family: 'Poppins', sans-serif;
    }

    .form-container {
      background: #fff;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      animation: fadeIn 1s ease-in-out;
    }

    h1, h4 {
      text-align: center;
      color: #1565c0;
    }

    .section-title {
      font-weight: 600;
      background-color: #bbdefb;
      padding: 12px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 1.2rem;
      color: #0d47a1;
    }

    .btn-submit {
      background-color: #4caf50;
      color: white;
    }

    .btn-submit:hover {
      background-color: #388e3c;
    }

    .step {
      display: none;
      animation: slideIn 0.6s ease;
    }

    .bg-information {
        background-image: url('ppdb_asset/bg-information.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        border-radius: 12px;
        padding: 40px;
        color: white;
        min-height: 300px;
        position: relative;
        overflow: hidden;
        }

    .bg-information::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.4); /* Lapisan gelap untuk meningkatkan keterbacaan teks */
        z-index: 0;
    }

    .bg-information > * {
        position: relative;
        z-index: 1;
    }

    .bg-information .text-content {
        text-align: center;
    }

    .step.active {
      display: block;
    }

    @keyframes slideIn {
      from { opacity: 0; transform: translateX(50px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    .progressbar {
      counter-reset: step;
      display: flex;
      justify-content: space-between;
      margin-bottom: 30px;
    }

    .progressbar li {
      list-style-type: none;
      position: relative;
      text-align: center;
      width: 100%;
      font-weight: 600;
      color: #9e9e9e;
    }

    .progressbar li:before {
      counter-increment: step;
      content: counter(step);
      width: 40px;
      height: 40px;
      line-height: 40px;
      border: 2px solid #90caf9;
      display: block;
      text-align: center;
      margin: 0 auto 10px auto;
      border-radius: 50%;
      background-color: #fff;
    }

    .progressbar li:after {
      content: '';
      position: absolute;
      width: 100%;
      height: 2px;
      background-color: #90caf9;
      top: 20px;
      left: -50%;
      z-index: -1;
    }

    .progressbar li:first-child:after {
      content: none;
    }

    .progressbar li.active {
      color: #1565c0;
    }

    .progressbar li.active:before {
      border-color: #1565c0;
      background-color: #bbdefb;
    }
  </style>
</head>
<body>

<div class="container py-5">
  <div class="form-container">
    <h1>🎓 Formulir Pendaftaran Siswa Baru</h1>
    <h4>SD Kreatif Muhammadiyah 3 Samarinda<br>Tahun Ajaran 2025/2026</h4>

    <!-- Progress Bar -->
    <ul class="progressbar mb-4" id="progressbar">
      <li class="active">Informasi</li>
      <li>Data Siswa</li>
      <li>Orang Tua</li>
      <li>Lampiran</li>
    </ul>

    <form id="ppdbForm">

      <div class="step active">
        <div class="section-title">📝 Informasi Pendaftaran</div>
       <div class="container-information bg-information">
            <div class="text-content">
                <h5>Selamat datang di Pendaftaran Peserta Didik Baru (PPDB) SD Kreatif Muhammadiyah 3 Samarinda!</h5>
                <p>Silakan lengkapi formulir berikut secara bertahap untuk mendaftarkan anak Anda pada Tahun Ajaran 2025/2026.</p>
            </div>
        </div>
      </div>
      <!-- Step 1 -->
      <div class="step ">
        <div class="section-title">🧒 Data Calon Siswa</div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Nama Lengkap Anak:</label>
            <input type="text" class="form-control" required />
          </div>
          <div class="col-md-3 mb-3">
            <label>Tempat Lahir:</label>
            <input type="text" class="form-control" required />
          </div>
          <div class="col-md-3 mb-3">
            <label>Tanggal Lahir:</label>
            <input type="date" class="form-control" required />
          </div>
          <div class="col-md-3 mb-3">
            <label>Jenis Kelamin:</label>
            <select class="form-select" required>
              <option value="">-- Pilih --</option>
              <option>Laki-laki</option>
              <option>Perempuan</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label>Agama:</label>
            <input type="text" class="form-control" value="Islam" readonly />
          </div>
          <div class="col-md-6 mb-3">
            <label>Alamat:</label>
            <textarea class="form-control" rows="2" required></textarea>
          </div>
        </div>
        <div class="text-end">
          <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut</button>
        </div>
      </div>

      <!-- Step 2 -->
      <div class="step active">
        <div class="section-title">👨‍👩‍👧 Data Orang Tua / Wali</div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label>Nama Ayah:</label>
            <input type="text" class="form-control" required />
          </div>
          <div class="col-md-6 mb-3">
            <label>Pekerjaan Ayah:</label>
            <input type="text" class="form-control" />
          </div>
          <div class="col-md-6 mb-3">
            <label>Nama Ibu:</label>
            <input type="text" class="form-control" required />
          </div>
          <div class="col-md-6 mb-3">
            <label>Pekerjaan Ibu:</label>
            <input type="text" class="form-control" />
          </div>
          <div class="col-md-6 mb-3">
            <label>No. HP Orang Tua:</label>
            <input type="tel" class="form-control" placeholder="08xxxxxxxxxx" required />
          </div>
        </div>
        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" onclick="prevStep()">⬅️ Kembali</button>
          <button type="button" class="btn btn-primary" onclick="nextStep()">Lanjut ➡️</button>
        </div>
      </div>

      <!-- Step 3 -->
      <div class="step">
        <div class="section-title">📎 Lampiran Dokumen</div>
        <div class="row">
          <div class="col-md-4 mb-3">
            <label>Upload Foto Anak (3x4):</label>
            <input type="file" class="form-control" required />
          </div>
          <div class="col-md-4 mb-3">
            <label>Upload Akta Kelahiran:</label>
            <input type="file" class="form-control" required />
          </div>
          <div class="col-md-4 mb-3">
            <label>Upload Kartu Keluarga:</label>
            <input type="file" class="form-control" required />
          </div>
        </div>
        <div class="d-flex justify-content-between">
          <button type="button" class="btn btn-secondary" onclick="prevStep()">⬅️ Kembali</button>
          <button type="submit" class="btn btn-submit px-4">🎒 Kirim Pendaftaran</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  let currentStep = 0;
  const steps = document.querySelectorAll('.step');
  const progressbar = document.querySelectorAll('#progressbar li');

  function showStep(index) {
    steps.forEach((step, i) => {
      step.classList.toggle('active', i === index);
      progressbar[i].classList.toggle('active', i <= index);
    });
  }

  function nextStep() {
    if (currentStep < steps.length - 1) {
      currentStep++;
      showStep(currentStep);
    }
  }

  function prevStep() {
    if (currentStep > 0) {
      currentStep--;
      showStep(currentStep);
    }
  }

  document.getElementById("ppdbForm").addEventListener("submit", function (e) {
    e.preventDefault();
    alert("✅ Terima kasih! Formulir berhasil dikirim.");
  });
</script>

</body>
</html>
