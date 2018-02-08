<?php
global $_G;
if(!defined('IN_DISCUZ') || $_G['adminid'] != '1') {
    exit('Access Denied');
}
require_once dirname(dirname(__FILE__)) . '/lib/function.php';
require_once dirname(dirname(__FILE__)) . '/lib/response.class.php';

class Members {
    public function find() {
        $key = get_request('key', 'phone');
        $value = get_request('value');
        $page = intval(get_request('page', 1));
        if (!in_array($key, array('username', 'phone', 'uid'))){
            return Response::notFound();
        }
        $members = $key == 'username' ? 
        C::t('#phone_auth#common_vphone')->findUserByUsername($value, $page) :
        C::t('#phone_auth#common_vphone')->findUser($key, $value, $page);
        return Response::success($members);
    }

}