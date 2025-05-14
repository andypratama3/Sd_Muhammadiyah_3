<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use App\Models\Berita;
use App\Models\Artikel;
use App\Models\Gallery;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SiteMapCommand extends Command
{
    protected $signature = 'app:site-map-command';
    protected $description = 'Generate the sitemap for the website';

    public function handle()
    {
        $sitemapPath = public_path('sitemap.xml');
        $sitemap = Sitemap::create();
        $baseUrl = rtrim(config('app.url'), '/');

        // ✅ Tambahkan halaman utama
        $sitemap->add(
            Url::create($baseUrl)
                ->setLastModificationDate(now())
                ->setPriority(1.0)
        );

        // ✅ Ambil semua route web statis tanpa parameter
        $staticRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return
                $route->methods === ['GET'] &&
                $route->getPrefix() === null &&
                count($route->parameterNames()) === 0 &&
                !str_contains($route->uri(), 'dashboard') &&
                !str_contains($route->uri(), '{');
        });

        foreach ($staticRoutes as $route) {
            $uri = $route->uri();
            $url = $baseUrl . '/' . ltrim($uri, '/');

            $sitemap->add(
                Url::create($url)
                    ->setLastModificationDate(now())
                    ->setPriority(0.7)
            );
        }

        // ✅ Tambahkan berita
        $beritas = Berita::orderBy('created_at', 'desc')->get();
        foreach ($beritas as $berita) {
            $sitemap->add(
                Url::create("$baseUrl/berita/{$berita->slug}")
                    ->setLastModificationDate($berita->updated_at)
                    ->setPriority(0.8)
            );
        }

        // ✅ Tambahkan artikel
        $artikels = Artikel::orderBy('created_at', 'desc')->get();
        foreach ($artikels as $artikel) {
            $sitemap->add(
                Url::create("$baseUrl/artikel/{$artikel->slug}")
                    ->setLastModificationDate($artikel->updated_at)
                    ->setPriority(0.8)
            );
        }

        // ✅ Tambahkan gallery
        $galleries = Gallery::orderBy('created_at', 'desc')->get();
        foreach ($galleries as $gallery) {
            $sitemap->add(
                Url::create("$baseUrl/gallery/{$gallery->slug}")
                    ->setLastModificationDate($gallery->updated_at)
                    ->setPriority(0.8)
            );
        }

        $prestasi = Prestasi::orderBy('created_at', 'desc')->get();
        foreach ($prestasi as $prestasi) {
            $sitemap->add(
                Url::create("$baseUrl/prestasi/{$prestasi->slug}")
                    ->setLastModificationDate($prestasi->updated_at)
                    ->setPriority(0.8)
            );
        }

        $Esktrakurikuler = Esktrakurikuler::orderBy('created_at', 'desc')->get();
        foreach ($Esktrakurikuler as $esktrakurikuler) {
            $sitemap->add(
                Url::create("$baseUrl/esktrakurikuler/{$esktrakurikuler->slug}")
                    ->setLastModificationDate($esktrakurikuler->updated_at)
                    ->setPriority(0.8)
            );
        }

        $fasilitas = Fasilitas::orderBy('created_at', 'desc')->get();
        foreach ($fasilitas as $fasilitas) {
            $sitemap->add(
                Url::create("$baseUrl/fasilitas/{$fasilitas->slug}")
                    ->setLastModificationDate($fasilitas->updated_at)
                    ->setPriority(0.8)
            );
        }

        // ✅ Simpan ke file
        $sitemap->writeToFile($sitemapPath);
        $this->info('✅ Sitemap berhasil diperbarui!');
    }
}
