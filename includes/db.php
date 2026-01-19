<?php
/**
 * FoodFlow - Database Connection
 */

require_once __DIR__ . '/config.php';

class Database
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            if (DEBUG_MODE) {
                die("Database connection failed: " . $e->getMessage());
            } else {
                die("Database connection failed. Please try again later.");
            }
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function query($sql, $params = [])
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = [])
    {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $set = [];
        $params = [];
        $i = 0;
        foreach ($data as $key => $value) {
            $placeholder = "set_{$key}";
            $set[] = "{$key} = :{$placeholder}";
            $params[$placeholder] = $value;
        }

        // Convert whereParams to use unique placeholders
        $processedWhere = $where;
        foreach ($whereParams as $key => $value) {
            $placeholder = "where_{$key}";
            $processedWhere = str_replace(":{$key}", ":{$placeholder}", $processedWhere);
            $params[$placeholder] = $value;
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $set) . " WHERE {$processedWhere}";
        return $this->query($sql, $params);
    }

    public function delete($table, $where, $params = [])
    {
        // Handle positional parameters (?)
        if (isset($params[0])) {
            $sql = "DELETE FROM {$table} WHERE {$where}";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        }

        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params);
    }

    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
}

// Helper function for quick access
function db()
{
    return Database::getInstance();
}
