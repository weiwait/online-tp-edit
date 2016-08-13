<?php
namespace dao;
use base\Dao;

class Main extends Dao
{
    public function __construct() {
        // 指定mysql表名字
        $this->table_name  = 'main';
        $this->server_name = 'main';
        parent::__construct();
    }
}
