<?php

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class Install {
    public static function createTables() {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('sessions') . "` (" .
        "`SessionKey` varchar(32) NOT NULL default '', ".
        "`SessionArray` blob NOT NULL, ".
        "`SessionExpTime` int(20) unsigned NOT NULL default '0', ".
        "PRIMARY KEY (`SessionKey`), ".
        "KEY `SessionKey` (`SessionKey`) ".
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8";

        runquery($sql);

        $sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('common_vphone') . "` (" .
        "`phone` bigint(20) unsigned NOT NULL," .
        "`uid` mediumint(8) unsigned NOT NULL," .
        "`country_code` char(40) NOT NULL default '86',".
        "PRIMARY KEY (`phone`)," .
        "UNIQUE KEY (`uid`)".
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8";

        runquery($sql);
    }

    public static function migrationData($data) {
        if(isset($data['name']) && strlen($data['name']) != 0) {
            try {
                $members = DB::fetch_all("SELECT * FROM ".DB::table($data['name'])); // if table not exists will throw exception
                for($i = 0; $i < count($members); $i++) {
                    $member = $members[$i];
                    if(!isset($member[$data['phone_key']])) {
                        return '&#23383;&#27573;'.$data['phone_key'].'&#19981;&#23384;&#22312;';                            
                    }
                    if(isset($data['name_key']) && strlen($data['name_key']) != 0) {
                        $key = $data['name_key'];
                        if(!isset($member[$key])) {
                            return '&#23383;&#27573;'.$key.'&#19981;&#23384;&#22312;';                            
                        }
                        $uid = C::t('common_member')->fetch_uid_by_username($member[$key]);
                    } else if (isset($data['id_key']) && strlen($data['id_key']) != 0) {
                        $key = $data['id_key'];
                        if(!isset($member[$key])) {
                            return '&#23383;&#27573;'.$key.'&#19981;&#23384;&#22312;';                            
                        }
                        $uid = $member[$key];
                    } else {
                        return '&#35831;&#22635;&#20889;&#21807;&#19968;&#23545;&#24212;&#23383;&#27573;&#21517;';
                    }
                    C::t('#phone_auth#common_vphone')->save($uid, $member[$data['phone_key']]);
                }
                return 'success';
            } catch (Exception $e) {
                return $e->getMessage();
            }
        } else {
            return '&#35831;&#22635;&#20889;&#34920;&#21517;&#31216;';      
        }
    }
}

Install::createTables();

$static_path = rtrim($_G['siteurl'], '/').'/source/plugin/phone_auth/static';
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = $_POST['table'];
    $res = Install::migrationData($data);
    if($res == 'success' || $_POST['skip'] == '1') {
        $finish = TRUE;
    } else {
        $error_msg = $res;
        include template('phone_auth:install');    
    }
} else {
    include template('phone_auth:install');    
}

