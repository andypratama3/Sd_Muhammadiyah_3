<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'kelas_id',
        'name',
        'html_template',
        'canvas_json',
        'generate_mode',
    ];

    protected $casts = [
        'canvas_json' => 'array',
        'generate_mode' => 'string',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'template_id');
    }

    public function kelasList(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id');
    }

    public function pelajarans(): BelongsToMany
    {
        return $this->belongsToMany(Pelajaran::class, 'document_template_pelajaran', 'document_template_id', 'pelajaran_id')
                    ->withTimestamps();
    }

    // =========================================================
    // VARIABLE HELPERS
    // =========================================================

    /**
     * Semua variabel {{...}} KECUALI reserved (logo, barcode_signature).
     * Dipakai untuk: form generate, mapping bulk, preview variables AJAX.
     *
     * @return array<string>
     */
    public function extractVariables(): array
    {
        return $this->parseVariables(exclude: ['logo', 'barcode_signature']);
    }

    /**
     * Semua variabel termasuk reserved (logo, barcode_signature).
     * Dipakai untuk: tampilan di tabel index agar admin tahu fitur apa saja.
     *
     * @return array<string>
     */
    public function allVariables(): array
    {
        return $this->parseVariables(exclude: []);
    }

    /**
     * Internal parser — extract unique variabel dari html_template.
     *
     * @param  array<string>  $exclude  nama variabel yang dikecualikan
     * @return array<string>
     */
    private function parseVariables(array $exclude = []): array
    {
        if (!$this->html_template) {
            return [];
        }

        preg_match_all('/\{\{(.*?)\}\}/', $this->html_template, $matches);

        return array_values(
            array_filter(
                array_unique(array_map('trim', $matches[1])),
                fn ($v) => $v !== '' && !in_array($v, $exclude)
            )
        );
    }

    // =========================================================
    // FLAG HELPERS
    // =========================================================

    public function hasLogo(): bool
    {
        return str_contains($this->html_template ?? '', '{{logo}}');
    }

    public function hasBarcodeSignature(): bool
    {
        return str_contains($this->html_template ?? '', '{{barcode_signature}}');
    }
}