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
    public function fetchRreo(
        string $ibgeCode,
        int $year,
        string $demonstrativo,
        int $period,
        string $sphere,
        string $annex
    ): array
    {
        $query = http_build_query([
            'an_exercicio' => $year,
            'nr_periodo' => $period,
            'co_tipo_demonstrativo' => $demonstrativo,
            'co_esfera' => $sphere,
            'no_anexo' => $annex,
            'id_ente' => $ibgeCode,
            'limit' => 5000,
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

        return $this->normalizeResponse($decoded, $ibgeCode, $year, $demonstrativo, $period, $sphere, $annex);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizeResponse(
        array $payload,
        string $ibgeCode,
        int $year,
        string $demonstrativo,
        int $period,
        string $sphere,
        string $annex
    ): array {
        $items = $payload['items'] ?? $payload['data'] ?? [];

        if (!is_array($items)) {
            $items = [];
        }

        $annexes = [];
        $annexMap = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $annexName = $item['no_anexo'] ?? $item['anexo'] ?? $annex;
            $tableLabel = $item['rotulo'] ?? 'Tabela Principal';
            $columnLabel = $item['coluna'] ?? 'Valor';
            $accountLabel = $item['conta'] ?? $item['cod_conta'] ?? 'Conta';
            $value = $item['valor'] ?? null;
            $unit = $item['unidade'] ?? null;

            if (!isset($annexMap[$annexName])) {
                $annexMap[$annexName] = [];
            }

            if (!isset($annexMap[$annexName][$tableLabel])) {
                $annexMap[$annexName][$tableLabel] = [
                    'columns' => [],
                    'rows' => [],
                    'unit' => $unit,
                ];
            }

            if (!in_array($columnLabel, $annexMap[$annexName][$tableLabel]['columns'], true)) {
                $annexMap[$annexName][$tableLabel]['columns'][] = $columnLabel;
            }

            if (!isset($annexMap[$annexName][$tableLabel]['rows'][$accountLabel])) {
                $annexMap[$annexName][$tableLabel]['rows'][$accountLabel] = [];
            }

            if ($value !== null && is_numeric($value)) {
                $value = (float) $value;
            }

            $annexMap[$annexName][$tableLabel]['rows'][$accountLabel][$columnLabel] = $value;
        }

        foreach ($annexMap as $annexName => $tables) {
            $tableList = [];

            foreach ($tables as $tableLabel => $tableData) {
                $headers = array_merge(['Conta'], $tableData['columns']);
                $rows = [];

                foreach ($tableData['rows'] as $account => $columnValues) {
                    $row = [$account];
                    foreach ($tableData['columns'] as $columnLabel) {
                        $row[] = $columnValues[$columnLabel] ?? null;
                    }
                    $rows[] = $row;
                }

                $tableList[] = [
                    'title' => $tableLabel,
                    'headers' => $headers,
                    'rows' => $rows,
                    'unit' => $tableData['unit'],
                ];
            }

            $annexes[] = [
                'title' => $annexName,
                'tables' => $tableList,
            ];
        }

        $firstItem = $items[0] ?? [];

        $metadata = [
            'municipio' => [
                'codigo_ibge' => $firstItem['cod_ibge'] ?? $ibgeCode,
                'nome' => $firstItem['instituicao'] ?? null,
                'uf' => $firstItem['uf'] ?? null,
                'populacao' => $firstItem['populacao'] ?? null,
            ],
            'ano' => $year,
            'periodo' => $period,
            'demonstrativo' => $demonstrativo,
            'esfera' => $sphere,
            'anexo' => $annex,
        ];

        return [
            'municipio' => $metadata['municipio'],
            'metadata' => $metadata,
            'annexes' => $annexes,
            'raw' => $items,
        ];
    }
}
