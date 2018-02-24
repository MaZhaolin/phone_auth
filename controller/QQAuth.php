<?php
global $_G;
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(dirname(__FILE__)) . '/lib/function.php';
require_once dirname(dirname(__FILE__)) . '/lib/session.class.php';

class QQAuth {
    private $id;
    private $key;

    public function __construct() {
        $this->id = '101393385';//'310322341';
        $this->key = 'deca6c4fbcc419704372a9054eb654a1';//'ABJxV2vXOcAx2aKc';
    }

    public function auth() {
        $state = uniqid();
        $redirectUri = 'http://www.cc8.cc/connect.php';
        $data = array(
            'response_type' => 'code',
            'client_id' => $this->id,
            'redirect_uri' => $redirectUri,
            'state' => ''
        );
        $query = http_build_query($data);
        redirect("https://graph.qq.com/oauth2.0/authorize?$query");
    }
    
    public function login() {
        $redirectUri = 'http://www.vaptcha.com/plugin.php?id=phone_auth&control=QQAuth&action=login';
        $mobile = $_REQUEST['mobile'] == 'yes' ; 
        $data = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->id,
            'client_secret' => $this->key,
            'code' => $_REQUEST['code'],
            'redirect_uri' => $redirectUri
        );
        $query = http_build_query($data);
        // $res = readContentFormGet("https://graph.qq.com/oauth2.0/token?$query");
        // $res = 'access_token=8A11EEBD1E005383C4C5D95D9112E8A7&expires_in=7776000&refresh_token=14A9DB939DDBFD967355A5666C464AD5';
        $res = 'access_token=61D9F6B91548410750188B3B212FEBA1&expires_in=7776000&refresh_token=E65924F6C542BDCB0F46678F3A1020BB';
        parse_str($res, $arr);
        $member = $this->getUserInfo($arr['access_token']);
        if(isset($member['uid'])) {
            require_once libfile('function/member');
            var_dump($member);
            setloginstatus($member, 2592000);
            return redirect($mobile ? get_site_url('/forum.php?mobile=yes') : get_site_url('/member.php?mod=register'));
       }
       Session::set('auth_activate', '1', 3600);
       Session::set('auth_type', 'qq', 3600);
       Session::set('auth_user', $member, 3600);
       return redirect($mobile ? get_site_url('/member.php?mod=register&mobile=yes') : get_site_url('/member.php?mod=register'));
        // var_dump(copy($user->figureurl_qq_2, $this->get_avatar(2, 'small')));
    }

    public function get_avatar($uid, $size = 'middle') {
        $size = in_array($size, array('big', 'middle', 'small')) ? $size : 'middle';
        $uid = abs(intval($uid)); //UID取整数绝对值
        $uid = sprintf("%09d", $uid); //前边加0补齐9位，例如UID为31的用户变成 000000031
        $dir1 = substr($uid, 0, 3);  //取左边3位，即 000
        $dir2 = substr($uid, 3, 2);  //取4-5位，即00
        $dir3 = substr($uid, 5, 2);  //取6-7位，即00
        $path = DISCUZ_ROOT.'uc_server/data/avatar/'.$dir1.'/'.$dir2.'/'.$dir3;
        mkdir($path, 0777,  true);
        return $path.'/'.substr($uid, -2)."_avatar_$size.jpg";
    }

    private function getUserInfo($accessToken) {
        $res = readContentFormGet("https://graph.qq.com/oauth2.0/me?access_token=$accessToken");
        $openId = explode('"', $res)[7];
        if(!$openId) {
            die($res);
        }
        $member = C::t('#phone_auth#common_vphone')->findUserByOpenid($openId);
        if(isset($member['uid'])) {
            return $member;
        }
        $data = array(
            'access_token' => $accessToken,
            'oauth_consumer_key' => $this->id,
            'openid' => $openId
        );
        $query = http_build_query($data);
        $user = readContentFormGet("https://graph.qq.com/user/get_user_info?$query");
        return json_decode($user, true);
    }
}