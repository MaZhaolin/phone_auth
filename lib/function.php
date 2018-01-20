<?php 
if(!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

function get_request($name, $default = null){
    return isset($_REQUEST[$name]) ? $_REQUEST[$name] : $default;
}

function get_params($name = null) {
    global $_G;
    if (isset($_G['setting']['phone_auth_setting'])) {
        $params = unserialize($_G['setting']['phone_auth_setting']);
    } else {
        $params = array (
            'vid' => '',
            'key' => '',
            'site_name' => $_G['setting']['bbname'],
            'style_color' => '3c8aff',
            'btn_style' => 'dark',
            'enable_inter' => '0', //open international sms
            'register_email' => '0',
            'register_qq' => '0'
       );
    }
    return $name ? $params[$name] : $params;
} 

function get_theme_style() {
    $color = get_params('style_color');
    return <<<STYLE
    <style>
        .dz-input:focus,.dz-select:focus, .m-dz .dz-input:focus, .m-dz .dz-select:focus{
            border-color: #$color;
        }
        .dz-btn, .m-dz .dz-btn, .m-dz .m-dz-top {
            background-color: #$color;
        }
        .dz-item-group .dz-link{
            color: #$color;
        }
    </style>
STYLE;
}

function get_site_url($url = '')
{
    global $_G;
    return rtrim($_G['siteurl'], '/').$url;
}

function get_static_path($path = '') {
    return get_site_url().'/source/plugin/phone_auth/static'.$path;
}

function redirect($url) {
    header('Location:'.$url);
}