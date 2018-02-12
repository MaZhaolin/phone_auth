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

$finish = TRUE;