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

    public function getUser($member){
        $m = getuserbyuid($member['uid']);
        return array_merge($member, array(
            'username' => characet($m['username']),
            'email' => $m['email'],
            'regdate' => $m['regdate'] * 1000
        ));
    }

    public function findUser($key, $value, $page = 1) {
        $pagecount = 20;
        $table = DB::table($this->_table);
        $start = ($page - 1) * $pagecount;
        $count  = DB::fetch_all("SELECT count(*) FROM $table WHERE $key LIKE '%$value%'");
        $count = intval($count['0']['count(*)']);
        $members  = DB::fetch_all("SELECT * FROM $table  WHERE $key LIKE '%$value%' ORDER BY uid DESC limit $start, $pagecount");
        foreach($members as $key => $member) {
            $members[$key] = $this->getUser($member);
        }
        return array(
            'members' => $members,
            'pageTotal' => ceil($count / $pagecount)
        );
    }

    public function findUserByUsername($value, $page = 1) {
        $pagecount = 20;
        $start = ($page - 1) * $pagecount;
        $table = DB::table('common_member');
        $value = characet($value, CHARSET, 'utf-8');
        $count  = DB::fetch_all("SELECT count(*) FROM $table  WHERE username LIKE '%$value%'");
        $count = intval($count['0']['count(*)']);
        $members  = DB::fetch_all("SELECT * FROM $table  WHERE username LIKE '%$value%' ORDER BY uid DESC limit $start, $pagecount");
        foreach($members as $key => $member) {
            $m = DB::fetch_first("SELECT * FROM %t WHERE uid=%d", array($this->_table, $member['uid']));
            $members[$key] = array_merge($m, array(
                'uid' => $member['uid'],
                'username' => characet($member['username']),
                'email' => $member['email'],
                'regdate' => $member['regdate'] * 1000
            ));
        }
        return array(
            'members' => $members,
            'pageTotal' => ceil($count / $pagecount)
        );
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
		$member = DB::fetch_first("SELECT * FROM %t WHERE phone=%s", array($this->_table, $phone));
        return $this->getUser($member);
    }

    public function fetch_by_uid($uid) {
        $member = DB::fetch_first("SELECT * FROM %t WHERE uid=%d", array($this->_table, $uid));
        return $this->getUser($member);
    }
    

    public function fetch_by_page($page) {
        $start = ($page - 1) * 20;
        $members = DB::fetch_all("SELECT * FROM %t limit $start, 20", array($this->_table));
        foreach($members as $key => $member) {
            $members[$key] = $this->getUser($member);
        }
        return $members;
    }

    public function update_phone($uid, $phone) {
		DB::query("UPDATE %t SET phone=%s WHERE uid=%d", array($this->_table, $phone, $uid));        
    }

    public function fetch_all() {
		return DB::fetch_all("SELECT * FROM %t", array($this->_table));
    }
}

?>