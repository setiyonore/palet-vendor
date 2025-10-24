<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ShipmentApiService
{
    protected string $baseUrl;
    protected string $username;
    protected string $password;
    protected const CACHE_KEY = 'shipment_api_jwt_token';

    public function __construct()
    {
        $this->baseUrl = config('services.shipment_api.url');
        $this->username = config('services.shipment_api.username');
        $this->password = config('services.shipment_api.password');
    }

    /**
     * 
     * Cek dari cache jika tidak ada, lakukan login untuk mendapatkan token baru.
     *
     * @return string|null
     */
    private function getToken(): ?string
    {

        $token = Cache::get(self::CACHE_KEY);
        if ($token) {
            return $token;
        }


        try {
            $response = Http::baseUrl($this->baseUrl)
                ->acceptJson()
                ->post('/api/shipment/login', [
                    'username' => $this->username,
                    'password' => $this->password,
                ]);

            if ($response->successful() && $response->json('access_token')) {
                $newToken = $response->json('access_token');
                $expiresInSeconds = $response->json('expires_in', 3600); // 1 jam 

                // Simpan token ke cache dengan masa berlaku
                Cache::put(self::CACHE_KEY, $newToken, $expiresInSeconds - 60);

                return $newToken;
            }

            Log::error('Shipment API Login Failed', ['status' => $response->status(), 'body' => $response->body()]);
            return null;
        } catch (Throwable $e) {
            Log::error('Shipment API Login Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Mengambil data shipment dari API.
     *
     * @param string $shipmentNumber
     * @return array|null
     */
    public function getShipment(string $shipmentNumber): ?array
    {
        $token = $this->getToken();

        if (!$token) {
            // Gagal mendapatkan token, tidak bisa melanjutkan
            return null;
        }
        try {

            $response = Http::baseUrl($this->baseUrl)
                ->withToken($token)
                ->acceptJson()
                ->timeout(30)
                ->post('/api/shipment', [
                    'shipment_number' => $shipmentNumber
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            // Jika token expired (401 Unauthorized), hapus token dari cache
            if ($response->status() === 401) {
                Cache::forget(self::CACHE_KEY);
            }

            Log::warning('Failed to fetch shipment data', [
                'status' => $response->status(),
                'body' => $response->body(),
                'shipment_number' => $shipmentNumber
            ]);

            return null;
        } catch (Throwable $e) {
            Log::error('Shipment API getShipment Exception', ['message' => $e->getMessage()]);
            return null;
        }
    }
}
