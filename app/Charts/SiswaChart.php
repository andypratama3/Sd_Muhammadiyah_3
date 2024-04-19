<?php

namespace App\Charts;

use App\Models\Kelas;
use App\Models\Siswa;
use ArielMejiaDev\LarapexCharts\LarapexChart;

class SiswaChart
{
    protected $chart;

    public function __construct(LarapexChart $chart)
    {
        $this->chart = $chart;
    }

    public function build(): \ArielMejiaDev\LarapexCharts\BarChart
    {
        // Retrieve all classes with their associated students
        $classes = Kelas::with('siswa')->orderBy('name','asc')->get();

        // Initialize arrays to store class names and student counts
        $kelas_name = [];
        $siswa_counts = [];

        // Iterate through each class
        foreach ($classes as $class) {
            // Get the class name
            $kelas_name[] = $class->name;

            // Get the count of students in the class
            $siswa_counts[] = $class->siswa->count();
        }

        // Create the bar chart
        $chart = $this->chart->barChart()
            ->setTitle('Siswa Chart')
            ->setSubtitle('Jumlah Siswa Per Kelas')
            ->addData('Jumlah Siswa', $siswa_counts)
        ->setXAxis($kelas_name);

    return $chart;
    }
}
