<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Object-Oriented Table Data Gateway encapsulating safe prepared statements.
 */
class TableGateway
{
    private \PDO $pdo;
    private string $table;
    private string $primaryKey;

    public function __construct(\PDO $pdo, string $table, string $primaryKey = 'id')
    {
        $this->pdo = $pdo;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
    }

    private function query(string $sql, array $parameters = []): \PDOStatement
    {
        try {
            $statement = $this->pdo->prepare($sql);
            $statement->execute($parameters);
            return $statement;
        } catch (\PDOException $e) {
            error_log('TableGateway Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    public function total(?string $field = null, mixed $value = null): int
    {
        $sql = 'SELECT COUNT(*) FROM `' . $this->table . '`';
        $parameters = [];

        if ($field !== null && $value !== null) {
            $sql .= ' WHERE `' . $field . '` = :value';
            $parameters['value'] = $value;
        }

        return (int) $this->query($sql, $parameters)->fetchColumn();
    }

    public function findById(mixed $value): array|false
    {
        $sql = 'SELECT * FROM `' . $this->table . '` WHERE `' . $this->primaryKey . '` = :value';
        return $this->query($sql, ['value' => $value])->fetch();
    }

    public function find(string $column, mixed $value, ?string $orderBy = null, ?string $direction = null): array
    {
        $sql = 'SELECT * FROM `' . $this->table . '` WHERE `' . $column . '` = :value';
        $sql .= $this->buildOrderByClause($orderBy, $direction);
        return $this->query($sql, ['value' => $value])->fetchAll();
    }

    public function findOne(string $column, mixed $value): array|false
    {
        $sql = 'SELECT * FROM `' . $this->table . '` WHERE `' . $column . '` = :value LIMIT 1';
        return $this->query($sql, ['value' => $value])->fetch();
    }

    public function findAll(?string $orderBy = null, ?string $direction = null): array
    {
        $sql = 'SELECT * FROM `' . $this->table . '`';
        $sql .= $this->buildOrderByClause($orderBy, $direction);
        return $this->query($sql)->fetchAll();
    }

    public function insert(array $fields): string
    {
        $columns = array_map(static fn(string $col): string => '`' . $col . '`', array_keys($fields));
        $placeholders = array_map(static fn(string $col): string => ':' . $col, array_keys($fields));

        $sql = 'INSERT INTO `' . $this->table . '` (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')';
        $this->query($sql, $fields);

        return $this->pdo->lastInsertId();
    }

    public function update(array $fields): void
    {
        $assignments = [];
        foreach (array_keys($fields) as $key) {
            $assignments[] = '`' . $key . '` = :' . $key;
        }

        $sql = 'UPDATE `' . $this->table . '` SET ' . implode(',', $assignments)
            . ' WHERE `' . $this->primaryKey . '` = :primaryKey';

        $fields['primaryKey'] = $fields[$this->primaryKey];
        $this->query($sql, $fields);
    }

    public function save(array $fields): string|int
    {
        if (empty($fields[$this->primaryKey])) {
            unset($fields[$this->primaryKey]);
            return $this->insert($fields);
        }

        if ($this->findById($fields[$this->primaryKey])) {
            $this->update($fields);
            return $fields[$this->primaryKey];
        }

        return $this->insert($fields);
    }

    public function delete(mixed $id): void
    {
        $sql = 'DELETE FROM `' . $this->table . '` WHERE `' . $this->primaryKey . '` = :id';
        $this->query($sql, ['id' => $id]);
    }

    public function deleteWhere(string $column, mixed $value): void
    {
        $sql = 'DELETE FROM `' . $this->table . '` WHERE `' . $column . '` = :value';
        $this->query($sql, ['value' => $value]);
    }

    public function customQuery(string $sql, array $parameters = []): array
    {
        return $this->query($sql, $parameters)->fetchAll();
    }

    public function customQuerySingle(string $sql, array $parameters = []): array|false
    {
        return $this->query($sql, $parameters)->fetch();
    }

    public function customExecute(string $sql, array $parameters = []): int
    {
        return $this->query($sql, $parameters)->rowCount();
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    private function buildOrderByClause(?string $orderBy, ?string $direction): string
    {
        if (empty($orderBy)) {
            return '';
        }

        $clause = ' ORDER BY ' . $orderBy;
        if (!empty($direction) && !preg_match('/\b(asc|desc)\b/i', $orderBy)) {
            $clause .= ' ' . (strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC');
        }

        return $clause;
    }
}
