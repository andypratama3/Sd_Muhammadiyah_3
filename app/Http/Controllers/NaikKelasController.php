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
    
        // Retrieve the "Lulus" class if it exists
        $lulus = Kelas::where('name', 'Lulus')->firstOrFail();
    
        // Loop through each student
        foreach ($siswas as $siswa) {
            // Get the current class of the student
            $currentKelas = $siswa->kelas()->first();
    
            // If the current class is "Lulus" or greater than 6, move the student to "Lulus" class and continue to the next student
            if ($currentKelas && intval(substr($currentKelas->name, 6)) >= 6) {
                $siswa->kelas()->sync([$lulus->id => ['category_kelas' => $siswa->kelas->first()->pivot->category_kelas]]);
                continue; // Skip the rest of the loop for this student
            }
    
            // If current class is less than 6, increment the class by 1
            if ($currentKelas) {
                preg_match('/Kelas (\d+)/', $currentKelas->name, $matches);
                if (isset($matches[1])) {
                    $nextClassNumber = intval($matches[1]) + 1;
                    $nextClassName = "Kelas $nextClassNumber";
                    $nextClass = Kelas::where('name', $nextClassName)->first();
                    if ($nextClass) {
                        $siswa->kelas()->sync([$nextClass->id => ['category_kelas' => $siswa->kelas->first()->pivot->category_kelas]]);
                    }
                }
            }
        }
    
        // Optionally, you may return a response indicating the process is completed
        return redirect()->route('dashboard.datamaster.siswa.index')->with('success','Fungsi Naik Kelas Berhasil');
    }
    
}
