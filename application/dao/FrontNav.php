<?php
namespace dao;
use base\Dao;

class FrontNav extends Dao
{
    public function __construct() {
        // 指定mysql表名字
        $this->table_name  = 'nav_info';
        $this->server_name = 'nemo_app';
        parent::__construct();
    }
}
