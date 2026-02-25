<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaryawanSeeder extends Seeder
{
    /**
     * Generate email dari nama:
     * 1. Ambil bagian nama sebelum koma pertama (hapus gelar)
     * 2. Hapus karakter selain huruf dan spasi
     * 3. Lowercase & ganti spasi dengan titik
     * Contoh: "Inayah Auliana Syahrani, S.Pd" → "inayah.auliana.syahrani@sdmuhammadiyah3smd.com"
     */
    private function generateEmail(string $name): string
    {
        // Ambil bagian sebelum koma (hapus gelar di belakang)
        $baseName = explode(',', $name)[0];

        // Hapus karakter selain huruf dan spasi, lalu trim
        $cleaned = preg_replace('/[^a-zA-Z\s]/', '', $baseName);
        $cleaned = trim(preg_replace('/\s+/', ' ', $cleaned));

        // Lowercase dan ganti spasi dengan titik
        $emailLocal = str_replace(' ', '.', strtolower($cleaned));

        return $emailLocal . '@sdmuhammadiyah3smd.com';
    }

    public function run(): void
    {
        /**
         * ======================
         * DATA GURU
         * ======================
         */
        $guru = [
            ['name' => 'Ansar HS, S.Pd., M.M., Gr.', 'sex' => 'Laki-Laki', 'phone' => ''],
            ['name' => "Misbahul Jum'ah, S.Pd.I", 'sex' => 'Laki-Laki', 'phone' => ''],
            ['name' => 'Inayah Auliana Syahrani, S.Pd', 'sex' => 'Perempuan', 'phone' => '085386829287'],
            ['name' => 'Tendri Ridwan, S.Pd., Gr', 'sex' => 'Laki-Laki', 'phone' => '081254787640'],
            ['name' => 'Aprizal Saputra', 'sex' => 'Laki-Laki', 'phone' => '085141234888'],
            ['name' => 'Mashitho Nurhidayah, S.Pd', 'sex' => 'Perempuan', 'phone' => '087886466544'],
            ['name' => 'Ranti Chandra, S.Pd', 'sex' => 'Perempuan', 'phone' => '0895700564573'],
            ['name' => 'Meirina Rosmayani, S.Pd', 'sex' => 'Perempuan', 'phone' => '081258742579'],
            ['name' => 'Nuratul Aulia, S.Sos', 'sex' => 'Perempuan', 'phone' => '081255638885'],
            ['name' => 'Olfia Fitry Ananova, S.Pd', 'sex' => 'Perempuan', 'phone' => '081352017024'],
            ['name' => 'Anisa Setya Dewi, S.Pd', 'sex' => 'Perempuan', 'phone' => '082191680235'],
            ['name' => 'Dhea Cahyantari Waluyo, S.Pd', 'sex' => 'Perempuan', 'phone' => '085849454631'],
            ['name' => 'Alpina Nur Padila, S.Sos', 'sex' => 'Perempuan', 'phone' => '085822324514'],
            ['name' => 'Bella Diyas Meira Kusumawardani, S.Pd', 'sex' => 'Perempuan', 'phone' => '082255594298'],
            ['name' => 'Dita Anggreini, S.Pd', 'sex' => 'Perempuan', 'phone' => '082350885953'],
            ['name' => 'Helda Saparina, S.Pd', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Bagus Abdurrahim, S.Ag., MOS., MTA', 'sex' => 'Laki-Laki', 'phone' => '081350001943'],
            ['name' => 'Erviana Arya Maharani', 'sex' => 'Perempuan', 'phone' => '085798202314'],
            ['name' => 'Ana Saripah, S.Pd', 'sex' => 'Perempuan', 'phone' => '081247504046'],
            ['name' => 'Ersa Dian Choirotunisak, S.Pd', 'sex' => 'Perempuan', 'phone' => '081549668057'],
            ['name' => 'Ummu Khairin Nisa, S.Pd', 'sex' => 'Perempuan', 'phone' => '085820869102'],
            ['name' => 'Nurul Azizah, S.Pd', 'sex' => 'Perempuan', 'phone' => '082353037338'],
            ['name' => 'Veronita I. M. L, S.Pd., Gr', 'sex' => 'Perempuan', 'phone' => '085348623800'],
            ['name' => 'Indryanti, M.Pd', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Fitriani, S.Pd', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Andriyani Muhamad, S.Pd., Gr', 'sex' => 'Perempuan', 'phone' => '085252537778'],
            ['name' => 'Muhammad Rizal, S.Pd', 'sex' => 'Laki-Laki', 'phone' => '085238083302'],
            ['name' => 'Wiwik Kurniasih, S.Pd', 'sex' => 'Perempuan', 'phone' => '085787334171'],
            ['name' => 'Marlina Dewi, S.Pd', 'sex' => 'Perempuan', 'phone' => '082351445259'],
            ['name' => 'Yuliani Kirana, S.Ag', 'sex' => 'Perempuan', 'phone' => '081350702229'],
            ['name' => 'Inayah', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Musdhalifah Zulhaifa, S.Pd., Gr', 'sex' => 'Perempuan', 'phone' => '081256048332'],
            ['name' => 'Nahdiah, S.H., M.E.', 'sex' => 'Perempuan', 'phone' => '089510837651'],
            ['name' => 'M. Rifqie Abrar', 'sex' => 'Laki-Laki', 'phone' => ''],
            ['name' => 'Meylisa Hidayah', 'sex' => 'Perempuan', 'phone' => '082350347543'],
            ['name' => 'Ismi Parihah', 'sex' => 'Perempuan', 'phone' => '085845113245'],
            ['name' => 'Indra Amaliyah Syahrani Amd. Keb', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Halimah Lutfi', 'sex' => 'Perempuan', 'phone' => '083116664601'],
            ['name' => 'Wanda Fadilah', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Siti Khadijah, S.H', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Norwana, S.Pd', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Ramadhania Iksan, S.Pd', 'sex' => 'Perempuan', 'phone' => '085654907906'],
            ['name' => 'Maysaroh', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Arieza Maghfirah', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Maulida Rahmah, S.Sos', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Amalia Putri', 'sex' => 'Perempuan', 'phone' => '0895337408009'],
            ['name' => 'Sakinah Adilah Bakri, S.Psi', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Nur Isma Mardhatillah, S.Pd', 'sex' => 'Perempuan', 'phone' => ''],
        ];

        foreach ($guru as $data) {
            $email = $this->generateEmail($data['name']);

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $email,
                'password' => Hash::make('sdmuhammadiyah3smd.com'),
            ]);

            $user->assignRole('guru');

            Karyawan::create([
                'name'          => $data['name'],
                'sex'           => $data['sex'],
                'user_id'       => $user->id,
                'phone'         => $data['phone'],
            ]);
        }

        /**
         * ======================
         * TENAGA KEPENDIDIKAN
         * ======================
         */
        $tendik = [
            ['name' => 'Rusmini, S.Pd', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Prananda Priyandan Mahmud, S.E', 'sex' => 'Laki-Laki', 'phone' => ''],
            ['name' => 'Rima Melati', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Fadhilaturrahman, S.Pd', 'sex' => 'Laki-Laki', 'phone' => ''],
            ['name' => 'Lisa Agus Tina, S.E., M.M.', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Nurhayati', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Rasya Putra Islami', 'sex' => 'Laki-Laki', 'phone' => ''],
            ['name' => 'Fatimah', 'sex' => 'Perempuan', 'phone' => ''],
            ['name' => 'Abdul Sahid', 'sex' => 'Laki-Laki', 'phone' => ''],
            ['name' => 'Renaldi', 'sex' => 'Laki-Laki', 'phone' => ''],
            ['name' => 'Farhan Ajran Y.', 'sex' => 'Laki-Laki', 'phone' => ''],
        ];

        foreach ($tendik as $data) {
            $email = $this->generateEmail($data['name']);

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $email,
                'password' => Hash::make('sdmuhammadiyah3smd.com'),
            ]);

            $user->assignRole('tenaga-pendidikan');

            Karyawan::create([
                'name'          => $data['name'],
                'sex'           => $data['sex'],
                'user_id'       => $user->id,
                'phone'         => $data['phone'],
            ]);
        }
    }
}