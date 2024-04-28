<?php

namespace App\Http\Controllers;
use App\Models\Siswa;
use App\Models\Kelas;

class NaikKelasController extends Controller
{
    public function naik()
    {
        // Retrieve all students
        $siswas = Siswa::all();

        // Retrieve all classes ordered by name
        $kelass = Kelas::orderBy('name', 'asc')->get();

        // Loop through each student
        foreach ($siswas as $siswa) {
            // Get the current class of the student
            $currentKelas = $siswa->kelas()->first();

            // Extract the class number from the current class name
            preg_match('/Kelas (\d+)/', $currentKelas->name, $matches);
            $currentClassNumber = intval($matches[1]);

            // Find the next class for the student
            $nextClass = null;
            foreach ($kelass as $kelas) {
                // Extract the class number from the class name
                if($kelas->name == 'Lulus'){
                    $nextClass = 'Lulus';
                }else{
                    preg_match('/Kelas (\d+)/', $kelas->name, $matches);
                    $nextClassNumber = intval($matches[1]);
                }

                // Check if the current class number is less than the next class number
                if ($currentClassNumber < $nextClassNumber) {
                    // If nextClass is null or the current next class is greater than the next class found
                    if (!$nextClass || $nextClassNumber < intval(preg_replace('/\D/', '', $nextClass->name))) {
                        $nextClass = $kelas;
                    }else{
                        $lulus = 'Lulus';
                        $kelas_find = Kelas::where('name', $lulus)->first();
                        $siswa->kelas()->sync($kelas_find->id);
                    }
                }
            }

            // Move the student to the next class if found
            if ($nextClass) {
                $siswa->kelas()->sync([$nextClass->id => ['category_kelas' => $siswa->kelas->first()->pivot->category_kelas]]);
            }
        }

        // Optionally, you may return a response indicating the process is completed
        return redirect()->route('dashboard.datamaster.siswa.index')->with('success','Fungsi Naik Kelas Berhasil');
    }
}
