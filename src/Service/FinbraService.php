<?php

namespace App\Service;

use Exception;

class FinbraService
{
    private string $baseUrl;

    private ?string $token;

    private int $timeout;

    public function __construct()
    {
        $config = require __DIR__ . '/../../config/config.php';
        $this->baseUrl = rtrim($config['api']['finbra_base_url'], '/');
        $this->token = $config['api']['token'] ?? null;
        $this->timeout = (int) ($config['api']['timeout'] ?? 30);
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchRreo(string $ibgeCode, int $year, string $periodicity, string $period, string $scope): array
    {
        $query = http_build_query([
            'municipio' => $ibgeCode,
            'ano' => $year,
            'periodicidade' => $periodicity,
            'periodo' => $period,
            'abrangencia' => $scope,
        ]);

        $url = sprintf('%s?%s', $this->baseUrl, $query);

        $headers = [
            'Accept: application/json',
        ];

        if (!empty($this->token)) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        $response = curl_exec($curl);

        if ($response === false) {
            $error = curl_error($curl);
            curl_close($curl);
            throw new Exception('Erro ao comunicar com a API FINBRA: ' . $error);
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($statusCode >= 400) {
            throw new Exception('A API FINBRA retornou um erro (HTTP ' . $statusCode . '). Verifique os parâmetros informados.');
        }

        $decoded = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (!is_array($decoded)) {
            throw new Exception('A resposta da API FINBRA está em um formato inesperado.');
        }

        return $decoded;
    }
}
