<?php

namespace App\Controller\Api;

use App\Repository\MunicipioRepository;

class MunicipioApiController
{
    public function __construct(private MunicipioRepository $municipioRepository)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $term = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $limit = (int) (filter_input(INPUT_GET, 'limit', FILTER_SANITIZE_NUMBER_INT) ?: 20);

        try {
            $municipios = $this->municipioRepository->searchByName($term, max(1, min($limit, 100)));
            echo json_encode([
                'data' => array_map(static fn ($municipio) => [
                    'id' => $municipio['codigo_ibge'],
                    'text' => sprintf('%s/%s', $municipio['nome'], $municipio['uf']),
                    'uf' => $municipio['uf'],
                ], $municipios),
            ], JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Não foi possível recuperar os municípios.',
                'details' => $exception->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }
    }
}
