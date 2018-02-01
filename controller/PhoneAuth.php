<?php
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once libfile('function/member');
require_once dirname(dirname(__FILE__)) . '/lib/function.php';
require_once dirname(dirname(__FILE__)) . '/lib/vaptcha.class.php';
require_once dirname(dirname(__FILE__)) . '/lib/session.class.php';
require_once dirname(dirname(__FILE__)) . '/lib/sms.class.php';

loaducenter();

class PhoneAuth {

    private $vaptcha;
    private $sms;

    public function __construct(){
        global $_G;
        $vid = get_params('vid');
        $key = get_params('key');
        $this->vaptcha = new Vaptcha($vid, $key);
        $this->sms = new sms();
    }

    public function response($status = 200, $msg = 'success', $error_pos = null) {
        $msg = lang('plugin/phone_auth', $msg);
        return Response::json(array(
            'msg' => characet($msg),
            'error_pos' => $error_pos
        ), $status);
    }

    public function getChallenge() {
        return $this->vaptcha->getChallenge();
    }

    public function downtime() {
        return $this->vaptcha->downTime($_GET['data']);
    }

    private function validate() {
        $token = $_REQUEST['vaptcha_token'];
        $challenge = $_REQUEST['vaptcha_challenge'];
        return $this->vaptcha->validate($challenge, $token);
    }

    /**
     * @param int $phone
     * @param string $type
     * @return Response
     */
    private function sendCodeMsgs($phone, $token, $type = 'default') {
        if(!preg_match('/^1([0-9]{9})/',$phone) || strlen($phone) != 11){
            return $this->response(401, 'phone_rule_error',  'phone');
        }
        $code = Session::getValue($type.'_verify_code');
        if ($code) {
            $now = time();
            $time =  Session::getValue($type.'_code_send_time');
            if ($time && 60 * 2 > ($now - $time)) {
                //2min not send code return valid time 
                Session::set($type.'_phone', $phone);
                return $this->response(301, 60 * 2 - ($now - $time), 'code');            
            } else {
                //10min not change code 
                $res = $this->sms->sendCode(array(
                    'phone' => $phone,
                    'code' => $code,
                    'token' => $token
                ));
                if ($res == 2001) {
                    Session::set($type.'_code_send_time', $now);
                    Session::set($type.'_phone', $phone);
                }
                return $this->responseCodeMsg($res);
            }
        } else {
            $code = rand(100000, 999999);
        }
        $res = $this->sms->sendCode(array(
            'phone' => $phone,
            'code' => $code,
            'token' => $token
        ));
        if ($res == 2001) {
            Session::set($type.'_verify_code', $code);
            Session::set($type.'_code_send_time', time());
            Session::set($type.'_phone', $phone);
        }
        return $this->responseCodeMsg($res);
    }

    
    // test method
    private function sendCodeMsg($phone, $token, $type = 'default') {
        if(!preg_match('/^1([0-9]{9})/',$phone) || strlen($phone) != 11){
            return $this->response(401, 'phone_rule_error',  'phone');
        }
        $code = Session::getValue($type.'_verify_code');
        if ($code) {
            $now = time();
            $time =  Session::getValue($type.'_code_send_time');
            if ($time && 60 * 2 > ($now - $time)) {
                //2min not send code return valid time 
                return $this->response(301, 60 * 2 - ($now - $time), $code);            
            } else {
                //10min not change code 
                Session::set($type.'_code_send_time', $now);
                Session::set($type.'_phone', $phone);
                return $this->response(200, $code);
            }
        } else {
            $code = rand(100000, 999999);
        }
        
        Session::set($type.'_verify_code', $code);
        Session::set($type.'_code_send_time', time());
        Session::set($type.'_phone', $phone);
        return $this->response(200, $code);
    }

