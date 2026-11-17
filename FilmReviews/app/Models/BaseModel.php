<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\TableGateway;

/**
 * Base Domain Model delegating table persistence through TableGateway.
 */
abstract class BaseModel
{
    protected TableGateway $gateway;

    public function __construct(\PDO $pdo, string $tableName, string $primaryKey = 'id')
    {
        $this->gateway = new TableGateway($pdo, $tableName, $primaryKey);
    }

    public function total(?string $field = null, mixed $value = null): int
    {
        return $this->gateway->total($field, $value);
    }

    public function findById(mixed $id): array|false
    {
        return $this->gateway->findById($id);
    }

    public function findOne(string $column, mixed $value): array|false
    {
        return $this->gateway->findOne($column, $value);
    }

    public function find(string $column, mixed $value, ?string $orderBy = null, ?string $direction = null): array
    {
        return $this->gateway->find($column, $value, $orderBy, $direction);
    }

    public function findAll(?string $orderBy = null, ?string $direction = null): array
    {
        return $this->gateway->findAll($orderBy, $direction);
    }

    public function insert(array $fields): string
    {
        return $this->gateway->insert($fields);
    }

    public function update(array $fields): void
    {
        $this->gateway->update($fields);
    }

    public function delete(mixed $id): void
    {
        $this->gateway->delete($id);
    }

    public function deleteWhere(string $column, mixed $value): void
    {
        $this->gateway->deleteWhere($column, $value);
    }

    public function getPdo(): \PDO
    {
        return $this->gateway->getPdo();
    }
}
