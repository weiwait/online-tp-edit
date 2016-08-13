<?php
namespace dao;
use base\DaoBranchDb;
class Shard extends DaoBranchDb
{
    /** 
     * 初始化
     */
    public function __construct() {
        // 指定mysql表名字
        $this->branch_rule = "shard"; //分表分库规则，默认为projekty
        $this->table_name  = "";
        $this->server_name = '';//由分表分库规则托管
        parent::__construct();
    }   
}
