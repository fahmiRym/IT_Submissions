<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\ArsipSignature;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class QrSignatureService
{
    private static function buildQr(string $data, int $size = 160): ?string
    {
        try {
            $builder = new Builder(
                writer: new PngWriter(),
                writerOptions: [],
                validateResult: false,
                data: $data,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::Medium,
                size: $size,
                margin: 4,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            );
            $result = $builder->build();
            return 'data:image/png;base64,' . base64_encode($result->getString());
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('QR build failed: ' . $e->getMessage());
            return null;
        }
    }

    public static function renderSignatureQrDataUri(Arsip $arsip, ArsipSignature $sig, int $size = 160): ?string
    {
        $token = $arsip->verify_token;
        $url = $token ? route('verify.show', $token) . '?sig=' . substr($sig->hash ?? '', 0, 12)
                      : 'arsip://' . $arsip->id . '/' . substr($sig->hash ?? '', 0, 12);
        return self::buildQr($url, $size);
    }

    public static function renderDocumentQrDataUri(Arsip $arsip, int $size = 160): ?string
    {
        $payload = $arsip->verify_token ? route('verify.show', $arsip->verify_token) : ($arsip->no_registrasi ?: 'NO-REG');
        return self::buildQr($payload, $size);
    }

    public static function renderTextQrDataUri(string $text, int $size = 120): ?string
    {
        return self::buildQr($text, $size);
    }
}
