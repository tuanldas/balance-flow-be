<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Contracts\TokenServiceInterface;
use Illuminate\Support\Facades\Http;

final readonly class TokenService implements TokenServiceInterface
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
            abort(500, 'Passport password client is not configured.');
        }

        $baseUrl = config('oauth.server_url', config('app.url'));
        $tokenUrl = rtrim($baseUrl, '/') . '/oauth/token';

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'password',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'username' => $username,
            'password' => $password,
            'scope' => '',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
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
            abort(500, 'Passport password client is not configured.');
        }

        $baseUrl = config('oauth.server_url', config('app.url'));
        $tokenUrl = rtrim($baseUrl, '/') . '/oauth/token';

        $response = Http::asForm()->post($tokenUrl, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'scope' => '',
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        return [];
    }
}