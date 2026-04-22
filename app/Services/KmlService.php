<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class KmlService
{
    /**
     * Parse KML file dan extract polygon coordinates
     */
    public function parseKml($kmlContent)
    {
        try {
            $xml = simplexml_load_string($kmlContent);
            $xml->registerXPathNamespace('kml', 'http://www.opengis.net/kml/2.2');

            $polygons   = [];
            $placemarks = $xml->xpath('//kml:Placemark');

            foreach ($placemarks as $placemark) {
                $polygonElement = $placemark->xpath('.//kml:Polygon/kml:outerBoundaryIs/kml:LinearRing/kml:coordinates');

                if (!empty($polygonElement)) {
                    $coordinates = (string) $polygonElement[0];
                    $points      = $this->parseCoordinates($coordinates);

                    if (!empty($points)) {
                        $name          = (string) ($placemark->name ?? 'Unnamed Area');
                        $polygons[] = [
                            'name'        => $name,
                            'area_type'   => $this->resolveAreaType($name),
                            'coordinates' => $points,
                            'point_count' => count($points)
                        ];
                    }
                }
            }

            return [
                'success'  => true,
                'polygons' => $polygons,
                'count'    => count($polygons)
            ];
        } catch (\Exception $e) {
            Log::error('Error parsing KML', ['error' => $e->getMessage()]);

            return [
                'success' => false,
                'message' => 'Gagal parse KML: ' . $e->getMessage()
            ];
        }
    }

    // =========================================================================
    // AREA TYPE RESOLUTION
    // =========================================================================

    /**
     * Tentukan tipe area berdasarkan nama polygon.
     *
     * Mapping dikonfigurasi di config/absensi.php:
     *
     *   'area_types' => [
     *       'kerja'  => ['SD Muhammadiyah 3 Samarinda'],
     *       'sholat' => ['Area Sholat'],
     *   ],
     *
     * Jika nama tidak cocok → fallback ke 'kerja'.
     */
    private function resolveAreaType(string $polygonName): string
    {
        $areaTypes = config('absensi.area_types', []);

        foreach ($areaTypes as $type => $names) {
            foreach ($names as $name) {
                if (stripos($polygonName, $name) !== false || stripos($name, $polygonName) !== false) {
                    return $type;
                }
            }
        }

        // Default: anggap area kerja
        return 'kerja';
    }

    /**
     * Ambil polygon berdasarkan tipe area ('kerja' atau 'sholat').
     *
     * @param  string $type  'kerja' | 'sholat'
     * @return array         [ 'success', 'polygons', 'count' ] atau [ 'success', 'message' ]
     */
    public function getPolygonsByType(string $type): array
    {
        $kmlData = $this->loadKmlFile();

        if (!$kmlData['success']) {
            return $kmlData;
        }

        $filtered = array_values(
            array_filter($kmlData['polygons'], fn ($p) => ($p['area_type'] ?? 'kerja') === $type)
        );

        if (empty($filtered)) {
            return [
                'success' => false,
                'message' => "Tidak ada polygon bertipe '{$type}' di file KML"
            ];
        }

        return [
            'success'  => true,
            'polygons' => $filtered,
            'count'    => count($filtered)
        ];
    }

    // =========================================================================
    // VALIDATION
    // =========================================================================

    /**
     * Validate lokasi terhadap semua polygon (behavior lama, dipertahankan).
     */
    public function validateLocation($latitude, $longitude): array
    {
        return $this->validateLocationByType($latitude, $longitude, 'kerja');
    }

    /**
     * Validate lokasi hanya terhadap polygon bertipe $type.
     *
     * @param  float  $latitude
     * @param  float  $longitude
     * @param  string $type  'kerja' | 'sholat'
     */
    public function validateLocationByType(float $latitude, float $longitude, string $type = 'kerja'): array
    {
        $kmlData = $this->getPolygonsByType($type);

        if (!$kmlData['success']) {
            return [
                'valid'     => false,
                'message'   => $kmlData['message'],
                'area_name' => null,
                'area_type' => $type
            ];
        }

        $result = $this->validatePointInAnyPolygon($latitude, $longitude, $kmlData['polygons']);

        if (!$result['valid']) {
            $labelMap = [
                'kerja'  => 'area sekolah yang diizinkan',
                'sholat' => 'area sholat yang diizinkan',
            ];
            $label = $labelMap[$type] ?? 'area yang diizinkan';

            return [
                'valid'     => false,
                'message'   => "Lokasi Anda berada di luar {$label}",
                'area_name' => null,
                'area_type' => $type
            ];
        }

        return [
            'valid'     => true,
            'message'   => 'Lokasi valid dalam area: ' . $result['area_name'],
            'area_name' => $result['area_name'],
            'area_type' => $type,
            'polygon'   => $result['polygon']
        ];
    }

    /**
     * Check apakah point berada dalam salah satu polygon
     */
    public function validatePointInAnyPolygon($pointLat, $pointLon, $polygons): array
    {
        foreach ($polygons as $polygon) {
            if ($this->isPointInPolygon($pointLat, $pointLon, $polygon['coordinates'])) {
                return [
                    'valid'     => true,
                    'area_name' => $polygon['name'] ?? 'Area',
                    'area_type' => $polygon['area_type'] ?? 'kerja',
                    'polygon'   => $polygon
                ];
            }
        }

        return [
            'valid'     => false,
            'area_name' => null,
            'area_type' => null,
            'polygon'   => null
        ];
    }

    /**
     * Check apakah point berada dalam polygon (Ray Casting Algorithm)
     */
    public function isPointInPolygon($pointLat, $pointLon, $polygon): bool
    {
        $numVertices = count($polygon);
        $isInside    = false;

        for ($i = 0, $j = $numVertices - 1; $i < $numVertices; $j = $i++) {
            $xi = $polygon[$i]['lon'];
            $yi = $polygon[$i]['lat'];
            $xj = $polygon[$j]['lon'];
            $yj = $polygon[$j]['lat'];

            $intersect = (($yi > $pointLat) != ($yj > $pointLat))
                && ($pointLon < ($xj - $xi) * ($pointLat - $yi) / ($yj - $yi) + $xi);

            if ($intersect) {
                $isInside = !$isInside;
            }
        }

        return $isInside;
    }

    // =========================================================================
    // FILE MANAGEMENT
    // =========================================================================

    /**
     * Load KML file from storage dengan caching
     */
    public function loadKmlFile(): array
    {
        $kmlPath = config('absensi.kml_file_path');

        if (!$kmlPath) {
            return [
                'success' => false,
                'message' => 'Path file KML belum dikonfigurasi di config/absensi.php'
            ];
        }

        $cacheKey = 'kml_polygons_' . md5($kmlPath);

        if (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            Log::debug('KML loaded from cache', ['polygon_count' => $cached['count']]);
            return $cached;
        }

        if (!Storage::disk('public')->exists($kmlPath)) {
            return [
                'success' => false,
                'message' => 'File KML tidak ditemukan: ' . $kmlPath
            ];
        }

        $kmlContent = Storage::disk('public')->get($kmlPath);
        $result     = $this->parseKml($kmlContent);

        if (!$result['success']) {
            return $result;
        }

        if (empty($result['polygons'])) {
            return [
                'success' => false,
                'message' => 'File KML tidak mengandung polygon yang valid'
            ];
        }

        Cache::put($cacheKey, $result, now()->addHours(24));

        Log::info('KML file loaded', [
            'path'          => $kmlPath,
            'polygon_count' => $result['count'],
            'area_types'    => array_column($result['polygons'], 'area_type')
        ]);

        return $result;
    }

    /**
     * Get KML file information
     */
    public function getKmlInfo(): array
    {
        $kmlPath = config('absensi.kml_file_path');

        if (!$kmlPath) {
            return ['exists' => false, 'message' => 'Path KML belum dikonfigurasi'];
        }

        if (!Storage::disk('public')->exists($kmlPath)) {
            return [
                'exists'    => false,
                'message'   => 'File KML tidak ditemukan',
                'file_path' => Storage::disk('public')->path($kmlPath)
            ];
        }

        $fullPath     = Storage::disk('public')->path($kmlPath);
        $size         = Storage::disk('public')->size($kmlPath);
        $lastModified = Storage::disk('public')->lastModified($kmlPath);
        $kmlData      = $this->loadKmlFile();

        $areas         = [];
        $totalPolygons = 0;
        $byType        = [];

        if ($kmlData['success']) {
            $totalPolygons = $kmlData['count'];
            foreach ($kmlData['polygons'] as $p) {
                $areas[]                         = $p['name'];
                $byType[$p['area_type'] ?? 'kerja'][] = $p['name'];
            }
        }

        $cacheKey = 'kml_polygons_' . md5($kmlPath);

        return [
            'exists'              => true,
            'file_name'           => basename($kmlPath),
            'file_path'           => $fullPath,
            'file_size'           => $size,
            'file_size_human'     => $this->formatBytes($size),
            'last_modified'       => date('Y-m-d H:i:s', $lastModified),
            'last_modified_human' => \Carbon\Carbon::createFromTimestamp($lastModified)->diffForHumans(),
            'total_polygons'      => $totalPolygons,
            'areas'               => $areas,
            'by_type'             => $byType,
            'is_cached'           => Cache::has($cacheKey)
        ];
    }

    /**
     * Clear KML cache
     */
    public function clearCache(): void
    {
        $kmlPath = config('absensi.kml_file_path');
        if ($kmlPath) {
            $cacheKey = 'kml_polygons_' . md5($kmlPath);
            Cache::forget($cacheKey);
            Log::info('KML cache cleared');
        }
    }

    /**
     * Save uploaded KML file
     */
    public function saveKmlFile($file): array
    {
        try {
            if ($file->getClientOriginalExtension() !== 'kml') {
                return ['success' => false, 'message' => 'File harus berformat KML'];
            }

            $kmlContent  = file_get_contents($file->getRealPath());
            $parseResult = $this->parseKml($kmlContent);

            if (!$parseResult['success']) {
                return $parseResult;
            }

            if (empty($parseResult['polygons'])) {
                return ['success' => false, 'message' => 'File KML tidak mengandung polygon yang valid'];
            }

            $fileName = 'kml_' . time() . '_' . $file->getClientOriginalName();
            $path     = $file->storeAs('kml-files', $fileName, 'public');

            $this->clearCache();

            return [
                'success'       => true,
                'path'          => $path,
                'full_path'     => Storage::disk('public')->path($path),
                'url'           => Storage::disk('public')->url($path),
                'kml_data'      => $kmlContent,
                'polygons'      => $parseResult['polygons'],
                'polygon_count' => $parseResult['count']
            ];
        } catch (\Exception $e) {
            Log::error('Error saving KML file', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Gagal menyimpan file KML: ' . $e->getMessage()];
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function parseCoordinates($coordinateString): array
    {
        $coordinates      = [];
        $coordinateString = trim($coordinateString);
        $points           = preg_split('/\s+/', $coordinateString);

        foreach ($points as $point) {
            $point = trim($point);
            if (empty($point)) continue;

            $parts = explode(',', $point);

            if (count($parts) >= 2) {
                $coordinates[] = [
                    'lat' => (float) $parts[1],
                    'lon' => (float) $parts[0]
                ];
            }
        }

        return $coordinates;
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function generateKmlTemplate($areaName = 'Area Absensi', $coordinates = []): string
    {
        if (empty($coordinates)) {
            $coordinates = [
                ['lat' => -0.479486, 'lon' => 117.154618],
                ['lat' => -0.479486, 'lon' => 117.155618],
                ['lat' => -0.480486, 'lon' => 117.155618],
                ['lat' => -0.480486, 'lon' => 117.154618],
                ['lat' => -0.479486, 'lon' => 117.154618],
            ];
        }

        $coordinateString = '';
        foreach ($coordinates as $coord) {
            $coordinateString .= $coord['lon'] . ',' . $coord['lat'] . ",0\n";
        }

        return <<<KML
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://www.opengis.net/kml/2.2">
  <Document>
    <name>Lokasi Absensi</name>
    <description>Area yang diizinkan untuk absensi</description>
    <Style id="areaStyle">
      <LineStyle><color>ff0000ff</color><width>2</width></LineStyle>
      <PolyStyle><color>4d0000ff</color></PolyStyle>
    </Style>
    <Placemark>
      <name>$areaName</name>
      <styleUrl>#areaStyle</styleUrl>
      <Polygon>
        <outerBoundaryIs>
          <LinearRing>
            <coordinates>
$coordinateString
            </coordinates>
          </LinearRing>
        </outerBoundaryIs>
      </Polygon>
    </Placemark>
  </Document>
</kml>
KML;
    }
}
