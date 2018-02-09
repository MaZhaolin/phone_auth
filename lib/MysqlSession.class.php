<?php
class MysqlSession
{ 
    private $DB_SERVER = ''; // 数据库服务器主机名
    private $DB_NAME = ''; // 数据库名字
    private $DB_USER = ''; // MYSQL 数据库访问用户名
    private $DB_PASS = ''; // MYSQL 数据库访问密码

    private $DB_SELECT_DB = ""; 
    private $SESS_LIFE = 0;
    
    public function MysqlSession()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . DB::table('sessions') . "` (" .
        "`SessionKey` varchar(32) NOT NULL default '', ".
        "`SessionArray` blob NOT NULL, ".
        "`SessionExpTime` int(20) unsigned NOT NULL default '0', ".
        "PRIMARY KEY (`SessionKey`), ".
        "KEY `SessionKey` (`SessionKey`) ".
        ") ENGINE=InnoDB DEFAULT CHARSET=utf8";
        DB::query($sql);
        global $_G;
        $config = $_G['config']['db'][1];
        $this->DB_SERVER = $config['dbhost'];
        $this->DB_NAME = $config['dbname'];
        $this->DB_USER = $config['dbuser'];
        $this->DB_PASS = $config['dbpw'];
        $this->IS_MYSQLI = false;
        //session_write_close();
        $this->SESS_LIFE = get_cfg_var("session.gc_maxlifetime");
        session_module_name('user');
        session_set_save_handler(
            array(&$this, 'sess_open'),
            array(&$this, 'sess_close'),
            array(&$this, 'sess_read'),
            array(&$this, 'sess_write'),
            array(&$this, 'sess_destroy'),
            array(&$this, 'sess_gc')
        );
        session_start();
    }
    public function sess_open($save_path, $session_name)
    { // 打开数据库连接
        if (function_exists('mysql_pconnect')) {
            if (!$this->DB_SELECT_DB = mysql_pconnect($this->DB_SERVER, $this->DB_USER, $this->DB_PASS)) {
                echo "SORRY! MYSQL ERROR : Can't connect to $this->DB_SERVER as $DB_USER";
                echo "MySQL Error: ", mysql_error();
                die;
            }
        
            if (!mysql_select_db($this->DB_NAME, $this->DB_SELECT_DB)) {
                echo "SORRY! MYSQL ERROR : Unable to select database $this->DB_NAME";
                die;
            }
        } else if (function_exists('mysql_connect')) {
            if (!$this->DB_SELECT_DB = mysql_connect($this->DB_SERVER, $this->DB_USER, $this->DB_PASS)) {
                echo "SORRY! MYSQL ERROR : Can't connect to $this->DB_SERVER as $DB_USER";
                echo "MySQL Error: ", mysql_error();
                die;
            }
        
            if (!mysql_select_db($this->DB_NAME, $this->DB_SELECT_DB)) {
                echo "SORRY! MYSQL ERROR : Unable to select database $this->DB_NAME";
                die;
            }
        } else if(function_exists('mysqli_connect')) {
            $this->IS_MYSQLI = true;
            if (!$this->DB_SELECT_DB = mysqli_connect($this->DB_SERVER, $this->DB_USER, $this->DB_PASS, $this->DB_NAME)) {
                echo "SORRY! MYSQL ERROR : Can't connect to $this->DB_SERVER as $DB_USER";
                echo "MySQL Error: ", mysql_error();
                die;
            }
        } else {
            echo" Can't connect to mysql ,please close phone register plugin";
            die();
        }
        return true;
    }
    public function sess_close()
    {
        return true;
    }
    public function sess_read($SessionKey)
    {
        $Query = "SELECT SessionArray FROM " . DB::table('sessions') . " WHERE SessionKey = '" . $SessionKey . "' AND SessionExpTime > " . time();
        // 过期不读取。
        $Result = $this->IS_MYSQLI ? mysqli_query($this->DB_SELECT_DB, $Query) : mysql_query($Query, $this->DB_SELECT_DB);
        if (list($SessionArray) = $this->IS_MYSQLI ? mysqli_fetch_row($Result) : mysql_fetch_row($Result)) {
            return $SessionArray;
        }
        return '';
    }
    public function sess_write($SessionKey, $VArray)
    {
        $SessionExpTime = time() + $this->SESS_LIFE;
        // 更新Session过期时间，Session过期时间 = 最后一次更新时间 + Session的最大使用时长
        $SessionArray = addslashes($VArray);
        $Query = "INSERT INTO " . DB::table('sessions') . " (SessionKey,SessionExpTime,SessionArray) VALUES ('" . $SessionKey . "','" . $SessionExpTime . "','" . $SessionArray . "')";
        $Result = $this->IS_MYSQLI ? mysqli_query($this->DB_SELECT_DB, $Query) : mysql_query($Query, $this->DB_SELECT_DB);
        if (!$Result) {
            $Query = "UPDATE " . DB::table('sessions') . " SET SessionExpTime = '" . $SessionExpTime . "', SessionArray = '" . $SessionArray . "' WHERE SessionKey = '" . $SessionKey."'";
            $Result = $this->IS_MYSQLI ? mysqli_query($this->DB_SELECT_DB, $Query) : mysql_query($Query, $this->DB_SELECT_DB);
        }
        return $Result;
    }
    public function sess_destroy($SessionKey)
    {
        $Query = "DELETE FROM " . DB::table('sessions') . " WHERE SessionKey = '" . $SessionKey . "'";
        $Result = $this->IS_MYSQLI ? mysqli_query($this->DB_SELECT_DB, $Query) : mysql_query($Query, $this->DB_SELECT_DB);
        return $Result;
    }
    public function sess_gc($maxlifetime)
    {
        $Query = "DELETE FROM " . DB::table('sessions') . " WHERE SessionExpTime < " . time();
        $Result = $this->IS_MYSQLI ? mysqli_query($this->DB_SELECT_DB, $Query) : mysql_query($Query, $this->DB_SELECT_DB);
        return $this->IS_MYSQLI ? mysqli_affected_rows($this->DB_SELECT_DB) : mysql_affected_rows($this->DB_SELECT_DB);
    }
}