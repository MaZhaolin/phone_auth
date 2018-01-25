<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

$sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('common_vphone') . "` (" .
  "`phone` bigint(20) unsigned NOT NULL," .
  "`uid` mediumint(8) unsigned NOT NULL," .
  "`country_code` char(40) NOT NULL default '86',".
  "PRIMARY KEY (`phone`)," .
  "UNIQUE KEY (`uid`)".
") ENGINE=InnoDB DEFAULT CHARSET=utf8";

runquery($sql);

$finish = TRUE;
?>