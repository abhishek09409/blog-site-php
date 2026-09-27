<?php

declare(strict_types=1);

class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        $config = config('database');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );
        try {
            $this->pdo = new PDO($dsn, (string) $config['username'], (string) $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new HttpException('Database connection failed.', 500, $e);
        }
    }

    public static function instance(): self
    {
        return self::$instance ??= new self();
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement
    {
        $statement = $this->pdo->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): int
    {
        $this->assertTable($table);
        $columns = array_keys($data);
        foreach ($columns as $column) {
            $this->assertColumn($column);
        }
        $holders = array_map(static fn (string $column): string => ':' . $column, $columns);
        $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES (' . implode(',', $holders) . ')';
        $this->query($sql, $data);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $this->assertTable($table);
        $sets = [];
        $params = [];
        foreach ($data as $column => $value) {
            $this->assertColumn((string) $column);
            $sets[] = '`' . $column . '` = ?';
            $params[] = $value;
        }
        foreach ($whereParams as $value) {
            $params[] = $value;
        }
        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $sets) . ' WHERE ' . $where;
        return $this->query($sql, $params)->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int
    {
        $this->assertTable($table);
        return $this->query('DELETE FROM `' . $table . '` WHERE ' . $where, $params)->rowCount();
    }

    public function transaction(callable $callback): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $callback($this);
            $this->pdo->commit();
            return $result;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function assertTable(string $table): void
    {
        if (!preg_match('/^[a-z_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name.');
        }
    }

    private function assertColumn(string $column): void
    {
        if (!preg_match('/^[a-z0-9_]+$/', $column)) {
            throw new InvalidArgumentException('Invalid column name.');
        }
    }
}

function db(): Database
{
    return Database::instance();
}