    public function responseCodeMsg($code) {
        switch($code) {
            case 2001: //send success
                return $this->response(200, 'send_success');
            case 2007: 
                return $this->response(401, 'phone_rule_error', 'phone');
            case 2002: // token empty
            case 2010: // token error
            case 2021: // token use limit 3
                return $this->response(401, 'validate_failure', 'vaptcha');
            case 2012: 
                return $this->response(401, 'not_sms');
            case 2018: 
                return $this->response(401, 'send_too_fast');
            default:
                return $this->response(401, 'error code '.$code);                
        }
    }
    
    public function login() {
        global $_G;
        if (!$this->validate()) {
            return $this->response(401, 'validate_failure');
        }
        return $this->response('username_nonexistence', 'user');
        require_once dirname(dirname(__FILE__))."/lib/logging_ctl.class.php";
        $ctl_obj = new logging_ctl();
        $ctl_obj->setting = $_G['setting'];
        $ctl_obj->template = 'member/login';
        return $ctl_obj->on_login();
    }
    
    public function sendCode() {
        $phone = trim($_REQUEST['phone']);
        if(!preg_match('/^1([0-9]{9})/',$phone) || strlen($phone) != 11){
            return $this->response(401, 'phone_rule_error',  'phone');
        }
        $member = C::t("#phone_auth#common_vphone")->fetch_by_phone($phone);
        if (!$member) return $this->response(401, 'phone_not_register', 'phone');
        return $this->sendCodeMsg($phone, $_REQUEST['vaptcha_token']);
    }

    public function verifyCode() {
        $phone = Session::getValue('default_phone');
        $code = Session::getValue('default_verify_code');
        if (!$phone || $phone != trim($_REQUEST['phone'])) {
            return $this->response(401, 'code_is_error', 'code');
        }
        if ($code != trim($_REQUEST['code'])) {
            return $this->response(401, 'code_is_error', 'code');
        }
        Session::set('validate_phone', $phone);
        return $this->response();
    }

    public function resetPassword() {
        $phone = Session::getValue('validate_phone');
        if (!isset($phone)) {
            return $this->response(401, 'Access denied');
        }
        $newPassword = $_REQUEST['new_password'];
        if (strlen($newPassword) >20 || strlen($newPassword) < 6) {
            return $this->response(401, 'password_error');
        }
        if (!function_exists('uc_user_edit')){
		    loaducenter();
        }
        $member = C::t("#phone_auth#common_vphone")->fetch_by_phone($phone);
        $member = getuserbyuid($member['uid']);
        $username = $member['username'];
        $res = uc_user_edit($username, '', $newPassword, '', 1);
        if ($res < 0) {
            return $this->response(401, 'reset_error');
        } else {
            Session::delete('validate_phone');
            Session::delete('default_verify_code');
            Session::delete('default_phone');
            return $this->response(200, 'password_reset_success');
        }
    }

    public function sendRegisterCode() {
        $phone = trim($_REQUEST['phone']);
        if(!preg_match('/^1([0-9]{9})/',$phone)){
            return $this->response(401, 'phone_rule_error', 'phone');
        }
        $member = C::t("#phone_auth#common_vphone")->fetch_by_phone($phone);
        if ($member) return $this->response(401, 'phone_is_register', 'phone');
        return $this->sendCodeMsg($phone, $_REQUEST['vaptcha_token'], $phone);
    }

    public function register() {
        $phone = $_REQUEST['phone'];
        if (!$phone || $phone != Session::getValue($phone.'_phone')) {
            return $this->response(401, 'code_is_error', 'code');
        }
        $code = Session::getValue($phone.'_verify_code');
        if ($code != $_REQUEST['code'] ) {
            return $this->response(401, 'code_is_error', 'code');            
        }
        global $_G;
        require_once dirname(dirname(__FILE__))."/lib/register_ctl.class.php";
        $ctl_obj = new register_ctl();
        $ctl_obj->setting = $_G['setting'];
        $ctl_obj->template = 'member/register';
        return $ctl_obj->on_register();
    }

