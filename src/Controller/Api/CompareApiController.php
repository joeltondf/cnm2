<?php

namespace App\Controller\Api;

use App\Security\CsrfTokenManager;
use App\Service\FinbraService;
use Exception;

class CompareApiController
{
    public function __construct(private FinbraService $finbraService, private CsrfTokenManager $csrfTokenManager)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function fetch(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        $token = filter_input(INPUT_POST, '_csrf_token', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        if (!$this->csrfTokenManager->isTokenValid('comparar_rreo', $token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Token CSRF inválido ou expirado.'], JSON_THROW_ON_ERROR);
            return;
        }

        $municipios = filter_input(INPUT_POST, 'municipios', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
        $year = (int) filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT);
        $demonstrativo = filter_input(INPUT_POST, 'demonstrativo', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $period = (int) filter_input(INPUT_POST, 'period', FILTER_SANITIZE_NUMBER_INT);
        $sphere = filter_input(INPUT_POST, 'sphere', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $annex = filter_input(INPUT_POST, 'annex', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

        if (empty($municipios) || $year === 0 || $demonstrativo === '' || $period === 0 || $sphere === '' || $annex === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Parâmetros obrigatórios ausentes.'], JSON_THROW_ON_ERROR);
            return;
        }

        try {
            $payload = [];
            foreach ($municipios as $ibge) {
                $payload[$ibge] = $this->finbraService->fetchRreo($ibge, $year, $demonstrativo, $period, $sphere, $annex);
            }

            $_SESSION['rreo_last_comparison'] = $payload;
            echo json_encode(['data' => $payload], JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Não foi possível comparar os municípios selecionados.',
                'details' => $exception->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }
    }
}
