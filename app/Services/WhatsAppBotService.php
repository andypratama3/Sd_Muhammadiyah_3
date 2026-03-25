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
 * States:
 *  new          → user belum pernah chat / session expired
 *  menu         → sudah disambut, menunggu pilihan menu atau NISN
 *  waiting_nisn → memilih cek tagihan, menunggu input NISN
 *  verified     → NISN + WA sudah terverifikasi
 *
 * Intents:
 *  islamic_greeting     → assalamualaikum & variannya
 *  greeting             → salam biasa (halo, hi, selamat pagi, dll)
 *  menu_selection       → user memilih angka dari menu (1–10)
 *  nisn_input           → input NISN 10 digit
 *  check_payment_intent → menyebut tagihan/spp tapi belum verified
 *  recheck              → cek ulang (sudah verified)
 *  thanks               → terima kasih, makasih, jazakallah
 *  goodbye              → pamit / penutup percakapan
 *  acknowledgement      → oke, siap, paham, mengerti
 *  faq                  → cocok dengan FAQ JSON
 *  help                 → minta bantuan / panduan
 *  invalid_nisn_format  → angka tapi bukan 10 digit
 *  back_to_menu         → user minta kembali ke menu utama
 *  unknown_verified     → verified tapi tidak dikenali
 *  unknown_new          → baru tapi tidak dikenali
 */
class WhatsAppBotService
{
    private const SESSION_TTL    = 86400;
    private const SESSION_PREFIX = 'wa_session:';
    private const FAQ_FILE       = 'public/asset_dashboard/faq-prompt.json';
    private const FAQ_CACHE_KEY  = 'wa_faq_data';
    private const FAQ_CACHE_TTL  = 3600;

    /** @var array<int, array{keywords: string[], question: string, answer: string}> */
    private array $faqData = [];

    // =========================================================================
    // MENU UTAMA — 10 Pilihan
    // =========================================================================

    /**
     * Definisi menu utama bot.
     * Setiap item: emoji, label, intent yang dipicu.
     */
    private const MAIN_MENU = [
        1  => ['label' => 'Cek Tagihan / SPP',            'intent' => 'check_payment_intent', 'emoji' => '💳'],
        2  => ['label' => 'Cara Pembayaran',               'intent' => 'faq_cara_bayar',       'emoji' => '🏦'],
        3  => ['label' => 'Info NISN',                     'intent' => 'faq_nisn',             'emoji' => '🔢'],
        4  => ['label' => 'Tagihan Belum Update / Error',  'intent' => 'faq_bayar_belum_masuk','emoji' => '⏳'],
        5  => ['label' => 'Jadwal & Info Sekolah',         'intent' => 'faq_jadwal',           'emoji' => '📅'],
        6  => ['label' => 'Pendaftaran Siswa Baru (PPDB)', 'intent' => 'faq_ppdb',             'emoji' => '🏫'],
        7  => ['label' => 'Izin Tidak Masuk',              'intent' => 'faq_izin',             'emoji' => '📝'],
        8  => ['label' => 'Ekstrakurikuler & Kegiatan',    'intent' => 'faq_ekskul',           'emoji' => '🎯'],
        9  => ['label' => 'Kontak & Jam Operasional TU',   'intent' => 'faq_kontak',           'emoji' => '📞'],
        10 => ['label' => 'Lainnya / Tanya Bebas',         'intent' => 'free_question',        'emoji' => '💬'],
    ];

    // =========================================================================
    // KEYWORD MAPS — sentralisasi semua keyword agar mudah di-maintain
    // =========================================================================

    private const KEYWORDS_ISLAMIC_GREETING = [
        'assalamualaikum', 'assalamu alaikum', "assalamu'alaikum",
        'waalaikumsalam', 'wa alaikum salam', 'alaikumsalam',
        'assalam', 'aslm', 'ass ',
    ];

    private const KEYWORDS_GREETING = [
        'halo', 'hai', 'hello', 'hi', 'hei', 'hallo', 'hey', 'hola',
        'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam',
        'met pagi', 'met siang', 'met sore', 'met malam',
        'good morning', 'good afternoon', 'good evening', 'good night',
        'permisi', 'pagi ', 'sore ', 'malam ',
    ];

    private const KEYWORDS_THANKS = [
        'terima kasih', 'terimakasih', 'makasih', 'makasi', 'makasii',
        'thanks', 'thank you', 'thx', 'tq', 'ty', 'tengkyu', 'tengkyuu',
        'mksh', 'trims', 'trimakasih',
        'jazakallah', 'jazakallahu', 'jazakallahukhair', 'jazakumullah',
        'barakallah', 'barakallahu', 'syukron', 'syukran',
        'matur nuwun', 'matur suwun', 'nuhun',
    ];

