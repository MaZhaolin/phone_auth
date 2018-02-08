<?php
require_once dirname(__FILE__) . '/lib/function.php';

$static_path  = rtrim($_G['siteurl'], '/').'/source/plugin/phone_auth/static';
$site_url = get_site_url();
$members = C::t('#phone_auth#common_vphone')->fetch_all();
include template('phone_auth:user_manage');
?>

