<?php
//时间基准设置
date_default_timezone_set('Asia/Shanghai');
//或当前文件的目录
$dirname = dirname(__FILE__);

//指定加载文件的目录
chdir($dirname.'/../');//自动加载方法实现

//自动加载类的方法
function __autoload($className){
	$CI = & get_instance();
	if(strpos($className, '_model')){
		$CI->load->model($className);
	}
	else if (strpos($className, 'CI_') === FALSE AND strpos($className, 'MY_') === FALSE) {
		$CI->load->library($className);
	}
}

//内网根据主机名判断环境
$host = gethostname();
$developers = array('qk-PC');
if(in_array($host, $developers)){
	define('ENVIRONMENT', 'development');
}else{
	define('ENVIRONMENT', 'testing');
}
//外网直接使用production
//define('ENVIRONMENT', 'production');

//加载CI入口文件
include 'index.php';
?>