<?php 
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}
require_once  dirname(__FILE__).'/config.php';

class Vaptcha
{
    private $vid;
    private $key;
    private $publicKey;
    private $lastCheckdownTime = 0;
    private $isDown = false;

    //宕机模式通过签证
    private static $passedSignatures = array();

    public function __construct($vid, $key)
    {
        $this->vid = $vid;
        $this->key = $key;
    }

    /**
     * 获取流水号
     *
     * @param string $sceneId 场景id
     * @return void
     */
    public function getChallenge($sceneId = "") 
    {
        $url = API_URL.GET_CHALLENGE_URL;
        $now = $this->getCurrentTime();
        $query = "id=$this->vid&scene=$sceneId&time=$now&version=".VERSION.'&sdklang='.SDK_LANG;
        $signature = $this->HMACSHA1($this->key, $query);
        if (!$this->isDown)
        {
            $challenge = self::readContentFormGet("$url?$query&signature=$signature");
            if ($challenge === REQUEST_USED_UP) {
                // $this->lastCheckdownTime = $now;
                // $this->isDown = true;
                self::$passedSignatures = array();
                return $this->getDownTimeCaptcha();
            }
            if (empty($challenge)) {
                if ($this->getIsDwon()) {
                    $this->lastCheckdownTime = $now;
                    $this->isDown = true;
                    self::$passedSignatures = array();
                }
                return $this->getDownTimeCaptcha();
            } 
            return array(
                "vid" =>  $this->vid,
                "challenge" => $challenge
            );
        } else {
        if ($now - $this->lastCheckdownTime > DOWNTIME_CHECK_TIME) {
                $this->lastCheckdownTime = $now;
                $challenge = self::readContentFormGet("$url?$query&signature=$signature");
                if ($challenge && $challenge != REQUEST_USED_UP){
                    $this->isDown = false;
                    self::$passedSignatures = array();
                    return array(
                        "vid" =>  $this->vid,
                        "challenge" => $challenge
                    );
                }
            }
            return $this->getDownTimeCaptcha();
        }
    }

    /**
     * 二次验证
     *
     * @param [string] $challenge 流水号
     * @param [sring] $token 验证信息
     * @param string $sceneId 场景ID 不填则为默认场景
     * @return void
     */
    public function validate($challenge, $token, $sceneId = "")
    {
        if ($this->isDown || !$challenge)
            return $this->downTimeValidate($token);
        else
            return $this->normalValidate($challenge, $token, $sceneId);
    }

    private function getPublicKey()
    {
        return self::readContentFormGet(PUBLIC_KEY_PATH);
    }

    private function getIsDwon()
    {
        return !!self::readContentFormGet(IS_DOWN_PATH) == 'true';
    }

    public function downTime($data)
    {
        if (!$data)
            return json_encode(array("error" => "params error"));
        $datas = explode(',', $data);
        switch($datas[0]) {
            case 'request': 
                return $this->getDownTimeCaptcha();
            case 'getsignature':
                if (count($datas) < 2)
                    return json_encode(array("error" => "params error"));
                else {
                    $time = (int)$datas[1];
                    if ((bool)$time)
                        return $this->getSignature($time);
                    else 
                        return json_encode(array("error" => "params error"));
                }
            case 'check':
                if (count($datas) < 5)
                    return json_encode(array("error" => "params error"));
                else {
                    $time1 = (int)$datas[1];
                    $time2 = (int)$datas[2];
                    $signature = $datas[3];
                    $captcha = $datas[4];
                    if ((bool)$time1 && (bool)$time2)
                        return $this->downTimeCheck($time1, $time2, $signature, $captcha);
                    return json_encode(array("error" => "parms error"));
                }
            default: 
                return json_encode(array("error" => "parms error"));
        }
    }

    private function getCurrentTime() {
        return number_format(floor(microtime(true) * 1000), 0, '', '');
    }

    private function getSignature($time)
    {
        $now = $this->getCurrentTime();
        if (($now - $time) > REQUEST_ABATE_TIME)
            return null;
        $signature = md5($now.$this->key);
        return json_encode(array(
            'time' => $now,
            'signature' => $signature
        ));
    }

    /**
     * 宕机模式验证
     * 
     * @param [int] $time1
     * @param [int] $time2
     * @param [string] $signature
     * @param [string] $captcha
     * @return void
     */
    private function downTimeCheck($time1, $time2, $signature, $captcha)
    {
        $now = $this->getCurrentTime();
        if ($now - $time1 > REQUEST_ABATE_TIME || 
            $signature != md5($time2.$this->key) || 
            $now - $time2 < VALIDATE_WAIT_TIME)
            return json_encode(array("result" => false));
        $trueCaptcha = substr(md5($time1.$this->key), 0, 3);
        if ($trueCaptcha == strtolower($captcha)) 
            return json_encode(array(
                "result" => true,
                'token' => $now.','.md5($now.$this->key.'vaptcha')
            ));
        else 
            return json_encode(array("result" => false));        
    }

