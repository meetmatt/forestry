<?php

namespace Forestry\Framework\Database;

class Postgres
{
    const DEFAULT_HOST = '127.0.0.1';
    const DEFAULT_PORT = 5432;

    private $db;

    /**
     * @param string $host
     * @param int $port
     * @param string $user
     * @param string $pass
     * @param string $dbname
     */
    public function __construct($host = self::DEFAULT_HOST, $port = self::DEFAULT_PORT, $user, $pass, $dbname)
    {
        $dsn = sprintf('pgsql:host=%s;port=%d;dbname=%s;user=%s;password=%s', $host, $port, $dbname, $user, $pass);

        $this->db = new \PDO($dsn);
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    }

    /**
     * @param array $options
     * @return Postgres
     */
    public static function createFromOptions(array $options)
    {
        return new self(
            isset($options['host']) ? $options['host'] : self::DEFAULT_HOST,
            isset($options['port']) ? $options['port'] : self::DEFAULT_PORT,
            $options['user'],
            $options['pass'],
            $options['dbname']
        );
    }

    /**
     * @param string $sql
     * @return int|bool
     */
    public function exec($sql)
    {
        return $this->db->exec($sql);
    }

    /**
     * @param string $sql
     * @param array $args
     * @return array|false
     */
    public function select($sql, array $args = [])
    {
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute($args) !== false) {
            return $stmt->fetchAll();
        }

        return false;
    }

    /**
     * @param string $sql
     * @param array $args
     * @return int|false Last inserted ID or false on failure
     */
    public function insert($sql, $args = [])
    {
        $stmt = $this->db->prepare($sql);
        if ($result = $stmt->execute($args)) {
            return $stmt->fetchAll()[0];
        }

        return false;
    }

    /**
     * @param string $sql
     * @param array $args
     * @return int|false Affected rows or false on failure
     */
    public function update($sql, $args = [])
    {
        $stmt = $this->db->prepare($sql);
        if ($result = $stmt->execute($args)) {
            return $stmt->rowCount();
        }

        return false;
    }

    /**
     * @param string $sql
     * @param array $args
     * @return int|false Deleted rows or false on failure
     */
    public function delete($sql, $args = [])
    {
        $stmt = $this->db->prepare($sql);
        if ($result = $stmt->execute($args)) {
            return $stmt->rowCount();
        }

        return false;
    }


    public function begin()
    {
        return $this->db->beginTransaction();
    }

    public function commit()
    {
        return $this->db->commit();
    }

    public function rollback()
    {
        return $this->db->rollBack();
    }
}
