<?php
global $_G;
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once dirname(dirname(__FILE__)) . '/lib/function.php';

class QQAuth {
    private $id;
    private $key;

    public function __construct() {
        $this->id = '101393385';
        $this->key = 'deca6c4fbcc419704372a9054eb654a1';
    }

    public function auth() {
        $state = uniqid();
        $redirectUri = 'http://www.vaptcha.com/plugin.php?id=phone_auth&control=QQAuth&action=login';
        $data = array(
            'response_type' => 'code',
            'client_id' => $this->id,
            'redirect_uri' => $redirectUri,
            'state' => $state
        );
        $query = http_build_query($data);
        redirect("https://graph.qq.com/oauth2.0/authorize?$query");
    }
    
    public function login() {
        $redirectUri = 'http://www.vaptcha.com/plugin.php?id=phone_auth&control=QQAuth&action=login';
        $data = array(
            'grant_type' => 'authorization_code',
            'client_id' => $this->id,
            'client_secret' => $this->key,
            'code' => $_REQUEST['code'],
            'redirect_uri' => $redirectUri
        );
        $query = http_build_query($data);
        // $res = readContentFormGet("https://graph.qq.com/oauth2.0/token?$query");
        $res = 'access_token=8A11EEBD1E005383C4C5D95D9112E8A7&expires_in=7776000&refresh_token=14A9DB939DDBFD967355A5666C464AD5';
        parse_str($res, $arr);
        var_dump($this->getUserInfo($arr['access_token']));
    }

    private function getUserInfo($accessToken) {
        $openId = readContentFormGet("https://graph.qq.com/oauth2.0/me?access_token=$accessToken");
        return $openId;
        $data = array(
            'access_token' => $accessToken,
            'oauth_consumer_key' => $this->id,
            'openid' => $openId
        );
        return readContentFormGet();
    }
}