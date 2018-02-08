<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');

require_once dirname(__FILE__) . '/lib/function.php';
require_once dirname(__FILE__) . '/lib/session.class.php';

class mobileplugin_phone_auth {

}

class mobileplugin_phone_auth_member extends mobileplugin_phone_auth{
    public function logging_code() {
        if(CURMODULE != 'logging' || $_GET['action'] == "logout") return;
        if(Session::getValue('bind_phone_user')) $route = '#bindphone';
        redirect(get_site_url('/plugin.php?id=phone_auth&action=mobile&mobile=no'.$route));
    }

    public function register_code() {
        redirect(get_site_url('/plugin.php?id=phone_auth&action=mobile&mobile=no#register'));
    }
}