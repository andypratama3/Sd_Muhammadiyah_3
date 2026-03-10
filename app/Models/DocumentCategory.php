<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'logo_path',
    ];

    public function templates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class, 'category_id');
    }

    public function getLogoBase64(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        $fullPath = storage_path('app/public/' . $this->logo_path);

        if (!file_exists($fullPath)) {
            return null;
        }

        $mime = mime_content_type($fullPath);
        $data = base64_encode(file_get_contents($fullPath));

        return "data:{$mime};base64,{$data}";
    }
}
