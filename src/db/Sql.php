<?php
/**
 * Created by PhpStorm.
 * User: wujie
 * Date: 2019-01-30
 * Time: 15:33
 */

namespace mysqldiff\db;


class Sql
{
    public $sql;

    private $driver;

    public static function make(MysqlDriver $driver)
    {
        return new self($driver);
    }

    public function __construct(MysqlDriver $driver)
    {
        $this->driver = $driver;
    }

    /**
     * 查询所有的表
     * @return array
     */
    public function getTables()
    {
        $this->sql = "show tables";
        $data = $this->exec();
        $tables = array_map(function ($row) {
            return $row[0];
        }, $data);
        return $tables;
    }

    /**
     * 不同的字段
     * @return string
     */
    private function getDiffRow()
    {
        return 'COLUMN_NAME , ORDINAL_POSITION , COLUMN_DEFAULT , IS_NULLABLE , DATA_TYPE , CHARACTER_MAXIMUM_LENGTH , CHARACTER_SET_NAME , COLUMN_COMMENT';
    }

    /**
     * 查询表字段
     * @param $table
     * @return array|null
     */
    public function getTableSchema($table)
    {
        $this->sql = "select " . $this->getDiffRow() . " from information_schema.COLUMNS where table_name = '" . $table . "' and table_schema = '" . $this->driver->getConfig()->getDbName() . "'";
        $data = $this->exec();
        $return = [];
        foreach ($data as $row) {
            $return[$row['COLUMN_NAME']] = $row;
        }
        return $return;
    }


    /**
     * 查询索引
     * @param $table
     * @return array|null
     */
    public function getIndexs($table)
    {
        $this->sql = <<<'SQL'
SELECT
    `s`.`INDEX_NAME` AS `name`,
    `s`.`COLUMN_NAME` AS `column_name`,
    `s`.`NON_UNIQUE` ^ 1 AS `index_is_unique`,
    `s`.`INDEX_NAME` = 'PRIMARY' AS `index_is_primary`,
    `s`.`SEQ_IN_INDEX` AS `swq_in_index`
FROM `information_schema`.`STATISTICS` AS `s`
WHERE `s`.`TABLE_SCHEMA` = COALESCE('%s', DATABASE()) AND `s`.`INDEX_SCHEMA` = `s`.`TABLE_SCHEMA` AND `s`.`TABLE_NAME` = '%s'
ORDER BY `s`.`SEQ_IN_INDEX` ASC
SQL;
        $this->sql = sprintf($this->sql, $this->driver->getConfig()->getDbName(), $table);
        return $this->exec();
    }

    /**
     * 查询索引
     * @param $table
     * @return array|null
     */
    public function getIndex($table)
    {
        $this->sql = "show index from `{$table}`";
        // echo $this->sql;
        return $this->exec();
    }

    /**
     * 显示表结构
     * @param $table
     * @return string
     */
    public function showTable($table)
    {
        $this->sql = "SHOW CREATE TABLE `" . $table . "`";
        return $this->exec()[0][1] ?? "";
    }

    public function exec()
    {
        if (!$this->sql) {
            return null;
        }
        $sth = $this->driver->getConnection()->prepare($this->sql);
        $sth->execute();
        return $sth->fetchAll();
    }

}