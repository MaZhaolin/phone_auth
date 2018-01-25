<?php 
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

session_start();

class Session {

    public static $pre = 'vaptcha_';
    
    /**
     * set session
     *
     * @param string $key
     * @param any $value
     * @param integer $expire default 10min
     * @return session
     */
    public static function set($key, $value, $expire = 600){
        $data = Session::get($key);
        return $_SESSION[Session::$pre.$key] = array(
            'value'  => $value,
            'create' => time(),
            'expire' => $expire
        );
    }

    /**
     * get session 
     *
     * @param string $key
     * @param any $default
     * @return any
     */
    public static function get($key, $default = null) {
        $data = $_SESSION[Session::$pre.$key];
        $now = time();
        if (!$data){
            return $default;
        } else if($now - $data['create'] > $data['expire']) {
            return $default;
        } else {
            return $data;
        }
    }

     /**
     * get session value
     *
     * @param string $key
     * @param any $default
     * @return any
     */
    public static function getValue($key, $default = null) {
        $data = Session::get($key);
        return $data ? $data['value'] : $default;
    }
    
    /**
     * get session live time
     *
     * @param string $key
     * @return int
     */
    public static function getLiveTime($key) {
        $data = $_SESSION[Session::$pre.$key];
        if (!$data){
            return 0;
        } else {
            return time() - $data['create'];
        }
    }

    /**
     * get session valid time
     *
     * @param string $key
     * @return int
     */
    public static function getValidTime($key) {
        $data = $_SESSION[Session::$pre.$key];
        if (!$data){
            return 0;
        } else {
            return $data['expire'] - Session::getLiveTime($key);
        }
    }
    
    /**
     * refrsh expire time
     *
     * @param string $key
     * @return int
     */
    public static function refresh($key) {
        $data = $_SESSION[Session::$pre.$key];
        if($data) {
            return $_SESSION[Session::$pre.$key]['create'] = time();
        }
        return false;
    }

    public static function delete($key) {
        unset($_SESSION[Session::$pre.$key]);
    }
}
