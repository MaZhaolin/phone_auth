<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');

require_once dirname(__FILE__) . '/lib/function.php';
require_once dirname(__FILE__) . '/lib/session.class.php';
require_once template('phone_auth:login');
require_once template('phone_auth:login_simple');
require_once template('phone_auth:register');

// disable vaptcha login & regsiter module
global $_G;
if(array_key_exists('vaptcha', $_G['cache']['plugin'])) {
    $vaptcha_modules = unserialize($_G['cache']['plugin']['vaptcha']['modules']);
    $_G['cache']['plugin']['vaptcha']['modules'] = serialize(array_diff($vaptcha_modules, array('1', '2')));
}

class plugin_phone_auth {
    public function global_login_extra() {
        return login_simple_template();
    }
}


class plugin_phone_auth_member extends plugin_phone_auth{
    public function logging_top() {
        if(CURMODULE != 'logging' || $_GET['action'] == "logout") return;
        return get_theme_style().login_template();
    }

    public function logging_code() { 
        if(CURMODULE != 'logging' || $_GET['action'] == "logout") return;
        if($_GET['lssubmit'] == "yes"){
            showmessage('禁止访问');
        }

        if (submitcheck('loginsubmit', 1, $seccodestatus)) {
            exit('Access Denied');
        }
    }

    public function register_bottom() {
        return get_theme_style().register_template();
    }

    public function register_code() { 
    }
}