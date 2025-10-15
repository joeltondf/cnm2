<?php

namespace App\Controller;

use App\Repository\MunicipioRepository;
use App\Security\CsrfTokenManager;

class CompareController extends BaseController
{
    public function __construct(private MunicipioRepository $municipioRepository, private CsrfTokenManager $csrfTokenManager)
    {
    }

    public function index(): void
    {
        $csrfToken = $this->csrfTokenManager->generateToken('comparar_rreo');

        $this->render('pages/compare', [
            'title' => 'Comparar Municípios',
            'csrfToken' => $csrfToken,
        ]);
    }
}
