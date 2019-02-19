<?php
/**
 * Created by PhpStorm.
 * User: wujie
 * Date: 2019-02-01
 * Time: 15:13
 */

namespace mysqldiff;


class IndexService
{

    /**
     * 索引排序
     * @param $table
     * @return array
     */
    public static function tableIndexSort($table)
    {
        if (!is_array($table) || empty($table)) {
            return [];
        }
        $min = [];
        foreach ($table as $row) {
            $min[$row['name']][$row['index_is_primary']] = $row;
        }
        return $min;
    }

    /**
     * 添加索引
     * @param $tableName
     * @param array $min
     * @return array|string
     */
    public static function addIndex($tableName, array $min)
    {
        $returnSql = [];
        foreach ($min as $row) {
            sort($row);
            $index_is_unique = $row[0]['index_is_unique'];
            $index_is_primary = $row[0]['index_is_primary'];
            $name = $row[0]['name'];
            $params = "";
            foreach ($row as $item) {
                $params = $params . "," . "`{$item['column_name']}`";
            }
            $params = trim($params, ",");
            $sql = "";
            if ($index_is_unique == 1 && $index_is_primary == 1) {
                $sql .= "PRIMARY ";
            } elseif ($index_is_unique == 1) {
                $sql .= "UNIQUE ";
            }
            $sql .= "KEY `{$name}`(" . $params . ")";
            $returnSql[] = "ALTER TABLE `{$tableName}` ADD " . $sql;
        }
        return $returnSql;
    }

    /**
     * 删除索引
     * @param $tableName
     * @param array $indexs
     * @return array|string
     */
    public static function delIndex($tableName, array $indexs)
    {
        $returnSql = [];
        foreach ($indexs as $row) {
            // var_dump($row);exit();
            sort($row);
            $index_is_unique = $row[0]['index_is_unique'];
            $index_is_primary = $row[0]['index_is_primary'];
            $name = $row[0]['name'];
            if ($index_is_unique == 1 && $index_is_primary == 1) {
                //主键
                continue;
            }
            $returnSql[] = "ALTER TABLE `{$tableName}` DROP INDEX `{$name}`";
        }
        return $returnSql;
    }

    /**
     * 索引
     * @param $tableName
     * @param array $table
     * @param array $newTable
     * @return array
     */
    public static function tableToIndex($tableName, array $table, array $newTable)
    {
        $oldIndexs = self::tableIndexSort($table);
        $newIndexs = self::tableIndexSort($newTable);
        $addArray = [];
        $delArray = [];
        if (empty($oldIndexs)) {
            $addArray = $newIndexs;
        } else {
            $lastNewIndex = $newIndexs;
            foreach ($oldIndexs as $key => $row) {
                $newRow = $newIndexs[$key] ?? [];
                if (empty($newRow)) {
                    $delArray[] = $row;
                    continue;
                }
                if (md5(json_encode($row)) != md5(json_encode($newRow))) {
                    $delArray[] = $row;
                    $addArray[] = $newRow;
                }
                unset($lastNewIndex[$key]);
            }
            $addArray = array_merge($addArray, $lastNewIndex);
        }
        return ["del" => self::delIndex($tableName, $delArray), "add" => self::addIndex($tableName, $addArray)];
    }

}