    private function normalValidate($challenge, $token, $sceneId)
    {
        if (!$token || !$challenge || $token != md5($this->key.'vaptcha'.$challenge))
            return false;
        $url = API_URL.VALIDATE_URL;
        $now = $this->getCurrentTime();
        $query = "id=$this->vid&scene=$sceneId&token=$token&time=$now&version=".VERSION.'&sdklang='.SDK_LANG;
        $signature = $this->HMACSHA1($this->key, $query);
        $response = self::postValidate($url, "$query&signature=$signature");
        return 'success' == $response;
    }

    private function downTimeValidate($token)
    {
        $strs = explode(',', $token);
        if (count($strs) < 2) 
            return false;
        else {
            $time = (int)$strs[0];
            $signature = $strs[1];
            $now = $this->getCurrentTime();
            if ($now - $time > VALIDATE_PASS_TIME)
                return false;
            else {
                $signatureTrue = md5($time.$this->key.'vaptcha');
                if ($sigantureTrue) {
                    if (in_array($signature, self::$passedSignatures))
                        return false;
                    else {
                        array_push(self::$passedSignatures, $signature);
                        $length = count(self::$passedSignatures);
                        if ($length > MAX_LENGTH)
                            array_splice(self::$passedSignatures, 0, $length - MAX_LENGTH + 1);
                        return true;
                    }
                } else 
                    return true;
            }
        }
    }

    private function getDownTimeCaptcha()
    {
        $time = $this->getCurrentTime();
        $md5 = md5($time.$this->key);
        $captcha = substr($md5, 0, 3);
        $verificationKey = substr($md5,30);
        if (!$this->publicKey)
            $this->publicKey = $this->getPublicKey();
        $url = md5($captcha.$verificationKey.$this->publicKey).PIC_POST_FIX;
        $url = DOWN_TIME_PATH.$url;
        return array(
            "time" => $time,
            "url" => $url
        );
    }

    private static function postValidate($url, $data)
    {
        if (function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);  
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HEADER, false);  
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('ContentType:application/x-www-form-urlencoded'));  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);  
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5*1000);  
            $errno = curl_errno($ch);
            $response = curl_exec($ch);
            curl_close($ch);
            return $errno > 0 ? 'error' : $response;
        } else {
            $opts = array(
                'http' => array(
                    'method' => 'POST',
                    'header'=> "Content-type: application/x-www-form-urlencoded\r\n" . "Content-Length: " . strlen($data) . "\r\n",
                    'content' => $data,
                    'timeout' => 1*1000
                ),
                'content' => $data
            );
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            return $response ? $response : 'error';
        }
        
    }

    public static function readContentFormGet($url)
    {
        if (function_exists('curl_exec')) {
            $ch = curl_init();  
            curl_setopt($ch, CURLOPT_URL, $url); 
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);  
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1*1000);  
            $errno = curl_errno($ch);
            $response = curl_exec($ch);
            curl_close($ch);
            return $errno > 0 ? false : $response;
        } else {
            $opts = array(
                'http' => array(
                    'method' => 'GET',
                    'timeout' => 1*1000
                )
            );
            $context = stream_context_create($opts);
            $response = @file_get_contents($url, false, $context);
            return $response ? $response : false;
        }
    }

    private function HMACSHA1($key, $str)
    {
        $signature = "";  
        if (function_exists('hash_hmac')) {
            $signature = hash_hmac("sha1", $str, $key, true);
        } else {
            $blocksize = 64;  
            $hashfunc = 'sha1';  
            if (strlen($key) > $blocksize) {  
                $key = pack('H*', $hashfunc($key));  
            }  
            $key = str_pad($key, $blocksize, chr(0x00));  
            $ipad = str_repeat(chr(0x36), $blocksize);  
            $opad = str_repeat(chr(0x5c), $blocksize);  
            $signature = pack(  
                    'H*', $hashfunc(  
                            ($key ^ $opad) . pack(  
                                    'H*', $hashfunc(  
                                            ($key ^ $ipad) . $str  
                                    )  
                            )  
                    )  
            );  
        }  
        $signature = str_replace(array('/', '+', '='), '', base64_encode($signature));
        return $signature;  
    }
}