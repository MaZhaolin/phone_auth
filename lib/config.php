<?php 
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

define("VALIDATE_PASS_TIME", 600);
define("REQUEST_ABATE_TIME", 250);
define("VALIDATE_WAIT_TIME", 2);
define("MAX_LENGTH", 50000);
define("PIC_POST_FIX", ".png");
define("PUBLIC_KEY_PATH", "http://down.vaptcha.com/publickey");
define("IS_DOWN_PATH", "http://down.vaptcha.com/isdown");
define("DOWN_TIME_PATH", "downtime/");
define("VERSION", '1.9.1');
define("SDK_LANG", 'php');
define("API_URL", 'http://api.vaptcha.com');
define("GET_CHALLENGE_URL", '/challenge');
define("VALIDATE_URL", '/validate');
define("REQUEST_USED_UP", '0209');
define("DOWNTIME_CHECK_TIME", 185000);






