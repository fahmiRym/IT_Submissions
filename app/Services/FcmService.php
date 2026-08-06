<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging (HTTP v1 API).
 *
 * Mengirim push notification ke perangkat Android. Otentikasi memakai
 * Service Account (file JSON dari Firebase Console) untuk menghasilkan
 * OAuth2 access token, lalu dikirim ke endpoint FCM v1.
 *
 * Tidak butuh paket tambahan: JWT ditandatangani dengan openssl bawaan PHP.
 */
class FcmService
{
    private const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';
    private const TOKEN_CACHE_KEY = 'fcm_access_token';

    /** Data service account hasil decode file JSON. */
    private ?array $credentials = null;

    public function __construct()
    {
        $this->loadCredentials();
    }

    /**
     * Apakah FCM siap dipakai (file kredensial valid).
     */
    public function isConfigured(): bool
    {
        return $this->credentials !== null;
    }

    /**
     * Kirim notifikasi ke semua perangkat milik 1 user (Android + web).
     */
    public function sendToUser(int $userId, string $title, string $body, array $data = []): void
    {
        $devices = DeviceToken::where('user_id', $userId)->get(['token', 'platform']);

        if ($devices->isEmpty()) {
            return;
        }

        $this->sendToDevices($devices->toArray(), $title, $body, $data);
    }

    /**
     * Kirim ke daftar device (mixed platform). Payload di-adjust per platform.
     * @param array $devices [['token'=>..., 'platform'=>'android'|'web'|null], ...]
     */
    public function sendToDevices(array $devices, string $title, string $body, array $data = []): void
    {
        if (! $this->isConfigured()) {
            Log::warning('FCM: kredensial belum dikonfigurasi, push dilewati.');
            return;
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) return;

        foreach ($devices as $d) {
            $this->sendOne($accessToken, $d['token'], $title, $body, $data, $d['platform'] ?? 'android');
        }
    }

    /**
     * Kirim notifikasi ke beberapa token sekaligus.
     */
    public function sendToTokens(array $tokens, string $title, string $body, array $data = []): void
    {
        if (! $this->isConfigured()) {
            Log::warning('FCM: kredensial belum dikonfigurasi, push dilewati.');
            return;
        }

        $accessToken = $this->getAccessToken();
        if (! $accessToken) {
            return;
        }

        foreach (array_unique($tokens) as $token) {
            $this->sendOne($accessToken, $token, $title, $body, $data);
        }
    }

