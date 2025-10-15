<?php

namespace App\Controller;

class AboutController extends BaseController
{
    public function index(): void
    {
        $this->render('pages/about', [
            'title' => 'Sobre o Projeto',
        ]);
    }
}
