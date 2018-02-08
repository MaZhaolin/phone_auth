<?php 
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
require_once dirname(__FILE__) . '/lib/function.php';

global $_G;

$params = get_params();
$params['site_url'] = characet($params['site_url'], CHARSET, 'utf-8');
$static_path  = rtrim($_G['siteurl'], '/').'/source/plugin/phone_auth/static';
$site_url = get_site_url();
include template('phone_auth:deposit');
?>
