<?php

class logging_ctl
{

    public function logging_ctl()
    {
        require_once libfile('function/misc');
        loaducenter();
    }

    public function response($msg, $error_pos = null, $values = null, $status = 401)
    {
        return Response::json(array(
            'msg' => characet(lang('message', $msg, $values)),
            'error_pos' => $error_pos,
        ), $status);
    }

    public function logging_more($questionexist, $secchecklogin2 = 0)
    {
        global $_G;
        if (empty($_GET['lssubmit'])) {
            return;
        }
        $auth = authcode($_GET['username'] . "\t" . $_GET['password'] . "\t" . ($questionexist ? 1 : 0), 'ENCODE', $_G['config']['security']['authkey']);
        $js = '<script type="text/javascript">showWindow(\'login\', \'member.php?mod=logging&action=login&auth=' . rawurlencode($auth) . '&referer=' . rawurlencode(dreferer()) . (!empty($_GET['cookietime']) ? '&cookietime=1' : '') . '\')</script>';
        return $this->response('location_login', '', array('type' => 1), array('extrajs' => $js));
    }

    public function on_login()
    {
        global $_G;
        if ($_G['uid']) {
            $referer = dreferer();
            $ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';
            $param = array('username' => $_G['member']['username'], 'usergroup' => $_G['group']['grouptitle'], 'uid' => $_G['member']['uid']);
            return $this->response('login_succeed', $referer ? $referer : './', $param, array('showdialog' => 1, 'locationtime' => true, 'extrajs' => $ucsynlogin), 200);
        }

        if (function_exists('seccheck')) {
            list($seccodecheck) = seccheck('login');
        }
        // if (!empty($_GET['auth'])) {
        //     $dauth = authcode($_GET['auth'], 'DECODE', $_G['config']['security']['authkey']);
        //     list(, , , $secchecklogin2) = explode("\t", $dauth);
        //     if ($secchecklogin2) {
        //         $seccodecheck = true;
        //     }
        // }
        $seccodecheck = false;
        $seccodestatus = !empty($_GET['lssubmit']) ? false : $seccodecheck;
        $invite = getinvite();

        if (!submitcheck('loginsubmit', 1, $seccodestatus)) {

            $auth = '';
            $username = !empty($_G['cookie']['loginuser']) ? dhtmlspecialchars($_G['cookie']['loginuser']) : '';

            if (!empty($_GET['auth'])) {
                list($username, $password, $questionexist) = explode("\t", authcode($_GET['auth'], 'DECODE', $_G['config']['security']['authkey']));
                $username = dhtmlspecialchars($username);
                $auth = dhtmlspecialchars($_GET['auth']);
            }

            $cookietimecheck = !empty($_G['cookie']['cookietime']) || !empty($_GET['cookietime']) ? 'checked="checked"' : '';

            if ($seccodecheck) {
                $seccode = random(6, 1) + $seccode{0} * 1000000;
            }

            if ($this->extrafile && file_exists($this->extrafile)) {
                require_once $this->extrafile;
            }

            $navtitle = lang('core', 'title_login');
            include template($this->template);

        } else {

            if (!empty($_GET['auth'])) {
                list($_GET['username'], $_GET['password']) = daddslashes(explode("\t", authcode($_GET['auth'], 'DECODE', $_G['config']['security']['authkey'])));
            }

            //phone_auth
            $name = $_GET['user'];
            $member = C::t("#phone_auth#common_vphone")->fetch_by_phone($name);
            if (isset($member['uid'])) {
                $member = getuserbyuid($member['uid']);
                $_GET['username'] = $member['username'];
            } else {
                $_GET['username'] = $name;
            }
            $res = userlogin($_GET['username'], $_GET['password'], '', '');
            if ($res['ucresult']['uid'] == -1) {
                return $this->response('username_nonexistence', 'user');
            }
            if ($res['ucresult']['uid'] == -2) {
                return $this->response('login_password_invalid', 'password');
            }
            $vphone = C::t("#phone_auth#common_vphone")->fetch_by_uid($res['ucresult']['uid']);
            if(!$vphone) {
                Session::set('bind_phone_user', $res['member']);
                return $this->response($res['ucresult']['username'], 'bind_phone');
            }
            // end

            $loginhash = !empty($_GET['loginhash']) && preg_match('/^\w+$/', $_GET['loginhash']) ? $_GET['loginhash'] : '';

            if (!($_G['member_loginperm'] = logincheck($_GET['username']))) {
                return $this->response('login_strike');
            }
            if ($_GET['fastloginfield']) {
                $_GET['loginfield'] = $_GET['fastloginfield'];
            }
            $_G['uid'] = $_G['member']['uid'] = 0;
            $_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
            if (!$_GET['password'] || $_GET['password'] != addslashes($_GET['password'])) {
                return $this->response('profile_passwd_illegal');
            }
            $result = userlogin($_GET['username'], $_GET['password'], $_GET['questionid'], $_GET['answer'], $this->setting['autoidselect'] ? 'auto' : $_GET['loginfield'], $_G['clientip']);
            $uid = $result['ucresult']['uid'];

            if (!empty($_GET['lssubmit']) && ($result['ucresult']['uid'] == -3 || $seccodecheck)) {
                $_GET['username'] = $result['ucresult']['username'];
                $this->logging_more($result['ucresult']['uid'] == -3);
            }

            if ($result['status'] == -1) {
                if (!$this->setting['fastactivation']) {
                    $auth = authcode($result['ucresult']['username'] . "\t" . FORMHASH, 'ENCODE');
                    return $this->response('location_activation', 'member.php?mod=' . $this->setting['regname'] . '&action=activation&auth=' . rawurlencode($auth) . '&referer=' . rawurlencode(dreferer()), array(), array('location' => true));
                } else {
                    $init_arr = explode(',', $this->setting['initcredits']);
                    $groupid = $this->setting['regverify'] ? 8 : $this->setting['newusergroupid'];

                    C::t('common_member')->insert($uid, $result['ucresult']['username'], md5(random(10)), $result['ucresult']['email'], $_G['clientip'], $groupid, $init_arr);
                    $result['member'] = getuserbyuid($uid);
                    $result['status'] = 1;
                }
            }

            if ($result['status'] > 0) {

                if ($this->extrafile && file_exists($this->extrafile)) {
                    require_once $this->extrafile;
                }

                setloginstatus($result['member'], $_GET['cookietime'] ? 2592000 : 0);
                return $this->response('login_succeed', '', array(
                    'username' => $result['ucresult']['username'],
                    'usergroup' => $_G['group']['grouptitle'],
                ), 200);
                checkfollowfeed();
                if ($_G['group']['forcelogin']) {
                    if ($_G['group']['forcelogin'] == 1) {
                        clearcookies();
                        return $this->response('location_login_force_qq');
                    } elseif ($_G['group']['forcelogin'] == 2 && $_GET['loginfield'] != 'email') {
                        clearcookies();
                        return $this->response('location_login_force_mail');
                    }
                }

                if ($_G['member']['lastip'] && $_G['member']['lastvisit']) {
                    dsetcookie('lip', $_G['member']['lastip'] . ',' . $_G['member']['lastvisit']);
                }
                C::t('common_member_status')->update($_G['uid'], array('lastip' => $_G['clientip'], 'port' => $_G['remoteport'], 'lastvisit' => TIMESTAMP, 'lastactivity' => TIMESTAMP));
                $ucsynlogin = $this->setting['allowsynlogin'] ? uc_user_synlogin($_G['uid']) : '';

                $pwold = false;
                if ($this->setting['strongpw'] && !$this->setting['pwdsafety']) {
                    if (in_array(1, $this->setting['strongpw']) && !preg_match("/\d+/", $_GET['password'])) {
                        $pwold = true;
                    }
                    if (in_array(2, $this->setting['strongpw']) && !preg_match("/[a-z]+/", $_GET['password'])) {
                        $pwold = true;
                    }
                    if (in_array(3, $this->setting['strongpw']) && !preg_match("/[A-Z]+/", $_GET['password'])) {
                        $pwold = true;
                    }
                    if (in_array(4, $this->setting['strongpw']) && !preg_match("/[^a-zA-z0-9]+/", $_GET['password'])) {
                        $pwold = true;
                    }
                }

                if ($_G['member']['adminid'] != 1) {
                    if ($this->setting['accountguard']['loginoutofdate'] && $_G['member']['lastvisit'] && TIMESTAMP - $_G['member']['lastvisit'] > 90 * 86400) {
                        C::t('common_member')->update($_G['uid'], array('freeze' => 2));
                        C::t('common_member_validate')->insert(array(
                            'uid' => $_G['uid'],
                            'submitdate' => TIMESTAMP,
                            'moddate' => 0,
                            'admin' => '',
                            'submittimes' => 1,
                            'status' => 0,
                            'message' => '',
                            'remark' => '',
                        ), false, true);
                        manage_addnotify('verifyuser');
                        return $this->response('location_login_outofdate', 'home.php?mod=spacecp&ac=profile&op=password&resend=1', array('type' => 1), array('showdialog' => true, 'striptags' => false, 'locationtime' => true));
                    }

                    if ($this->setting['accountguard']['loginpwcheck'] && $pwold) {
                        $freeze = $pwold;
                        if ($this->setting['accountguard']['loginpwcheck'] == 2 && $freeze) {
                            C::t('common_member')->update($_G['uid'], array('freeze' => 1));
                        }
                    }
                }

                $seccheckrule = &$_G['setting']['seccodedata']['rule']['login'];
                if ($seccheckrule['allow'] == 2) {
                    if ($seccheckrule['nolocal']) {
                        require_once libfile('function/misc');
                        $lastipConvert = process_ipnotice(convertip($_G['member']['lastip']));
                        $nowipConvert = process_ipnotice(convertip($_G['clientip']));
                        if ($lastipConvert != $nowipConvert && stripos($lastipConvert, $nowipConvert) == false && stripos($nowipConvert, $lastipConvert) == false) {
                            $seccodecheck = true;
                        }
                    }
                    if (!$seccodecheck && $seccheckrule['pwsimple'] && $pwold) {
                        $seccodecheck = true;
                    }
                    if (!$seccodecheck && $seccheckrule['outofday'] && $_G['member']['lastvisit'] && TIMESTAMP - $_G['member']['lastvisit'] > $seccheckrule['outofday'] * 86400) {
                        $seccodecheck = true;
                    }
                    if (!$seccodecheck && $_G['member_loginperm'] < 4) {
                        $seccodecheck = true;
                    }
                    if (!$seccodecheck && $seccheckrule['numiptry']) {
                        $seccodecheck = failedipcheck($seccheckrule['numiptry'], $seccheckrule['timeiptry']);
                    }
                    if ($seccodecheck && !$secchecklogin2) {
                        clearcookies();
                        $auth = authcode($_GET['username'] . "\t" . $_GET['password'] . "\t" . ($_GET['questionid'] ? 1 : 0) . "\t1", 'ENCODE', $_G['config']['security']['authkey']);
                        $location = 'member.php?mod=logging&action=login&auth=' . rawurlencode($auth) . '&referer=' . rawurlencode(dreferer()) . (!empty($_GET['cookietime']) ? '&cookietime=1' : '');
                        if (defined('IN_MOBILE')) {
                            return $this->response('login_seccheck2', $location);
                        } else {
                            $js = '<script type="text/javascript">location.href=\'' . $location . '\'</script>';
                            return $this->response('login_seccheck2', '', array('type' => 1), array('extrajs' => $js));
                        }
                    }
                }

                if ($invite['id']) {
                    $result = C::t('common_invite')->count_by_uid_fuid($invite['uid'], $uid);
                    if (!$result) {
                        C::t('common_invite')->update($invite['id'], array('fuid' => $uid, 'fusername' => $_G['username']));
                        updatestat('invite');
                    } else {
                        $invite = array();
                    }
                }
                if ($invite['uid']) {
                    require_once libfile('function/friend');
                    friend_make($invite['uid'], $invite['username'], false);
                    dsetcookie('invite_auth', '');
                    if ($invite['appid']) {
                        updatestat('appinvite');
                    }
                }

                $param = array(
                    'username' => $result['ucresult']['username'],
                    'usergroup' => $_G['group']['grouptitle'],
                    'uid' => $_G['member']['uid'],
                    'groupid' => $_G['groupid'],
                    'syn' => $ucsynlogin ? 1 : 0,
                );

                $extra = array(
                    'showdialog' => true,
                    'locationtime' => true,
                    'extrajs' => $ucsynlogin,
                );

                if (!$freeze || !$this->setting['accountguard']['loginpwcheck']) {
                    $loginmessage = $_G['groupid'] == 8 ? 'login_succeed_inactive_member' : 'login_succeed';
                    $location = $invite || $_G['groupid'] == 8 ? 'home.php?mod=space&do=home' : dreferer();
                } else {
                    $loginmessage = 'login_succeed_password_change';
                    $location = 'home.php?mod=spacecp&ac=profile&op=password';
                    $_GET['lssubmit'] = 0;
                }
                if (empty($_GET['handlekey']) || !empty($_GET['lssubmit'])) {
                    if (defined('IN_MOBILE')) {
                        return $this->response($loginmessage, $location, $param, array('location' => true));
                    } else {
                        if (!empty($_GET['lssubmit'])) {
                            if (!$ucsynlogin) {
                                $extra['location'] = true;
                            }
                            return $this->response($loginmessage, $location, $param, $extra);
                        } else {
                            $href = str_replace("'", "\'", $location);
                            return $this->response('location_login_succeed', $location, array(), 200);
                        }
                    }
                } else {
                    return $this->response($loginmessage, $location, $param, $extra);
                }
            } else {
                $password = preg_replace("/^(.{" . round(strlen($_GET['password']) / 4) . "})(.+?)(.{" . round(strlen($_GET['password']) / 6) . "})$/s", "\\1***\\3", $_GET['password']);
                $errorlog = dhtmlspecialchars(
                    TIMESTAMP . "\t" .
                    ($result['ucresult']['username'] ? $result['ucresult']['username'] : $_GET['username']) . "\t" .
                    $password . "\t" .
                    "Ques #" . intval($_GET['questionid']) . "\t" .
                    $_G['clientip']);
                writelog('illegallog', $errorlog);
                loginfailed($_GET['username']);
                failedip();
                $fmsg = $result['ucresult']['uid'] == '-3' ? (empty($_GET['questionid']) || $answer == '' ? 'login_question_empty' : 'login_question_invalid') : 'login_invalid';
                if ($_G['member_loginperm'] > 1) {
                    return $this->response($fmsg, '', array('loginperm' => $_G['member_loginperm'] - 1));
                } elseif ($_G['member_loginperm'] == -1) {
                    return $this->response('login_password_invalid');
                } else {
                    return $this->response('login_strike');
                }
            }

        }

    }

    public function on_logout()
    {
        global $_G;

        $ucsynlogout = $this->setting['allowsynlogin'] ? uc_user_synlogout() : '';

        if ($_GET['formhash'] != $_G['formhash']) {
            return $this->response('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout, 'referer' => rawurlencode(dreferer())));
        }

        clearcookies();
        $_G['groupid'] = $_G['member']['groupid'] = 7;
        $_G['uid'] = $_G['member']['uid'] = 0;
        $_G['username'] = $_G['member']['username'] = $_G['member']['password'] = '';
        $_G['setting']['styleid'] = $this->setting['styleid'];

        if (defined('IN_MOBILE')) {
            return $this->response('location_logout_succeed_mobile', dreferer(), array('formhash' => FORMHASH, 'referer' => rawurlencode(dreferer())));
        } else {
            return $this->response('logout_succeed', dreferer(), array('formhash' => FORMHASH, 'ucsynlogout' => $ucsynlogout, 'referer' => rawurlencode(dreferer())));
        }
    }

}
