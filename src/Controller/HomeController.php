<?php

namespace App\Controller;

use App\Repository\MunicipioRepository;
use App\Security\CsrfTokenManager;

class HomeController extends BaseController
{
    public function __construct(private MunicipioRepository $municipioRepository, private CsrfTokenManager $csrfTokenManager)
    {
    }

    public function index(): void
    {
        $municipiosCount = 0;

        try {
            $municipiosCount = $this->municipioRepository->count();
        } catch (\Throwable $exception) {
            $error = $exception->getMessage();
        }

        $csrfToken = $this->csrfTokenManager->generateToken('consulta_rreo');

        $this->render('pages/home', [
            'title' => 'Consulta RREO',
            'municipiosCount' => $municipiosCount,
            'csrfToken' => $csrfToken,
            'error' => $error ?? null,
        ]);
    }
}
