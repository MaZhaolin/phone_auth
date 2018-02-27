<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');

require_once dirname(__FILE__) . '/lib/function.php';
require_once dirname(__FILE__) . '/lib/session.class.php';
require_once dirname(__FILE__) . '/lib/response.class.php';
require_once template('phone_auth:login');
require_once template('phone_auth:login_simple');
require_once template('phone_auth:register');
require_once template('phone_auth:bind_popup');

// disable vaptcha login & regsiter module
//old version
global $_G;
if(array_key_exists('vaptcha', $_G['cache']['plugin'])) {
    $vaptcha_modules = unserialize($_G['cache']['plugin']['vaptcha']['modules']);
    $_G['cache']['plugin']['vaptcha']['modules'] = serialize(array_diff($vaptcha_modules, array('1', '2')));
}
//new version
if(array_key_exists('vaptcha', $_G['setting'])) {
    $_G['setting']['vaptcha']['enableModules'] = range(3, 11);
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
        if($_GET['action'] == "logout") return Session::delete('isBind');
        if(CURMODULE != 'logging') return;
        if($_GET['lssubmit'] == "yes"){
            exit('Access Denied');
        }

        if (submitcheck('loginsubmit', 1, $seccodestatus)) {
            exit('Access Denied');
        }
    }

    public function register_bottom() {
        return get_theme_style().register_template();
    }

    public function register_code() { 
        if(CURMODULE != 'register') return;
        if (submitcheck('regsubmit')) {
            exit('Access Denied');            
        }
    }

    public function connect_code() {
        include template('phone_auth:connect');        
    }

}

class plugin_phone_auth_forum extends plugin_phone_auth {
    
    function isbind() {
        global $_G;
        if(!isset($_G['uid']) || empty($_G['uid']) || Session::getValue('isBind', false)) return true;
        $member = C::t('#phone_auth#common_vphone')->fetch_by_uid($_G['uid']);
        if(!isset($member['phone'])) {
            Session::set('isBind', false, 24 * 60 * 60 * 7);
            return false;
        }
        Session::set('isBind', true, 24 * 60 * 60 * 7);
        return true;
    }

    function viewthread_fastpost_btn_extra() {
		if(!$this->isbind()) return bind_popup();
	}
	function post_btn_extra() {
		if(!$this->isbind()) return bind_popup();
	}
	
	function forumdisplay_fastpost_btn_extra() {
		if(!$this->isbind()) return bind_popup();
    }
    
    public function post_recode() {
        global $_G;
        if($this->isbind()) return;
        if ($_GET['action'] == 'reply' && $_GET['inajax'] == '1' && $_GET['handlekey'] == 'qreply_'.$_GET['tid'] && $_GET['replysubmit'] == 'yes') {
            showmessage('请先完善手机号');
		} else if (submitcheck('topicsubmit') || submitcheck('replysubmit') || submitcheck('editsubmit')) {
            showmessage('请先完善手机号');
        }
    }
}