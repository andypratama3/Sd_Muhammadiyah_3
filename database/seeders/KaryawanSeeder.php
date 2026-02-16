<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Karyawan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaryawanSeeder extends Seeder
{
    public function run(): void
    {
        /**
         * ======================
         * DATA GURU
         * ======================
         */
         $guru = [
            ['name' => 'Ansar HS, S.Pd., M.M., Gr.', 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
            ['name' => "Misbahul Jum'ah, S.Pd.I", 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
            ['name' => 'Inayah Auliana Syahrani, S.Pd', 'sex' => 'Perempuan', 'email' => 'inayahauliana07@gmail.com', 'phone' => '085386829287'],
            ['name' => 'Tendri Ridwan, S.Pd., Gr', 'sex' => 'Laki-Laki', 'email' => 'tendriridwan@gmail.com', 'phone' => '081254787640'],
            ['name' => 'Aprizal Saputra', 'sex' => 'Laki-Laki', 'email' => 'aprizalsaputraical04@gmail.com', 'phone' => '085141234888'],
            ['name' => 'Mashitho Nurhidayah, S.Pd', 'sex' => 'Perempuan', 'email' => 'mashitonurhidayah@gmail.com', 'phone' => '087886466544'],
            ['name' => 'Ranti Chandra, S.Pd', 'sex' => 'Perempuan', 'email' => 'rantic232@gmail.com', 'phone' => '0895700564573'],
            ['name' => 'Meirina Rosmayani, S.Pd', 'sex' => 'Perempuan', 'email' => 'mmeirina@0gmail.com', 'phone' => '081258742579'],
            ['name' => 'Nuratul Aulia, S.Sos', 'sex' => 'Perempuan', 'email' => 'nuratulaulia2@gmail.com', 'phone' => '081255638885'],
            ['name' => 'Olfia Fitry Ananova, S.Pd', 'sex' => 'Perempuan', 'email' => 'olfia.ana@gmail.com', 'phone' => '081352017024'],
            ['name' => 'Anisa Setya Dewi, S.Pd', 'sex' => 'Perempuan', 'email' => 'setyadewianisa@gmail.com', 'phone' => '082191680235'],
            ['name' => 'Dhea Cahyantari Waluyo, S.Pd', 'sex' => 'Perempuan', 'email' => 'Dheacahyantari1321@gmail.com', 'phone' => '085849454631'],
            ['name' => 'Alpina Nur Padila, S.Sos', 'sex' => 'Perempuan', 'email' => 'alpinapadila@gmail.com', 'phone' => '085822324514'],
            ['name' => 'Bella Diyas Meira Kusumawardani, S.Pd', 'sex' => 'Perempuan', 'email' => 'belladmk26@gmail.com', 'phone' => '082255594298'],
            ['name' => 'Dita Anggreini, S.Pd', 'sex' => 'Perempuan', 'email' => 'kazehaya.reim@gmail.com', 'phone' => '082350885953'],
            ['name' => 'Helda Saparina, S.Pd', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Bagus Abdurrahim, S.Ag., MOS., MTA', 'sex' => 'Laki-Laki', 'email' => 'gusabd9@gmail.com', 'phone' => '081350001943'],
            ['name' => 'Erviana Arya Maharani', 'sex' => 'Perempuan', 'email' => 'ervianaarya02@gmail.com', 'phone' => '085798202314'],
            ['name' => 'Ana Saripah, S.Pd', 'sex' => 'Perempuan', 'email' => 'anasrph1453@gmail.com', 'phone' => '081247504046'],
            ['name' => 'Ersa Dian Choirotunisak, S.Pd', 'sex' => 'Perempuan', 'email' => 'erzovi03@gmail.com', 'phone' => '081549668057'],
            ['name' => 'Ummu Khairin Nisa, S.Pd', 'sex' => 'Perempuan', 'email' => 'nisaummukhairin01@gmail.com', 'phone' => '085820869102'],
            ['name' => 'Nurul Azizah, S.Pd', 'sex' => 'Perempuan', 'email' => 'nurulazizah3700@gmail.com', 'phone' => '082353037338'],
            ['name' => 'Veronita I. M. L, S.Pd., Gr', 'sex' => 'Perempuan', 'email' => 'adjisaka1990@gmail.com', 'phone' => '085348623800'],
            ['name' => 'Indryanti, M.Pd', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Fitriani, S.Pd', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Andriyani Muhamad, S.Pd., Gr', 'sex' => 'Perempuan', 'email' => 'andriyanimuhamad@gmail.com', 'phone' => '085252537778'],
            ['name' => 'Muhammad Rizal, S.Pd', 'sex' => 'Laki-Laki', 'email' => 'rizalmuhammad939@gmail.com', 'phone' => '085238083302'],
            ['name' => 'Wiwik Kurniasih, S.Pd', 'sex' => 'Perempuan', 'email' => 'wkurniasih14@gmail.com', 'phone' => '085787334171'],
            ['name' => 'Marlina Dewi, S.Pd', 'sex' => 'Perempuan', 'email' => 'marlina.dewi118@guru.sd.belajar.id', 'phone' => '082351445259'],
            ['name' => 'Yuliani Kirana, S.Ag', 'sex' => 'Perempuan', 'email' => 'kiranayuliani040@gmail.com', 'phone' => '081350702229'],
            ['name' => 'Inayah', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Musdhalifah Zulhaifa, S.Pd., Gr', 'sex' => 'Perempuan', 'email' => 'haifaiffah19@gmail.com', 'phone' => '081256048332'],
            ['name' => 'Nahdiah, S.H., M.E.', 'sex' => 'Perempuan', 'email' => 'nahdynahdiah@gmail.com', 'phone' => '089510837651'],
            ['name' => 'M. Rifqie Abrar', 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
            ['name' => 'Meylisa Hidayah', 'sex' => 'Perempuan', 'email' => 'meylisamemey2@gmail.com', 'phone' => '082350347543'],
            ['name' => 'Ismi Parihah', 'sex' => 'Perempuan', 'email' => 'parihahismi@gmail.com', 'phone' => '085845113245'],
            ['name' => 'Indra Amaliyah Syahrani Amd. Keb', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Halimah Lutfi', 'sex' => 'Perempuan', 'email' => 'lutfihalimah04@gmail.com', 'phone' => '083116664601'],
            ['name' => 'Wanda Fadilah', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Siti Khadijah, S.H', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Norwana, S.Pd', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Ramadhania Iksan, S.Pd', 'sex' => 'Perempuan', 'email' => 'nia.iksan97@gmail.com', 'phone' => '085654907906'],
            ['name' => 'Maysaroh', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Arieza Maghfirah', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Maulida Rahmah, S.Sos', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Amalia Putri', 'sex' => 'Perempuan', 'email' => 'amaliaptri091@gmail.com', 'phone' => '0895337408009'],
            ['name' => 'Sakinah Adilah Bakri, S.Psi', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Nur Isma Mardhatillah, S.Pd', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
        ];

        foreach ($guru as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('sdmuhammadiyah3smd.com'),
            ]);

            $user->assignRole('guru');

            Karyawan::create([
                'name' => $data['name'],
                'sex' => $data['sex'],
                'jenis_pegawai' => 'guru',
                'user_id' => $user->id,
                'phone' => $data['phone'],
            ]);
        }

        /**
         * ======================
         * TENAGA KEPENDIDIKAN
         * ======================
         */
        $tendik = [
            ['name' => 'Rusmini, S.Pd', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Prananda Priyandan Mahmud, S.E', 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
            ['name' => 'Rima Melati', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Fadhilaturrahman, S.Pd', 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
            ['name' => 'Lisa Agus Tina, S.E., M.M.', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Nurhayati', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Rasya Putra Islami', 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
            ['name' => 'Fatimah', 'sex' => 'Perempuan', 'email' => '', 'phone' => ''],
            ['name' => 'Abdul Sahid', 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
            ['name' => 'Renaldi', 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
            ['name' => 'Farhan Ajran Y.', 'sex' => 'Laki-Laki', 'email' => '', 'phone' => ''],
        ];

        foreach ($tendik as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('sdmuhammadiyah3smd.com'),
            ]);

            $user->assignRole('tenaga-kependidikan');

            Karyawan::create([
                'name' => $data['name'],
                'sex' => $data['sex'],
                'jenis_pegawai' => 'tenaga_kependidikan',
                'user_id' => $user->id,
                'phone' => $data['phone'],
            ]);
        }
    }
}