    private const KEYWORDS_GOODBYE = [
        'sampai jumpa', 'dadah', 'bye', 'bye bye', 'selamat tinggal',
        'sampai ketemu', 'wassalam', 'wassalamualaikum', 'wslm', 'ws',
        'sudah cukup', 'cukup sekian', 'sekian', 'itu saja', 'selesai',
        'ok makasih', 'oke makasih', 'ok terima kasih', 'oke terima kasih',
        'baik terima kasih', 'siap terima kasih', 'ya sudah',
    ];

    private const KEYWORDS_ACKNOWLEDGEMENT = [
        'oke', 'ok', 'okey', 'okay',
        'siap', 'baik', 'ya', 'yap', 'yep',
        'paham', 'mengerti', 'ngerti', 'oh iya', 'ohh', 'ooh',
        'noted', 'sip', 'mantap', 'lanjut', 'gas',
        'udah', 'sudah', 'iya', 'iyah',
    ];

    private const KEYWORDS_BACK_TO_MENU = [
        '0', 'menu', 'kembali', 'back', 'home',
        'kembali ke menu', 'menu utama', 'daftar menu',
        'pilihan', 'tampilkan menu', 'mulai lagi', 'list',
    ];

    private const KEYWORDS_PAYMENT = [
        'spp', 'bayar', 'tagihan', 'tunggakan', 'cicilan',
        'pembayaran', 'lunas', 'belum bayar', 'cek bayar',
        'info bayar', 'biaya', 'iuran', 'dpp', 'seragam',
        'virtual account', 'va ', 'transfer', 'nunggak',
    ];

    private const KEYWORDS_RECHECK = [
        'cek', 'cek ulang', 'refresh', 'update', 'perbarui',
        'lihat', 'tampil', 'tampilkan',
    ];

    private const KEYWORDS_HELP = [
        'help', 'bantuan', 'panduan', 'cara', 'gimana', 'bagaimana',
        'petunjuk', 'bisa apa', 'apa yang bisa', 'fitur', '?',
    ];

    // =========================================================================
    // MENU-SPECIFIC FAQ KEYWORDS — mapping menu intent ke keyword FAQ JSON
    // =========================================================================

    private const MENU_FAQ_KEYWORDS = [
        'faq_cara_bayar'        => ['cara bayar spp', 'pembayaran spp', 'bayar spp', 'va bank'],
        'faq_nisn'              => ['nisn itu apa', 'lupa nisn', 'cari nisn', 'apa itu nisn'],
        'faq_bayar_belum_masuk' => ['sudah bayar tapi belum lunas', 'pembayaran tidak masuk', 'konfirmasi bayar'],
        'faq_jadwal'            => ['jam masuk sekolah', 'libur sekolah', 'jadwal ujian', 'jam pulang sekolah'],
        'faq_ppdb'              => ['ppdb', 'pendaftaran murid baru', 'cara masuk sdm3', 'syarat masuk sd'],
        'faq_izin'              => ['izin tidak masuk', 'izin sakit wa', 'anak sakit izin', 'cara izin anak'],
        'faq_ekskul'            => ['kegiatan ekstra', 'ekstrakurikuler', 'ekskul apa saja', 'pramuka'],
        'faq_kontak'            => ['nomor telepon sekolah', 'jam operasional sekolah', 'jam buka tu'],
    ];

    public function __construct(
        private readonly WhatsappMetaService $whatsapp
    ) {
        $this->loadFaq();
    }

    // =========================================================================
    // FAQ LOADER
    // =========================================================================

