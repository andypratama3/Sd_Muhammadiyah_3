<?php

namespace App\Services;

use App\Models\Siswa;
use App\Models\Charge;
use App\Models\WaRequestLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * WhatsAppBotService
 *
 * Menangani seluruh logic percakapan bot WhatsApp:
 *  - Session management via Redis (TTL 24 jam)
 *  - Intent detection dari teks bebas
 *  - Routing ke handler yang sesuai
 *  - Format pesan balasan
 *  - FAQ matching dari faq-prompt.json
 *
 * States:
 *  new          → user belum pernah chat / session expired
 *  waiting_nisn → sudah disambut, menunggu input NISN
 *  verified     → NISN + WA sudah terverifikasi, bisa recheck
 */
class WhatsAppBotService
{
    // TTL session = 24 jam
    private const SESSION_TTL = 86400;

    // Prefix cache key
    private const SESSION_PREFIX = 'wa_session:';

    // Path ke file FAQ JSON (relatif terhadap base_path())
    private const FAQ_FILE = 'public/asset_dashboard/faq-prompt.json';

    // Cache key untuk FAQ
    private const FAQ_CACHE_KEY = 'wa_faq_data';

    // TTL cache FAQ = 1 jam (agar update file tidak perlu restart)
    private const FAQ_CACHE_TTL = 3600;

    /** @var array<int, array{keywords: string[], question: string, answer: string}> */
    private array $faqData = [];

    public function __construct(
        private readonly WhatsappMetaService $whatsapp
    ) {
        $this->loadFaq();
    }

    // =========================================================================
    // FAQ LOADER
    // =========================================================================

    /**
     * Muat data FAQ dari faq-prompt.json
     *
     * File diletakkan di base_path() (root Laravel project).
     * Hasil di-cache 1 jam via Redis untuk performa.
     * Jika file tidak ditemukan atau JSON tidak valid, FAQ dinonaktifkan
     * secara silent agar bot tetap berjalan normal.
     */
    private function loadFaq(): void
    {
        try {
            $this->faqData = Cache::remember(self::FAQ_CACHE_KEY, self::FAQ_CACHE_TTL, function () {
                $path = base_path(self::FAQ_FILE);

                if (!file_exists($path)) {
                    Log::channel('whatsapp')->warning('⚠️ FAQ file not found', ['path' => $path]);
                    return [];
                }

                $raw = file_get_contents($path);
                $decoded = json_decode($raw, true);

                if (!is_array($decoded)) {
                    Log::channel('whatsapp')->error('❌ FAQ JSON invalid', ['path' => $path]);
                    return [];
                }

                Log::channel('whatsapp')->info('📚 FAQ loaded', ['count' => count($decoded)]);

                return $decoded;
            });
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ FAQ load error', ['error' => $e->getMessage()]);
            $this->faqData = [];
        }
    }

    /**
     * Paksa reload FAQ (invalidate cache) — berguna saat file diupdate
     */
    public function reloadFaq(): void
    {
        Cache::forget(self::FAQ_CACHE_KEY);
        $this->loadFaq();
    }

    /**
     * Cari FAQ yang cocok dengan teks input user
     *
     * Mengembalikan jawaban FAQ jika ada keyword yang cocok,
     * atau null jika tidak ada yang match.
     */
    private function matchFaq(string $text): ?string
    {
        if (empty($this->faqData)) {
            return null;
        }

        $lower = mb_strtolower(trim($text));

        foreach ($this->faqData as $item) {
            if (empty($item['keywords']) || empty($item['answer'])) {
                continue;
            }

            foreach ($item['keywords'] as $keyword) {
                if (str_contains($lower, mb_strtolower($keyword))) {
                    return $item['answer'];
                }
            }
        }

        return null;
    }

    // =========================================================================
    // ENTRY POINT - dipanggil dari WebhookController
    // =========================================================================

