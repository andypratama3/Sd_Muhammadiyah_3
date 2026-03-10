<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'student_id',
        'created_by',
        'data_json',
        'file_path',
        'verification_code',
        'bulk_batch_id',   // untuk grouping bulk generate
    ];

    protected $casts = [
        'data_json' => 'array',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * URL publik file PDF.
     */
    public function getPublicUrlAttribute(): string
    {
        return \Storage::disk('public')->url($this->file_path);
    }

    /**
     * URL halaman verifikasi dokumen.
     */
    public function getVerificationUrlAttribute(): string
    {
        return url('/verify/' . $this->verification_code);
    }

    /**
     * Apakah file PDF masih tersedia di storage?
     */
    public function fileExists(): bool
    {
        return $this->file_path
            && \Storage::disk('public')->exists($this->file_path);
    }
}