    /**
     * Kirim ke satu token. Token invalid akan dihapus dari DB.
     */
    private function sendOne(string $accessToken, string $token, string $title, string $body, array $data, string $platform = 'android'): void
    {
        // FCM v1 mensyaratkan semua value pada "data" berupa string.
        $stringData = ['title' => $title, 'message' => $body];
        foreach ($data as $key => $value) {
            $stringData[(string) $key] = is_scalar($value) ? (string) $value : json_encode($value);
        }

        $tag = !empty($stringData['no_registrasi'])
            ? 'arsip-' . $stringData['no_registrasi']
            : 'arsip-' . ($stringData['notification_id'] ?? ($stringData['share_id'] ?? 'global'));

        $message = [
            'token' => $token,
            'notification' => [
                'title' => $title,
                'body'  => $body,
            ],
            'data' => $stringData,
        ];

        if ($platform === 'web') {
            // WEB PUSH — bekerja walau browser closed (Service Worker handle).
            $message['webpush'] = [
                'headers' => [
                    'Urgency' => 'high',
                    'TTL'     => '600',
                ],
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'icon'  => url('img/logo.png'),
                    'badge' => url('img/logo.png'),
                    'tag'   => $tag,
                    'requireInteraction' => false,
                ],
                'fcm_options' => [
                    'link' => !empty($stringData['click_url']) ? $stringData['click_url'] : url('/'),
                ],
            ];
        } else {
            // ANDROID — heads-up dgn channel + priority MAX.
            $message['android'] = [
                'priority' => 'high',
                'ttl' => '600s',
                'collapse_key' => $tag,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'channel_id' => 'submission_channel_v2',
                    'notification_priority' => 'PRIORITY_MAX',
                    'visibility' => 'PUBLIC',
                    'default_sound' => true,
                    'default_vibrate_timings' => true,
                    'default_light_settings' => true,
                    'tag' => $tag,
                    'sticky' => false,
                    'event_time' => now()->toRfc3339String(),
                ],
            ];
        }

        $payload = ['message' => $message];

        try {
            $response = Http::withToken($accessToken)
                ->timeout(15)
                ->post(
                    "https://fcm.googleapis.com/v1/projects/{$this->credentials['project_id']}/messages:send",
                    $payload
                );

            if ($response->successful()) {
                return;
            }

            // Token tidak valid / tidak terdaftar lagi -> bersihkan dari DB.
            $errorStatus = $response->json('error.status');
            if (in_array($errorStatus, ['NOT_FOUND', 'UNREGISTERED', 'INVALID_ARGUMENT'], true)
                || $response->status() === 404) {
                DeviceToken::where('token', $token)->delete();
            }

            Log::warning('FCM: gagal kirim push', [
                'status' => $response->status(),
                'error' => $response->json('error.status'),
                'message' => $response->json('error.message'),
            ]);
        } catch (\Throwable $e) {
            Log::error('FCM: exception saat kirim push: ' . $e->getMessage());
        }
    }

    /**
     * Ambil OAuth2 access token (di-cache ~55 menit).
     */
    private function getAccessToken(): ?string
    {
        return Cache::remember(self::TOKEN_CACHE_KEY, now()->addMinutes(55), function () {
            return $this->fetchAccessToken();
        });
    }

    /**
     * Tukar JWT service account dengan access token Google.
     */
    private function fetchAccessToken(): ?string
    {
        $now = time();

        $jwt = $this->buildSignedJwt([
            'iss' => $this->credentials['client_email'],
            'scope' => self::SCOPE,
            'aud' => $this->credentials['token_uri'],
            'iat' => $now,
            'exp' => $now + 3600,
        ]);

        if (! $jwt) {
            return null;
        }

        try {
            $response = Http::asForm()->timeout(15)->post($this->credentials['token_uri'], [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error('FCM: gagal ambil access token', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        } catch (\Throwable $e) {
            Log::error('FCM: exception ambil access token: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Bangun & tandatangani JWT (RS256) dengan private key service account.
     */
    private function buildSignedJwt(array $claims): ?string
    {
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode($claims));
        $signingInput = $header . '.' . $payload;

        $signature = '';
        $ok = openssl_sign($signingInput, $signature, $this->credentials['private_key'], OPENSSL_ALGO_SHA256);

        if (! $ok) {
            Log::error('FCM: gagal menandatangani JWT (private_key tidak valid?).');
            return null;
        }

        return $signingInput . '.' . $this->base64UrlEncode($signature);
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Muat & validasi file kredensial service account.
     */
    private function loadCredentials(): void
    {
        $path = config('services.fcm.credentials');

        if (! $path || ! is_file($path)) {
            return;
        }

        $json = json_decode((string) file_get_contents($path), true);

        if (! is_array($json) || empty($json['client_email']) || empty($json['private_key'])) {
            Log::error('FCM: file kredensial tidak valid: ' . $path);
            return;
        }

        // Project ID dari config jika di-set, kalau tidak ambil dari file JSON.
        $json['project_id'] = config('services.fcm.project_id') ?: ($json['project_id'] ?? null);
        $json['token_uri'] = $json['token_uri'] ?? 'https://oauth2.googleapis.com/token';

        if (empty($json['project_id'])) {
            Log::error('FCM: project_id tidak ditemukan (set FCM_PROJECT_ID atau pastikan ada di file JSON).');
            return;
        }

        $this->credentials = $json;
    }
}
