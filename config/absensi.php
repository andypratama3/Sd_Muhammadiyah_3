<?php

return [
    'use_kml' => env('ABSENSI_USE_KML', true),
    'kml_file_path' => env(
        'KML_RELATIVE_PATH',
        'kml/SD_Muhammadiyah_3_Samarinda.kml'
    ),

    'cache_kml' => true,
    'cache_ttl' => 3600,
];
