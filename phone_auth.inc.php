<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');
require_once dirname(__FILE__) . '/lib/response.class.php';
require_once dirname(__FILE__) . '/controller/PhoneAuth.php';

$action = $_GET['action'];
$phone_auth_instance = new PhoneAuth();
if (method_exists($phone_auth_instance, $action)) {
    echo $phone_auth_instance->$action();
} else {
    echo Response::json(array(
        'msg' => 'Not Found',
        'version' => VERSION
    ), 404);
}


