<?php

namespace App\Repository;

use App\Database\Connection;
use PDO;

class MunicipioRepository
{
    private PDO $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?: Connection::getInstance();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAll(): array
    {
        $statement = $this->connection->query('SELECT * FROM municipios ORDER BY nome');

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function searchByName(string $term, int $limit = 20): array
    {
        $statement = $this->connection->prepare('SELECT * FROM municipios WHERE nome LIKE :term ORDER BY nome LIMIT :limit');
        $likeTerm = '%' . $term . '%';
        $statement->bindParam(':term', $likeTerm, PDO::PARAM_STR);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getByUf(string $uf): array
    {
        $statement = $this->connection->prepare('SELECT * FROM municipios WHERE uf = :uf ORDER BY nome');
        $statement->bindParam(':uf', $uf, PDO::PARAM_STR);
        $statement->execute();

        return $statement->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getByIbge(string $codigoIbge): ?array
    {
        $statement = $this->connection->prepare('SELECT * FROM municipios WHERE codigo_ibge = :codigo LIMIT 1');
        $statement->bindParam(':codigo', $codigoIbge, PDO::PARAM_STR);
        $statement->execute();

        $result = $statement->fetch();

        return $result ?: null;
    }

    public function count(): int
    {
        $statement = $this->connection->query('SELECT COUNT(*) as total FROM municipios');

        return (int) $statement->fetchColumn();
    }
}
