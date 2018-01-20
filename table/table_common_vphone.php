<?php
if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class table_common_vphone extends discuz_table
{
	public function __construct() {

		$this->_table = 'common_vphone';
		$this->_pk    = 'uid';

		parent::__construct();
    }
    
    public function save($uid, $phone, $country_code = '86') {
        $data = array (
            "phone" => $phone,
            "uid" => $uid,
            'country_code' => $country_code
        );
        DB::insert($this->_table,$data);
    }
    
	public function fetch_by_phone($phone) {
		return DB::fetch_first("SELECT * FROM %t WHERE phone=%d", array($this->_table, $phone));
    }

    public function fetch_by_uid($uid) {
		return DB::fetch_first("SELECT * FROM %t WHERE uid=%d", array($this->_table, $uid));
    }

    public function update_phone($uid, $phone) {
		DB::query("UPDATE %t SET phone=%s WHERE uid=%d", array($this->_table, $phone, $uid));        
    }
}

?>