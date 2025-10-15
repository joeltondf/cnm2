<?php

namespace App\Controller;

class BaseController
{
    protected function render(string $template, array $parameters = []): void
    {
        extract($parameters, EXTR_OVERWRITE);
        $appConfig = require __DIR__ . '/../../config/config.php';
        $appName = $appConfig['app']['name'];
        $baseUrl = $appConfig['app']['base_url'] ?? '';
        $pageTitle = $parameters['title'] ?? $appName;

        include __DIR__ . '/../../templates/layout/header.php';
        include __DIR__ . '/../../templates/' . $template . '.php';
        include __DIR__ . '/../../templates/layout/footer.php';
    }
}
