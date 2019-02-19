[小工具地址 点击 ,只是做校验](https://github.com/wuwu123/mysql_table_diff)

### 小工具原由

```
日常开发经常遇到数据库结构变更，但是不能实时记录下来，上线以后会造成测试和线上的数据库机构不一致；

这个小工具的主要解决这个问题；主要是验证，更新后的数据结构是否一致
```



### 使用方法

```php
<?php

include_once __DIR__ . "/../vendor/autoload.php";

//目前线上的数据库
$config = mysqldiff\db\MysqlConfig::make("0", "0", '0', "0");

//开发环境的数据库
$newConfig = mysqldiff\db\MysqlConfig::make("0", "0", '0', "0");


$model = new \mysqldiff\Run($config, $newConfig);
$model->exec();
```





### 执行结果



```sql
#########新增表############




#########修改表############




#########索引变化############


表--
删除索引
ALTER TABLE `--` DROP INDEX `INDEX_TITLE`
添加索引
ALTER TABLE `--` ADD UNIQUE KEY `update_time`(`update_time`)
```



### 自带的数据库,  information_schema 介绍

```
1 记录所有的数据库信息
```



1.  SCHEMATA :  提供了当前mysql实例中所有数据库的信息 , 包含字符编码
2.  TABLES ： 记录数据库包含所有的表信息
3.  COLUMNS： 表中每一列的信息
4.  STATISTICS表：提供了关于表索引的信息。是show index from schemaname.tablename的结果取之此表
5.  USER_PRIVILEGES（用户权限）表：给出了关于全程权限的信息。该信息源自mysql.user授权表。是非标准表