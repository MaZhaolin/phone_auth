<?php 
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(__FILE__) . '/lib/function.php';
require_once dirname(__FILE__) . '/lib/session.class.php';
require_once libfile('function/member');

global $_G;

$static_path  = get_static_path();
$site_url  = get_site_url();
$vphone = C::t("#phone_auth#common_vphone")->fetch_by_uid($_G['uid']);

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $result = userlogin($_G['username'], $_REQUEST['password'], '', '');
    if($result['member']) {
        $rebind = true;
        $password = $_REQUEST['password'];
    } else {
        $rebind = false;
        $error_msg = '密码错误';
    }
}
$params = get_params();