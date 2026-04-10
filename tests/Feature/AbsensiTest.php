<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Karyawan;
use App\Models\Absensi;
use App\Models\JamKerja;
use App\Models\LokasiAbsensi;
use App\Models\Role;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AbsensiTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function buatRole(string $roleName): Role
    {
        // Skema roles project ini: id, name, slug, deleted_at, created_at, updated_at
        // TANPA guard_name — sesuai hasil dump DB
        return Role::firstOrCreate(['name' => $roleName]);
    }

    private function buatKaryawanDenganRole(string $roleName): array
    {
        $role     = $this->buatRole($roleName);
        $user     = User::factory()->create();
        $user->roles()->sync([$role->id]);
        $karyawan = Karyawan::factory()->create(['user_id' => $user->id]);
        $karyawan->load('user.roles');

        return compact('user', 'karyawan', 'role');
    }

    private function buatJamKerja(string $jenisPegawai, array $override = []): JamKerja
    {
        return JamKerja::factory()->create(array_merge([
            'jenis_pegawai' => $jenisPegawai,
            'hari'          => strtolower(Carbon::now('Asia/Makassar')->locale('id')->dayName),
        ], $override));
    }

    private function buatLokasi(): LokasiAbsensi
    {
        return LokasiAbsensi::factory()->create();
    }

    // =========================================================================
    // 1. MODEL: getJenisPegawaiFromRoleAttribute
    // =========================================================================

    /** @test */
    public function model_mengembalikan_label_guru_untuk_role_guru(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('guru');
        $this->assertEquals('Guru', $karyawan->jenis_pegawai_from_role);
    }

    /** @test */
    public function model_mengembalikan_label_tenaga_pendidik_untuk_role_tenaga_pendidikan(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('tenaga-pendidikan');
        $this->assertEquals('Tenaga Pendidik', $karyawan->jenis_pegawai_from_role);
    }

    /** @test */
    public function model_mengembalikan_label_shadow_teacher(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('shadow-teacher');
        $this->assertEquals('Shadow Teacher', $karyawan->jenis_pegawai_from_role);
    }

    /** @test */
    public function model_mengembalikan_label_admin(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('admin');
        $this->assertEquals('Admin', $karyawan->jenis_pegawai_from_role);
    }

    /** @test */
    public function model_mengembalikan_nama_role_asli_jika_tidak_ada_di_map(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('role-tidak-dikenal');
        $this->assertEquals('role-tidak-dikenal', $karyawan->jenis_pegawai_from_role);
    }

    /** @test */
    public function model_mengembalikan_jenis_pegawai_db_jika_tidak_ada_user(): void
    {
        $karyawan = Karyawan::factory()->create(['user_id' => null, 'jenis_pegawai' => 'guru']);
        $this->assertEquals('guru', $karyawan->jenis_pegawai_from_role);
    }

    // =========================================================================
    // 2. SERVICE: getJenisPegawaiFromRole
    // =========================================================================

    /** @test */
    public function service_mengembalikan_slug_guru(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('guru');
        $this->assertEquals('guru', app(AbsensiService::class)->getJenisPegawaiFromRole($karyawan));
    }

    /** @test */
    public function service_mengembalikan_slug_tenaga_pendidikan(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('tenaga-pendidikan');
        $this->assertEquals('tenaga-pendidikan', app(AbsensiService::class)->getJenisPegawaiFromRole($karyawan));
    }

    /** @test */
    public function service_mengembalikan_umum_untuk_admin(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('admin');
        $this->assertEquals('umum', app(AbsensiService::class)->getJenisPegawaiFromRole($karyawan));
    }

    /** @test */
    public function service_mengembalikan_umum_untuk_superadmin(): void
    {
        ['karyawan' => $karyawan] = $this->buatKaryawanDenganRole('superadmin');
        $this->assertEquals('umum', app(AbsensiService::class)->getJenisPegawaiFromRole($karyawan));
    }

    /** @test */
    public function service_mengembalikan_umum_jika_tidak_ada_role(): void
    {
        $karyawan = Karyawan::factory()->create(['user_id' => null]);
        $this->assertEquals('umum', app(AbsensiService::class)->getJenisPegawaiFromRole($karyawan));
    }

    // =========================================================================
    // 3. ABSEN MASUK — Happy Path
    // =========================================================================

    /** @test */
    public function guru_bisa_absen_masuk_sebelum_batas_jam(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('guru');
        $this->buatJamKerja('guru');
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 30));

        $result = app(AbsensiService::class)->absenMasuk(
            $user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id
        );

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertEquals('tepat_waktu', $result['data']['status']);
        $this->assertEquals(4000, $result['data']['rp_masuk']);
    }

    /** @test */
    public function tenaga_pendidikan_absen_masuk_sebelum_jam_masuk_dapat_poin(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('tenaga-pendidikan');
        $this->buatJamKerja('tenaga-pendidikan', ['jam_masuk' => '06:45:00', 'batas_masuk' => '07:00:00']);
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 40));

        $result = app(AbsensiService::class)->absenMasuk(
            $user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id
        );

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertEquals('tepat_waktu', $result['data']['status']);
        $this->assertEquals(4000, $result['data']['rp_masuk']);
    }

    /** @test */
    public function tenaga_pendidikan_terlambat_tidak_dapat_poin_tapi_masih_bisa_absen(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('tenaga-pendidikan');
        $this->buatJamKerja('tenaga-pendidikan', ['jam_masuk' => '06:45:00', 'batas_masuk' => '07:00:00']);
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 50));

        $result = app(AbsensiService::class)->absenMasuk(
            $user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id
        );

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertEquals('terlambat', $result['data']['status']);
        $this->assertEquals(0, $result['data']['rp_masuk']);
    }

    /** @test */
    public function shadow_teacher_tidak_mendapat_poin_masuk(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('shadow-teacher');
        $this->buatJamKerja('shadow-teacher');
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 30));

        $result = app(AbsensiService::class)->absenMasuk(
            $user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id
        );

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertEquals(0, $result['data']['rp_masuk']);
    }

    // =========================================================================
    // 4. ABSEN MASUK — Blocked Cases
    // =========================================================================

    /** @test */
    public function semua_role_terbatas_diblokir_setelah_batas_masuk(): void
    {
        $lokasi = $this->buatLokasi();

        foreach (['guru', 'tenaga-pendidikan', 'shadow-teacher'] as $roleName) {
            ['user' => $user] = $this->buatKaryawanDenganRole($roleName);
            $this->buatJamKerja($roleName, ['batas_masuk' => '07:00:00']);

            Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(7, 1));

            $result = app(AbsensiService::class)->absenMasuk(
                $user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id
            );

            $this->assertFalse($result['success'], "Role {$roleName} seharusnya diblokir");
            $this->assertStringContainsStringIgnoringCase('terlambat', $result['message']);
        }
    }

    /** @test */
    public function tidak_bisa_absen_masuk_dua_kali_dalam_sehari(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('guru');
        $this->buatJamKerja('guru');
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 30));
        app(AbsensiService::class)->absenMasuk($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        $result = app(AbsensiService::class)->absenMasuk($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('sudah absen masuk', $result['message']);
    }

    // =========================================================================
    // 5. ABSEN PULANG
    // =========================================================================

    /** @test */
    public function guru_absen_pulang_tepat_waktu_dapat_poin(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('guru');
        $this->buatJamKerja('guru', ['batas_pulang' => '14:00:00']);
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 30));
        app(AbsensiService::class)->absenMasuk($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(14, 5));
        $result = app(AbsensiService::class)->absenPulang($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertEquals('tepat_waktu', $result['data']['status']);
        $this->assertEquals(4000, $result['data']['rp_pulang']);
    }

    /** @test */
    public function pulang_cepat_tidak_dapat_poin_rp_pulang(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('guru');
        $this->buatJamKerja('guru', ['batas_pulang' => '14:00:00']);
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 30));
        app(AbsensiService::class)->absenMasuk($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(12, 0));
        $result = app(AbsensiService::class)->absenPulang($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        $this->assertTrue($result['success'], $result['message'] ?? '');
        $this->assertEquals('pulang_cepat', $result['data']['status']);
        $this->assertEquals(0, $result['data']['rp_pulang']);
    }

    /** @test */
    public function tidak_bisa_absen_pulang_tanpa_absen_masuk(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('guru');
        $lokasi = $this->buatLokasi();

        $result = app(AbsensiService::class)->absenPulang($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('belum melakukan absensi masuk', $result['message']);
    }

    /** @test */
    public function tidak_bisa_absen_pulang_dua_kali(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('guru');
        $this->buatJamKerja('guru');
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 30));
        app(AbsensiService::class)->absenMasuk($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(14, 5));
        app(AbsensiService::class)->absenPulang($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        $result = app(AbsensiService::class)->absenPulang($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('sudah absen pulang', $result['message']);
    }

    /** @test */
    public function jika_tidak_dapat_poin_masuk_maka_pulang_juga_tidak_dapat(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('tenaga-pendidikan');
        $this->buatJamKerja('tenaga-pendidikan');
        $lokasi = $this->buatLokasi();

        // Absen masuk terlambat (06:50) -> Rp 0
        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 50));
        $resMasuk = app(AbsensiService::class)->absenMasuk($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);
        $this->assertEquals(0, $resMasuk['data']['rp_masuk']);

        // Absen pulang tepat waktu (14:05) -> Harus tetap Rp 0 karena masuknya telat
        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(14, 5));
        $resPulang = app(AbsensiService::class)->absenPulang($user->id, $lokasi->latitude, $lokasi->longitude, $lokasi->id);
        
        $this->assertTrue($resPulang['success']);
        $this->assertEquals(0, $resPulang['data']['rp_pulang']);
    }

    // =========================================================================
    // 6. VALIDASI LOKASI
    // =========================================================================

    /** @test */
    public function absen_ditolak_jika_di_luar_radius(): void
    {
        ['user' => $user] = $this->buatKaryawanDenganRole('guru');
        $this->buatJamKerja('guru');
        $lokasi = $this->buatLokasi();

        Carbon::setTestNow(Carbon::now('Asia/Makassar')->setTime(6, 30));

        $result = app(AbsensiService::class)->absenMasuk($user->id, 0.0, 0.0, $lokasi->id);

        $this->assertFalse($result['success']);
        $this->assertStringContainsStringIgnoringCase('radius', $result['message']);
    }

    // =========================================================================
    // 7. RIWAYAT ABSENSI
    // =========================================================================

    /** @test */
    public function riwayat_absensi_mengembalikan_data_bulan_ini(): void
    {
        ['user' => $user, 'karyawan' => $karyawan] = $this->buatKaryawanDenganRole('guru');

        Absensi::factory()->count(3)->create([
            'karyawan_id' => $karyawan->id,
            'tanggal'     => now()->format('Y-m-d'),
        ]);

        $result = app(AbsensiService::class)->getRiwayatAbsensi($user->id);

        $this->assertTrue($result['success']);
        $this->assertCount(3, $result['data']['riwayat']);
        $this->assertEquals($karyawan->name, $result['data']['pegawai']['nama']);
        $this->assertEquals('guru', $result['data']['pegawai']['jenis_pegawai']);
    }

    /** @test */
    public function riwayat_gagal_jika_karyawan_tidak_ditemukan(): void
    {
        $result = app(AbsensiService::class)->getRiwayatAbsensi('uuid-tidak-ada-sama-sekali');

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak ditemukan', $result['message']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}