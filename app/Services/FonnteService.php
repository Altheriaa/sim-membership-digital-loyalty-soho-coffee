<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FonnteService
{
    /**
     * Send an OTP code via WhatsApp using Fonnte API.
     *
     * @return array<string, mixed>
     *
     * @throws RuntimeException
     */
    public function sendOTP(
        string $phone,
        string $code,
        ?string $customMessage = null
    ): array {
        if (app()->environment('testing')) {
            return [
                'status' => true,
                'target' => $phone,
                'message' => 'Simulated in testing environment.',
            ];
        }

        $token = config('services.fonnte.token');

        if (! $token) {
            throw new RuntimeException('Token Fonnte belum dikonfigurasi di file .env (FONNTE_TOKEN).');
        }

        $message = $customMessage ?? "Kode OTP SOHO Coffee Anda adalah *{$code}*. Jangan bagikan kode ini kepada siapapun demi keamanan akun Anda.";

        try {
            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->connectTimeout(15)
                ->timeout(25)
                ->retry(2, 500, throw: false)
                ->post('https://api.fonnte.com/send', [
                    'target' => $phone,
                    'message' => $message,
                    'countryCode' => '62',
                ]);
        } catch (\Throwable $e) {
            Log::error('Fonnte HTTP request error: '.$e->getMessage(), [
                'phone' => $phone,
                'exception' => $e,
            ]);

            $friendlyMessage = str_contains($e->getMessage(), 'timed out') || str_contains($e->getMessage(), 'error 28')
                ? 'Koneksi ke server WhatsApp (Fonnte) sedang lambat atau terputus. Silakan coba kirim ulang.'
                : 'Koneksi ke server WhatsApp gagal: '.$e->getMessage();

            throw new RuntimeException($friendlyMessage, 0, $e);
        }

        if (! $response->successful()) {
            $errorDetail = $response->json('reason') ?? $response->body();
            Log::error('Fonnte API HTTP non-200 response', [
                'status' => $response->status(),
                'response' => $errorDetail,
            ]);

            throw new RuntimeException('Fonnte error (HTTP '.$response->status().'): '.$errorDetail);
        }

        $result = $response->json();

        if (! is_array($result) || ! ($result['status'] ?? false)) {
            $reason = is_array($result) ? ($result['reason'] ?? 'Fonnte gagal mengirim pesan.') : 'Format respons Fonnte tidak valid.';

            Log::error('Fonnte send failed: '.$reason, [
                'phone' => $phone,
                'response' => $result,
            ]);

            throw new RuntimeException($reason);
        }

        return $result;
    }
}
