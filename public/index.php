<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Controller\AboutController;
use App\Controller\CompareController;
use App\Controller\GlossaryController;
use App\Controller\HomeController;
use App\Controller\Api\MunicipioApiController;
use App\Controller\Api\RreoApiController;
use App\Controller\Api\CompareApiController;
use App\Repository\MunicipioRepository;
use App\Security\CsrfTokenManager;
use App\Service\FinbraService;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$municipioRepository = new MunicipioRepository();
$csrfManager = new CsrfTokenManager();
$finbraService = new FinbraService();

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'home':
        (new HomeController($municipioRepository, $csrfManager))->index();
        break;
    case 'compare':
        (new CompareController($municipioRepository, $csrfManager))->index();
        break;
    case 'glossary':
        (new GlossaryController())->index();
        break;
    case 'about':
        (new AboutController())->index();
        break;
    case 'api-municipios':
        (new MunicipioApiController($municipioRepository))->search();
        break;
    case 'api-rreo':
        (new RreoApiController($finbraService, $csrfManager))->fetch();
        break;
    case 'api-compare':
        (new CompareApiController($finbraService, $csrfManager))->fetch();
        break;
    default:
        http_response_code(404);
        echo 'Página não encontrada.';
}
