<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

// Semua komunikasi ke API server Bandung Spiders. Client tidak punya database member sendiri.
// Setiap metode mengembalikan ['status' => kode HTTP, 'success' => bool, 'data' => isi JSON].
class SpidersApiService
{
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('api.server_url'), '/');
    }

    public function masuk(string $login, string $password): array
    {
        return $this->send(fn () => $this->http(null)->post("{$this->baseUrl}/masuk", ['login' => $login, 'password' => $password, 'perangkat' => 'Client web']));
    }

    public function keluar(string $token): array
    {
        return $this->send(fn () => $this->http($token)->post("{$this->baseUrl}/keluar"));
    }

    public function getKelompokUsia(string $token): array
    {
        return $this->send(fn () => $this->http($token)->get("{$this->baseUrl}/kelompok-usia"));
    }

    public function getAllMember(string $token, array $filter = []): array
    {
        return $this->send(fn () => $this->http($token)->get("{$this->baseUrl}/member", $filter));
    }

    public function getMember(string $token, int $id): array
    {
        return $this->send(fn () => $this->http($token)->get("{$this->baseUrl}/member/{$id}"));
    }

    public function createMember(string $token, array $data): array
    {
        return $this->send(fn () => $this->http($token)->post("{$this->baseUrl}/member", $data));
    }

    public function updateMember(string $token, int $id, array $data): array
    {
        return $this->send(fn () => $this->http($token)->put("{$this->baseUrl}/member/{$id}", $data));
    }

    public function patchMember(string $token, int $id, array $data): array
    {
        return $this->send(fn () => $this->http($token)->patch("{$this->baseUrl}/member/{$id}", $data));
    }

    public function deleteMember(string $token, int $id): array
    {
        return $this->send(fn () => $this->http($token)->delete("{$this->baseUrl}/member/{$id}"));
    }

    private function http(?string $token): PendingRequest
    {
        $http = Http::acceptJson()->asJson()->timeout(5);

        return $token ? $http->withToken($token) : $http;
    }

    private function send(callable $request): array
    {
        try {
            return $this->handleResponse($request());
        } catch (ConnectionException) {
            return ['status' => 503, 'success' => false, 'data' => null];
        }
    }

    private function handleResponse(Response $response): array
    {
        return [
            'status' => $response->status(),
            'success' => $response->successful(),
            'data' => $response->json(),
        ];
    }
}
