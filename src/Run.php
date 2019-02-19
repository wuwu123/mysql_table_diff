<?php
/**
 * Created by PhpStorm.
 * User: wujie
 * Date: 2019-01-30
 * Time: 16:31
 */

namespace mysqldiff;


use mysqldiff\db\MysqlConfig;

class Run
{
    private $dbTool;
    private $diffDbTool;

    /**
     * table
     * @var array
     */
    private $table = [
        'old' => [],
        "new" => [],
        "check" => []
    ];

    private $tableSql = [
        'old' => [],
        "new" => []
    ];

    public function __construct(MysqlConfig $config, MysqlConfig $diffConfig)
    {
        $this->dbTool = new DbTool($config);
        $this->diffDbTool = new DbTool($diffConfig);
    }

    /**
     * table 变化
     */
    public function tableDiff()
    {
        $tables = $this->dbTool->getSqlModel()->getTables();
        $diffTables = $this->diffDbTool->getSqlModel()->getTables();
        foreach ($tables as $table) {
            if (!in_array($table, $diffTables)) {
                $this->table['old'][] = $table;
            } else {
                $this->table['check'][] = $table;
            }
        }
        $this->table["new"] = array_diff($this->table['check'], $diffTables);
    }

    /**
     * table 不一样的sql
     */
    public function tableDetailDiff()
    {
        if (!$this->table['check']) {
            return;
        }
        foreach ($this->table['check'] as $table) {
            $oldTable = $this->dbTool->getSqlModel()->getTableSchema($table);
            $newTable = $this->diffDbTool->getSqlModel()->getTableSchema($table);
            $newTableDesc = $this->diffDbTool->getSqlModel()->showTable($table);
            foreach ($newTable as $name => $row) {
                $oldRow = $oldTable[$name] ?? null;
                $match = [];
                preg_match_all("/`{$name}`.*\,/i", $newTableDesc, $match);
                $sql = trim($match[0][0], ",");
                if ($oldRow === null) {
                    $this->tableSql["new"][$table][] = "alter table `{$table}` add " . $sql;
                }
                if (md5(json_encode($oldRow)) == md5(json_encode($row))) {
                    $this->tableSql["new"][$table][] = "alter table `{$table}` CHANGE " . $sql;
                }
            }
        }
    }

    /**
     * 索引变化
     * @return array
     */
    public function tableIndexDiff()
    {
        if (!$this->table['check']) {
            return [];
        }
        $return = [];
        foreach ($this->table['check'] as $table) {
            $oldTable = $this->dbTool->getSqlModel()->getIndexs($table);
            $newTable = $this->diffDbTool->getSqlModel()->getIndexs($table);
            $data = IndexService::tableToIndex($table, $oldTable, $newTable);
            if ($data["del"] || $data["add"]) {
                $return[$table] = $data;
            }
        }
        return $return;
    }

    public static function echof($data)
    {
        echo "<pre>";
        print_r($data);
        echo "</pre>";
    }

    public function exec()
    {
        $this->tableDiff();
        $this->tableDetailDiff();
        $new = $this->table["new"];
        echo "#########新增表############" . PHP_EOL;
        foreach ($new as $table) {
            echo $this->diffDbTool->getSqlModel()->showTable($table) . PHP_EOL;
        }
        $sql = $this->tableSql['new'];
        echo PHP_EOL . PHP_EOL . PHP_EOL . PHP_EOL . "#########修改表############" . PHP_EOL;
        foreach ($sql as $table => $sqlArray) {
            echo PHP_EOL . PHP_EOL . "表" . $table . PHP_EOL;
            foreach ($sqlArray as $sql) {
                echo $sql . PHP_EOL;
            }
        }


        echo PHP_EOL.PHP_EOL.PHP_EOL.PHP_EOL."#########索引变化############" . PHP_EOL;
        $data = $this->tableIndexDiff();

        foreach ($data as $table => $row) {
            echo PHP_EOL . PHP_EOL . "表" . $table . PHP_EOL;
            if ($row['del']){
                echo "删除索引".PHP_EOL;
                foreach ($row["del"] as $sql){
                    echo  $sql.PHP_EOL;
                }
            }
            if ($row['add']){
                echo "添加索引".PHP_EOL;
                foreach ($row["add"] as $sql){
                    echo  $sql.PHP_EOL;
                }
            }
        }


    }

}