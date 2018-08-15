<?php 
if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
    exit('Access Denied');
}
require_once dirname(__FILE__) . '/lib/function.php';

global $_G;

$params = get_params();
$params['site_url'] = characet($params['site_url'], CHARSET, 'utf-8');
$static_path  = $static_path  = get_static_path();
$site_url = get_site_url();
$plugin = C::t('common_plugin')->fetch_by_identifier('phone_auth');
include template('phone_auth:deposit');
?>