    /**
     * Proses pesan masuk dan kirim balasan
     */
    public function handle(string $phone, string $messageText, string $profileName = 'Bapak/Ibu'): void
    {
        $startTime = microtime(true);

        try {
            Log::channel('whatsapp')->info('🤖 Bot handle', [
                'phone'   => $this->maskPhone($phone),
                'message' => mb_substr($messageText, 0, 50),
            ]);

            $session = $this->getSession($phone);
            $intent  = $this->detectIntent($messageText, $session['state']);

            Log::channel('whatsapp')->info('🧠 Intent detected', [
                'state'  => $session['state'],
                'intent' => $intent,
            ]);

            $replyMessage = $this->route($intent, $phone, $messageText, $profileName, $session);

            // Kirim balasan via WhatsApp API
            $this->whatsapp->sendMessage($phone, $replyMessage);

            $ms = (int) ((microtime(true) - $startTime) * 1000);
            Log::channel('whatsapp')->info('✅ Bot reply sent', ['ms' => $ms]);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Bot handle error', [
                'error' => $e->getMessage(),
                'phone' => $this->maskPhone($phone),
            ]);

            // Kirim pesan error generic agar user tidak menunggu tanpa balasan
            try {
                $this->whatsapp->sendMessage(
                    $phone,
                    "⚠️ *Sistem sedang mengalami gangguan*\n\nMohon coba beberapa saat lagi.\n\nJika masalah berlanjut, hubungi admin sekolah."
                );
            } catch (\Exception) {
                // Silent fail — sudah di-log di atas
            }
        }
    }

    // =========================================================================
    // INTENT DETECTION
    // =========================================================================

    /**
     * Deteksi intent dari teks bebas user
     *
     * Priority order:
     *  1. Angka tepat 10 digit  → nisn_input
     *  2. Keyword pembayaran    → check_payment / recheck
     *  3. Recheck / refresh     → recheck
     *  4. Greeting              → greeting
     *  5. Bantuan / help        → help
     *  6. FAQ match             → faq
     *  7. Semua lainnya         → unknown
     */
    private function detectIntent(string $text, string $state): string
    {
        $clean = trim($text);
        $lower = mb_strtolower($clean);

        // --- 1. NISN: tepat 10 digit angka ---
        if (preg_match('/^\d{10}$/', $clean)) {
            return 'nisn_input';
        }

        // --- 2. Keyword NISN eksplisit (e.g. "nisn 1234567890") ---
        if (preg_match('/nisn\s*[:\-]?\s*(\d{10})/i', $clean)) {
            return 'nisn_input';
        }

        // --- 3. Keyword pembayaran / tagihan ---
        $paymentKeywords = [
            'spp', 'bayar', 'tagihan', 'tunggakan', 'cicilan',
            'pembayaran', 'lunas', 'belum bayar', 'cek bayar',
            'info bayar', 'biaya', 'iuran', 'dpp', 'seragam',
        ];
        foreach ($paymentKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                // Jika sudah verified, langsung recheck
                return $state === 'verified' ? 'recheck' : 'check_payment_intent';
            }
        }

        // --- 4. Recheck / refresh ---
        $recheckKeywords = ['1', 'cek', 'cek ulang', 'refresh', 'update', 'perbarui', 'lagi'];
        if (in_array($lower, $recheckKeywords, true) || preg_match('/^(cek|lihat|tampil)/i', $lower)) {
            return $state === 'verified' ? 'recheck' : 'check_payment_intent';
        }

        // --- 5. Greeting ---
        $greetingKeywords = [
            'halo', 'hai', 'hello', 'hi', 'hei',
            'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam',
            'assalamu', 'assalam', 'salam', 'p ', 'permisi', 'hallo',
            'met pagi', 'met siang', 'met sore', 'met malam',
        ];
        foreach ($greetingKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return 'greeting';
            }
        }

        // --- 6. Bantuan ---
        $helpKeywords = ['help', 'bantuan', 'panduan', 'cara', 'gimana', 'bagaimana', 'petunjuk', '?'];
        foreach ($helpKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                // Cek FAQ dulu — mungkin pertanyaan spesifik
                if ($this->matchFaq($text) !== null) {
                    return 'faq';
                }
                return 'help';
            }
        }

        // --- 7. FAQ match ---
        if ($this->matchFaq($text) !== null) {
            return 'faq';
        }

        // --- 8. Default berdasarkan state ---
        if ($state === 'waiting_nisn') {
            // User kirim sesuatu tapi bukan NISN 10 digit
            return 'invalid_nisn_format';
        }

        if ($state === 'verified') {
            // User verified tapi kirim sesuatu yang tidak dikenali
            return 'unknown_verified';
        }

        return 'unknown_new';
    }

    // =========================================================================
    // ROUTER
    // =========================================================================

    private function route(
        string $intent,
        string $phone,
        string $messageText,
        string $profileName,
        array  $session
    ): string {
        return match ($intent) {
            'greeting'             => $this->handleGreeting($phone, $profileName, $session),
            'nisn_input'           => $this->handleNisnInput($phone, $messageText, $profileName, $session),
            'check_payment_intent' => $this->handleCheckPaymentIntent($phone, $profileName, $session),
            'recheck'              => $this->handleRecheck($phone, $session),
            'invalid_nisn_format'  => $this->handleInvalidNisn($phone),
            'faq'                  => $this->handleFaq($phone, $messageText, $session),
            'help'                 => $this->handleHelp($phone, $session),
            'unknown_verified'     => $this->handleUnknownVerified($phone, $session),
            'unknown_new'          => $this->handleUnknownNew($phone, $profileName, $session),
            default                => $this->handleUnknownNew($phone, $profileName, $session),
        };
    }

    // =========================================================================
    // HANDLERS
    // =========================================================================

    /**
     * Sambutan — selalu sambut ulang dan minta NISN
     */
    private function handleGreeting(string $phone, string $profileName, array $session): string
    {
        $this->updateSession($phone, ['state' => 'waiting_nisn']);

        $greeting = $this->getTimeGreeting();

        return "{$greeting}, *{$profileName}* 👋\n\n"
            . "Selamat datang di layanan informasi pembayaran\n"
            . "🏫 *SD Muhammadiyah 3 Samarinda*\n\n"
            . "Untuk melihat informasi tagihan putra/putri Anda,\n"
            . "silakan kirimkan *NISN* (10 digit angka).\n\n"
            . "_Contoh: 1234567890_";
    }

    /**
     * User menyebut kata pembayaran/tagihan tapi belum terverifikasi
     */
    private function handleCheckPaymentIntent(string $phone, string $profileName, array $session): string
    {
        if ($session['state'] === 'verified' && $session['nisn']) {
            return $this->handleRecheck($phone, $session);
        }

        $this->updateSession($phone, ['state' => 'waiting_nisn']);

        return "Tentu, saya bantu cek informasi tagihan 📋\n\n"
            . "Silakan kirimkan *NISN* putra/putri Anda (10 digit angka).\n\n"
            . "_Contoh: 1234567890_";
    }

    /**
     * User menginput NISN — validasi dan query database
     */
    private function handleNisnInput(string $phone, string $messageText, string $profileName, array $session): string
    {
        // Ekstrak NISN dari teks (bisa "nisn: 1234567890" atau hanya "1234567890")
        $nisn = $this->extractNisn($messageText);

        if (!$nisn) {
            return $this->handleInvalidNisn($phone);
        }

        $startTime = microtime(true);
        $result    = $this->queryPaymentData($phone, $nisn);
        $ms        = (int) ((microtime(true) - $startTime) * 1000);

        // Log request
        $this->logRequest($phone, $nisn, $result, $ms);

        if ($result['success']) {
            // Update session ke verified
            $this->updateSession($phone, [
                'state'    => 'verified',
                'nisn'     => $nisn,
                'siswa_id' => $result['data']['siswa']['id'] ?? null,
            ]);

            return $this->formatPaymentMessage($result['data']);
        }

        // Handle error responses
        return $this->formatErrorMessage($result, $phone);
    }

    /**
     * User sudah verified, minta cek ulang
     */
    private function handleRecheck(string $phone, array $session): string
    {
        if (!$session['nisn']) {
            $this->updateSession($phone, ['state' => 'waiting_nisn']);
            return "Sesi Anda telah berakhir. Silakan kirimkan *NISN* kembali.\n\n_Contoh: 1234567890_";
        }

        $startTime = microtime(true);
        $result    = $this->queryPaymentData($phone, $session['nisn']);
        $ms        = (int) ((microtime(true) - $startTime) * 1000);

        $this->logRequest($phone, $session['nisn'], $result, $ms);

        if ($result['success']) {
            // Refresh session TTL
            $this->touchSession($phone);
            return $this->formatPaymentMessage($result['data']);
        }

        return $this->formatErrorMessage($result, $phone);
    }

    /**
     * Format NISN tidak valid
     */
    private function handleInvalidNisn(string $phone): string
    {
        return "Maaf, format tidak dikenali. 🙏\n\n"
            . "Silakan kirimkan *NISN* putra/putri Anda berupa *10 digit angka*.\n\n"
            . "_Contoh: 1234567890_\n\n"
            . "Jika Anda tidak mengetahui NISN, silakan hubungi Tata Usaha sekolah.";
    }

    /**
     * Jawab pertanyaan dari FAQ
     *
     * Sesi tidak berubah — FAQ bersifat informatif dan tidak menginterupsi
     * alur verifikasi NISN. Jika user sudah verified, tambahkan reminder
     * untuk cek tagihan di akhir pesan.
     */
    private function handleFaq(string $phone, string $messageText, array $session): string
    {
        $answer = $this->matchFaq($messageText);

        if (!$answer) {
            // Fallback: seharusnya tidak sampai sini karena intent sudah match
            return $this->handleHelp($phone, $session);
        }

        Log::channel('whatsapp')->info('📖 FAQ matched', [
            'phone'   => $this->maskPhone($phone),
            'message' => mb_substr($messageText, 0, 50),
        ]);

        // Jika user belum verified, tambahkan prompt NISN setelah jawaban FAQ
        if ($session['state'] !== 'verified') {
            $answer .= "\n\n━━━━━━━━━━━━━━━━━\n"
                . "💡 Untuk cek tagihan, kirimkan *NISN* putra/putri Anda (10 digit).";
        } else {
            $answer .= "\n\n━━━━━━━━━━━━━━━━━\n"
                . "💡 Ketik *cek* atau *1* untuk melihat tagihan terbaru.";
        }

        return $answer;
    }

    /**
     * Bantuan umum
     */
    private function handleHelp(string $phone, array $session): string
    {
        $nisn = $session['nisn'] ? "✅ NISN: {$session['nisn']} (sudah terdaftar)" : "❌ NISN belum terdaftar";

        return "📌 *Panduan Penggunaan*\n"
            . "🏫 SD Muhammadiyah 3 Samarinda\n"
            . "━━━━━━━━━━━━━━━━━\n\n"
            . "Layanan ini membantu Anda mengecek informasi tagihan sekolah.\n\n"
            . "*Cara menggunakan:*\n"
            . "1. Kirimkan *NISN* putra/putri Anda (10 digit)\n"
            . "2. Sistem akan menampilkan tagihan dan riwayat pembayaran\n"
            . "3. Ketik *cek* atau *1* untuk memperbarui data\n\n"
            . "*Status Anda:* {$nisn}\n\n"
            . "━━━━━━━━━━━━━━━━━\n"
            . "📞 Butuh bantuan lebih lanjut?\nHubungi Tata Usaha sekolah.";
    }

    /**
     * User verified tapi kirim sesuatu yang tidak dikenali
     */
    private function handleUnknownVerified(string $phone, array $session): string
    {
        $nama = $session['siswa_name'] ?? 'putra/putri Anda';

        return "Maaf, perintah tidak dikenali. 🙏\n\n"
            . "Yang bisa saya bantu:\n"
            . "• Ketik *cek* atau *1* untuk lihat tagihan {$nama}\n"
            . "• Kirim *NISN* baru untuk ganti siswa\n"
            . "• Ketik *bantuan* untuk panduan\n\n"
            . "_Atau hubungi admin sekolah untuk bantuan lebih lanjut._";
    }

    /**
     * User baru / session expired kirim sesuatu yang tidak dikenali
     */
    private function handleUnknownNew(string $phone, string $profileName, array $session): string
    {
        $this->updateSession($phone, ['state' => 'waiting_nisn']);

        return "Halo, *{$profileName}* 👋\n\n"
            . "Untuk menggunakan layanan ini, silakan kirimkan *NISN* "
            . "putra/putri Anda (10 digit angka).\n\n"
            . "_Contoh: 1234567890_\n\n"
            . "Ketik *bantuan* jika memerlukan panduan.";
    }

    // =========================================================================
    // DATABASE QUERY
    // =========================================================================

    /**
     * Query data pembayaran siswa dari database
     * Validasi: NISN + nomor WA harus match di tabel siswas
     */
    private function queryPaymentData(string $phone, string $nisn): array
    {
        try {
            // Format nomor WA ke format lokal (08xxx) untuk match di DB
            $localPhone = $this->phoneToLocal($phone);

            Log::channel('whatsapp')->info('🔍 Query payment', [
                'nisn'       => $nisn,
                'phone_mask' => $this->maskPhone($phone),
                'local'      => $localPhone,
            ]);

            // Cari siswa: NISN + no_hp harus match
            $siswa = Siswa::where('nisn', $nisn)
                ->where('no_hp', $localPhone)
                ->whereNull('deleted_at')
                ->with(['kelas' => function ($q) {
                    $q->select('kelas.id', 'kelas.name', 'kelas.grade')
                      ->withPivot('category_kelas');
                }])
                ->first();

            if (!$siswa) {
                Log::channel('whatsapp')->warning('⚠️ Siswa not found', [
                    'nisn'  => $nisn,
                    'phone' => $this->maskPhone($phone),
                ]);

                return [
                    'success' => false,
                    'code'    => 'NOT_FOUND',
                    'message' => 'Data tidak ditemukan',
                ];
            }

            // Ambil kelas aktif (pivot category_kelas = 'aktif' atau yang terbaru)
            $kelasAktif = $siswa->kelas->first();
            $namaKelas  = $kelasAktif ? ($kelasAktif->name ?? '-') : '-';

            // Ambil SEMUA charges siswa ini
            $charges = Charge::where('siswa_id', $siswa->id)
                ->whereNull('deleted_at')
                ->with('kategori_pembayaran')
                ->orderByDesc('created_at')
                ->get();

            // Pisah: LUNAS vs BELUM LUNAS
            $statusLunas  = ['settlement', 'pay_offline'];
            $statusBelum  = ['pending', 'expired'];

            $tagihan = $charges->whereIn('transaction_status', $statusBelum)
                ->values()
                ->map(fn($c) => $this->formatTagihanItem($c));

            $lunas = $charges->whereIn('transaction_status', $statusLunas)
                ->sortByDesc('transaction_time')
                ->take(5) // Tampilkan 5 riwayat lunas terakhir
                ->values()
                ->map(fn($c) => $this->formatLunasItem($c));

            $totalBelumBayar = $charges->whereIn('transaction_status', $statusBelum)
                ->sum('gross_amount');

            Log::channel('whatsapp')->info('✅ Payment data found', [
                'siswa_id'      => $siswa->id,
                'tagihan_count' => $tagihan->count(),
                'lunas_count'   => $lunas->count(),
            ]);

            return [
                'success' => true,
                'data'    => [
                    'siswa' => [
                        'id'    => $siswa->id,
                        'nama'  => $siswa->name,
                        'nisn'  => $siswa->nisn,
                        'kelas' => $namaKelas,
                    ],
                    'tagihan' => $tagihan->toArray(),
                    'lunas'   => $lunas->toArray(),
                    'summary' => [
                        'jumlah_tagihan'           => $tagihan->count(),
                        'total_tagihan_belum_bayar' => $totalBelumBayar,
                    ],
                ],
            ];

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Query payment error', [
                'error' => $e->getMessage(),
                'nisn'  => $nisn,
            ]);

            return [
                'success' => false,
                'code'    => 'SERVER_ERROR',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format item tagihan belum lunas
     */
    private function formatTagihanItem(Charge $charge): array
    {
        $nominal = 'Rp ' . number_format($charge->gross_amount, 0, ',', '.');

        $statusLabel = match ($charge->transaction_status) {
            'pending' => '⏳ Menunggu Pembayaran',
            'expired' => '❌ Kadaluarsa',
            default   => $charge->transaction_status,
        };

        return [
            'judul'     => $charge->name ?? ($charge->kategori_pembayaran->name ?? 'Tagihan'),
            'nominal'   => $nominal,
            'status'    => $statusLabel,
            'bank'      => $charge->bank ?? '-',
            'va_number' => $charge->va_number ?? '-',
            'tanggal'   => $charge->transaction_time
                ? \Carbon\Carbon::parse($charge->transaction_time)->isoFormat('D MMM Y')
                : '-',
        ];
    }

    /**
     * Format item riwayat lunas
     */
    private function formatLunasItem(Charge $charge): array
    {
        $nominal = 'Rp ' . number_format($charge->gross_amount, 0, ',', '.');

        $tgl = $charge->transaction_time
            ? \Carbon\Carbon::parse($charge->transaction_time)->isoFormat('D MMM Y')
            : ($charge->updated_at ? $charge->updated_at->isoFormat('D MMM Y') : '-');

        $statusLabel = $charge->transaction_status === 'pay_offline' ? '💵 Tunai' : '💳 Online';

        return [
            'judul'   => $charge->name ?? ($charge->kategori_pembayaran->name ?? 'Pembayaran'),
            'nominal' => $nominal,
            'tanggal' => $tgl,
            'metode'  => $statusLabel,
        ];
    }

    // =========================================================================
    // MESSAGE FORMATTER
    // =========================================================================

    private function formatPaymentMessage(array $data): string
    {
        $siswa   = $data['siswa'];
        $tagihan = $data['tagihan'];
        $lunas   = $data['lunas'];
        $summary = $data['summary'];

        $msg  = "*Informasi Pembayaran*\n";
        $msg .= "🏫 SD Muhammadiyah 3 Samarinda\n";
        $msg .= "━━━━━━━━━━━━━━━━━\n\n";
        $msg .= "👤 *Siswa :* {$siswa['nama']}\n";
        $msg .= "🔢 *NISN  :* {$siswa['nisn']}\n";
        $msg .= "📚 *Kelas :* {$siswa['kelas']}\n";

        // --- Tagihan belum lunas ---
        if (count($tagihan) > 0) {
            $msg .= "\n📋 *Tagihan Belum Lunas ({$summary['jumlah_tagihan']})*\n";
            $msg .= "━━━━━━━━━━━━━━━━━\n";

            foreach ($tagihan as $i => $t) {
                $no   = $i + 1;
                $msg .= "\n{$no}. *{$t['judul']}*\n";
                $msg .= "   💰 {$t['nominal']}\n";
                $msg .= "   📌 {$t['status']}\n";
                if ($t['va_number'] !== '-' && $t['bank'] !== '-') {
                    $bank = strtoupper($t['bank']);
                    $msg .= "   🏦 {$bank} — VA: {$t['va_number']}\n";
                }
                if ($t['tanggal'] !== '-') {
                    $msg .= "   📅 {$t['tanggal']}\n";
                }
            }

            $total = number_format($summary['total_tagihan_belum_bayar'], 0, ',', '.');
            $msg  .= "\n💳 *Total Tagihan: Rp {$total}*\n";

        } else {
            $msg .= "\n✅ *Semua tagihan sudah lunas*\n";
            $msg .= "_Tidak ada tagihan yang perlu dibayar saat ini._\n";
        }

        // --- Riwayat lunas ---
        if (count($lunas) > 0) {
            $msg .= "\n\n✅ *Riwayat Pembayaran (5 terakhir)*\n";
            $msg .= "━━━━━━━━━━━━━━━━━\n";
            foreach ($lunas as $l) {
                $msg .= "• {$l['judul']} — {$l['nominal']} ({$l['tanggal']}) {$l['metode']}\n";
            }
        }

        $msg .= "\n━━━━━━━━━━━━━━━━━\n";
        $msg .= "Ketik *cek* atau *1* untuk perbarui data\n";
        $msg .= "_Data diperbarui secara realtime_";

        return $msg;
    }

    private function formatErrorMessage(array $result, string $phone): string
    {
        return match ($result['code'] ?? 'SERVER_ERROR') {
            'NOT_FOUND' =>
                "❌ *Data tidak ditemukan*\n\n"
                . "NISN atau nomor WhatsApp yang Anda masukkan tidak cocok dengan data di sistem kami.\n\n"
                . "Pastikan:\n"
                . "• NISN yang dimasukkan benar (10 digit)\n"
                . "• Nomor WA ini terdaftar di sistem sekolah\n\n"
                . "Kirim ulang NISN untuk mencoba lagi, atau hubungi Tata Usaha sekolah.",

            'INVALID_INPUT' =>
                "⚠️ *Format NISN tidak valid*\n\n"
                . "NISN harus berupa *10 digit angka*.\n\n"
                . "_Contoh: 1234567890_\n\n"
                . "Silakan kirimkan NISN yang benar.",

            default =>
                "⚠️ *Sistem sedang tidak dapat diakses*\n\n"
                . "Terjadi gangguan sementara. Silakan coba beberapa saat lagi.\n\n"
                . "Jika masalah berlanjut, hubungi admin sekolah.",
        };
    }

    // =========================================================================
    // SESSION MANAGEMENT (Redis via Laravel Cache)
    // =========================================================================

    private function getSession(string $phone): array
    {
        $key     = self::SESSION_PREFIX . $phone;
        $session = Cache::get($key);

        if (!$session) {
            // Session baru / expired
            $session = [
                'state'       => 'new',
                'nisn'        => null,
                'siswa_id'    => null,
                'siswa_name'  => null,
                'created_at'  => now()->timestamp,
            ];
            Cache::put($key, $session, self::SESSION_TTL);
        }

        return $session;
    }

    private function updateSession(string $phone, array $data): void
    {
        $key     = self::SESSION_PREFIX . $phone;
        $session = Cache::get($key, [
            'state'      => 'new',
            'nisn'       => null,
            'siswa_id'   => null,
            'siswa_name' => null,
            'created_at' => now()->timestamp,
        ]);

        $session = array_merge($session, $data, ['last_activity' => now()->timestamp]);
        Cache::put($key, $session, self::SESSION_TTL);

        Log::channel('whatsapp')->debug('Session updated', [
            'phone' => $this->maskPhone($phone),
            'state' => $session['state'],
        ]);
    }

    /**
     * Refresh TTL session tanpa mengubah data (untuk recheck)
     */
    private function touchSession(string $phone): void
    {
        $key     = self::SESSION_PREFIX . $phone;
        $session = Cache::get($key);

        if ($session) {
            $session['last_activity'] = now()->timestamp;
            Cache::put($key, $session, self::SESSION_TTL);
        }
    }

    /**
     * Hapus session (reset ke new)
     */
    public function resetSession(string $phone): void
    {
        Cache::forget(self::SESSION_PREFIX . $phone);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    /**
     * Ekstrak NISN dari teks bebas
     * Mendukung: "1234567890", "nisn: 1234567890", "NISN 1234567890"
     */
    private function extractNisn(string $text): ?string
    {
        $text = trim($text);

        // Tepat 10 digit
        if (preg_match('/^\d{10}$/', $text)) {
            return $text;
        }

        // Dengan prefix nisn
        if (preg_match('/nisn\s*[:\-]?\s*(\d{10})/i', $text, $m)) {
            return $m[1];
        }

        return null;
    }

    /**
     * Konversi nomor WA (628xxx) ke format lokal (08xxx)
     */
    private function phoneToLocal(string $phone): string
    {
        $cleaned = preg_replace('/\D/', '', $phone);

        if (str_starts_with($cleaned, '62')) {
            return '0' . substr($cleaned, 2);
        }

        if (str_starts_with($cleaned, '0')) {
            return $cleaned;
        }

        return '0' . $cleaned;
    }

    /**
     * Masking nomor HP untuk log
     */
    private function maskPhone(string $phone): string
    {
        if (strlen($phone) > 8) {
            return substr($phone, 0, 4) . '****' . substr($phone, -4);
        }
        return '****';
    }

    /**
     * Greeting sesuai waktu (WIB/WITA)
     */
    private function getTimeGreeting(): string
    {
        // Timezone WITA (Asia/Makassar)
        $hour = (int) now('Asia/Makassar')->format('H');

        return match (true) {
            $hour >= 4  && $hour < 12 => "Selamat pagi",
            $hour >= 12 && $hour < 15 => "Selamat siang",
            $hour >= 15 && $hour < 19 => "Selamat sore",
            default                   => "Selamat malam",
        };
    }

    /**
     * Simpan log request ke database
     */
    private function logRequest(string $phone, string $nisn, array $result, int $ms): void
    {
        try {
            WaRequestLog::create([
                'phone'            => $phone,
                'nisn'             => $nisn,
                'siswa_id'         => $result['data']['siswa']['id'] ?? null,
                'status'           => $result['success'] ? 'success' : ($result['code'] ?? 'error'),
                'ip_address'       => request()->ip(),
                'response_time_ms' => $ms,
                'error_message'    => !$result['success'] ? ($result['message'] ?? null) : null,
                'requested_at'     => now(),
            ]);
        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Log request error', ['error' => $e->getMessage()]);
        }
    }
}