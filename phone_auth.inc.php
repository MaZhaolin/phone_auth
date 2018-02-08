<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');
require_once dirname(__FILE__) . '/lib/function.php';
require_once dirname(__FILE__) . '/lib/response.class.php';

$action = get_request('action');
$controller = get_request('control', 'PhoneAuth');
$filepath = dirname(__FILE__) . "/controller/$controller.php";

if (file_exists($filepath)) {
    require_once $filepath;
    $instance = new $controller();
    if (method_exists($instance, $action)){
        echo $instance->$action();
        die();
    }
}
echo Response::notFound();


