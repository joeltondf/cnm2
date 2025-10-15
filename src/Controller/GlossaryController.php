<?php

namespace App\Controller;

class GlossaryController extends BaseController
{
    public function index(): void
    {
        $this->render('pages/glossary', [
            'title' => 'Glossário e Ajuda',
        ]);
    }
}
