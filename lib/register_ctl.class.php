<?php

class register_ctl {

    var $showregisterform = 1;

    function response($msg, $error_pos = null, $value = array()){
        return Response::json(array(
            'msg' => characet(lang('message', $msg, $value)),
            'error_pos' => $error_pos
        ), 200);
    }

    function responseError($msg, $error_pos = null, $value = array()){
        return Response::json(array(
            'msg' => lang('message', $msg, $value),
            'error_pos' => $error_pos
        ), 401);
    }

    public function register_ctl() {
        global $_G;
        if($_G['setting']['bbclosed']) {
            if(($_GET['action'] != 'activation' && !$_GET['activationauth']) || !$_G['setting']['closedallowactivation'] ) {
                return $this->responseError('register_disable', NULL, array(), array('login' => 1));
            }
        }

        loadcache(array('modreasons', 'stamptypeid', 'fields_required', 'fields_optional', 'fields_register', 'ipctrl'));
        require_once libfile('function/misc');
        require_once libfile('function/profile');
        if(!function_exists('sendmail')) {
            include libfile('function/mail');
        }
        loaducenter();
    }

    function on_register() {
        global $_G;

        // $_GET['username'] = trim($_GET[''.$this->setting['reginput']['username']]);
        // $_GET['password'] = $_GET[''.$this->setting['reginput']['password']];
        // $_GET['password2'] = $_GET[''.$this->setting['reginput']['password2']];
        // $_GET['email'] = $_GET[''.$this->setting['reginput']['email']];

        $_GET['username'] = trim($_GET['username']);
        $_GET['password'] = $_GET['password'];
        $_GET['password2'] = $_GET['password2'];
        $_GET['email'] = $_GET['email'];

        if($_G['uid']) {
            $ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
            $url_forward = dreferer();
            if(strpos($url_forward, $this->setting['regname']) !== false) {
                $url_forward = 'forum.php';
            }
            return $this->response('login_succeed', $url_forward ? $url_forward : './', array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']), array('extrajs' => $ucsynlogin));
        } elseif(!$this->setting['regclosed'] && (!$this->setting['regstatus'] || !$this->setting['ucactivation'])) {
            if($_GET['action'] == 'activation' || $_GET['activationauth']) {
                if(!$this->setting['ucactivation'] && !$this->setting['closedallowactivation']) {
                    return $this->responseError('register_disable_activation');
                }
            } elseif(!$this->setting['regstatus']) {
                if($this->setting['regconnect']) {
                    dheader('location:connect.php?mod=login&op=init&referer=forum.php&statfrom=login_simple');
                }
                return $this->responseError(!$this->setting['regclosemessage'] ? 'register_disable' : str_replace(array("\r", "\n"), '', $this->setting['regclosemessage']));
            }
        }

        $bbrules = & $this->setting['bbrules'];
        $bbrulesforce = & $this->setting['bbrulesforce'];
        $bbrulestxt = & $this->setting['bbrulestxt'];
        $welcomemsg = & $this->setting['welcomemsg'];
        $welcomemsgtitle = & $this->setting['welcomemsgtitle'];
        $welcomemsgtxt = & $this->setting['welcomemsgtxt'];
        $regname = $this->setting['regname'];

        if($this->setting['regverify']) {
            if($this->setting['areaverifywhite']) {
                $location = $whitearea = '';
                $location = trim(convertip($_G['clientip'], "./"));
                if($location) {
                    $whitearea = preg_quote(trim($this->setting['areaverifywhite']), '/');
                    $whitearea = str_replace(array("\\*"), array('.*'), $whitearea);
                    $whitearea = '.*'.$whitearea.'.*';
                    $whitearea = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea).')$/i';
                    if(@preg_match($whitearea, $location)) {
                        $this->setting['regverify'] = 0;
                    }
                }
            }

            if($_G['cache']['ipctrl']['ipverifywhite']) {
                foreach(explode("\n", $_G['cache']['ipctrl']['ipverifywhite']) as $ctrlip) {
                    if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
                        $this->setting['regverify'] = 0;
                        break;
                    }
                }
            }
        }

