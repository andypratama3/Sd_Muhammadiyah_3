<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SignatureController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'verification_code' => 'required|string|max:255',
        ]);

        $verificationCode = $request->input('verification_code');

        // ── 1. Cari dokumen ────────────────────────────────
        $document = Document::with(['siswa', 'template', 'creator'])
            ->where('verification_code', $verificationCode)
            ->first();

        if (!$document) {
            return $this->success([
                'status' => 'not_found',
                'data'   => null,
            ], 'Signature tidak ditemukan. Dokumen mungkin palsu.');
        }

        // ── 2. Cek revoked ─────────────────────────────────
        if ($document->status === 'revoked') {
            return $this->success([
                'status' => 'revoked',
                'data'   => $this->formatData($document),
            ], 'Dokumen ini telah dicabut oleh penerbit.');
        }

        // ── 3. Cek expired ─────────────────────────────────
        if ($document->valid_until && now()->isAfter($document->valid_until)) {
            return $this->success([
                'status' => 'expired',
                'data'   => $this->formatData($document),
            ], 'Masa berlaku dokumen ini telah habis.');
        }

        // ── 4. Valid — increment scan count ────────────────
        $document->increment('scan_count');

        return $this->success([
            'status' => 'valid',
            'data'   => $this->formatData($document),
        ], 'Dokumen ini terverifikasi dan sah.');
    }

    private function formatData(Document $document): array
    {
        $dataJson = $document->data_json ?? [];
        $siswa    = $document->siswa;
        $template = $document->template;

        return [
            'label'         => $dataJson['label']         ?? $template?->name         ?? 'Dokumen',
            'description'   => $dataJson['description']   ?? $template?->description  ?? null,
            'issued_to'     => $dataJson['issued_to']     ?? $siswa?->nama_lengkap    ?? '-',
            'issued_by'     => $dataJson['issued_by']     ?? $document->creator?->name ?? '-',
            'document_type' => $dataJson['document_type'] ?? $template?->type         ?? 'lainnya',
            'valid_from'    => $document->valid_from?->toDateString(),
            'valid_until'   => $document->valid_until?->toDateString(),
            'scan_count'    => $document->scan_count,
            'status_label'  => match ($document->status) {
                'valid'   => 'Valid',
                'revoked' => 'Dicabut',
                default   => 'Kedaluwarsa',
            },
            'file_url'      => $document->fileExists() ? $document->public_url : null,
        ];
    }
}