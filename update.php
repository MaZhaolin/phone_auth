<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('sessions') . "` (" .
    "`SessionKey` varchar(32) NOT NULL default '', ".
    "`SessionArray` blob NOT NULL, ".
    "`SessionExpTime` int(20) unsigned NOT NULL default '0', ".
    "PRIMARY KEY (`SessionKey`), ".
    "KEY `SessionKey` (`SessionKey`) ".
    ") ENGINE=InnoDB DEFAULT CHARSET=utf8";

runquery($sql);

$query = DB::query("DESCRIBE ".DB::table('common_vphone')." qq_openid");
$temp = DB::fetch($query);
if(!$temp) {
    $sql = 'ALTER TABLE '.DB::table('common_vphone').' ADD COLUMN qq_openid char(32) DEFAULT ""';
    runquery($sql);
    $sql = 'ALTER TABLE '.DB::table('common_vphone').' ADD COLUMN wechat_openid char(32) DEFAULT ""';
    runquery($sql); 
}

$finish = TRUE;