<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\Database\Connection;

session_start();

$messages = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo = Connection::getInstance();
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

    if (!empty($_FILES['estados']['tmp_name'])) {
        $messages[] = 'Iniciando carga de estados...';
        try {
            $pdo->beginTransaction();
            $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
            $pdo->exec('DELETE FROM municipios');
            $pdo->exec('DELETE FROM estados');
            $stmt = $pdo->prepare('INSERT INTO estados (codigo, uf, nome, regiao) VALUES (:codigo, :uf, :nome, :regiao)');
            $handle = fopen($_FILES['estados']['tmp_name'], 'rb');
            if ($handle === false) {
                throw new \RuntimeException('Não foi possível abrir o arquivo de estados.');
            }
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                $stmt->execute([
                    ':codigo' => $data['codigo_uf'] ?? $data['codigo'] ?? null,
                    ':uf' => $data['sigla'] ?? $data['uf'] ?? '',
                    ':nome' => $data['nome'] ?? '',
                    ':regiao' => $data['regiao'] ?? '',
                ]);
            }
            fclose($handle);
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            $pdo->commit();
            $messages[] = 'Estados carregados com sucesso.';
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
            $errors[] = 'Erro ao carregar estados: ' . $exception->getMessage();
        }
    }

    if (!empty($_FILES['municipios']['tmp_name'])) {
        $messages[] = 'Iniciando carga de municípios...';
        try {
            $pdo->beginTransaction();
            $pdo->exec('DELETE FROM municipios');
            $stmt = $pdo->prepare('INSERT INTO municipios (codigo_ibge, nome, uf, capital, latitude, longitude, siafi_id, ddd, fuso_horario) VALUES (:codigo_ibge, :nome, :uf, :capital, :latitude, :longitude, :siafi, :ddd, :fuso)');
            $handle = fopen($_FILES['municipios']['tmp_name'], 'rb');
            if ($handle === false) {
                throw new \RuntimeException('Não foi possível abrir o arquivo de municípios.');
            }
            $header = fgetcsv($handle);
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                $stmt->execute([
                    ':codigo_ibge' => $data['codigo_ibge'] ?? $data['ibge'] ?? null,
                    ':nome' => $data['nome'] ?? '',
                    ':uf' => $data['uf'] ?? '',
                    ':capital' => isset($data['capital']) ? (int) $data['capital'] : 0,
                    ':latitude' => $data['latitude'] ?? null,
                    ':longitude' => $data['longitude'] ?? null,
                    ':siafi' => $data['siafi_id'] ?? null,
                    ':ddd' => $data['ddd'] ?? null,
                    ':fuso' => $data['fuso_horario'] ?? null,
                ]);
            }
            fclose($handle);
            $pdo->commit();
            $messages[] = 'Municípios carregados com sucesso.';
        } catch (\Throwable $exception) {
            $pdo->rollBack();
            $errors[] = 'Erro ao carregar municípios: ' . $exception->getMessage();
        }
    }

    if (empty($_FILES['estados']['tmp_name']) && empty($_FILES['municipios']['tmp_name'])) {
        $errors[] = 'Selecione ao menos um arquivo para importar.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>População de Municípios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body class="bg-light py-5">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h1 class="h5 mb-0">Carregar estados e municípios</h1>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="estados" class="form-label">Arquivo de estados (CSV)</label>
                            <input class="form-control" type="file" id="estados" name="estados" accept=".csv">
                            <div class="form-text">Use o arquivo <code>estados.csv</code> do repositório Municípios Brasileiros.</div>
                        </div>
                        <div class="mb-3">
                            <label for="municipios" class="form-label">Arquivo de municípios (CSV)</label>
                            <input class="form-control" type="file" id="municipios" name="municipios" accept=".csv">
                            <div class="form-text">Use o arquivo <code>municipios.csv</code> do mesmo conjunto de dados.</div>
                        </div>
                        <button type="submit" class="btn btn-primary">Processar arquivos</button>
                    </form>
                </div>
            </div>
            <?php if ($messages) : ?>
                <div class="alert alert-success mt-3" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($messages as $message) : ?>
                            <li><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if ($errors) : ?>
                <div class="alert alert-danger mt-3" role="alert">
                    <ul class="mb-0">
                        <?php foreach ($errors as $message) : ?>
                            <li><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
