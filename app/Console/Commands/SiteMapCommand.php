<?php

namespace App\Console\Commands;

use App\Models\Artikel;
use App\Models\Berita;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SiteMapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:site-map-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the sitemap for the website';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Path file sitemap.xml
        $sitemapPath = public_path('sitemap.xml');

        // Buat instance sitemap baru
        $sitemap = Sitemap::create();

        // Ambil base URL tanpa trailing slash
        $baseUrl = rtrim(config('app.url'), '/');

        // Tambahkan halaman utama
        $sitemap->add(
            Url::create($baseUrl)
                ->setLastModificationDate(now())
                ->setPriority(1.0)
        );

        // Tambahkan halaman statis
        $staticPages = [
            'berita', 'guru', 'visimisi', 'ekstrakurikuler', 'pembayaran',
            'fasilitas', 'tenagapendidikan', 'gallery', 'jadwal', 'kontak',
            'prestasi-siswa', 'prestasi-sekolah'
        ];

        foreach ($staticPages as $page) {
            $sitemap->add(
                Url::create("$baseUrl/$page")
                    ->setLastModificationDate(now())
                    ->setPriority(0.7)
            );
        }

        // Ambil data artikel & berita
        $artikels = Artikel::orderBy('created_at', 'desc')->get();
        $beritas = Berita::orderBy('created_at', 'desc')->get();

        // Tambahkan berita ke sitemap
        foreach ($beritas as $berita) {
            $sitemap->add(
                Url::create("$baseUrl/berita/{$berita->slug}")
                    ->setLastModificationDate($berita->updated_at)
                    ->setPriority(0.8)
            );
        }

        // Tambahkan artikel ke sitemap
        foreach ($artikels as $artikel) {
            $sitemap->add(
                Url::create("$baseUrl/artikel/{$artikel->slug}")
                    ->setLastModificationDate($artikel->updated_at)
                    ->setPriority(0.8)
            );
        }

        // Simpan sitemap ke file
        $sitemap->writeToFile($sitemapPath);

        $this->info('✅ Sitemap berhasil diperbarui tanpa duplikasi!');
    }
}
