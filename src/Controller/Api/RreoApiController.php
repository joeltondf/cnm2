<?php

namespace App\Controller\Api;

use App\Security\CsrfTokenManager;
use App\Service\FinbraService;
use Exception;

class RreoApiController
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

        if (!$this->csrfTokenManager->isTokenValid('consulta_rreo', $token)) {
            http_response_code(400);
            echo json_encode(['error' => 'Token CSRF inválido ou expirado.'], JSON_THROW_ON_ERROR);
            return;
        }

        $ibge = filter_input(INPUT_POST, 'ibge', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $year = (int) filter_input(INPUT_POST, 'year', FILTER_SANITIZE_NUMBER_INT);
        $periodicity = filter_input(INPUT_POST, 'periodicity', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $period = filter_input(INPUT_POST, 'period', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';
        $scope = filter_input(INPUT_POST, 'scope', FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? '';

        if ($ibge === '' || $year === 0 || $periodicity === '' || $period === '' || $scope === '') {
            http_response_code(422);
            echo json_encode(['error' => 'Parâmetros obrigatórios ausentes.'], JSON_THROW_ON_ERROR);
            return;
        }

        try {
            $data = $this->finbraService->fetchRreo($ibge, $year, $periodicity, $period, $scope);
            $_SESSION['rreo_last_result'] = $data;
            echo json_encode(['data' => $data], JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            http_response_code(500);
            echo json_encode([
                'error' => 'Não foi possível obter os dados do RREO.',
                'details' => $exception->getMessage(),
            ], JSON_THROW_ON_ERROR);
        }
    }
}