        $invitestatus = false;
        if($this->setting['regstatus'] == 2) {
            if($this->setting['inviteconfig']['inviteareawhite']) {
                $location = $whitearea = '';
                $location = trim(convertip($_G['clientip'], "./"));
                if($location) {
                    $whitearea = preg_quote(trim($this->setting['inviteconfig']['inviteareawhite']), '/');
                    $whitearea = str_replace(array("\\*"), array('.*'), $whitearea);
                    $whitearea = '.*'.$whitearea.'.*';
                    $whitearea = '/^('.str_replace(array("\r\n", ' '), array('.*|.*', ''), $whitearea).')$/i';
                    if(@preg_match($whitearea, $location)) {
                        $invitestatus = true;
                    }
                }
            }

            if($this->setting['inviteconfig']['inviteipwhite']) {
                foreach(explode("\n", $this->setting['inviteconfig']['inviteipwhite']) as $ctrlip) {
                    if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
                        $invitestatus = true;
                        break;
                    }
                }
            }
        }

        $groupinfo = array();
        if($this->setting['regverify']) {
            $groupinfo['groupid'] = 8;
        } else {
            $groupinfo['groupid'] = $this->setting['newusergroupid'];
        }

        if (function_exists('seccheck')) {
            list($seccodecheck, $secqaacheck) = seccheck('register');
        }
        $seccodecheck= false;
        $fromuid = !empty($_G['cookie']['promotion']) && $this->setting['creditspolicy']['promotion_register'] ? intval($_G['cookie']['promotion']) : 0;
        $username = isset($_GET['username']) ? $_GET['username'] : '';
        $bbrulehash = $bbrules ? substr(md5(FORMHASH), 0, 8) : '';
        $auth = $_GET['auth'];

        if(!$invitestatus) {
            $invite = getinvite();
        }
        $sendurl = $this->setting['sendregisterurl'] ? true : false;
        if($sendurl) {
            if(!empty($_GET['hash'])) {
                $_GET['hash'] = preg_replace("/[^\[A-Za-z0-9_\]%\s+-\/=]/", '', $_GET['hash']);
                $hash = explode("\t", authcode($_GET['hash'], 'DECODE', $_G['config']['security']['authkey']));
                if(is_array($hash) && isemail($hash[0]) && TIMESTAMP - $hash[1] < 259200) {
                    $sendurl = false;
                }
            }
        }
        if(!submitcheck('regsubmit', 0, $seccodecheck, $secqaacheck)) {

            if($_GET['action'] == 'activation') {
                $auth = explode("\t", authcode($auth, 'DECODE'));
                if(FORMHASH != $auth[1]) {
                    return $this->responseError('register_activation_invalid', 'member.php?mod=logging&action=login');
                }
                $username = $auth[0];
                $activationauth = authcode("$auth[0]\t".FORMHASH, 'ENCODE');
                $sendurl = false;
            }

            if(!$sendurl) {

                if($fromuid) {
                    $member = getuserbyuid($fromuid);
                    if(!empty($member)) {
                        $fromuser = dhtmlspecialchars($member['username']);
                    } else {
                        dsetcookie('promotion');
                    }
                }

                if($_GET['action'] == 'activation') {
                    $auth = dhtmlspecialchars($auth);
                }

                if($seccodecheck) {
                    $seccode = random(6, 1);
                }

                $username = dhtmlspecialchars($username);

                $htmls = $settings = array();
                foreach($_G['cache']['fields_register'] as $field) {
                    $fieldid = $field['fieldid'];
                    $html = profile_setting($fieldid, array(), false, false, true);
                    if($html) {
                        $settings[$fieldid] = $_G['cache']['profilesetting'][$fieldid];
                        $htmls[$fieldid] = $html;
                    }
                }

                $navtitle = $this->setting['reglinkname'];

                if($this->extrafile && file_exists($this->extrafile)) {
                    require_once $this->extrafile;
                }
            }
            $bbrulestxt = nl2br("\n$bbrulestxt\n\n");
            $dreferer = dreferer();

            include template($this->template);

        } else {

            $activationauth = array();
            if(isset($_GET['activationauth']) && $_GET['activationauth']) {
                $activationauth = explode("\t", authcode($_GET['activationauth'], 'DECODE'));
                if($activationauth[1] != FORMHASH) {
                    return $this->responseError('register_activation_invalid', 'member.php?mod=logging&action=login');
                }
                $sendurl = false;
            }
            if(!$activationauth && $sendurl) {
                checkemail($_GET['email']);
            }
            if($sendurl) {
                $hashstr = urlencode(authcode("$_GET[email]\t$_G[timestamp]", 'ENCODE', $_G['config']['security']['authkey']));
                $registerurl = "{$_G[siteurl]}member.php?mod=".$this->setting['regname']."&amp;hash={$hashstr}&amp;email={$_GET[email]}";
                $email_register_message = lang('email', 'email_register_message', array(
                    'bbname' => $this->setting['bbname'],
                    'siteurl' => $_G['siteurl'],
                    'url' => $registerurl
                ));
                if(!sendmail("$_GET[email] <$_GET[email]>", lang('email', 'email_register_subject'), $email_register_message)) {
                    runlog('sendmail', "$_GET[email] sendmail failed.");
                }
                return $this->responseError('register_email_send_succeed', dreferer(), array('bbname' => $this->setting['bbname']), array('showdialog' => false, 'msgtype' => 3, 'closetime' => 10));
            }
            $emailstatus = 0;
            if($this->setting['sendregisterurl'] && !$sendurl) {
                $_GET['email'] = strtolower($hash[0]);
                $this->setting['regverify'] = $this->setting['regverify'] == 1 ? 0 : $this->setting['regverify'];
                if(!$this->setting['regverify']) {
                    $groupinfo['groupid'] = $this->setting['newusergroupid'];
                }
                $emailstatus = 1;
            }

            if($this->setting['regstatus'] == 2 && empty($invite) && !$invitestatus) {
                return $this->responseError('not_open_registration_invite');
            }

            if($bbrules && $bbrulehash != $_POST['agreebbrule']) {
                return $this->responseError('register_rules_agree');
            }

            $activation = array();
            if(isset($_GET['activationauth']) && $activationauth && is_array($activationauth)) {
                if($activationauth[1] == FORMHASH && !($activation = uc_get_user($activationauth[0]))) {
                    return $this->responseError('register_activation_invalid', 'member.php?mod=logging&action=login');
                }
            }

            if(!$activation) {
                $usernamelen = dstrlen($username);
                if($usernamelen < 3) {
                    return $this->responseError('profile_username_tooshort', 'username');
                } elseif($usernamelen > 15) {
                    return $this->responseError('profile_username_toolong', 'username');
                }
                if(uc_get_user(addslashes($username)) && !C::t('common_member')->fetch_uid_by_username($username) && !C::t('common_member_archive')->fetch_uid_by_username($username)) {
                    if($_G['inajax']) {
                        return $this->responseError('profile_username_duplicate', 'username');
                    } else {
                        return $this->responseError('register_activation_message', 'member.php?mod=logging&action=login', array('username' => $username));
                    }
                }
                if($this->setting['pwlength']) {
                    if(strlen($_GET['password']) < $this->setting['pwlength']) {
                        return $this->responseError('profile_password_tooshort', 'password', array('pwlength' => $this->setting['pwlength']));
                    }
                }
                if($this->setting['strongpw']) {
                    $strongpw_str = array();
                    if(in_array(1, $this->setting['strongpw']) && !preg_match("/\d+/", $_GET['password'])) {
                        $strongpw_str[] = lang('member/template', 'strongpw_1');
                    }
                    if(in_array(2, $this->setting['strongpw']) && !preg_match("/[a-z]+/", $_GET['password'])) {
                        $strongpw_str[] = lang('member/template', 'strongpw_2');
                    }
                    if(in_array(3, $this->setting['strongpw']) && !preg_match("/[A-Z]+/", $_GET['password'])) {
                        $strongpw_str[] = lang('member/template', 'strongpw_3');
                    }
                    if(in_array(4, $this->setting['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['password'])) {
                        $strongpw_str[] = lang('member/template', 'strongpw_4');
                    }
                    if($strongpw_str) {
                        return $this->responseError(lang('member/template', 'password_weak').implode(',', $strongpw_str));
                    }
                }
                $email = strtolower(trim($_GET['email']));
                if(empty($this->setting['ignorepassword'])) {
                    /* if($_GET['password'] !== $_GET['password2']) {
                        return $this->responseError('profile_passwd_notmatch');
                    } */

                    if(!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
                        return $this->responseError('profile_passwd_illegal');
                    }
                    $password = $_GET['password'];
                } else {
                    $password = md5(random(10));
                }
            }

            $censorexp = '/^('.str_replace(array('\\*', "\r\n", ' '), array('.*', '|', ''), preg_quote(($this->setting['censoruser'] = trim($this->setting['censoruser'])), '/')).')$/i';

            if($this->setting['censoruser'] && @preg_match($censorexp, $username)) {
                return $this->responseError('profile_username_protect', 'username');
            }

            if($this->setting['regverify'] == 2 && !trim($_GET['regmessage'])) {
                return $this->responseError('profile_required_info_invalid');
            }

            if($_G['cache']['ipctrl']['ipregctrl']) {
                foreach(explode("\n", $_G['cache']['ipctrl']['ipregctrl']) as $ctrlip) {
                    if(preg_match("/^(".preg_quote(($ctrlip = trim($ctrlip)), '/').")/", $_G['clientip'])) {
                        $ctrlip = $ctrlip.'%';
                        $this->setting['regctrl'] = $this->setting['ipregctrltime'];
                        break;
                    } else {
                        $ctrlip = $_G['clientip'];
                    }
                }
            } else {
                $ctrlip = $_G['clientip'];
            }

            if($this->setting['regctrl']) {
                if(C::t('common_regip')->count_by_ip_dateline($ctrlip, $_G['timestamp']-$this->setting['regctrl']*3600)) {
                    return $this->responseError('register_ctrl', NULL, array('regctrl' => $this->setting['regctrl']));
                }
            }

            $setregip = null;
            if($this->setting['regfloodctrl']) {
                $regip = C::t('common_regip')->fetch_by_ip_dateline($_G['clientip'], $_G['timestamp']-86400);
                if($regip) {
                    if($regip['count'] >= $this->setting['regfloodctrl']) {
                        return $this->responseError('register_flood_ctrl', NULL, array('regfloodctrl' => $this->setting['regfloodctrl']));
                    } else {
                        $setregip = 1;
                    }
                } else {
                    $setregip = 2;
                }
            }

            $profile = $verifyarr = array();
            foreach($_G['cache']['fields_register'] as $field) {
                if(defined('IN_MOBILE')) {
                    break;
                }
                $field_key = $field['fieldid'];
                $field_val = $_GET[''.$field_key];
                if($field['formtype'] == 'file' && !empty($_FILES[$field_key]) && $_FILES[$field_key]['error'] == 0) {
                    $field_val = true;
                }

                if(!profile_check($field_key, $field_val)) {
                    $showid = !in_array($field['fieldid'], array('birthyear', 'birthmonth')) ? $field['fieldid'] : 'birthday';
                    return $this->responseError($field['title'].lang('message', 'profile_illegal'), '', array(), array(
                        'showid' => 'chk_'.$showid,
                        'extrajs' => $field['title'].lang('message', 'profile_illegal').($field['formtype'] == 'text' ? '<script type="text/javascript">'.
                            '$(\'registerform\').'.$field['fieldid'].'.className = \'px er\';'.
                            '$(\'registerform\').'.$field['fieldid'].'.onblur = function () { if(this.value != \'\') {this.className = \'px\';$(\'chk_'.$showid.'\').innerHTML = \'\';}}'.
                            '</script>' : '')
                    ));
                }
                if($field['needverify']) {
                    $verifyarr[$field_key] = $field_val;
                } else {
                    $profile[$field_key] = $field_val;
                }
            }

            function get_regip() {
                $cip = getenv('HTTP_CLIENT_IP');
                $xip = getenv('HTTP_X_FORWARDED_FOR');
                $rip = getenv('REMOTE_ADDR');
                $srip = $_SERVER['REMOTE_ADDR'];
                if($cip && strcasecmp($cip, 'unknown')) {
                    $onlineip = $cip;
                } elseif($xip && strcasecmp($xip, 'unknown')) {
                    $onlineip = $xip;
                } elseif($rip && strcasecmp($rip, 'unknown')) {
                    $onlineip = $rip;
                } elseif($srip && strcasecmp($srip, 'unknown')) {
                    $onlineip = $srip;
                }
                preg_match("/[\d\.]{7,15}/", $onlineip, $match);
                $onlineip = $match[0] ? $match[0] : 'unknown';
                return $onlineip;
            }

            function add_user($username, $password, $regip = '') {
                $salt = substr(uniqid(rand()), -6);
                $password = md5(md5($password).$salt);
                $uid = DB::insert("ucenter_members",array(
                    'username' => $username,
                    'password' => $password,
                    'email' => '',
                    'regip' => $regip ? $regip : get_regip(), 
                    'regdate' => time(), 
                    'salt' => $salt
                ), true);
                DB::query("INSERT INTO ".DB::table('ucenter_memberfields')." SET uid='$uid'");
                return $uid;
            }

            if(!$activation) {
                if (is_numeric(addslashes($username))) {
                    return $this->responseError(lang('plugin/phone_auth', 'username_not_number'), 'username');            
                }
                $uid = uc_user_register(addslashes($username), $password, $email, $questionid, $answer, $_G['clientip']);
                if ($uid <= -4 && $uid >= -6 && get_params('register_email') == '0') {
                    $uid = add_user(addslashes($username), $password, 0, $questionid, $answer, $_G['clientip']);                        
                } else if($uid <= 0) {
                    if($uid == -1) {
                        return $this->responseError('profile_username_illegal', 'username');
                    } elseif($uid == -2) {
                        return $this->responseError('profile_username_protect', 'username');
                    } elseif($uid == -3) {
                        return $this->responseError('profile_username_duplicate', 'username');
                    } elseif($uid == -4) {
                        return $this->responseError('profile_email_illegal', 'email');
                    } elseif($uid == -5) {
                        return $this->responseError('profile_email_domain_illegal', 'email');
                    } elseif($uid == -6) {
                        return $this->responseError('profile_email_duplicate', 'email');
                    } else {
                        return $this->responseError('undefined_action');
                    }
                }
            } else {
                list($uid, $username, $email) = $activation;
            }
            $_G['username'] = $username;
            if(getuserbyuid($uid, 1)) {
                if(!$activation) {
                    uc_user_delete($uid);
                }
                return $this->responseError('profile_uid_duplicate', '', array('uid' => $uid));
            }

            $password = md5(random(10));
            $secques = $questionid > 0 ? random(8) : '';

            if(isset($_POST['birthmonth']) && isset($_POST['birthday'])) {
                $profile['constellation'] = get_constellation($_POST['birthmonth'], $_POST['birthday']);
            }
            if(isset($_POST['birthyear'])) {
                $profile['zodiac'] = get_zodiac($_POST['birthyear']);
            }

            if($_FILES) {
                $upload = new discuz_upload();

                foreach($_FILES as $key => $file) {
                    $field_key = 'field_'.$key;
                    if(!empty($_G['cache']['fields_register'][$field_key]) && $_G['cache']['fields_register'][$field_key]['formtype'] == 'file') {

                        $upload->init($file, 'profile');
                        $attach = $upload->attach;

                        if(!$upload->error()) {
                            $upload->save();

                            if(!$upload->get_image_info($attach['target'])) {
                                @unlink($attach['target']);
                                continue;
                            }

                            $attach['attachment'] = dhtmlspecialchars(trim($attach['attachment']));
                            if($_G['cache']['fields_register'][$field_key]['needverify']) {
                                $verifyarr[$key] = $attach['attachment'];
                            } else {
                                $profile[$key] = $attach['attachment'];
                            }
                        }
                    }
                }
            }

            if($setregip !== null) {
                if($setregip == 1) {
                    C::t('common_regip')->update_count_by_ip($_G['clientip']);
                } else {
                    C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => 1, 'dateline' => $_G['timestamp']));
                }
            }

            if($invite && $this->setting['inviteconfig']['invitegroupid']) {
                $groupinfo['groupid'] = $this->setting['inviteconfig']['invitegroupid'];
            }

            $init_arr = array('credits' => explode(',', $this->setting['initcredits']), 'profile'=>$profile, 'emailstatus' => $emailstatus);
            
            C::t('common_member')->insert($uid, $username, $password, $email, $_G['clientip'], $groupinfo['groupid'], $init_arr);
            
            $phone = $_REQUEST["phone"];
            C::t("#phone_auth#common_vphone")->save($uid, $phone);
            
            if($emailstatus) {
                updatecreditbyaction('realemail', $uid);
            }
            if($verifyarr) {
                $setverify = array(
                    'uid' => $uid,
                    'username' => $username,
                    'verifytype' => '0',
                    'field' => serialize($verifyarr),
                    'dateline' => TIMESTAMP,
                );
                C::t('common_member_verify_info')->insert($setverify);
                C::t('common_member_verify')->insert(array('uid' => $uid));
            }

            require_once libfile('cache/userstats', 'function');
            build_cache_userstats();

            if($this->extrafile && file_exists($this->extrafile)) {
                require_once $this->extrafile;
            }

            if($this->setting['regctrl'] || $this->setting['regfloodctrl']) {
                C::t('common_regip')->delete_by_dateline($_G['timestamp']-($this->setting['regctrl'] > 72 ? $this->setting['regctrl'] : 72)*3600);
                if($this->setting['regctrl']) {
                    C::t('common_regip')->insert(array('ip' => $_G['clientip'], 'count' => -1, 'dateline' => $_G['timestamp']));
                }
            }

            $regmessage = dhtmlspecialchars($_GET['regmessage']);
            if($this->setting['regverify'] == 2) {
                C::t('common_member_validate')->insert(array(
                    'uid' => $uid,
                    'submitdate' => $_G['timestamp'],
                    'moddate' => 0,
                    'admin' => '',
                    'submittimes' => 1,
                    'status' => 0,
                    'message' => $regmessage,
                    'remark' => '',
                ), false, true);
                manage_addnotify('verifyuser');
            }

            setloginstatus(array(
                'uid' => $uid,
                'username' => $_G['username'],
                'password' => $password,
                'groupid' => $groupinfo['groupid'],
            ), 0);
            include_once libfile('function/stat');
            updatestat('register');

            if($invite['id']) {
                $result = C::t('common_invite')->count_by_uid_fuid($invite['uid'], $uid);
                if(!$result) {
                    C::t('common_invite')->update($invite['id'], array('fuid'=>$uid, 'fusername'=>$_G['username'], 'regdateline' => $_G['timestamp'], 'status' => 2));
                    updatestat('invite');
                } else {
                    $invite = array();
                }
            }
            if($invite['uid']) {
                if($this->setting['inviteconfig']['inviteaddcredit']) {
                    updatemembercount($uid, array($this->setting['inviteconfig']['inviterewardcredit'] => $this->setting['inviteconfig']['inviteaddcredit']));
                }
                if($this->setting['inviteconfig']['invitedaddcredit']) {
                    updatemembercount($invite['uid'], array($this->setting['inviteconfig']['inviterewardcredit'] => $this->setting['inviteconfig']['invitedaddcredit']));
                }
                require_once libfile('function/friend');
                friend_make($invite['uid'], $invite['username'], false);
                notification_add($invite['uid'], 'friend', 'invite_friend', array('actor' => '<a href="home.php?mod=space&uid='.$invite['uid'].'" target="_blank">'.$invite['username'].'</a>'), 1);

                space_merge($invite, 'field_home');
                if(!empty($invite['privacy']['feed']['invite'])) {
                    require_once libfile('function/feed');
                    $tite_data = array('username' => '<a href="home.php?mod=space&uid='.$_G['uid'].'">'.$_G['username'].'</a>');
                    feed_add('friend', 'feed_invite', $tite_data, '', array(), '', array(), array(), '', '', '', 0, 0, '', $invite['uid'], $invite['username']);
                }
                if($invite['appid']) {
                    updatestat('appinvite');
                }
            }

            if($welcomemsg && !empty($welcomemsgtxt)) {
                $welcomemsgtitle = replacesitevar($welcomemsgtitle);
                $welcomemsgtxt = replacesitevar($welcomemsgtxt);
                if($welcomemsg == 1) {
                    $welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
                    notification_add($uid, 'system', $welcomemsgtxt, array('from_id' => 0, 'from_idtype' => 'welcomemsg'), 1);
                } elseif($welcomemsg == 2) {
                    sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);
                } elseif($welcomemsg == 3) {
                    sendmail_cron($email, $welcomemsgtitle, $welcomemsgtxt);
                    $welcomemsgtxt = nl2br(str_replace(':', '&#58;', $welcomemsgtxt));
                    notification_add($uid, 'system', $welcomemsgtxt, array('from_id' => 0, 'from_idtype' => 'welcomemsg'), 1);
                }
            }

            if($fromuid) {
                updatecreditbyaction('promotion_register', $fromuid);
                dsetcookie('promotion', '');
            }
            dsetcookie('loginuser', '');
            dsetcookie('activationauth', '');
            dsetcookie('invite_auth', '');

            $url_forward = dreferer();
            $refreshtime = 3000;
            switch($this->setting['regverify']) {
                case 1:
                    $idstring = random(6);
                    $authstr = $this->setting['regverify'] == 1 ? "$_G[timestamp]\t2\t$idstring" : '';
                    C::t('common_member_field_forum')->update($_G['uid'], array('authstr' => $authstr));
                    $verifyurl = "{$_G[siteurl]}member.php?mod=activate&amp;uid={$_G[uid]}&amp;id=$idstring";
                    $email_verify_message = lang('email', 'email_verify_message', array(
                        'username' => $_G['member']['username'],
                        'bbname' => $this->setting['bbname'],
                        'siteurl' => $_G['siteurl'],
                        'url' => $verifyurl
                    ));
                    if(!sendmail("$username <$email>", lang('email', 'email_verify_subject'), $email_verify_message)) {
                        runlog('sendmail', "$email sendmail failed.");
                    }
                    $message = 'register_email_verify';
                    $locationmessage = 'register_email_verify_location';
                    $refreshtime = 10000;
                    break;
                case 2:
                    $message = 'register_manual_verify';
                    $locationmessage = 'register_manual_verify_location';
                    break;
                default:
                    $message = 'register_succeed';
                    $locationmessage = 'register_succeed_location';
                    break;
            }
            $param = array('bbname' => $this->setting['bbname'], 'username' => $_G['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['uid']);
            if(strpos($url_forward, $this->setting['regname']) !== false || strpos($url_forward, 'buyinvitecode') !== false) {
                $url_forward = 'forum.php';
            }
            $href = str_replace("'", "\'", $url_forward);
            $extra = array(
                'showid' => 'succeedmessage',
                'extrajs' => '<script type="text/javascript">'.
                    'setTimeout("window.location.href =\''.$href.'\';", '.$refreshtime.');'.
                    '$(\'succeedmessage_href\').href = \''.$href.'\';'.
                    '$(\'main_message\').style.display = \'none\';'.
                    '$(\'main_succeed\').style.display = \'\';'.
                    '$(\'succeedlocation\').innerHTML = \''.lang('message', $locationmessage).'\';'.
                '</script>',
                'striptags' => false,
            );
            return $this->response($message, $url_forward, $param, $extra);
        }
    }

}
?>
