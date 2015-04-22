<?php
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
	//helper通常是函数，那么基本上就用不到自动加载了
	/*if(strpos($className, '_helper')){
		$CI->load->helper($className);
	}*/
	$CI->load->library($className);
}

//加载CI入口文件
include 'index.php';
?>