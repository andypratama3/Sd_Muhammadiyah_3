<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'template_id',
        'siswa_id',
        'created_by',
        'data_json',
        'file_path',
        'verification_code',
        'bulk_batch_id',
        'status',
        'valid_from',
        'valid_until',
        'scan_count',
        'revoke_reason',
        'revoked_at',
    ];

    protected $casts = [
        'data_json'   => 'array',
        'valid_from'  => 'datetime',
        'valid_until' => 'datetime',
        'revoked_at'  => 'datetime',
    ];

    // =========================================================
    // RELATIONS
    // =========================================================

    public function template(): BelongsTo
    {
    return $this->belongsTo(DocumentTemplate::class);
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // =========================================================
    // HELPERS
    // =========================================================

    public function getPublicUrlAttribute(): string
    {
        return \Storage::disk('public')->url($this->file_path);
    }

    public function getVerificationUrlAttribute(): string
    {
        return url('/verify/' . $this->verification_code);
    }

    public function fileExists(): bool
    {
        return $this->file_path
            && \Storage::disk('public')->exists($this->file_path);
    }

    // =========================================================
    // STATUS HELPERS
    // =========================================================

    public function isValid(): bool
    {
        if ($this->status === 'revoked') return false;
        if ($this->valid_until && now()->isAfter($this->valid_until)) return false;
        return true;
    }

    public function isExpired(): bool
    {
        return $this->valid_until && now()->isAfter($this->valid_until);
    }

    public function isRevoked(): bool
    {
        return $this->status === 'revoked';
    }
}