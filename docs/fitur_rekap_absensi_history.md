# Dokumentasi Fitur Rekap Absensi History

**Tanggal:** 28 April 2026  
**Waktu Pengerjaan:** Sore hingga Malam  
**Status:** Draft (Belum selesai, lanjut jam 23:00)

## 1. Tujuan Fitur
Admin dapat meng-generate rekap absensi (PDF/Excel) berdasarkan tanggal, sistem menyimpan history-nya, dan admin dapat mengatur status (Draft/Publish). Karyawan dapat mengakses file individual mereka jika status sudah "Published".

## 2. File yang Dibuat/Diubah

### A. Database
- **Migration:** `database/migrations/2026_04_28_150013_create_rekap_absensi_histories_table.php`
  - Tabel: `rekap_absensi_histories`
  - Field: `id`, `user_id` (UUID, FK ke users), `start_date`, `end_date`, `zip_file_path`, `zip_filename`, `status` (enum: draft/published), `file_per_karyawan` (JSON), `keterangan`, `timestamps`.
  - **Status:** Sudah di-migrate.

### B. Models
- **Model Baru:** `app/Models/RekapAbsensiHistory.php`
  - Relasi: `user()` (belongsTo User).
  - Casting: `start_date`, `end_date` ke date, `file_per_karyawan` ke json.
  - Accessor: `date_range_label`.

### C. Controllers
- **Modified:** `app/Http/Controllers/Dashboard/Absensi/RekapAbsensiController.php`
  - Menambahkan import `RekapAbsensiHistory` dan `Storage`.
  - Modifikasi method `exportPdf()`: Setelah generate ZIP, menyimpan record ke `rekap_absensi_histories` dengan status 'draft' dan memindahkan file ZIP ke `storage/app/public/rekap_absensi/[Y/m]/`.
  - Modifikasi method `exportExcel()`: Logika sama dengan PDF.

- **Baru:** `app/Http/Controllers/Dashboard/Absensi/RekapAbsensiHistoryController.php`
  - **Method Admin:**
    - `index()`: List history (DataTables).
    - `show()`: Detail history & list file per karyawan.
    - `publish()`: Update status ke 'published'.
    - `unpublish()`: Kembalikan ke 'draft'.
    - `download()`: Download ZIP file.
    - `destroy()`: Hapus history & file ZIP.
  - **Method Karyawan:**
    - `karyawanIndex()`: List rekap yang berstatus 'published' untuk diri sendiri.
    - `karyawanDownload()`: Download file individual dari ZIP berdasarkan `karyawan_id`.

### D. Views
- **Folder Baru:** `resources/views/dashboard/absensis/rekap/history/`
- `index.blade.php`: Tabel DataTables history untuk admin, tombol Publish/Unpublish/Download/Delete.
- `show.blade.php`: Detail history, menampilkan metadata dan daftar file per karyawan.
- `karyawan_index.blade.php`: View untuk karyawan melihat rekap yang sudah dipublish.

### E. Routes
- **File:** `routes/web.php`
- Menambahkan import `RekapAbsensiHistoryController`.
- **Route Group:** `rekap-absensi-history` (prefix: `dashboard/rekap-absensi-history`)
  - `GET /`: Index history.
  - `GET /{id}`: Show detail.
  - `POST /{id}/publish`: Publish.
  - `POST /{id}/unpublish`: Unpublish.
  - `GET /{id}/download`: Download ZIP.
  - `DELETE /{id}`: Delete.
  - `GET /karyawan-index`: Karyawan index.
  - `GET /{id}/karyawan-download/{karyawanId}`: Karyawan download.

## 3. Catatan Pengerjaan
- **Kendala:** Migration awal gagal karena `user_id` menggunakan `foreignId` (bigint) padahal tabel `users` menggunakan `uuid`. Diperbaiki dengan manual define `$table->uuid('user_id')` dan `$table->foreign(...)`.
- **Storage:** File ZIP dipindah dari `storage/app/temp/` ke `storage/app/public/rekap_absensi/` agar permanen dan bisa diakses oleh karyawan.

## 4. To-Do List (Lanjutan Jam 23:00)
- [ ] **Cek Error:** Jalankan `php artisan route:clear`, `config:clear`, `view:clear`.
- [ ] **Test:** Coba generate PDF/Excel dari menu Rekap Absensi, pastikan record masuk ke tabel `rekap_absensi_histories` dengan status 'draft'.
- [ ] **Test:** Coba akses `/dashboard/rekap-absensi-history` (Admin) dan pastikan DataTables muncul.
- [ ] **Test:** Fitur Publish/Unpublish.
- [ ] **Test:** Akses karyawan ke file mereka setelah publish.
- [ ] **Perbaikan:** Jika ada error syntax atau logic di controller/views.
- [ ] **Link Navigasi:** Tambahkan menu "History Rekap" di sidebar dashboard jika perlu.

## 5. Dependencies
- Laravel 10
- Maatwebsite Excel (untuk Export)
- DOMPDF (untuk PDF)
- Yajra DataTables
- Spatie/Laravel-ActivityLog (jika digunakan untuk logging)
