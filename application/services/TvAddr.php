<?php

namespace services;
use base\Object;
use base\DaoFactory;
use base\Curd;
use base\ServiceFactory;
use dal\Memcached;
use constants\ContentManagerConst;

/*
CREATE TABLE IF NOT EXISTS `tv_addr` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id',
  `channel_id` int(11) NULL COMMENT '频道id',
  `addr` varchar(256) NULL COMMENT '直播地址',
  `name` varchar(32) NULL COMMENT '直播名称',
  `source` varchar(64) NULL COMMENT '地址来源',
  `source_desc` varchar(64) NULL COMMENT '来源说明',
  `comment` varchar(64) NULL COMMENT '备注',
  `isdelete` int(1) NULL COMMENT '是否删除',
  `isactive` int(1) NULL COMMENT '是否启用',
  `err_count` int(11) NULL COMMENT '报错数量',

  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100 ;
*/

class TvAddr extends Curd
{
    public $tableName = "tv_addr";
    public $realDelete = false;
    public $deleteStatusField = "isdelete";
    public $idKey = "id";
    public $tableConfig = array(
        "pk"=>"id",
        "autoIncrement"=>true,
        "fields"=>array(
            "id"=>array(
                "name"=>"id",
                "update"=>false,
            ),
            "channel_id"=>array(
                "name"=>"频道id",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
                "ref"=>array("TvChannel", "id"),
                "cmpType"=>self::FIELD_CMP_TYPE_IN,
            ),
            "addr"=>array(
                "name"=>"直播地址",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
                "cmpType"=>self::FIELD_CMP_TYPE_LIKE,
            ),
            "name"=>array(
                "name"=>"直播名称",
                "type"=>self::FIELD_TYPE_VARCHAR,
                //"require"=>true,
            ),
            "source"=>array(
                "name"=>"地址来源",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
            "source_desc"=>array(
                "name"=>"来源说明",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
            "comment"=>array(
                "name"=>"备注",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),

            /*
            "website_channel"=>array(
                "name"=>"官方网站",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
            "program_addr"=>array(
                "name"=>"节目单地址",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
            "logo_tv"=>array(
                "name"=>"电视台Logo",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
            "logo_channel"=>array(
                "name"=>"频道Logo",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
            "area"=>array(
                "name"=>"覆盖地区",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
            "desc"=>array(
                "name"=>"频道说明",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
            "comment"=>array(
                "name"=>"备注",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "require"=>true,
            ),
*/
            "isdelete"=>array(
                "name"=>"是否删除",
                "type"=>self::FIELD_TYPE_INT,
                "default"=>"0",
            ),
            "isactive"=>array(
                "name"=>"是否激活",
                "type"=>self::FIELD_TYPE_INT,
                "default"=>"1",
            ),
            "err_count"=>array(
                "name"=>"报错数量",
                "type"=>self::FIELD_TYPE_INT,
                "default"=>"0",
            ),
            "heat"=>array(
                "name"=>"热度",
                "type"=>self::FIELD_TYPE_INT,
                "default"=>"0",
            ),
            "recommend"=>array(
                "name"=>"推荐",
                "type"=>self::FIELD_TYPE_INT,
                "default"=>"0",
            ),
            "quality"=>array(
                "name"=>"属性",
                "type"=>self::FIELD_TYPE_VARCHAR,
                "default"=>"",
            ),
        ),
    );

    public function deleteById($id)
    {
        $sql = "delete from tv_addr where id='".$id."' limit 1";
        $this->query($sql);
    }

    public function active($id)
    {
        $detail = $this->getDetailById($id); 
        $sql = "update tv_addr set isactive='1' where id='".$id."' limit 1";
        $this->query($sql);

        ServiceFactory::getService("TvChannel")->checkStatus($detail['channel_id']);
    }

    public function fixTvAddrName($channelId)
    {
        $sql = "select id, quality from tv_addr where channel_id='".$channelId."' and isdelete='0' and isactive='1' order by recommend desc, id desc "; 
        $data = $this->query($sql);
        //$total = count($data);
        $num = 1;
        foreach($data as $item)
        {
            $id = $item['id'];
            $quality = $item['quality']; 
            $name = "Link ".$num."";
            if(!empty($quality))
            {
                $name .= " - ".$quality.""; 
            }

            $sql = "update tv_addr set name='".$name."' where id='".$id."' limit 1";
            $this->query($sql);
            $num++;
        }
    }

    public function getDetailById($id)
    {
        $sql = "select * from tv_addr where id='".$id."' limit 1"; 
        $data = $this->query($sql);
        return $data[0];
    }

    public function getAllChannelId()
    {
        $sql = "select distinct channel_id from tv_addr "; 
        $data = $this->query($sql);
        return $data;
    }

    public function sync()
    {
        $ver = date("YmdHi");
        $sql =  "select tv_addr.id as id, tv_addr.addr as url, tv_channel.name as name from tv_addr, tv_channel where tv_addr.channel_id = tv_channel.id"; 
        $data = $this->query($sql);
        foreach($data as $item)
        {
            $tvAddrId = $item['id'];
            $url = $item['url'];
            $name = $item['name'];
            if(!ServiceFactory::getService("OnlineTv")->checkUrlExists($url))
            {
                ServiceFactory::getService("OnlineTv")->addFromSync($ver, $url, $tvAddrId, $name); 
            }
        }
    }

    public function getActiveStatus($idArray)
    {
        if(empty($idArray))
        {
            return array();
        }
        $sql = "select id , isactive from tv_addr where id in (".implode(",", $idArray).")"; 
        $data = $this->query($sql);
        $ret = array();
        foreach($data as $item)
        {
            $ret[$item['id']] = $item['isactive'];
        }
        return $ret;
    }

    public function getActiveNum($channelId)
    {
        $sql = "select count(1) as num from tv_addr where channel_id='".$channelId."' and isactive='1'"; 
        $data = $this->query($sql);
        return $data[0]['num'];
    }

    public function __construct() {
        $this->table_name  = $this->tableName;
        $this->server_name = 'nemo_app';
        $this->use_cache = false;
        parent::__construct();
    }   
}

