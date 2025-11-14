<?php

declare(strict_types=1);

namespace App\Adapters;

use App\Adapters\Contracts\TokenAdapterInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final readonly class PassportTokenAdapter implements TokenAdapterInterface
{
    /**
     * Lấy token theo Password Grant
     *
     * @return array<string, mixed>
     */
    public function getPasswordGrantToken(string $username, string $password): array
    {
        $clientId = (string) config('passport.password_client_id');
        $clientSecret = (string) config('passport.password_client_secret');

        if ($clientId === '' || $clientSecret === '') {
            throw new RuntimeException('Passport password client is not configured. Please run php artisan passport:install');
        }

        $baseUrl = config('oauth.server_url', config('app.url'));
        $tokenUrl = rtrim((string) $baseUrl, '/').'/oauth/token';

        try {
            $response = Http::timeout(10)
                ->asForm()
                ->post($tokenUrl, [
                    'grant_type' => 'password',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'username' => $username,
                    'password' => $password,
                    'scope' => '',
                ]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('Passport token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Passport token request exception', [
                'message' => $e->getMessage(),
                'username' => $username,
            ]);

            return [];
        }
    }

    /**
     * Refresh access token theo chuẩn Password Grant
     *
     * @return array<string, mixed>
     */
    public function refreshGrantToken(string $refreshToken): array
    {
        $clientId = (string) config('passport.password_client_id');
        $clientSecret = (string) config('passport.password_client_secret');

        if ($clientId === '' || $clientSecret === '') {
            throw new RuntimeException('Passport password client is not configured. Please run php artisan passport:install');
        }

        $baseUrl = config('oauth.server_url', config('app.url'));
        $tokenUrl = rtrim((string) $baseUrl, '/').'/oauth/token';

        try {
            $response = Http::timeout(10)
                ->asForm()
                ->post($tokenUrl, [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refreshToken,
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'scope' => '',
                ]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::warning('Passport refresh token request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('Passport refresh token request exception', [
                'message' => $e->getMessage(),
            ]);

            return [];
        }
    }
}
