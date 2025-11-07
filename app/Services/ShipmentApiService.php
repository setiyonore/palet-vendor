<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use Carbon\Carbon;

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
            Log::error('Shipment API config not loaded. Check config/services.php and .env variables.');
            throw new \InvalidArgumentException('Konfigurasi API Shipment (URL, Username, Password) tidak ditemukan. Harap periksa file .env dan config/services.php.');
        }
    }

    /**
     * Mengambil token JWT.
     * Cek dari cache, jika tidak ada, lakukan login untuk mendapatkan token baru.
     */
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

            // --- LOGGING DITAMBAHKAN ---
            Log::debug('ShipmentAPI login request', [
                'url' => $this->baseUrl . '/api/shipment/login',
                'payload' => ['username' => $this->username, 'password' => '***'], // Password disamarkan
            ]);

            $response = Http::baseUrl($this->baseUrl)
                ->acceptJson()
                ->post('/api/shipment/login', $payload);

            // --- LOGGING DITAMBAHKAN ---
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

            // --- PERBAIKAN: Melemparkan Error agar terlihat di UI ---
            Log::error('Shipment API Login Failed', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \Exception("Shipment API Login Failed. Status: {$response->status()} | Body: {$response->body()}");
        } catch (Throwable $e) {
            Log::error('Shipment API Login Exception', ['message' => $e->getMessage()]);
            // Melemparkan ulang error agar bisa ditangkap oleh Filament
            throw new \Exception("Shipment API Login Exception: {$e->getMessage()}");
        }
    }

    /**
     * Mengambil data shipment dari API.
     */
    public function getShipment(string $shipmentNumber, string $dateStart, string $dateEnd): ?array
    {
        $token = $this->getToken();
        if (!$token) {
            throw new \Exception('Gagal mendapatkan token API. Lihat log untuk detail login.');
        }

        try {
            // --- PERBAIKAN: Gunakan $dateStart dan $dateEnd dari parameter (input form) ---
            // Baris yang memaksa default tanggal bulan ini telah dihapus.
            $payload = [
                'shipment_number' => $shipmentNumber,
                'no_spj' => "",
                'shipment_date_start' => $dateStart, // <-- Menggunakan input dari form
                'shipment_date_end' => $dateEnd, // <-- Menggunakan input dari form
                'last_update_date' => "",
                'code_plant' => "",
                'shipment_status' => "",
                'vendor_name' => "",
                'nopol' => "",
                'nopin' => "",
                'shipto_name' => ""
            ];
            // --- SELESAI ---

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

            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? true) === false && str_contains($data['message'] ?? '', 'Data tidak ditemukan')) {
                    throw new \Exception("Data tidak ditemukan di API untuk shipment {$shipmentNumber} pada rentang tanggal tersebut.");
                }
                return $data;
            }

            if ($response->status() === 401) {
                Cache::forget(self::CACHE_KEY);
            }

            Log::warning('Failed to fetch shipment data', [
                'status' => $response->status(),
                'body' => $response->body(),
                'payload' => $payload
            ]);
            throw new \Exception("Failed to fetch shipment data. Status: {$response->status()} | Body: {$response->body()}");
        } catch (Throwable $e) {
            Log::error('Shipment API getShipment Exception', ['message' => $e->getMessage()]);
            throw new \Exception($e->getMessage());
        }
    }
}
