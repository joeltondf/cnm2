<?php
return [
    'app' => [
        'name' => 'Explorador RREO FINBRA',
        'base_url' => getenv('APP_BASE_URL') ?: '',
    ],
    'api' => [
        'finbra_base_url' => getenv('FINBRA_API_URL') ?: 'https://api.fazenda.gov.br/finbra/rreo',
        'token' => getenv('FINBRA_API_TOKEN') ?: null,
        'timeout' => 30,
    ],
];
