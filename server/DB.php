<?php
class DB
{
    private static $instance = null;
    private $pdo;
    private $prefix;

    private function __construct()
    {
        $cfg = require __DIR__ . '/config.php';
        $db = $cfg['db'];
        $this->prefix = $db['prefix'];
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
        $this->pdo = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function table($name)
    {
        return $this->prefix . $name;
    }

    public function fetch($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function insert($table, $data)
    {
        $table = $this->table($table);
        $fields = array_keys($data);
        $placeholders = array_map(function ($f) {
            return ':' . $f;
        }, $fields);
        $sql = "INSERT INTO `{$table}` (`" . implode('`,`', $fields) . "`) VALUES (" . implode(',', $placeholders) . ")";
        $stmt = $this->pdo->prepare($sql);
        $params = [];
        foreach ($data as $k => $v) {
            $params[':' . $k] = $v;
        }
        $stmt->execute($params);
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $table = $this->table($table);
        $sets = [];
        $params = [];
        foreach ($data as $k => $v) {
            $sets[] = "`{$k}` = :s_{$k}";
            $params[":s_{$k}"] = $v;
        }
        $sql = "UPDATE `{$table}` SET " . implode(',', $sets) . " WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        $params = array_merge($params, $whereParams);
        return $stmt->execute($params);
    }

    public function delete($table, $where, $params = [])
    {
        $table = $this->table($table);
        $sql = "DELETE FROM `{$table}` WHERE {$where}";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function count($table, $where = '1=1', $params = [])
    {
        $table = $this->table($table);
        $sql = "SELECT COUNT(*) as cnt FROM `{$table}` WHERE {$where}";
        $row = $this->fetch($sql, $params);
        return (int)$row['cnt'];
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    public function commit()
    {
        $this->pdo->commit();
    }

    public function rollBack()
    {
        $this->pdo->rollBack();
    }
}