    private function loadFaq(): void
    {
        try {
            $this->faqData = Cache::remember(self::FAQ_CACHE_KEY, self::FAQ_CACHE_TTL, function () {
                $path = base_path(self::FAQ_FILE);

                if (!file_exists($path)) {
                    Log::channel('whatsapp')->warning('⚠️ FAQ file not found', ['path' => $path]);
                    return [];
                }

                $raw     = file_get_contents($path);
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

    public function reloadFaq(): void
    {
        Cache::forget(self::FAQ_CACHE_KEY);
        $this->loadFaq();
    }

    /**
     * Cari FAQ yang cocok dengan teks input user.
     * Mengembalikan jawaban atau null jika tidak ada yang match.
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

    /**
     * Cari FAQ berdasarkan keyword intent menu tertentu.
     * Digunakan ketika user memilih nomor menu.
     */
    private function matchFaqByMenuIntent(string $menuIntent): ?string
    {
        $keywords = self::MENU_FAQ_KEYWORDS[$menuIntent] ?? [];

        foreach ($keywords as $keyword) {
            $answer = $this->matchFaq($keyword);
            if ($answer !== null) {
                return $answer;
            }
        }

        return null;
    }

    // =========================================================================
    // ENTRY POINT
    // =========================================================================

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

            $this->whatsapp->sendMessage($phone, $replyMessage);

            $ms = (int) ((microtime(true) - $startTime) * 1000);
            Log::channel('whatsapp')->info('✅ Bot reply sent', ['ms' => $ms]);

        } catch (\Exception $e) {
            Log::channel('whatsapp')->error('❌ Bot handle error', [
                'error' => $e->getMessage(),
                'phone' => $this->maskPhone($phone),
            ]);

            try {
                $this->whatsapp->sendMessage(
                    $phone,
                    "⚠️ *Sistem sedang mengalami gangguan*\n\nMohon coba beberapa saat lagi.\n\nJika masalah berlanjut, hubungi admin sekolah."
                );
            } catch (\Exception) {
                // Silent fail
            }
        }
    }

    // =========================================================================
    // INTENT DETECTION — Priority-based
    // =========================================================================

    /**
     * Deteksi intent dari teks bebas user.
     *
     * Priority order:
     *  1.  NISN tepat 10 digit                → nisn_input
     *  2.  "nisn: 1234567890"                 → nisn_input
     *  3.  Angka 0 atau 1–10 + state = menu   → menu_selection / back_to_menu
     *  4.  Back to menu keywords              → back_to_menu
     *  5.  Islamic greeting                   → islamic_greeting
     *  6.  Goodbye                            → goodbye
     *  7.  Thanks                             → thanks
     *  8.  Acknowledgement (exact match)      → acknowledgement
     *  9.  Greeting biasa                     → greeting
     *  10. Payment keywords                   → check_payment_intent / recheck
     *  11. Recheck keywords                   → recheck (jika verified)
     *  12. Help keywords (+ cek FAQ)          → faq / help
     *  13. FAQ match langsung                 → faq
     *  14. Angka bukan 10 digit + waiting     → invalid_nisn_format
     *  15. State fallback                     → unknown_*
     */
    private function detectIntent(string $text, string $state): string
    {
        $clean = trim($text);
        $lower = mb_strtolower($clean);

        // --- 1. NISN: tepat 10 digit ---
        if (preg_match('/^\d{10}$/', $clean)) {
            return 'nisn_input';
        }

        // --- 2. NISN dengan prefix eksplisit ---
        if (preg_match('/nisn\s*[:\-]?\s*(\d{10})/i', $clean)) {
            return 'nisn_input';
        }

        // --- 3. Pilihan menu (angka 1–10) atau "0" ---
        if (preg_match('/^(10|[0-9])$/', $clean)) {
            if ($clean === '0') {
                return 'back_to_menu';
            }
            // Angka 1–10: jika di state menu → pilih menu, jika verified & "1" → recheck
            if ($state === 'menu') {
                return 'menu_selection';
            }
            if ($state === 'verified' && $clean === '1') {
                return 'recheck';
            }
        }

        // --- 4. Back to menu keywords ---
        foreach (self::KEYWORDS_BACK_TO_MENU as $kw) {
            if ($lower === $kw || str_contains($lower, $kw)) {
                return 'back_to_menu';
            }
        }

        // --- 5. Islamic greeting (sebelum greeting biasa agar tidak tertimpa) ---
        foreach (self::KEYWORDS_ISLAMIC_GREETING as $kw) {
            if (str_contains($lower, $kw)) {
                return 'islamic_greeting';
            }
        }

        // --- 6. Goodbye / penutup ---
        foreach (self::KEYWORDS_GOODBYE as $kw) {
            if (str_contains($lower, $kw)) {
                return 'goodbye';
            }
        }

        // --- 7. Thanks ---
        foreach (self::KEYWORDS_THANKS as $kw) {
            if (str_contains($lower, $kw)) {
                return 'thanks';
            }
        }

        // --- 8. Acknowledgement (exact match agar tidak overmatch) ---
        foreach (self::KEYWORDS_ACKNOWLEDGEMENT as $kw) {
            if ($lower === $kw || $lower === $kw . '!' || $lower === $kw . '.') {
                return 'acknowledgement';
            }
        }

        // --- 9. Greeting biasa ---
        foreach (self::KEYWORDS_GREETING as $kw) {
            if (str_contains($lower, $kw)) {
                return 'greeting';
            }
        }

        // --- 10. Payment keywords ---
        foreach (self::KEYWORDS_PAYMENT as $kw) {
            if (str_contains($lower, $kw)) {
                return $state === 'verified' ? 'recheck' : 'check_payment_intent';
            }
        }

        // --- 11. Recheck keywords ---
        foreach (self::KEYWORDS_RECHECK as $kw) {
            if ($lower === $kw || str_starts_with($lower, $kw . ' ')) {
                return $state === 'verified' ? 'recheck' : 'check_payment_intent';
            }
        }

        // --- 12. Help keywords — cek FAQ dulu ---
        foreach (self::KEYWORDS_HELP as $kw) {
            if (str_contains($lower, $kw)) {
                return $this->matchFaq($text) !== null ? 'faq' : 'help';
            }
        }

        // --- 13. FAQ match langsung ---
        if ($this->matchFaq($text) !== null) {
            return 'faq';
        }

        // --- 14. Angka selain NISN 10 digit ---
        if (preg_match('/^\d+$/', $clean) && $state === 'waiting_nisn') {
            return 'invalid_nisn_format';
        }

        // --- 15. Fallback berdasarkan state ---
        return match ($state) {
            'verified'     => 'unknown_verified',
            'waiting_nisn' => 'invalid_nisn_format',
            default        => 'unknown_new',
        };
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
            // Salam
            'islamic_greeting'      => $this->handleIslamicGreeting($phone, $profileName, $session),
            'greeting'              => $this->handleGreeting($phone, $profileName, $session),

            // Navigasi menu
            'menu_selection'        => $this->handleMenuSelection($phone, $messageText, $profileName, $session),
            'back_to_menu'          => $this->handleBackToMenu($phone, $session),

            // Pembayaran
            'nisn_input'            => $this->handleNisnInput($phone, $messageText, $profileName, $session),
            'check_payment_intent'  => $this->handleCheckPaymentIntent($phone, $profileName, $session),
            'recheck'               => $this->handleRecheck($phone, $session),
            'invalid_nisn_format'   => $this->handleInvalidNisn($phone),

            // Sosial / percakapan
            'thanks'                => $this->handleThanks($phone, $session),
            'goodbye'               => $this->handleGoodbye($phone, $session),
            'acknowledgement'       => $this->handleAcknowledgement($phone, $session),

            // FAQ dari menu
            'faq_cara_bayar',
            'faq_nisn',
            'faq_bayar_belum_masuk',
            'faq_jadwal',
            'faq_ppdb',
            'faq_izin',
            'faq_ekskul',
            'faq_kontak'            => $this->handleMenuFaq($phone, $intent, $session),

            // FAQ & tanya bebas
            'faq'                   => $this->handleFaq($phone, $messageText, $session),
            'free_question'         => $this->handleFreeQuestion($phone, $session),
            'help'                  => $this->handleHelp($phone, $session),

            // Fallback
            'unknown_verified'      => $this->handleUnknownVerified($phone, $session),
            'unknown_new'           => $this->handleUnknownNew($phone, $profileName, $session),
            default                 => $this->handleUnknownNew($phone, $profileName, $session),
        };
    }

    // =========================================================================
    // HANDLERS — SALAM & SOSIAL
    // =========================================================================

    /**
     * Balas assalamualaikum → tampilkan menu utama.
     */
    private function handleIslamicGreeting(string $phone, string $profileName, array $session): string
    {
        $this->updateSession($phone, ['state' => 'menu']);

        $greeting = $this->getTimeGreeting();

        return "Wa'alaikumsalam Warahmatullahi Wabarakatuh 🤲\n\n"
            . "{$greeting}, *{$profileName}* 👋\n\n"
            . "Selamat datang di layanan informasi\n"
            . "🏫 *SD Muhammadiyah 3 Samarinda*\n\n"
            . $this->buildMainMenu();
    }

    /**
     * Salam biasa → tampilkan menu utama.
     */
    private function handleGreeting(string $phone, string $profileName, array $session): string
    {
        $this->updateSession($phone, ['state' => 'menu']);

        $greeting = $this->getTimeGreeting();

        return "{$greeting}, *{$profileName}* 👋\n\n"
            . "Selamat datang di layanan informasi\n"
            . "🏫 *SD Muhammadiyah 3 Samarinda*\n\n"
            . $this->buildMainMenu();
    }

    /**
     * Terima kasih → respon hangat + hint shortcut.
     */
    private function handleThanks(string $phone, array $session): string
    {
        $responses = [
            "Sama-sama! Alhamdulillah bisa membantu 😊🙏",
            "Dengan senang hati! Semoga informasinya bermanfaat. 😊",
            "Sama-sama, senang bisa membantu! 🌟",
        ];

        $msg = $responses[array_rand($responses)];

        // Tambahkan hint sesuai state
        if ($session['state'] === 'verified') {
            $msg .= "\n\nKetik *cek* untuk lihat tagihan terbaru,\natau *0* / *menu* untuk daftar pilihan lainnya. 🙏";
        } else {
            $msg .= "\n\nKetik *0* atau *menu* jika masih ada yang bisa dibantu. 🙏";
        }

        return $msg;
    }

    /**
     * Pamit → balas hangat tanpa reset session.
     */
    private function handleGoodbye(string $phone, array $session): string
    {
        $greeting = $this->getTimeGreeting();

        return "Baik, terima kasih sudah menggunakan layanan kami! 🙏\n\n"
            . "{$greeting} dan sampai jumpa kembali.\n\n"
            . "_Jika sewaktu-waktu butuh bantuan, kami siap melayani._\n\n"
            . "🏫 *SD Muhammadiyah 3 Samarinda*\n"
            . "Wassalamualaikum Warahmatullahi Wabarakatuh 🤲";
    }

    /**
     * Acknowledgement (oke, siap, dll) → respon singkat + hint.
     */
    private function handleAcknowledgement(string $phone, array $session): string
    {
        if ($session['state'] === 'verified') {
            return "Baik! 😊\n\nKetik *cek* untuk lihat tagihan terbaru,\natau *0* / *menu* untuk pilihan lainnya.";
        }

        if ($session['state'] === 'waiting_nisn') {
            return "Baik! 😊\n\nSilakan kirimkan *NISN* putra/putri Anda (10 digit angka).\n\n_Contoh: 1234567890_";
        }

        return "Baik! 😊\n\nKetik *0* atau *menu* untuk melihat daftar layanan. 🙏";
    }

    // =========================================================================
    // HANDLERS — MENU
    // =========================================================================

    /**
     * Bangun string menu utama.
     */
    private function buildMainMenu(): string
    {
        $menu  = "Silakan pilih layanan yang Anda butuhkan\n";
        $menu .= "_(Balas dengan angka pilihan)_\n";
        $menu .= "━━━━━━━━━━━━━━━━━\n\n";

        foreach (self::MAIN_MENU as $no => $item) {
            $menu .= "{$item['emoji']} *{$no}.* {$item['label']}\n";
        }

        $menu .= "\n━━━━━━━━━━━━━━━━━\n";
        $menu .= "_Atau langsung kirim *NISN* (10 digit) untuk cek tagihan._\n";
        $menu .= "_Ketik *0* atau *menu* untuk kembali ke sini kapan saja._";

        return $menu;
    }

    /**
     * User memilih nomor dari menu (1–10).
     */
    private function handleMenuSelection(string $phone, string $messageText, string $profileName, array $session): string
    {
        $no   = (int) trim($messageText);
        $item = self::MAIN_MENU[$no] ?? null;

        if (!$item) {
            return "Pilihan tidak valid. Silakan pilih angka *1–10*.\n\n"
                . $this->buildMainMenu();
        }

        $intent = $item['intent'];

        Log::channel('whatsapp')->info('📋 Menu selected', [
            'phone'  => $this->maskPhone($phone),
            'no'     => $no,
            'intent' => $intent,
        ]);

        return match ($intent) {
            'check_payment_intent' => $this->handleCheckPaymentIntent($phone, $profileName, $session),
            'free_question'        => $this->handleFreeQuestion($phone, $session),
            default                => $this->handleMenuFaq($phone, $intent, $session),
        };
    }

    /**
     * Kembali ke menu utama.
     */
    private function handleBackToMenu(string $phone, array $session): string
    {
        $this->updateSession($phone, ['state' => 'menu']);

        $extra = '';
        if ($session['state'] === 'verified' && $session['nisn']) {
            $extra = "\n✅ _NISN {$session['nisn']} masih aktif. Ketik *cek* untuk lihat tagihan._\n";
        }

        return "📋 *Menu Utama*\n"
            . "🏫 SD Muhammadiyah 3 Samarinda\n"
            . $extra . "\n"
            . $this->buildMainMenu();
    }

    /**
     * Tampilkan jawaban FAQ berdasarkan menu intent (pilihan 2–9).
     */
    private function handleMenuFaq(string $phone, string $menuIntent, array $session): string
    {
        // Coba match dari FAQ JSON terlebih dahulu
        $answer = $this->matchFaqByMenuIntent($menuIntent);

        // Jika tidak ada, gunakan teks statis fallback
        if (!$answer) {
            $answer = $this->getFaqFallback($menuIntent);
        }

        Log::channel('whatsapp')->info('📖 Menu FAQ served', [
            'phone'  => $this->maskPhone($phone),
            'intent' => $menuIntent,
        ]);

        // Tambahkan footer navigasi
        $answer .= "\n\n━━━━━━━━━━━━━━━━━\n";
        $answer .= "Ketik *0* atau *menu* untuk kembali ke daftar pilihan.";
        if ($session['state'] === 'verified') {
            $answer .= "\nKetik *cek* untuk melihat tagihan terbaru.";
        }

        return $answer;
    }

    /**
     * Fallback statis per menu intent jika FAQ JSON tidak match.
     */
    private function getFaqFallback(string $menuIntent): string
    {
        return match ($menuIntent) {
            'faq_cara_bayar' =>
                "🏦 *Cara Pembayaran*\n\n"
                . "1. Kirim *NISN* putra/putri Anda ke bot ini\n"
                . "2. Sistem menampilkan nomor *Virtual Account* & bank\n"
                . "3. Transfer sesuai nominal ke VA tersebut\n"
                . "4. Atau bayar *tunai langsung* ke sekolah\n\n"
                . "_Konfirmasi pembayaran otomatis dalam 1×24 jam._",

            'faq_nisn' =>
                "🔢 *Info NISN*\n\n"
                . "NISN = Nomor Induk Siswa Nasional (10 digit unik per siswa).\n\n"
                . "📍 Bisa ditemukan di:\n"
                . "• Rapor semester\n"
                . "• Kartu pelajar\n"
                . "• Situs: *nisn.data.kemdikbud.go.id*\n\n"
                . "_Jika tidak ketemu, hubungi Tata Usaha sekolah._",

            'faq_bayar_belum_masuk' =>
                "⏳ *Pembayaran Belum Terupdate?*\n\n"
                . "Biasanya verifikasi memerlukan *1×24 jam*.\n\n"
                . "Jika lebih dari 24 jam belum update:\n"
                . "• Kirim ulang NISN untuk cek status terbaru\n"
                . "• Hubungi TU dengan membawa *bukti transfer*\n\n"
                . "_Data diperbarui otomatis setelah terverifikasi._",

            'faq_jadwal' =>
                "📅 *Info Jadwal Sekolah*\n\n"
                . "Informasi jadwal disampaikan melalui:\n"
                . "• *Wali kelas* via WhatsApp\n"
                . "• *Grup WhatsApp kelas*\n"
                . "• *Tata Usaha* sekolah\n\n"
                . "_Hubungi wali kelas untuk info jadwal terkini._",

            'faq_ppdb' =>
                "🏫 *Pendaftaran Siswa Baru (PPDB)*\n\n"
                . "Dokumen yang biasa diperlukan:\n"
                . "• Akta kelahiran\n"
                . "• Kartu Keluarga\n"
                . "• Pas foto\n"
                . "• Ijazah/STTB TK (jika ada)\n\n"
                . "📍 Daftar ke *Tata Usaha* sekolah.\n"
                . "_Pendaftaran dibuka menjelang tahun ajaran baru._",

            'faq_izin' =>
                "📝 *Izin Tidak Masuk*\n\n"
                . "Cara izin tidak masuk:\n"
                . "1. Hubungi *wali kelas* via WA\n"
                . "2. Sampaikan alasan & berapa hari\n"
                . "3. Sakit >2 hari: lampirkan surat dokter\n"
                . "4. Non-sakit >1 hari: buat surat izin tertulis\n\n"
                . "_Segera lapor agar tidak tercatat alpa._",

            'faq_ekskul' =>
                "🎯 *Ekstrakurikuler & Kegiatan*\n\n"
                . "SD Muhammadiyah 3 menyediakan berbagai ekskul untuk\n"
                . "pengembangan bakat siswa.\n\n"
                . "Untuk daftar ekskul, jadwal & pendaftaran:\n"
                . "• Tanya *wali kelas*\n"
                . "• Atau langsung ke *Tata Usaha*\n\n"
                . "_Info ekskul disampaikan di awal tahun ajaran._",

            'faq_kontak' =>
                "📞 *Kontak & Jam Operasional*\n\n"
                . "🏫 *SD Muhammadiyah 3 Samarinda*\n"
                . "📍 Samarinda, Kalimantan Timur\n\n"
                . "⏰ Jam Operasional TU:\n"
                . "Senin – Jumat | Selama jam sekolah\n\n"
                . "_Datang langsung ke sekolah untuk info kontak resmi._",

            default =>
                "ℹ️ Informasi tidak tersedia saat ini.\n\n"
                . "Silakan hubungi *Tata Usaha* sekolah untuk informasi lebih lanjut.",
        };
    }

    /**
     * Pilihan 10: Tanya Bebas.
     */
    private function handleFreeQuestion(string $phone, array $session): string
    {
        return "💬 *Tanya Bebas*\n\n"
            . "Silakan ketik pertanyaan Anda seputar:\n"
            . "• Administrasi & dokumen sekolah\n"
            . "• Kegiatan & program sekolah\n"
            . "• Informasi umum SDM 3\n"
            . "• Dan lainnya\n\n"
            . "Saya akan berusaha membantu sebaik mungkin. 😊\n\n"
            . "_Atau hubungi *Tata Usaha* untuk info yang lebih spesifik._\n\n"
            . "Ketik *0* atau *menu* untuk kembali ke daftar pilihan.";
    }

    // =========================================================================
    // HANDLERS — PEMBAYARAN
    // =========================================================================

    /**
     * User sebut tagihan/SPP tapi belum verified → minta NISN.
     */
    private function handleCheckPaymentIntent(string $phone, string $profileName, array $session): string
    {
        if ($session['state'] === 'verified' && $session['nisn']) {
            return $this->handleRecheck($phone, $session);
        }

        $this->updateSession($phone, ['state' => 'waiting_nisn']);

        return "💳 *Cek Tagihan Sekolah*\n\n"
            . "Untuk melihat informasi tagihan putra/putri Anda,\n"
            . "silakan kirimkan *NISN* (10 digit angka).\n\n"
            . "_Contoh: 1234567890_\n\n"
            . "━━━━━━━━━━━━━━━━━\n"
            . "💡 _Pastikan nomor WA ini terdaftar di sekolah._\n"
            . "Ketik *0* atau *menu* untuk kembali.";
    }

    /**
     * User input NISN → validasi + query database.
     */
    private function handleNisnInput(string $phone, string $messageText, string $profileName, array $session): string
    {
        $nisn = $this->extractNisn($messageText);

        if (!$nisn) {
            return $this->handleInvalidNisn($phone);
        }

        $startTime = microtime(true);
        $result    = $this->queryPaymentData($phone, $nisn);
        $ms        = (int) ((microtime(true) - $startTime) * 1000);

        $this->logRequest($phone, $nisn, $result, $ms);

        if ($result['success']) {
            $this->updateSession($phone, [
                'state'      => 'verified',
                'nisn'       => $nisn,
                'siswa_id'   => $result['data']['siswa']['id'] ?? null,
                'siswa_name' => $result['data']['siswa']['nama'] ?? null,
            ]);

            return $this->formatPaymentMessage($result['data']);
        }

        return $this->formatErrorMessage($result, $phone);
    }

    /**
     * User sudah verified → cek ulang tagihan.
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
            $this->touchSession($phone);
            return $this->formatPaymentMessage($result['data']);
        }

        return $this->formatErrorMessage($result, $phone);
    }

    /**
     * Format NISN tidak valid.
     */
    private function handleInvalidNisn(string $phone): string
    {
        return "⚠️ *Format tidak dikenali*\n\n"
            . "NISN harus berupa *10 digit angka* tanpa spasi.\n\n"
            . "_Contoh: 1234567890_\n\n"
            . "━━━━━━━━━━━━━━━━━\n"
            . "Jika tidak mengetahui NISN, silakan:\n"
            . "• Cek di rapor / kartu pelajar\n"
            . "• Hubungi *Tata Usaha* sekolah\n"
            . "• Cek di nisn.data.kemdikbud.go.id\n\n"
            . "Ketik *0* atau *menu* untuk kembali ke daftar pilihan.";
    }

    // =========================================================================
    // HANDLERS — FAQ & HELP
    // =========================================================================

    /**
     * Jawab pertanyaan dari FAQ JSON (tanya bebas / teks panjang).
     */
    private function handleFaq(string $phone, string $messageText, array $session): string
    {
        $answer = $this->matchFaq($messageText);

        if (!$answer) {
            return $this->handleHelp($phone, $session);
        }

        Log::channel('whatsapp')->info('📖 FAQ matched', [
            'phone'   => $this->maskPhone($phone),
            'message' => mb_substr($messageText, 0, 50),
        ]);

        $answer .= "\n\n━━━━━━━━━━━━━━━━━\n";
        $answer .= $session['state'] === 'verified'
            ? "💡 Ketik *cek* untuk lihat tagihan terbaru.\n"
            : "💡 Kirim *NISN* (10 digit) untuk cek tagihan.\n";
        $answer .= "Ketik *0* atau *menu* untuk daftar pilihan.";

        return $answer;
    }

    /**
     * Bantuan umum + tampilkan menu.
     */
    private function handleHelp(string $phone, array $session): string
    {
        $nisnStatus = $session['nisn']
            ? "✅ NISN: {$session['nisn']} (terverifikasi)"
            : "❌ NISN belum diinput";

        return "📌 *Panduan Penggunaan*\n"
            . "🏫 SD Muhammadiyah 3 Samarinda\n"
            . "━━━━━━━━━━━━━━━━━\n\n"
            . "*Status Anda:* {$nisnStatus}\n\n"
            . "*Cara menggunakan:*\n"
            . "1. Pilih menu dengan mengetik angka *1–10*\n"
            . "2. Atau langsung kirim *NISN* untuk cek tagihan\n"
            . "3. Ketik *cek* untuk perbarui data tagihan (jika sudah verified)\n"
            . "4. Ketik *0* atau *menu* untuk kembali ke daftar\n\n"
            . "━━━━━━━━━━━━━━━━━\n\n"
            . $this->buildMainMenu();
    }

    /**
     * Verified tapi pesan tidak dikenali.
     */
    private function handleUnknownVerified(string $phone, array $session): string
    {
        $nama = $session['siswa_name'] ?? 'putra/putri Anda';

        return "Maaf, perintah tidak dikenali. 🙏\n\n"
            . "Yang bisa saya bantu:\n"
            . "• Ketik *cek* atau *1* → tagihan {$nama}\n"
            . "• Ketik *0* atau *menu* → daftar pilihan\n"
            . "• Kirim *NISN* baru → ganti siswa\n"
            . "• Ketik *bantuan* → panduan\n\n"
            . "_Atau hubungi Tata Usaha untuk bantuan lebih lanjut._";
    }

    /**
     * User baru / session expired → tampilkan menu utama.
     */
    private function handleUnknownNew(string $phone, string $profileName, array $session): string
    {
        $this->updateSession($phone, ['state' => 'menu']);

        $greeting = $this->getTimeGreeting();

        return "{$greeting}, *{$profileName}* 👋\n\n"
            . "Selamat datang di layanan\n"
            . "🏫 *SD Muhammadiyah 3 Samarinda*\n\n"
            . $this->buildMainMenu();
    }

    // =========================================================================
    // DATABASE QUERY
    // =========================================================================

    private function queryPaymentData(string $phone, string $nisn): array
    {
        try {
            $localPhone = $this->phoneToLocal($phone);

            Log::channel('whatsapp')->info('🔍 Query payment', [
                'nisn'       => $nisn,
                'phone_mask' => $this->maskPhone($phone),
                'local'      => $localPhone,
            ]);

            $siswa = Siswa::where('nisn', $nisn)
                ->where('no_hp', $localPhone)
                ->whereNull('deleted_at')
                ->with(['kelas' => function ($q) {
                    $q->select('kelas.id', 'kelas.name')
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

            $kelasAktif = $siswa->kelas->first();
            $namaKelas  = $kelasAktif ? ($kelasAktif->name ?? '-') : '-';

            $charges = Charge::where('siswa_id', $siswa->id)
                ->whereNull('deleted_at')
                ->with('kategori_pembayaran')
                ->orderByDesc('created_at')
                ->get();

            $statusLunas = ['settlement', 'pay_offline'];
            $statusBelum = ['pending', 'expired'];

            $tagihan = $charges->whereIn('transaction_status', $statusBelum)
                ->values()
                ->map(fn($c) => $this->formatTagihanItem($c));

            $lunas = $charges->whereIn('transaction_status', $statusLunas)
                ->sortByDesc('transaction_time')
                ->take(5)
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

        if (count($lunas) > 0) {
            $msg .= "\n\n✅ *Riwayat Pembayaran (5 terakhir)*\n";
            $msg .= "━━━━━━━━━━━━━━━━━\n";
            foreach ($lunas as $l) {
                $msg .= "• {$l['judul']} — {$l['nominal']} ({$l['tanggal']}) {$l['metode']}\n";
            }
        }

        $msg .= "\n━━━━━━━━━━━━━━━━━\n";
        $msg .= "Ketik *cek* untuk perbarui data\n";
        $msg .= "Ketik *0* atau *menu* untuk pilihan lainnya\n";
        $msg .= "_Data diperbarui secara realtime_";

        return $msg;
    }

    private function formatErrorMessage(array $result, string $phone): string
    {
        return match ($result['code'] ?? 'SERVER_ERROR') {
            'NOT_FOUND' =>
                "❌ *Data tidak ditemukan*\n\n"
                . "NISN atau nomor WhatsApp tidak cocok dengan data di sistem.\n\n"
                . "Pastikan:\n"
                . "• NISN yang dimasukkan benar (10 digit)\n"
                . "• Nomor WA ini terdaftar di sistem sekolah\n\n"
                . "━━━━━━━━━━━━━━━━━\n"
                . "Kirim ulang NISN untuk mencoba lagi.\n"
                . "Ketik *0* atau *menu* untuk pilihan lainnya.\n"
                . "Atau hubungi *Tata Usaha* sekolah.",

            'INVALID_INPUT' =>
                "⚠️ *Format NISN tidak valid*\n\n"
                . "NISN harus berupa *10 digit angka*.\n\n"
                . "_Contoh: 1234567890_",

            default =>
                "⚠️ *Sistem sedang tidak dapat diakses*\n\n"
                . "Terjadi gangguan sementara. Silakan coba beberapa saat lagi.\n\n"
                . "Ketik *0* atau *menu* untuk kembali.",
        };
    }

    // =========================================================================
    // SESSION MANAGEMENT
    // =========================================================================

    private function getSession(string $phone): array
    {
        $key     = self::SESSION_PREFIX . $phone;
        $session = Cache::get($key);

        if (!$session) {
            $session = [
                'state'      => 'new',
                'nisn'       => null,
                'siswa_id'   => null,
                'siswa_name' => null,
                'created_at' => now()->timestamp,
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

    private function touchSession(string $phone): void
    {
        $key     = self::SESSION_PREFIX . $phone;
        $session = Cache::get($key);

        if ($session) {
            $session['last_activity'] = now()->timestamp;
            Cache::put($key, $session, self::SESSION_TTL);
        }
    }

    public function resetSession(string $phone): void
    {
        Cache::forget(self::SESSION_PREFIX . $phone);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function extractNisn(string $text): ?string
    {
        $text = trim($text);

        if (preg_match('/^\d{10}$/', $text)) {
            return $text;
        }

        if (preg_match('/nisn\s*[:\-]?\s*(\d{10})/i', $text, $m)) {
            return $m[1];
        }

        return null;
    }

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

    private function maskPhone(string $phone): string
    {
        if (strlen($phone) > 8) {
            return substr($phone, 0, 4) . '****' . substr($phone, -4);
        }
        return '****';
    }

    private function getTimeGreeting(): string
    {
        $hour = (int) now('Asia/Makassar')->format('H');

        return match (true) {
            $hour >= 4  && $hour < 12 => 'Selamat pagi',
            $hour >= 12 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 19 => 'Selamat sore',
            default                   => 'Selamat malam',
        };
    }

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