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

    public function unbind() {
        $phone = $_POST['phone'];
        $res = C::t('#phone_auth#common_vphone')->unbind($phone);
        return Response::success();
    }

    public function export() {
        $data = C::t('#phone_auth#common_vphone')->all();
        $this->export_csv($data,array('手机号', '用户id', '国别码', '用户名','邮箱', '注册时间'), '用户数据.csv');
    }

    public function import(){
        $data = $this->read_csv_lines($_FILES["file"]["tmp_name"]);
        $result['success'] = 0;
        $result['faild'] = 0;
        foreach($data as $member) {
            try {
                if(isset($member[0]) && isset($member[1])  && isset($member[2]) ) {
                    C::t('#phone_auth#common_vphone')->save($member[1], $member[0], $member[2]);
                    $result['success'] ++;
                }
            } catch(Exception $e) {
                $result['faild'] ++;
            }
        }
        return Response::success($result);
    }

    private function export_csv($data = array(), $header_data = array(), $file_name = '')
    {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename='.$file_name);
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output', 'a');
        if (!empty($header_data)) {
            foreach ($header_data as $key => $value) {
                $header_data[$key] = iconv('utf-8', 'gbk', $value);
            }
            fputcsv($fp, $header_data);
        }
        $num = 0;
        //每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $limit = 100000;
        //逐行取出数据，不浪费内存
        $count = count($data);
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $num++;
                //刷新一下输出buffer，防止由于数据过多造成问题
                if ($limit == $num) {
                    ob_flush();
                    flush();
                    $num = 0;
                }
                $row = $data[$i];
                foreach ($row as $key => $value) {
                    $row[$key] = iconv('utf-8', 'gbk', $value);
                }
                fputcsv($fp, $row);
            }
        }
        fclose($fp);
    }

    private function read_csv_lines($csv_file = '', $lines = 0, $offset = 0)
    {
        if (!$fp = fopen($csv_file, 'r')) {
            return false;
        }
        $i = $j = 0;
        while (false !== ($line = fgets($fp))) {
            if ($i++ < $offset) {
                continue;
            }
            break;
        }
        $data = array();
        while (!feof($fp)) {
            $d = fgetcsv($fp);
            $d[3] = iconv('utf-8', 'gbk', $d[3]);
            $data[] = $d;
        }
        fclose($fp);
        return $data;
    }

}