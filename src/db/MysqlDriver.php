<?php
/**
 * Created by PhpStorm.
 * User: wujie
 * Date: 2019-01-30
 * Time: 15:07
 */

namespace mysqldiff\db;


class MysqlDriver
{
    /**
     * @var MysqlConfig;
     */
    private $config;

    /**
     * @return MysqlConfig
     */
    public function getConfig(): MysqlConfig
    {
        return $this->config;
    }

    /**
     * @var \PDO
     */
    private $connection;

    private static $connectionArray;

    public function __construct(MysqlConfig $config)
    {
        $this->config = $config;
        $this->setConnection();
    }


    /**
     * @param MysqlConfig $config
     * @return self
     */
    public static function make(MysqlConfig $config)
    {
        $key = md5(serialize($config));
        if (!isset(self::$connectionArray[$key])) {
            self::$connectionArray[$key] = new self($config);
        }
        return self::$connectionArray[$key];
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    private function setConnection()
    {
        $this->connection = new \PDO("mysql:dbname={$this->config->getDbName()};host={$this->config->getHost()}", $this->config->getUser(), $this->config->getPassword());
        if ($this->config->getCharset()) {
            $this->connection->exec("set character set '" . $this->config->getCharset() . "'");
        }
    }
}