<?php

namespace App\Services;

use Carbon\Carbon;
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

        if (empty($this->baseUrl) || empty($this->username) || empty($this->password)) {
            Log::error('Shipment API config not loaded.');
            throw new \InvalidArgumentException('Konfigurasi API Shipment tidak ditemukan.');
        }
    }

    private function getToken(): ?string
    {
        $token = Cache::get(self::CACHE_KEY);
        if ($token) {
            return $token;
        }

        try {
            $payload = [
                'username' => $this->username,
                'password' => $this->password,
            ];
            Log::debug('ShipmentAPI login request', [
                'url' => $this->baseUrl . '/api/shipment/login',
                'payload' => ['username' => $this->username, 'password' => '***'],
            ]);

            $response = Http::baseUrl($this->baseUrl)
                ->acceptJson()
                ->post('/api/shipment/login', $payload);

            Log::debug('ShipmentAPI login response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if ($response->successful() && $response->json('access_token')) {
                $newToken = $response->json('access_token');
                $expiresInSeconds = $response->json('expires_in', 3600);
                Cache::put(self::CACHE_KEY, $newToken, $expiresInSeconds - 60);
                return $newToken;
            }

            Log::error('Shipment API Login Failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception("Shipment API Login Failed. Status: {$response->status()} | Body: {$response->body()}");
        } catch (Throwable $e) {
            Log::error('Shipment API Login Exception', ['message' => $e->getMessage()]);
            throw new \Exception("Shipment API Login Exception: {$e->getMessage()}");
        }
    }

    /**
     * Mengambil data shipment dari API.
     */
    public function getShipment(string $shipmentNumber, string $dateStart, string $dateEnd): ?array
    {
        try {
            $token = $this->getToken();
            if (!$token) {
                throw new \Exception('Gagal mendapatkan token API. Lihat log untuk detail login.');
            }

            // Lakukan permintaan pertama
            $response = $this->makeDataRequest($token, $shipmentNumber, $dateStart, $dateEnd);

            // --- PERBAIKAN: Logika Otomatis Login Ulang (Retry) ---
            if ($response->status() === 401) {
                Log::warning('Token 401 (Invalid/Expired). Menghapus token lama dan mencoba login ulang.');

                // 1. Hapus token lama yang tidak valid
                Cache::forget(self::CACHE_KEY);

                // 2. Dapatkan token baru (ini akan memaksa login ulang)
                $token = $this->getToken();

                // 3. Coba lagi permintaan data dengan token baru
                $response = $this->makeDataRequest($token, $shipmentNumber, $dateStart, $dateEnd);
            }
            // --- SELESAI PERBAIKAN ---

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? true) === false && str_contains($data['message'] ?? '', 'Data tidak ditemukan')) {
                    throw new \Exception("Data tidak ditemukan di API untuk shipment {$shipmentNumber} pada rentang tanggal tersebut.");
                }
                return $data;
            }

            // Jika setelah retry tetap gagal
            Log::warning('Failed to fetch shipment data after retry', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception("Failed to fetch shipment data. Status: {$response->status()} | Body: {$response->body()}");
        } catch (Throwable $e) {
            Log::error('Shipment API getShipment Exception', ['message' => $e->getMessage()]);
            // Melemparkan ulang error agar bisa ditangkap oleh Filament
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Helper method untuk mengirim permintaan data (menghindari duplikasi kode)
     */
    private function makeDataRequest(?string $token, string $shipmentNumber, string $dateStart, string $dateEnd)
    {
        $payload = [
            'shipment_number' => $shipmentNumber,
            'no_spj' => "",
            'shipment_date_start' => $dateStart,
            'shipment_date_end' => $dateEnd,
            'last_update_date' => "",
            'code_plant' => "",
            'shipment_status' => "",
            'vendor_name' => "",
            'nopol' => "",
            'nopin' => "",
            'shipto_name' => ""
        ];

        Log::debug('ShipmentAPI data request', [
            'url' => $this->baseUrl . '/api/shipment',
            'payload' => $payload,
        ]);

        $response = Http::baseUrl($this->baseUrl)
            ->withToken($token)
            ->acceptJson()
            ->timeout(30)
            ->post('/api/shipment', $payload);

        Log::debug('ShipmentAPI data response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return $response;
    }
}
