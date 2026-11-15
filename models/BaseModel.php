<?php

abstract class BaseModel {
    protected PDO $db;
    protected string $table;
    protected string $pk;
    protected string $alias;
    protected bool $audit = true;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    abstract public function fields(): array;
    abstract protected function selectSql(): string;
    abstract protected function fromSql(): string;
    abstract protected function searchSql(): string;
    abstract protected function sortMap(): array;

    protected function filterSql(): array {
        return [];
    }

    protected function defaultSort(): array {
        return ['id', 'desc'];
    }

    public function crossCheck(array $clean, array &$errors, ?int $id): void {
    }

    protected function prepareData(array $clean): array {
        return $clean;
    }

    protected function auditSelect(): string {
        return ", {$this->alias}.created_at, {$this->alias}.updated_at, cu.username AS created_by_name, uu.username AS updated_by_name";
    }

    protected function auditJoin(): string {
        return " LEFT JOIN app_user cu ON cu.user_id = {$this->alias}.created_by LEFT JOIN app_user uu ON uu.user_id = {$this->alias}.updated_by";
    }

    protected function run(string $sql, array $bind = []): PDOStatement {
        $stmt = $this->db->prepare($sql);
        foreach ($bind as $name => $value) {
            if (is_int($value)) {
                $stmt->bindValue($name, $value, PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $stmt->bindValue($name, $value, PDO::PARAM_BOOL);
            } elseif ($value === null) {
                $stmt->bindValue($name, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue($name, $value, PDO::PARAM_STR);
            }
        }
        $stmt->execute();
        return $stmt;
    }

    public function paginate(array $params, int $page, int $perPage): array {
        $where = [];
        $bind = [];

        $q = trim((string) ($params['q'] ?? ''));
        if ($q !== '') {
            $where[] = '(' . $this->searchSql() . ' ILIKE :search)';
            $bind[':search'] = '%' . addcslashes($q, '%_\\') . '%';
        }

        foreach ($this->filterSql() as $key => $def) {
            $value = trim((string) ($params[$key] ?? ''));
            if ($value === '') {
                continue;
            }
            if ($def['type'] === 'int' && !ctype_digit($value)) {
                continue;
            }
            if ($def['type'] === 'date') {
                $d = DateTime::createFromFormat('Y-m-d', $value);
                if (!$d || $d->format('Y-m-d') !== $value) {
                    continue;
                }
            }
            $where[] = $def['sql'];
            $bind[':' . $key] = $def['type'] === 'int' ? (int) $value : ($def['type'] === 'like' ? '%' . addcslashes($value, '%_\\') . '%' : $value);
        }

        $whereSql = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        $total = (int) $this->run('SELECT COUNT(*) FROM ' . $this->fromSql() . $whereSql, $bind)->fetchColumn();

        $sorts = $this->sortMap();
        [$defaultKey, $defaultDir] = $this->defaultSort();
        $sortKey = isset($sorts[$params['sort'] ?? '']) ? $params['sort'] : $defaultKey;
        $dir = strtolower($params['dir'] ?? $defaultDir) === 'asc' ? 'ASC' : 'DESC';
        $orderSql = ' ORDER BY ' . $sorts[$sortKey] . ' ' . $dir . ', ' . $this->alias . '.' . $this->pk . ' ASC';

        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, $page), $pages);
        $bind[':limit'] = $perPage;
        $bind[':offset'] = ($page - 1) * $perPage;

        $sql = 'SELECT ' . $this->selectSql() . ' FROM ' . $this->fromSql() . $whereSql . $orderSql . ' LIMIT :limit OFFSET :offset';
        $rows = $this->run($sql, $bind)->fetchAll();

        return [
            'rows' => $rows,
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'sort' => $sortKey,
            'dir' => strtolower($dir),
        ];
    }

    public function find(int $id): ?array {
        $sql = 'SELECT ' . $this->selectSql() . ' FROM ' . $this->fromSql() . ' WHERE ' . $this->alias . '.' . $this->pk . ' = :id';
        $row = $this->run($sql, [':id' => $id])->fetch();
        return $row ?: null;
    }

    public function create(array $clean, ?int $userId): int {
        $data = $this->prepareData($clean);
        $columns = array_keys($data);
        $bind = [];
        foreach ($data as $column => $value) {
            $bind[':' . $column] = $value;
        }
        if ($this->audit) {
            $columns[] = 'created_by';
            $columns[] = 'updated_by';
            $bind[':created_by'] = $userId;
            $bind[':updated_by'] = $userId;
        }
        $placeholders = array_map(fn($c) => ':' . $c, $columns);
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ") RETURNING {$this->pk}";
        return (int) $this->run($sql, $bind)->fetchColumn();
    }

    public function update(int $id, array $clean, ?int $userId): void {
        $data = $this->prepareData($clean);
        $sets = [];
        $bind = [':id' => $id];
        foreach ($data as $column => $value) {
            $sets[] = "$column = :$column";
            $bind[':' . $column] = $value;
        }
        if ($this->audit) {
            $sets[] = 'updated_by = :updated_by';
            $bind[':updated_by'] = $userId;
        }
        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->pk} = :id";
        $this->run($sql, $bind);
    }

    public function delete(int $id): bool {
        $stmt = $this->run("DELETE FROM {$this->table} WHERE {$this->pk} = :id", [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    protected function valueExists(string $column, $value, ?int $excludeId): bool {
        $sql = "SELECT 1 FROM {$this->table} WHERE {$column} = :value";
        $bind = [':value' => $value];
        if ($excludeId !== null) {
            $sql .= " AND {$this->pk} <> :id";
            $bind[':id'] = $excludeId;
        }
        return (bool) $this->run($sql, $bind)->fetchColumn();
    }
}