    public function bindPhoneCode() {
        $phone = $_REQUEST['phone'];        
        $member = Session::getValue('bind_phone_user');
        if (!$member) {
            return $this->response(401, 'Access denied');
        }
        if(!preg_match('/^1([0-9]{9})/',$phone) || strlen($phone) != 11){
            return $this->response(401, 'phone_rule_error',  'phone');
        }
        $vphone_member = C::t("#phone_auth#common_vphone")->fetch_by_phone($phone);
        if ($vphone_member) return $this->response(401, 'phone_is_bind', 'phone');
        return $this->sendCodeMsg($phone, $_REQUEST['vaptcha_token'], 'bind_phone');
    }

    public function bindPhone(){
        $code = $_REQUEST['code'];
        $member = Session::getValue('bind_phone_user');
        if (!$member) {
            return $this->response(401, 'Access denied');
        }
        $phone = Session::getValue('bind_phone_phone');
        if (!$phone || $phone != $_REQUEST['phone']) {
            return $this->response(401, 'code_is_error', 'code');
        }
        $code = Session::getValue('bind_phone_verify_code');
        if ($code != $_REQUEST['code'] ) {
            return $this->response(401, 'code_is_error', 'code');            
        }
        Session::delete('bind_phone_user');
        Session::delete('bind_phone_phone');
        Session::delete('bind_phone_verify_code');
        C::t("#phone_auth#common_vphone")->save($member['uid'], $phone);
        setloginstatus($member, 2592000);
        return $this->response(200, 'success');
    }

    public function modifyPhoneCode() {
        global $_G;
        $phone = trim($_REQUEST['phone']);        
        $result = userlogin($_G['username'], $_REQUEST['password'], '', '');
        if(!isset($result['member'])) {
            return $this->response(401, 'Access denied');
        }
        if(!preg_match('/^1([0-9]{9})/',$phone) || strlen($phone) != 11){
            return $this->response(401, 'phone_rule_error',  'phone');
        }
        $vphone_member = C::t("#phone_auth#common_vphone")->fetch_by_phone($phone);
        if ($vphone_member) return $this->response(401, 'phone_is_bind', 'phone');
        return $this->sendCodeMsg($phone, $_REQUEST['vaptcha_token'], 'modify_phone');
    }

    public function modifyPhone() {
        global $_G;
        $code = $_REQUEST['code'];
        $result = userlogin($_G['username'], $_REQUEST['password'], '', '');
        if(!isset($result['member'])) {
            return $this->response(401, 'Access denied');
        }
        $phone = Session::getValue('modify_phone_phone');
        if(!preg_match('/^1([0-9]{9})/', $phone)){
            return $this->response(401, 'phone_rule_error');
        }
        if (!$phone || $phone != $_REQUEST['phone']) {
            return $this->response(401, 'code_is_error', 'code');
        }
        $code = Session::getValue('modify_phone_verify_code');
        if ($code != $_REQUEST['code'] ) {
            return $this->response(401, 'code_is_error', 'code');            
        }
        Session::delete('modify_phone_phone');
        Session::delete('modify_phone_verify_code');
        C::t("#phone_auth#common_vphone")->fetch_by_uid($_G['uid']) ? 
        C::t("#phone_auth#common_vphone")->update_phone($_G['uid'], $phone) :
        C::t("#phone_auth#common_vphone")->save($_G['uid'], $phone);
        return $this->response(200, 'modify_phone_success');
    }

    public function mobile() {
        include_once (DISCUZ_ROOT . '/source/discuz_version.php');
        include template('phone_auth:mobile');
    }

    public function smsData() {
        global $_G;
        if ($_G['adminid'] != '1') {
            exit('Access Denied');
        }
        $type = $_REQUEST['type']; 
        $page = $_REQUEST['page'];
        if ($type == 'order') {
            return $this->sms->getOrders($page);
        } else {
            return $this->sms->getSendRecord($page);
        }
    }

    public function payCheck() {		
        $token = $_REQUEST['token'];		
        return $this->sms->getOrderState($token);
    }

    public function smsPay() {
        $type = $_REQUEST['type'];
        $amount = $_REQUEST['amount'];
        return $this->sms->getPayUrl($type, $amount);
    }
}