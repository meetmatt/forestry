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
     * @param array $args
     * @return \PDOStatement
     */
    public function exec($sql, array $args = [])
    {
        $statement = $this->db->query($sql);

        return $statement->execute($args);
    }
}
