<?php
return [
    'app' => [
        'name' => 'Explorador RREO FINBRA',
        'base_url' => getenv('APP_BASE_URL') ?: 'https://cnm2.luizdivino.com',
    ],
    'api' => [
        'finbra_base_url' => getenv('FINBRA_API_URL') ?: 'https://apidatalake.tesouro.gov.br/ords/siconfi/tt/rreo_anexo',
        'token' => getenv('FINBRA_API_TOKEN') ?: null,
        'timeout' => 30,
    ],
];
