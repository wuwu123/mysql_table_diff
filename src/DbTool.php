<?php
/**
 * Created by PhpStorm.
 * User: wujie
 * Date: 2019-01-30
 * Time: 16:36
 */

namespace mysqldiff;


use mysqldiff\db\MysqlConfig;
use mysqldiff\db\MysqlDriver;
use mysqldiff\db\Sql;

class DbTool
{
    /**
     * @var MysqlConfig
     */
    private $config;

    /**
     * @var MysqlDriver
     */
    private $driver;

    /**
     * @var Sql
     */
    private $sqlModel;

    /**
     * @return MysqlConfig
     */
    public function getConfig(): MysqlConfig
    {
        return $this->config;
    }

    /**
     * @return MysqlDriver
     */
    public function getDriver(): MysqlDriver
    {
        return $this->driver;
    }

    /**
     * @return Sql
     */
    public function getSqlModel(): Sql
    {
        return $this->sqlModel;
    }

    public function __construct(MysqlConfig $config)
    {
        $this->config = $config;
        $this->driver = MysqlDriver::make($config);
        $this->sqlModel = Sql::make($this->driver);
    }
}