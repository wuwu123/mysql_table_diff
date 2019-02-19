<?php
/**
 * Created by PhpStorm.
 * User: wujie
 * Date: 2019-01-30
 * Time: 15:08
 */

namespace mysqldiff\db;


class MysqlConfig
{
    private $password;
    private $host;
    private $user;
    private $dbName;

    private $charset = "utf8";

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDbName()
    {
        return $this->dbName;
    }

    /**
     * @param mixed $dbName
     * @return $this
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * @param string $charset
     * @return $this
     */
    public function setCharset(string $charset)
    {
        $this->charset = $charset;
        return $this;
    }


    public static function make($hostname, $user, $password, $dbName, $charset = null)
    {
        return new self($hostname, $user, $password, $dbName, $charset);

    }

    public function __construct($hostname, $user, $password, $dbName, $charset = null)
    {
        $this->setHost($hostname)->setUser($user)->setPassword($password)->setDbName($dbName);
        if ($charset) {
            $this->setCharset($charset);
        }
    }

}