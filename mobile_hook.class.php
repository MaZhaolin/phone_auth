<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
loadcache('plugin');

require_once dirname(__FILE__) . '/lib/function.php';
require_once dirname(__FILE__) . '/lib/session.class.php';
class mobileplugin_phone_auth {
    function isbind() {
        global $_G;
        if(!isset($_G['uid']) || empty($_G['uid']) || Session::getValue('isBind', false)) return true;
        $member = C::t('#phone_auth#common_vphone')->fetch_by_uid($_G['uid']);
        if(!isset($member['phone'])) {
            Session::set('isBind', false, 3 * 60 * 60);
            return false;
        }
        Session::set('isBind', true, 3 * 60 * 60);
        return true;
    }

    function common() {
        if(!$this->isbind() && $_GET['id'] != 'phone_auth')
        return redirect(get_site_url("/plugin.php?id=phone_auth&action=mobile&bp=yes#bindphone"));
    }
}

class mobileplugin_phone_auth_member extends mobileplugin_phone_auth{
    public function logging_code() {
        if(CURMODULE != 'logging' || $_GET['action'] == "logout") return;
        if(Session::getValue('bind_phone_user')) $route = '#bindphone';
        redirect(get_site_url('/plugin.php?id=phone_auth&action=mobile'.$route));
        die();
    }

    public function register_code() {
        redirect(get_site_url('/plugin.php?id=phone_auth&action=mobile#register'));
        die();
    }

    public function connect_code() {
        if(CURMODULE == 'connect') {
            showmessage('&#113;&#113;&#21495;&#26410;&#32465;&#23450;&#36134;&#21495;&#35831;&#20808;&#27880;&#20876;', get_site_url('/plugin.php?id=phone_auth&action=mobile#register'));
        }
    }
}

class mobileplugin_phone_auth_forum extends mobileplugin_phone_auth {

    public function post_recode() {
        global $_G;
        if($this->isbind()) return;
        if ($_GET['action'] == 'reply' && $_GET['inajax'] == '1' && $_GET['handlekey'] == 'qreply_'.$_GET['tid'] && $_GET['replysubmit'] == 'yes') {
            showmessage(lang('plugin/phone_auth', 'please_bind_phone'));
		} else if (submitcheck('topicsubmit') || submitcheck('replysubmit') || submitcheck('editsubmit')) {
            showmessage(lang('plugin/phone_auth', 'please_bind_phone'));
        }
    }
}
