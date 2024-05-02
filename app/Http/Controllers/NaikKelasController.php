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
        $lulus = Kelas::where('name', 'Lulus')->first();
        $kelass = Kelas::all();
        // Loop through each student
        foreach ($siswas as $siswa) {
            // Get the current class of the student
            $currentKelas = $siswa->kelas()->first();

            // If the current class is "Lulus", move the student to "Lulus" class and continue to the next student
            if ($currentKelas && $currentKelas->name === 'Lulus') {
                $siswa->kelas()->sync([$lulus->id => ['category_kelas' => $siswa->kelas->first()->pivot->category_kelas]]);
                continue; // Skip the rest of the loop for this student
            }

            // Extract the class number from the current class name
            preg_match('/Kelas (\d+)/', $currentKelas->name, $matches);
            // Check if the pattern matched
            if (isset($matches[1])) {
                $currentClassNumber = intval($matches[1]);
            } else {
                // Handle cases where the pattern did not match (e.g., if the class name format is unexpected)
                // You may log an error or handle it based on your application's logic
                continue; // Skip processing this student and move to the next one
            }

            // Find the next class for the student
            $nextClass = null;
            foreach ($kelass as $kelas) {


                // Extract the class number from the class name
                preg_match('/Kelas (\d+)/', $kelas->name, $matches);
                // Check if the pattern matched
                if (isset($matches[1])) {
                    if($kelas->name == 'Lulus'){
                        break;
                    }
                    $nextClassNumber = intval($matches[1]);
                } else {
                    $nextClass = $lulus;
                    // continue;
                }

                // Check if the current class number is less than the next class number
                if ($currentClassNumber < $nextClassNumber) {
                    // If nextClass is null or the current next class is greater than the next class found
                    if (!$nextClass || $nextClassNumber < intval(preg_replace('/\D/', '', $nextClass->name))) {
                        $nextClass = $kelas;
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
